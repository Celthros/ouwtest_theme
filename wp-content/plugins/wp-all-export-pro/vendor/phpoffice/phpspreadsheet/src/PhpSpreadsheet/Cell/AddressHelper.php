<?php

namespace PhpOffice\PhpSpreadsheet\Cell;

use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Calculation\Functions;
use PhpOffice\PhpSpreadsheet\Exception;

class AddressHelper {
	public const R1C1_COORDINATE_REGEX = '/(R((?:\[-?\d*\])|(?:\d*))?)(C((?:\[-?\d*\])|(?:\d*))?)/i';

	/** @return string[] */
	public static function getRowAndColumnChars() {
		$rowChar = 'R';
		$colChar = 'C';
		if ( Functions::getCompatibilityMode() === Functions::COMPATIBILITY_EXCEL ) {
			$rowColChars = Calculation::localeFunc( '*RC' );
			if ( mb_strlen( $rowColChars ) === 2 ) {
				$rowChar = mb_substr( $rowColChars, 0, 1 );
				$colChar = mb_substr( $rowColChars, 1, 1 );
			}
		}

		return [ $rowChar, $colChar ];
	}

	/**
	 * Converts an R1C1 format cell address to an A1 format cell address.
	 */
	public static function convertToA1(
		string $address, int $currentRowNumber = 1, int $currentColumnNumber = 1, bool $useLocale = true
	): string {
		[ $rowChar, $colChar ] = $useLocale ? self::getRowAndColumnChars() : [ 'R', 'C' ];
		$regex         = '/^(' . $rowChar . '(\[?[-+]?\d*\]?))(' . $colChar . '(\[?[-+]?\d*\]?))$/i';
		$validityCheck = preg_match( $regex, $address, $cellReference );

		if ( empty( $validityCheck ) ) {
			throw new Exception( 'Invalid R1C1-format Cell Reference' );
		}

		$rowReference = $cellReference[2];
		//    Empty R reference is the current row
		if ( $rowReference === '' ) {
			$rowReference = (string) $currentRowNumber;
		}
		//    Bracketed R references are relative to the current row
		if ( $rowReference[0] === '[' ) {
			$rowReference = $currentRowNumber + (int) trim( $rowReference, '[]' );
		}
		$columnReference = $cellReference[4];
		//    Empty C reference is the current column
		if ( $columnReference === '' ) {
			$columnReference = (string) $currentColumnNumber;
		}
		//    Bracketed C references are relative to the current column
		if ( is_string( $columnReference ) && $columnReference[0] === '[' ) {
			$columnReference = $currentColumnNumber + (int) trim( $columnReference, '[]' );
		}
		$columnReference = (int) $columnReference;

		if ( $columnReference <= 0 || $rowReference <= 0 ) {
			throw new Exception( 'Invalid R1C1-format Cell Reference, Value out of range' );
		}
		$A1CellReference = Coordinate::stringFromColumnIndex( $columnReference ) . $rowReference;

		return $A1CellReference;
	}

	protected static function convertSpreadsheetMLFormula( string $formula ): string {
		$formula = substr( $formula, 3 );
		$temp    = explode( '"', $formula );
		$key     = false;
		foreach ( $temp as &$value ) {
			//    Only replace in alternate array entries (i.e. non-quoted blocks)
			$key = $key === false;
			if ( $key ) {
				$value = str_replace( [ '[.', ':.', ']' ], [ '', ':', '' ], $value );
			}
		}
		unset( $value );

		return implode( '"', $temp );
	}

	/**
	 * Converts a formula that uses R1C1/SpreadsheetXML format cell address to an A1 format cell address.
	 */
	public static function convertFormulaToA1(
		string $formula, int $currentRowNumber = 1, int $currentColumnNumber = 1
	): string {
		if ( substr( $formula, 0, 3 ) == 'of:' ) {
			// We have an old-style SpreadsheetML Formula
			return self::convertSpreadsheetMLFormula( $formula );
		}

		//    Convert R1C1 style references to A1 style references (but only when not quoted)
		$temp = explode( '"', $formula );
		$key  = false;
		foreach ( $temp as &$value ) {
			//    Only replace in alternate array entries (i.e. non-quoted blocks)
			$key = $key === false;
			if ( $key ) {
				preg_match_all( self::R1C1_COORDINATE_REGEX, $value, $cellReferences, PREG_SET_ORDER + PREG_OFFSET_CAPTURE );
				//    Reverse the matches array, otherwise all our offsets will become incorrect if we modify our way
				//        through the formula from left to right. Reversing means that we work right to left.through
				//        the formula
				$cellReferences = array_reverse( $cellReferences );
				//    Loop through each R1C1 style reference in turn, converting it to its A1 style equivalent,
				//        then modify the formula to use that new reference
				foreach ( $cellReferences as $cellReference ) {
					$A1CellReference = self::convertToA1( $cellReference[0][0], $currentRowNumber, $currentColumnNumber, false );
					$value           = substr_replace( $value, $A1CellReference, $cellReference[0][1], strlen( $cellReference[0][0] ) );
				}
			}
		}
		unset( $value );

		//    Then rebuild the formula string
		return implode( '"', $temp );
	}

	/**
	 * Converts an A1 format cell address to an R1C1 format cell address.
	 * If $currentRowNumber or $currentColumnNumber are provided, then the R1C1 address will be formatted as a relative address.
	 */
	public static function convertToR1C1(
		string $address, ?int $currentRowNumber = null, ?int $currentColumnNumber = null
	): string {
		$validityCheck = preg_match( Coordinate::A1_COORDINATE_REGEX, $address, $cellReference );

		if ( $validityCheck === 0 ) {
			throw new Exception( 'Invalid A1-format Cell Reference' );
		}

		if ( $cellReference['col'][0] === '$' ) {
			// Column must be absolute address
			$currentColumnNumber = null;
		}
		$columnId = Coordinate::columnIndexFromString( ltrim( $cellReference['col'], '$' ) );

		if ( $cellReference['row'][0] === '$' ) {
			// Row must be absolute address
			$currentRowNumber = null;
		}
		$rowId = (int) ltrim( $cellReference['row'], '$' );

		if ( $currentRowNumber !== null ) {
			if ( $rowId === $currentRowNumber ) {
				$rowId = '';
			} else {
				$rowId = '[' . ( $rowId - $currentRowNumber ) . ']';
			}
		}

		if ( $currentColumnNumber !== null ) {
			if ( $columnId === $currentColumnNumber ) {
				$columnId = '';
			} else {
				$columnId = '[' . ( $columnId - $currentColumnNumber ) . ']';
			}
		}

		$R1C1Address = "R{$rowId}C{$columnId}";

		return $R1C1Address;
	}
}
