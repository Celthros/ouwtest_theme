<?php
function pmxi_wp_ajax_auto_detect_sf() {

	if ( ! check_ajax_referer( 'wp_all_import_secure', 'security', false ) ) {
		exit( json_encode( array( 'result' => array(), 'msg' => __( 'Security check', 'wp-all-import-pro' ) ) ) );
	}

	if ( ! current_user_can( PMXI_Plugin::$capabilities ) ) {
		exit( json_encode( array( 'result' => array(), 'msg' => __( 'Security check', 'wp-all-import-pro' ) ) ) );
	}

	$input     = new PMXI_Input();
	$fieldName = $input->post( 'name', '' );
	$post_type = $input->post( 'post_type', 'post' );
	global $wpdb;

	$result = array();

	if ( $fieldName ) {

		switch ( $post_type ) {
			case 'import_users':
			case 'shop_customer':
				$values = $wpdb->get_results( "
                    SELECT DISTINCT usermeta.meta_value
                    FROM " . $wpdb->usermeta . " as usermeta
                    WHERE usermeta.meta_key='" . $fieldName . "'
                ", ARRAY_A );
				break;
			case 'taxonomies':
				$values = $wpdb->get_results( "
                    SELECT DISTINCT termmeta.meta_value
                    FROM " . $wpdb->termmeta . " as termmeta
                    WHERE termmeta.meta_key='" . $fieldName . "'
                ", ARRAY_A );
				break;
			case 'woo_reviews':
			case 'comments':
				$values = $wpdb->get_results( "
                    SELECT DISTINCT commentmeta.meta_value
                    FROM " . $wpdb->commentmeta . " as commentmeta
                    WHERE commentmeta.meta_key='" . $fieldName . "'
                ", ARRAY_A );
				break;
			default:
				$values = $wpdb->get_results( "
                    SELECT DISTINCT postmeta.meta_value
                    FROM " . $wpdb->postmeta . " as postmeta
                    WHERE postmeta.meta_key='" . $fieldName . "'
                ", ARRAY_A );
				break;
		}

		if ( ! empty( $values ) ) {
			foreach ( $values as $key => $value ) {
				if ( ! empty( $value['meta_value'] ) and is_serialized( $value['meta_value'] ) ) {
					$v = \pmxi_maybe_unserialize( $value['meta_value'] );
					if ( ! empty( $v ) and is_array( $v ) ) {
						foreach ( $v as $skey => $svalue ) {
							$result[] = array(
								'key' => $skey,
								'val' => maybe_serialize( $svalue ),
							);
						}
						break;
					}
				}
			}
		}

	}

	exit( json_encode( array( 'result' => $result ) ) );

}