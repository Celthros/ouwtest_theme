<?php

function pmxe_wpallexport_custom_types( $custom_types ) {
	foreach ( $custom_types as $k => $custom_type ) {

		$custom_types[ $k ]         = clone $custom_type;
		$custom_types[ $k ]->labels = clone $custom_type->labels;
	}

	if ( class_exists( 'WooCommerce' ) ) {
		if ( ! empty( $custom_types['product'] ) ) {
			$custom_types['product']->labels->name = esc_html__( 'WooCommerce Products', 'wp_all_export_plugin' );
		}
		if ( ! empty( $custom_types['shop_order'] ) ) {
			$custom_types['shop_order']->labels->name = esc_html__( 'WooCommerce Orders', 'wp_all_export_plugin' );
		}
		if ( ! empty( $custom_types['shop_coupon'] ) ) {
			$custom_types['shop_coupon']->labels->name = esc_html__( 'WooCommerce Coupons', 'wp_all_export_plugin' );
		}
		if ( ! empty( $custom_types['product_variation'] ) ) {
			unset( $custom_types['product_variation'] );
		}
		if ( ! empty( $custom_types['shop_order_refund'] ) ) {
			unset( $custom_types['shop_order_refund'] );
		}

		$order = array( 'shop_order', 'shop_coupon', 'shop_customer', 'product' );

		$ordered_custom_types = array();

		foreach ( $order as $type ) {

			if ( isset( $ordered_custom_types[ $type ] ) ) {
				continue;
			}

			if ( $type == 'shop_customer' ) {
				$ordered_custom_types['shop_customer']               = new stdClass();
				$ordered_custom_types['shop_customer']->labels       = new stdClass();
				$ordered_custom_types['shop_customer']->labels->name = esc_html__( 'WooCommerce Customers', 'wp_all_export_plugin' );
			} else {
				foreach ( $custom_types as $key => $custom_type ) {
					if ( isset( $ordered_custom_types[ $key ] ) ) {
						continue;
					}

					if ( in_array( $key, $order ) ) {
						if ( $key == $type ) {
							$ordered_custom_types[ $key ] = $custom_type;
						}
					} else {
						$ordered_custom_types[ $key ] = $custom_type;
					}
				}
			}
		}

		return $ordered_custom_types;
	}

	return $custom_types;
}