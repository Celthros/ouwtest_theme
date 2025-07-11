<?php

namespace Wpae\App\Field;


class CostOfGoodsSold extends Field {
	const SECTION = 'availabilityPrice';

	public function getValue( $snippetData ) {
		$availabilityPrice = $this->feed->getSectionFeedData( self::SECTION );

		if ( isset( $availabilityPrice['costOfGoodsSold'] ) ) {
			return $availabilityPrice['costOfGoodsSold'];
		} else {
			return '';
		}

	}

	public function getFieldName() {
		return 'cost_of_goods_sold';
	}

}