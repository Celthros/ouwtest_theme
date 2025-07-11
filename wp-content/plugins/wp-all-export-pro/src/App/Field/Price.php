<?php

namespace Wpae\App\Field;


class Price extends Field {
	const SECTION = 'availabilityPrice';

	public function getValue( $snippetData ) {
		$availabilityPriceData = $this->feed->getSectionFeedData( self::SECTION );

		if ( $availabilityPriceData['price'] == 'useProductPrice' ) {
			$product = wc_get_product( $this->entry->ID );
			$price   = $product->get_regular_price();
		} else if ( $availabilityPriceData['price'] == self::CUSTOM_VALUE_TEXT ) {
			$price = $this->replaceSnippetsInValue( $availabilityPriceData['priceCV'], $snippetData );

		} else {
			throw new \Exception( 'Unknown field value' );
		}

		if ( $availabilityPriceData['adjustPriceValue'] ) {
			$adjustPriceValue = $this->replaceSnippetsInValue( $availabilityPriceData['adjustPriceValue'], $snippetData );
			$adjustPriceValue = floatval( $adjustPriceValue );
			$price            = floatval( $price );

			if ( $availabilityPriceData['adjustPriceType'] == '%' ) {
				if ( $price != 0 ) {
					$price = $adjustPriceValue / 100 * $price;
				} else {
					$price = 0;
				}
			} else {
				$price = $price + $adjustPriceValue;
			}
		}

		$rawPrices = false;
		$rawPrices = apply_filters( 'wp_all_export_raw_prices', $rawPrices );

		if ( ! $rawPrices ) {
			if ( $price ) {
				if ( is_numeric( $price ) ) {
					return number_format( $price, 2 ) . ' ' . $availabilityPriceData['currency'];
				} else {
					return $price . ' ' . $availabilityPriceData['currency'];
				}

			} else {
				return "";
			}
		} else {
			return $price;
		}

	}

	public function getFieldName() {
		return 'price';
	}
}