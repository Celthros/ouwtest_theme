<?php

namespace Wpae\App\Field;


class SalePrice extends Field {
	const SECTION = 'availabilityPrice';

	public function getValue( $snippetData ) {
		$availabilityPriceData = $this->feed->getSectionFeedData( self::SECTION );

		if ( $availabilityPriceData['salePrice'] == self::CUSTOM_VALUE_TEXT ) {
			$price = $this->replaceSnippetsInValue( $availabilityPriceData['salePriceCV'], $snippetData );

		} else if ( $availabilityPriceData['salePrice'] == 'useProductSalePrice' ) {
			$product = wc_get_product( $this->entry->ID );
			$price   = $product->get_sale_price();

		} else {
			throw new \Exception( 'Unknown field value ' . $availabilityPriceData['salePrice'] );
		}

		if ( $availabilityPriceData['adjustSalePriceValue'] ) {
			$adjustPriceValue = $this->replaceSnippetsInValue( $availabilityPriceData['adjustSalePriceValue'], $snippetData );
			$adjustPriceValue = floatval( $adjustPriceValue );
			$price            = floatval( $price );

			if ( $availabilityPriceData['adjustSalePriceType'] == '%' ) {
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
		return 'sale_price';
	}
}