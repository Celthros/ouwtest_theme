<?php

namespace Wpae\App\Field;


class AdditionalImageLink extends Field {
	const SECTION = 'basicInformation';

	public function getValue( $snippetData ) {
		$basicInformationData = $this->feed->getSectionFeedData( self::SECTION );
		$product              = wc_get_product( $this->entry->ID );

		if ( $basicInformationData['additionalImageLink'] == 'productImages' ) {

			if ( $this->wooCommerceVersion->isWooCommerceNewerThan( '3.0' ) ) {
				$attachment_ids = $product->get_gallery_image_ids();
			} else {
				$attachment_ids = $product->get_gallery_attachment_ids();
			}

			if ( is_array( $attachment_ids ) && count( $attachment_ids ) ) {
				return wp_get_attachment_url( $attachment_ids[0] );
			}

		} else if ( $basicInformationData['additionalImageLink'] == self::CUSTOM_VALUE_TEXT ) {
			return $basicInformationData['additionalImageLinkCV'];
		} else {
			throw new \Exception( 'Unknown value ' . $basicInformationData['additionalImageLink'] . ' for additional image link' );
		}

		return '';

	}

	public function getFieldName() {
		return 'additional_image_link';
	}

}