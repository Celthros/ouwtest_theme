<?php

namespace PhpOffice\PhpSpreadsheet\Cell;

use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Calculation\Engine\FormattedNumber;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AdvancedValueBinder extends DefaultValueBinder implements IValueBinder {
	/**
	 * Bind value to a cell.
	 *
	 * @param Cell $cell Cell to bind value to
	 * @param mixed $value Value to bind in cell
	 *
	 * @return bool
	 */
	public function bindValue( Cell $cell, $value = null ) {
		if ( $value === null ) {
			return parent::bindValue( $cell, $value );
		} elseif ( is_string( $value ) ) {
			// sanitize UTF-8 strings
			$value = StringHelper::sanitizeUTF8( $value );
		}

		// Find out data type
		$dataType = parent::dataTypeForValue( $value );

		// Style logic - strings
		if ( $dataType === DataType::TYPE_STRING && ! $value instanceof RichText ) {
			//    Test for booleans using locale-setting
			if ( StringHelper::strToUpper( $value ) === Calculation::getTRUE() ) {
				$cell->setValueExplicit( true, DataType::TYPE_BOOL );

				return true;
			} elseif ( StringHelper::strToUpper( $value ) === Calculation::getFALSE() ) {
				$cell->setValueExplicit( false, DataType::TYPE_BOOL );

				return true;
			}

			// Check for fractions
			if ( preg_match( '/^([+-]?)\s*(\d+)\s?\/\s*(\d+)$/', $value, $matches ) ) {
				return $this->setProperFraction( $matches, $cell );
			} elseif ( preg_match( '/^([+-]?)(\d*) +(\d*)\s?\/\s*(\d*)$/', $value, $matches ) ) {
				return $this->setImproperFraction( $matches, $cell );
			}

			$decimalSeparatorNoPreg = StringHelper::getDecimalSeparator();
			$decimalSeparator       = preg_quote( $decimalSeparatorNoPreg, '/' );
			$thousandsSeparator     = preg_quote( StringHelper::getThousandsSeparator(), '/' );

			// Check for percentage
			if ( preg_match( '/^\-?\d*' . $decimalSeparator . '?\d*\s?\%$/', preg_replace( '/(\d)' . $thousandsSeparator . '(\d)/u', '$1$2', $value ) ) ) {
				return $this->setPercentage( preg_replace( '/(\d)' . $thousandsSeparator . '(\d)/u', '$1$2', $value ), $cell );
			}

			// Check for currency
			if ( preg_match( FormattedNumber::currencyMatcherRegexp(), preg_replace( '/(\d)' . $thousandsSeparator . '(\d)/u', '$1$2', $value ), $matches, PREG_UNMATCHED_AS_NULL ) ) {
				// Convert value to number
				$sign         = ( $matches['PrefixedSign'] ?? $matches['PrefixedSign2'] ?? $matches['PostfixedSign'] ) ?? null;
				$currencyCode = $matches['PrefixedCurrency'] ?? $matches['PostfixedCurrency'];
				$value        = (float) ( $sign . trim( str_replace( [
						$decimalSeparatorNoPreg,
						$currencyCode,
						' ',
						'-',
					], [
						'.',
						'',
						'',
						'',
					], preg_replace( '/(\d)' . $thousandsSeparator . '(\d)/u', '$1$2', $value ) ) ) ); // @phpstan-ignore-line

				return $this->setCurrency( $value, $cell, $currencyCode ); // @phpstan-ignore-line
			}

			// Check for time without seconds e.g. '9:45', '09:45'
			if ( preg_match( '/^(\d|[0-1]\d|2[0-3]):[0-5]\d$/', $value ) ) {
				return $this->setTimeHoursMinutes( $value, $cell );
			}

			// Check for time with seconds '9:45:59', '09:45:59'
			if ( preg_match( '/^(\d|[0-1]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $value ) ) {
				return $this->setTimeHoursMinutesSeconds( $value, $cell );
			}

			// Check for datetime, e.g. '2008-12-31', '2008-12-31 15:59', '2008-12-31 15:59:10'
			if ( ( $d = Date::stringToExcel( $value ) ) !== false ) {
				// Convert value to number
				$cell->setValueExplicit( $d, DataType::TYPE_NUMERIC );
				// Determine style. Either there is a time part or not. Look for ':'
				if ( strpos( $value, ':' ) !== false ) {
					$formatCode = 'yyyy-mm-dd h:mm';
				} else {
					$formatCode = 'yyyy-mm-dd';
				}
				$cell->getWorksheet()->getStyle( $cell->getCoordinate() )->getNumberFormat()->setFormatCode( $formatCode );

				return true;
			}

			// Check for newline character "\n"
			if ( strpos( $value, "\n" ) !== false ) {
				$cell->setValueExplicit( $value, DataType::TYPE_STRING );
				// Set style
				$cell->getWorksheet()->getStyle( $cell->getCoordinate() )->getAlignment()->setWrapText( true );

				return true;
			}
		}

		// Not bound yet? Use parent...
		return parent::bindValue( $cell, $value );
	}

	protected function setImproperFraction( array $matches, Cell $cell ): bool {
		// Convert value to number
		$value = $matches[2] + ( $matches[3] / $matches[4] );
		if ( $matches[1] === '-' ) {
			$value = 0 - $value;
		}
		$cell->setValueExplicit( (float) $value, DataType::TYPE_NUMERIC );

		// Build the number format mask based on the size of the matched values
		$dividend     = str_repeat( '?', strlen( $matches[3] ) );
		$divisor      = str_repeat( '?', strlen( $matches[4] ) );
		$fractionMask = "# {$dividend}/{$divisor}";
		// Set style
		$cell->getWorksheet()->getStyle( $cell->getCoordinate() )->getNumberFormat()->setFormatCode( $fractionMask );

		return true;
	}

	protected function setProperFraction( array $matches, Cell $cell ): bool {
		// Convert value to number
		$value = $matches[2] / $matches[3];
		if ( $matches[1] === '-' ) {
			$value = 0 - $value;
		}
		$cell->setValueExplicit( (float) $value, DataType::TYPE_NUMERIC );

		// Build the number format mask based on the size of the matched values
		$dividend     = str_repeat( '?', strlen( $matches[2] ) );
		$divisor      = str_repeat( '?', strlen( $matches[3] ) );
		$fractionMask = "{$dividend}/{$divisor}";
		// Set style
		$cell->getWorksheet()->getStyle( $cell->getCoordinate() )->getNumberFormat()->setFormatCode( $fractionMask );

		return true;
	}

	protected function setPercentage( string $value, Cell $cell ): bool {
		// Convert value to number
		$value = ( (float) str_replace( '%', '', $value ) ) / 100;
		$cell->setValueExplicit( $value, DataType::TYPE_NUMERIC );

		// Set style
		$cell->getWorksheet()->getStyle( $cell->getCoordinate() )->getNumberFormat()->setFormatCode( NumberFormat::FORMAT_PERCENTAGE_00 );

		return true;
	}

	protected function setCurrency( float $value, Cell $cell, string $currencyCode ): bool {
		$cell->setValueExplicit( $value, DataType::TYPE_NUMERIC );
		// Set style
		$cell->getWorksheet()->getStyle( $cell->getCoordinate() )->getNumberFormat()->setFormatCode( str_replace( '$', '[$' . $currencyCode . ']', NumberFormat::FORMAT_CURRENCY_USD ) );

		return true;
	}

	protected function setTimeHoursMinutes( string $value, Cell $cell ): bool {
		// Convert value to number
		[ $hours, $minutes ] = explode( ':', $value );
		$hours   = (int) $hours;
		$minutes = (int) $minutes;
		$days    = ( $hours / 24 ) + ( $minutes / 1440 );
		$cell->setValueExplicit( $days, DataType::TYPE_NUMERIC );

		// Set style
		$cell->getWorksheet()->getStyle( $cell->getCoordinate() )->getNumberFormat()->setFormatCode( NumberFormat::FORMAT_DATE_TIME3 );

		return true;
	}

	protected function setTimeHoursMinutesSeconds( string $value, Cell $cell ): bool {
		// Convert value to number
		[ $hours, $minutes, $seconds ] = explode( ':', $value );
		$hours   = (int) $hours;
		$minutes = (int) $minutes;
		$seconds = (int) $seconds;
		$days    = ( $hours / 24 ) + ( $minutes / 1440 ) + ( $seconds / 86400 );
		$cell->setValueExplicit( $days, DataType::TYPE_NUMERIC );

		// Set style
		$cell->getWorksheet()->getStyle( $cell->getCoordinate() )->getNumberFormat()->setFormatCode( NumberFormat::FORMAT_DATE_TIME4 );

		return true;
	}
}
