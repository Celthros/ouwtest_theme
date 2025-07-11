<?php

namespace PhpOffice\PhpSpreadsheet\Reader;

use DateTime;
use DateTimeZone;
use PhpOffice\PhpSpreadsheet\Cell\AddressHelper;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\DefinedName;
use PhpOffice\PhpSpreadsheet\Reader\Security\XmlScanner;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx\Namespaces;
use PhpOffice\PhpSpreadsheet\Reader\Xml\PageSettings;
use PhpOffice\PhpSpreadsheet\Reader\Xml\Properties;
use PhpOffice\PhpSpreadsheet\Reader\Xml\Style;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Shared\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use SimpleXMLElement;

/**
 * Reader for SpreadsheetML, the XML schema for Microsoft Office Excel 2003.
 */
class Xml extends BaseReader {
	public const NAMESPACES_SS = 'urn:schemas-microsoft-com:office:spreadsheet';

	/**
	 * Formats.
	 *
	 * @var array
	 */
	protected $styles = [];

	/**
	 * Create a new Excel2003XML Reader instance.
	 */
	public function __construct() {
		parent::__construct();
		$this->securityScanner = XmlScanner::getInstance( $this );
	}

	/** @var string */
	private $fileContents = '';

	public static function xmlMappings(): array {
		return array_merge( Style\Fill::FILL_MAPPINGS, Style\Border::BORDER_MAPPINGS );
	}

	/**
	 * Can the current IReader read the file?
	 */
	public function canRead( string $filename ): bool {
		//    Office                    xmlns:o="urn:schemas-microsoft-com:office:office"
		//    Excel                    xmlns:x="urn:schemas-microsoft-com:office:excel"
		//    XML Spreadsheet            xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
		//    Spreadsheet component    xmlns:c="urn:schemas-microsoft-com:office:component:spreadsheet"
		//    XML schema                 xmlns:s="uuid:BDC6E3F0-6DA3-11d1-A2A3-00AA00C14882"
		//    XML data type            xmlns:dt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882"
		//    MS-persist recordset    xmlns:rs="urn:schemas-microsoft-com:rowset"
		//    Rowset                    xmlns:z="#RowsetSchema"
		//

		$signature = [
			'<?xml version="1.0"',
			'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet',
		];

		// Open file
		$data = (string) file_get_contents( $filename );
		$data = $this->getSecurityScannerOrThrow()->scan( $data );

		$valid = true;
		foreach ( $signature as $match ) {
			// every part of the signature must be present
			if ( strpos( $data, $match ) === false ) {
				$valid = false;

				break;
			}
		}

		$this->fileContents = $data;

		return $valid;
	}

	/**
	 * Check if the file is a valid SimpleXML.
	 *
	 * @param string $filename
	 *
	 * @return false|SimpleXMLElement
	 */
	public function trySimpleXMLLoadString( $filename ) {
		try {
			$xml = simplexml_load_string( $this->getSecurityScannerOrThrow()->scan( $this->fileContents ?: file_get_contents( $filename ) ) );
		} catch ( \Exception $e ) {
			throw new Exception( 'Cannot load invalid XML file: ' . $filename, 0, $e );
		}
		$this->fileContents = '';

		return $xml;
	}

	/**
	 * Reads names of the worksheets from a file, without parsing the whole file to a Spreadsheet object.
	 *
	 * @param string $filename
	 *
	 * @return array
	 */
	public function listWorksheetNames( $filename ) {
		File::assertFile( $filename );
		if ( ! $this->canRead( $filename ) ) {
			throw new Exception( $filename . ' is an Invalid Spreadsheet file.' );
		}

		$worksheetNames = [];

		$xml = $this->trySimpleXMLLoadString( $filename );
		if ( $xml === false ) {
			throw new Exception( "Problem reading {$filename}" );
		}

		$xml_ss = $xml->children( self::NAMESPACES_SS );
		foreach ( $xml_ss->Worksheet as $worksheet ) {
			$worksheet_ss     = self::getAttributes( $worksheet, self::NAMESPACES_SS );
			$worksheetNames[] = (string) $worksheet_ss['Name'];
		}

		return $worksheetNames;
	}

	/**
	 * Return worksheet info (Name, Last Column Letter, Last Column Index, Total Rows, Total Columns).
	 *
	 * @param string $filename
	 *
	 * @return array
	 */
	public function listWorksheetInfo( $filename ) {
		File::assertFile( $filename );
		if ( ! $this->canRead( $filename ) ) {
			throw new Exception( $filename . ' is an Invalid Spreadsheet file.' );
		}

		$worksheetInfo = [];

		$xml = $this->trySimpleXMLLoadString( $filename );
		if ( $xml === false ) {
			throw new Exception( "Problem reading {$filename}" );
		}

		$worksheetID = 1;
		$xml_ss      = $xml->children( self::NAMESPACES_SS );
		foreach ( $xml_ss->Worksheet as $worksheet ) {
			$worksheet_ss = self::getAttributes( $worksheet, self::NAMESPACES_SS );

			$tmpInfo                     = [];
			$tmpInfo['worksheetName']    = '';
			$tmpInfo['lastColumnLetter'] = 'A';
			$tmpInfo['lastColumnIndex']  = 0;
			$tmpInfo['totalRows']        = 0;
			$tmpInfo['totalColumns']     = 0;

			$tmpInfo['worksheetName'] = "Worksheet_{$worksheetID}";
			if ( isset( $worksheet_ss['Name'] ) ) {
				$tmpInfo['worksheetName'] = (string) $worksheet_ss['Name'];
			}

			if ( isset( $worksheet->Table->Row ) ) {
				$rowIndex = 0;

				foreach ( $worksheet->Table->Row as $rowData ) {
					$columnIndex = 0;
					$rowHasData  = false;

					foreach ( $rowData->Cell as $cell ) {
						if ( isset( $cell->Data ) ) {
							$tmpInfo['lastColumnIndex'] = max( $tmpInfo['lastColumnIndex'], $columnIndex );
							$rowHasData                 = true;
						}

						++ $columnIndex;
					}

					++ $rowIndex;

					if ( $rowHasData ) {
						$tmpInfo['totalRows'] = max( $tmpInfo['totalRows'], $rowIndex );
					}
				}
			}

			$tmpInfo['lastColumnLetter'] = Coordinate::stringFromColumnIndex( $tmpInfo['lastColumnIndex'] + 1 );
			$tmpInfo['totalColumns']     = $tmpInfo['lastColumnIndex'] + 1;

			$worksheetInfo[] = $tmpInfo;
			++ $worksheetID;
		}

		return $worksheetInfo;
	}

	/**
	 * Loads Spreadsheet from string.
	 */
	public function loadSpreadsheetFromString( string $contents ): Spreadsheet {
		// Create new Spreadsheet
		$spreadsheet = new Spreadsheet();
		$spreadsheet->removeSheetByIndex( 0 );

		// Load into this instance
		return $this->loadIntoExisting( $contents, $spreadsheet, true );
	}

	/**
	 * Loads Spreadsheet from file.
	 */
	protected function loadSpreadsheetFromFile( string $filename ): Spreadsheet {
		// Create new Spreadsheet
		$spreadsheet = new Spreadsheet();
		$spreadsheet->removeSheetByIndex( 0 );

		// Load into this instance
		return $this->loadIntoExisting( $filename, $spreadsheet );
	}

	/**
	 * Loads from file or contents into Spreadsheet instance.
	 *
	 * @param string $filename file name if useContents is false else file contents
	 */
	public function loadIntoExisting( string $filename, Spreadsheet $spreadsheet, bool $useContents = false ): Spreadsheet {
		if ( $useContents ) {
			$this->fileContents = $filename;
		} else {
			File::assertFile( $filename );
			if ( ! $this->canRead( $filename ) ) {
				throw new Exception( $filename . ' is an Invalid Spreadsheet file.' );
			}
		}

		$xml = $this->trySimpleXMLLoadString( $filename );
		if ( $xml === false ) {
			throw new Exception( "Problem reading {$filename}" );
		}

		$namespaces = $xml->getNamespaces( true );

		( new Properties( $spreadsheet ) )->readProperties( $xml, $namespaces );

		$this->styles = ( new Style() )->parseStyles( $xml, $namespaces );
		if ( isset( $this->styles['Default'] ) ) {
			$spreadsheet->getCellXfCollection()[0]->applyFromArray( $this->styles['Default'] );
		}

		$worksheetID = 0;
		$xml_ss      = $xml->children( self::NAMESPACES_SS );

		/** @var null|SimpleXMLElement $worksheetx */
		foreach ( $xml_ss->Worksheet as $worksheetx ) {
			$worksheet    = $worksheetx ?? new SimpleXMLElement( '<xml></xml>' );
			$worksheet_ss = self::getAttributes( $worksheet, self::NAMESPACES_SS );

			if ( isset( $this->loadSheetsOnly, $worksheet_ss['Name'] ) && ( ! in_array( $worksheet_ss['Name'], /** @scrutinizer ignore-type */ $this->loadSheetsOnly ) ) ) {
				continue;
			}

			// Create new Worksheet
			$spreadsheet->createSheet();
			$spreadsheet->setActiveSheetIndex( $worksheetID );
			$worksheetName = '';
			if ( isset( $worksheet_ss['Name'] ) ) {
				$worksheetName = (string) $worksheet_ss['Name'];
				//    Use false for $updateFormulaCellReferences to prevent adjustment of worksheet references in
				//        formula cells... during the load, all formulae should be correct, and we're simply bringing
				//        the worksheet name in line with the formula, not the reverse
				$spreadsheet->getActiveSheet()->setTitle( $worksheetName, false, false );
			}
			if ( isset( $worksheet_ss['Protected'] ) ) {
				$protection = (string) $worksheet_ss['Protected'] === '1';
				$spreadsheet->getActiveSheet()->getProtection()->setSheet( $protection );
			}

			// locally scoped defined names
			if ( isset( $worksheet->Names[0] ) ) {
				foreach ( $worksheet->Names[0] as $definedName ) {
					$definedName_ss = self::getAttributes( $definedName, self::NAMESPACES_SS );
					$name           = (string) $definedName_ss['Name'];
					$definedValue   = (string) $definedName_ss['RefersTo'];
					$convertedValue = AddressHelper::convertFormulaToA1( $definedValue );
					if ( $convertedValue[0] === '=' ) {
						$convertedValue = substr( $convertedValue, 1 );
					}
					$spreadsheet->addDefinedName( DefinedName::createInstance( $name, $spreadsheet->getActiveSheet(), $convertedValue, true ) );
				}
			}

			$columnID = 'A';
			if ( isset( $worksheet->Table->Column ) ) {
				foreach ( $worksheet->Table->Column as $columnData ) {
					$columnData_ss = self::getAttributes( $columnData, self::NAMESPACES_SS );
					$colspan       = 0;
					if ( isset( $columnData_ss['Span'] ) ) {
						$spanAttr = (string) $columnData_ss['Span'];
						if ( is_numeric( $spanAttr ) ) {
							$colspan = max( 0, (int) $spanAttr );
						}
					}
					if ( isset( $columnData_ss['Index'] ) ) {
						$columnID = Coordinate::stringFromColumnIndex( (int) $columnData_ss['Index'] );
					}
					$columnWidth = null;
					if ( isset( $columnData_ss['Width'] ) ) {
						$columnWidth = $columnData_ss['Width'];
					}
					$columnVisible = null;
					if ( isset( $columnData_ss['Hidden'] ) ) {
						$columnVisible = ( (string) $columnData_ss['Hidden'] ) !== '1';
					}
					while ( $colspan >= 0 ) {
						if ( isset( $columnWidth ) ) {
							$spreadsheet->getActiveSheet()->getColumnDimension( $columnID )->setWidth( $columnWidth / 5.4 );
						}
						if ( isset( $columnVisible ) ) {
							$spreadsheet->getActiveSheet()->getColumnDimension( $columnID )->setVisible( $columnVisible );
						}
						++ $columnID;
						-- $colspan;
					}
				}
			}

			$rowID = 1;
			if ( isset( $worksheet->Table->Row ) ) {
				$additionalMergedCells = 0;
				foreach ( $worksheet->Table->Row as $rowData ) {
					$rowHasData = false;
					$row_ss     = self::getAttributes( $rowData, self::NAMESPACES_SS );
					if ( isset( $row_ss['Index'] ) ) {
						$rowID = (int) $row_ss['Index'];
					}
					if ( isset( $row_ss['Hidden'] ) ) {
						$rowVisible = ( (string) $row_ss['Hidden'] ) !== '1';
						$spreadsheet->getActiveSheet()->getRowDimension( $rowID )->setVisible( $rowVisible );
					}

					$columnID = 'A';
					foreach ( $rowData->Cell as $cell ) {
						$cell_ss = self::getAttributes( $cell, self::NAMESPACES_SS );
						if ( isset( $cell_ss['Index'] ) ) {
							$columnID = Coordinate::stringFromColumnIndex( (int) $cell_ss['Index'] );
						}
						$cellRange = $columnID . $rowID;

						if ( $this->getReadFilter() !== null ) {
							if ( ! $this->getReadFilter()->readCell( $columnID, $rowID, $worksheetName ) ) {
								++ $columnID;

								continue;
							}
						}

						if ( isset( $cell_ss['HRef'] ) ) {
							$spreadsheet->getActiveSheet()->getCell( $cellRange )->getHyperlink()->setUrl( (string) $cell_ss['HRef'] );
						}

						if ( ( isset( $cell_ss['MergeAcross'] ) ) || ( isset( $cell_ss['MergeDown'] ) ) ) {
							$columnTo = $columnID;
							if ( isset( $cell_ss['MergeAcross'] ) ) {
								$additionalMergedCells += (int) $cell_ss['MergeAcross'];
								$columnTo              = Coordinate::stringFromColumnIndex( (int) ( Coordinate::columnIndexFromString( $columnID ) + $cell_ss['MergeAcross'] ) );
							}
							$rowTo = $rowID;
							if ( isset( $cell_ss['MergeDown'] ) ) {
								$rowTo = $rowTo + $cell_ss['MergeDown'];
							}
							$cellRange .= ':' . $columnTo . $rowTo;
							$spreadsheet->getActiveSheet()->mergeCells( $cellRange, Worksheet::MERGE_CELL_CONTENT_HIDE );
						}

						$hasCalculatedValue = false;
						$cellDataFormula    = '';
						if ( isset( $cell_ss['Formula'] ) ) {
							$cellDataFormula    = $cell_ss['Formula'];
							$hasCalculatedValue = true;
						}
						if ( isset( $cell->Data ) ) {
							$cellData    = $cell->Data;
							$cellValue   = (string) $cellData;
							$type        = DataType::TYPE_NULL;
							$cellData_ss = self::getAttributes( $cellData, self::NAMESPACES_SS );
							if ( isset( $cellData_ss['Type'] ) ) {
								$cellDataType = $cellData_ss['Type'];
								switch ( $cellDataType ) {
									/*
									const TYPE_STRING        = 's';
									const TYPE_FORMULA        = 'f';
									const TYPE_NUMERIC        = 'n';
									const TYPE_BOOL            = 'b';
									const TYPE_NULL            = 'null';
									const TYPE_INLINE        = 'inlineStr';
									const TYPE_ERROR        = 'e';
									*/ case 'String':
									$type = DataType::TYPE_STRING;

									break;
									case 'Number':
										$type      = DataType::TYPE_NUMERIC;
										$cellValue = (float) $cellValue;
										if ( floor( $cellValue ) == $cellValue ) {
											$cellValue = (int) $cellValue;
										}

										break;
									case 'Boolean':
										$type      = DataType::TYPE_BOOL;
										$cellValue = ( $cellValue != 0 );

										break;
									case 'DateTime':
										$type      = DataType::TYPE_NUMERIC;
										$dateTime  = new DateTime( $cellValue, new DateTimeZone( 'UTC' ) );
										$cellValue = Date::PHPToExcel( $dateTime );

										break;
									case 'Error':
										$type               = DataType::TYPE_ERROR;
										$hasCalculatedValue = false;

										break;
								}
							}

							if ( $hasCalculatedValue ) {
								$type            = DataType::TYPE_FORMULA;
								$columnNumber    = Coordinate::columnIndexFromString( $columnID );
								$cellDataFormula = AddressHelper::convertFormulaToA1( $cellDataFormula, $rowID, $columnNumber );
							}

							$spreadsheet->getActiveSheet()->getCell( $columnID . $rowID )->setValueExplicit( ( ( $hasCalculatedValue ) ? $cellDataFormula : $cellValue ), $type );
							if ( $hasCalculatedValue ) {
								$spreadsheet->getActiveSheet()->getCell( $columnID . $rowID )->setCalculatedValue( $cellValue );
							}
							$rowHasData = true;
						}

						if ( isset( $cell->Comment ) ) {
							$this->parseCellComment( $cell->Comment, $spreadsheet, $columnID, $rowID );
						}

						if ( isset( $cell_ss['StyleID'] ) ) {
							$style = (string) $cell_ss['StyleID'];
							if ( ( isset( $this->styles[ $style ] ) ) && ( ! empty( $this->styles[ $style ] ) ) ) {
								//if (!$spreadsheet->getActiveSheet()->cellExists($columnID . $rowID)) {
								//    $spreadsheet->getActiveSheet()->getCell($columnID . $rowID)->setValue(null);
								//}
								$spreadsheet->getActiveSheet()->getStyle( $cellRange )->applyFromArray( $this->styles[ $style ] );
							}
						}
						++ $columnID;
						while ( $additionalMergedCells > 0 ) {
							++ $columnID;
							-- $additionalMergedCells;
						}
					}

					if ( $rowHasData ) {
						if ( isset( $row_ss['Height'] ) ) {
							$rowHeight = $row_ss['Height'];
							$spreadsheet->getActiveSheet()->getRowDimension( $rowID )->setRowHeight( (float) $rowHeight );
						}
					}

					++ $rowID;
				}
			}

			$dataValidations = new Xml\DataValidations();
			$dataValidations->loadDataValidations( $worksheet, $spreadsheet );
			$xmlX = $worksheet->children( Namespaces::URN_EXCEL );
			if ( isset( $xmlX->WorksheetOptions ) ) {
				if ( isset( $xmlX->WorksheetOptions->FreezePanes ) ) {
					$freezeRow = $freezeColumn = 1;
					if ( isset( $xmlX->WorksheetOptions->SplitHorizontal ) ) {
						$freezeRow = (int) $xmlX->WorksheetOptions->SplitHorizontal + 1;
					}
					if ( isset( $xmlX->WorksheetOptions->SplitVertical ) ) {
						$freezeColumn = (int) $xmlX->WorksheetOptions->SplitVertical + 1;
					}
					$spreadsheet->getActiveSheet()->freezePane( Coordinate::stringFromColumnIndex( $freezeColumn ) . (string) $freezeRow );
				}
				( new PageSettings( $xmlX ) )->loadPageSettings( $spreadsheet );
				if ( isset( $xmlX->WorksheetOptions->TopRowVisible, $xmlX->WorksheetOptions->LeftColumnVisible ) ) {
					$leftTopRow    = (string) $xmlX->WorksheetOptions->TopRowVisible;
					$leftTopColumn = (string) $xmlX->WorksheetOptions->LeftColumnVisible;
					if ( is_numeric( $leftTopRow ) && is_numeric( $leftTopColumn ) ) {
						$leftTopCoordinate = Coordinate::stringFromColumnIndex( (int) $leftTopColumn + 1 ) . (string) ( $leftTopRow + 1 );
						$spreadsheet->getActiveSheet()->setTopLeftCell( $leftTopCoordinate );
					}
				}
				$rangeCalculated = false;
				if ( isset( $xmlX->WorksheetOptions->Panes->Pane->RangeSelection ) ) {
					if ( 1 === preg_match( '/^R(\d+)C(\d+):R(\d+)C(\d+)$/', (string) $xmlX->WorksheetOptions->Panes->Pane->RangeSelection, $selectionMatches ) ) {
						$selectedCell = Coordinate::stringFromColumnIndex( (int) $selectionMatches[2] ) . $selectionMatches[1] . ':' . Coordinate::stringFromColumnIndex( (int) $selectionMatches[4] ) . $selectionMatches[3];
						$spreadsheet->getActiveSheet()->setSelectedCells( $selectedCell );
						$rangeCalculated = true;
					}
				}
				if ( ! $rangeCalculated ) {
					if ( isset( $xmlX->WorksheetOptions->Panes->Pane->ActiveRow ) ) {
						$activeRow = (string) $xmlX->WorksheetOptions->Panes->Pane->ActiveRow;
					} else {
						$activeRow = 0;
					}
					if ( isset( $xmlX->WorksheetOptions->Panes->Pane->ActiveCol ) ) {
						$activeColumn = (string) $xmlX->WorksheetOptions->Panes->Pane->ActiveCol;
					} else {
						$activeColumn = 0;
					}
					if ( is_numeric( $activeRow ) && is_numeric( $activeColumn ) ) {
						$selectedCell = Coordinate::stringFromColumnIndex( (int) $activeColumn + 1 ) . (string) ( $activeRow + 1 );
						$spreadsheet->getActiveSheet()->setSelectedCells( $selectedCell );
					}
				}
			}
			++ $worksheetID;
		}

		// Globally scoped defined names
		$activeSheetIndex = 0;
		if ( isset( $xml->ExcelWorkbook->ActiveSheet ) ) {
			$activeSheetIndex = (int) (string) $xml->ExcelWorkbook->ActiveSheet;
		}
		$activeWorksheet = $spreadsheet->setActiveSheetIndex( $activeSheetIndex );
		if ( isset( $xml->Names[0] ) ) {
			foreach ( $xml->Names[0] as $definedName ) {
				$definedName_ss = self::getAttributes( $definedName, self::NAMESPACES_SS );
				$name           = (string) $definedName_ss['Name'];
				$definedValue   = (string) $definedName_ss['RefersTo'];
				$convertedValue = AddressHelper::convertFormulaToA1( $definedValue );
				if ( $convertedValue[0] === '=' ) {
					$convertedValue = substr( $convertedValue, 1 );
				}
				$spreadsheet->addDefinedName( DefinedName::createInstance( $name, $activeWorksheet, $convertedValue ) );
			}
		}

		// Return
		return $spreadsheet;
	}

	protected function parseCellComment(
		SimpleXMLElement $comment, Spreadsheet $spreadsheet, string $columnID, int $rowID
	): void {
		$commentAttributes = $comment->attributes( self::NAMESPACES_SS );
		$author            = 'unknown';
		if ( isset( $commentAttributes->Author ) ) {
			$author = (string) $commentAttributes->Author;
		}

		$node       = $comment->Data->asXML();
		$annotation = strip_tags( (string) $node );
		$spreadsheet->getActiveSheet()->getComment( $columnID . $rowID )->setAuthor( $author )->setText( $this->parseRichText( $annotation ) );
	}

	protected function parseRichText( string $annotation ): RichText {
		$value = new RichText();

		$value->createText( $annotation );

		return $value;
	}

	private static function getAttributes( ?SimpleXMLElement $simple, string $node ): SimpleXMLElement {
		return ( $simple === null ) ? new SimpleXMLElement( '<xml></xml>' ) : ( $simple->attributes( $node ) ?? new SimpleXMLElement( '<xml></xml>' ) );
	}
}
