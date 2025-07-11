<?php

namespace Wpae\App\Field;


class ShippingLabel extends Field {
	const SECTION = 'shipping';

	public function getValue( $snippetData ) {
		$shippingData = $this->feed->getSectionFeedData( self::SECTION );

		if ( isset( $shippingData['shippingLabel'] ) ) {
			return $shippingData['shippingLabel'];
		} else {
			return '';
		}
	}

	public function getFieldName() {
		return 'shipping_label';
	}
}