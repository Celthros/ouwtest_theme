<?php

namespace Wpae\App\Field;


class ShippingWidth extends Field {
	const SECTION = 'shipping';

	public function getValue( $snippetData ) {
		$shippingData = $this->feed->getSectionFeedData( self::SECTION );

		if ( $shippingData['dimensions'] == 'useWooCommerceProductValues' ) {

			$currentUnit = get_option( 'woocommerce_dimension_unit' );
			$toUnit      = $shippingData['convertTo'];

			$product = $_product = wc_get_product( $this->entry->ID );

			if ( $currentUnit !== $toUnit ) {

				$shippingWidth = $product->get_width();

				if ( is_numeric( $shippingWidth ) ) {
					$width = wc_get_dimension( $shippingWidth, $toUnit, $currentUnit );
				} else {
					$width = $shippingWidth;
				}

			} else {
				$width = $product->get_width();
			}

			if ( $width ) {
				return $width . ' ' . $toUnit;
			} else {
				return '';
			}
		} else {
			if ( isset( $shippingData['dimensionsCV'] ) ) {
				return $shippingData['dimensionsCV'];
			} else {
				return '';
			}
		}
	}

	public function getFieldName() {
		return 'shipping_width';
	}
}