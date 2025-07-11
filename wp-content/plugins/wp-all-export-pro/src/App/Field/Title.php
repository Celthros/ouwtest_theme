<?php

namespace Wpae\App\Field;


class Title extends Field {
	const SECTION = 'basicInformation';

	public function getValue( $snippetData ) {
		$basicInformationData = $this->feed->getSectionFeedData( self::SECTION );

		if ( $basicInformationData['itemTitle'] == 'productTitle' ) {
			if ( $this->entry->post_type == 'product_variation' && $basicInformationData['useParentTitleForVariableProducts'] ) {
				$parent = get_post( $this->entry->post_parent );
				if ( is_object( $parent ) ) {
					$value = $parent->post_title;
				} else {
					$value = '';
				}
			} else {
				$value = $this->entry->post_title;
			}

			$value = str_replace( "[", "**OPENSHORTCODE**", $value );
			$value = str_replace( "]", "**CLOSESHORTCODE**", $value );

		} else if ( $basicInformationData['itemTitle'] == self::CUSTOM_VALUE_TEXT ) {
			$customValue = $basicInformationData['itemTitleCV'];
			$value       = $customValue;
		} else {
			throw new \Exception( 'Unknown field value' );
		}

		return $value;
	}

	public function getFieldName() {
		return 'title';
	}

}