<?php
if ( ! function_exists( 'wp_all_import_template_notifications' ) ) {
	function wp_all_import_template_notifications( $post, $type = 'warning' ) {
		$notifications = array();
		// import template was generated via WP All Export
		if ( ! empty( $post['required_add_ons'] ) ) {
			foreach ( $post['required_add_ons'] as $key => $addon ) {
				if ( class_exists( $key ) ) {
					continue;
				}

				$notifications[] = sprintf( __( 'The import template you are using requires the %s. If you continue without it your data may import incorrectly.<br/><br/><a href="%s" target="_blank">' . ( $addon['paid'] ? 'Purchase' : 'Download' ) . ' the %s</a>.', 'wp-all-import-pro' ), $addon['name'], $addon['url'], $addon['name'] );
			}
		} else // Custom Import Template
		{
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			if ( $post['custom_type'] == 'import_users' && ! class_exists( 'PMUI_Plugin' ) ) {
				$notifications[] = __( 'The import template you are using requires the User Add-On. If you continue without it your data may import incorrectly.<br/><br/><a href="http://www.wpallimport.com/add-ons/user-import/?utm_source=wordpress.org&utm_medium=wpai-import-template&utm_campaign=free+wp+all+export+plugin" target="_blank">Purchase the User Add-On</a>.', 'wp-all-import-pro' );
			}

			if ( $post['custom_type'] == 'shop_customer' && ! class_exists( 'PMUI_Plugin' ) ) {
				$notifications[] = __( 'The import template you are using requires the User Add-On. If you continue without having this add-on active, your data may import incorrectly.<br/><br/><a href="http://www.wpallimport.com/add-ons/user-import/?utm_source=wordpress.org&utm_medium=wpai-import-template&utm_campaign=free+wp+all+export+plugin" target="_blank">Purchase the User Add-On</a>.', 'wp-all-import-pro' );
			} elseif ( $post['custom_type'] == 'product' && ! class_exists( 'PMWI_Plugin' ) && class_exists( 'Woocommerce' ) ) {
				$notifications[] = __( 'The import template you are using requires the WooCommerce Import Add-On. If you continue without it your data may import incorrectly.<br/><br/><a href="http://www.wpallimport.com/woocommerce-product-import/" target="_blank">Purchase the WooCommerce Import Add-On</a>.', 'wp-all-import-pro' );
			} // Realia Add-On
			elseif ( ! empty( $post['realia_addon'] ) and ! is_plugin_active( 'realia-xml-csv-property-listings-import/realia-add-on.php' ) ) {
				$notifications[] = __( 'The import template you are using requires the Realia Add-On. If you continue without it your data may import incorrectly.<br/><br/><a href="https://wordpress.org/plugins/realia-xml-csv-property-listings-import/" target="_blank">Download the Realia Add-On</a>.', 'wp-all-import-pro' );
			} // WP Residence Add-On
			elseif ( ! empty( $post['realhomes_addon'] ) and isset( $post['realhomes_addon']['property_price'] ) and ! is_plugin_active( 'wp-residence-add-on-for-wp-all-import/wp-residence-add-on.php' ) ) {
				$notifications[] = __( 'The import template you are using requires the WP Residence Add-On. If you continue without it your data may import incorrectly.<br/><br/><a href="https://wordpress.org/plugins/wp-residence-add-on-for-wp-all-import/" target="_blank">Download the WP Residence Add-On</a>.', 'wp-all-import-pro' );
			} // RealHomes Add-On
			elseif ( ! empty( $post['realhomes_addon'] ) and isset( $post['realhomes_addon']['REAL_HOMES_property_price'] ) and ! is_plugin_active( 'realhomes-xml-csv-property-listings-import/realhomes-add-on.php' ) ) {
				$notifications[] = __( 'The import template you are using requires the RealHomes Add-On. If you continue without it your data may import incorrectly.<br/><br/><a href="https://wordpress.org/plugins/realhomes-xml-csv-property-listings-import/" target="_blank">Download the RealHomes Add-On</a>.', 'wp-all-import-pro' );
			} // Jobify Add-On
			elseif ( ! empty( $post['jobify_addon'] ) and ! is_plugin_active( 'jobify-xml-csv-listings-import/jobify-add-on.php' ) ) {
				$notifications[] = __( 'The import template you are using requires the Jobify Add-On. If you continue without it your data may import incorrectly.<br/><br/><a href="https://wordpress.org/plugins/jobify-xml-csv-listings-import/" target="_blank">Download the Jobify Add-On</a>.', 'wp-all-import-pro' );
			} // Listify Add-On
			elseif ( ! empty( $post['listify_addon'] ) and ! is_plugin_active( 'listify-xml-csv-listings-import/listify-add-on.php' ) ) {
				$notifications[] = __( 'The import template you are using requires the Listify Add-On. If you continue without it your data may import incorrectly.<br/><br/><a href="https://wordpress.org/plugins/listify-xml-csv-listings-import/" target="_blank">Download the Listify Add-On</a>.', 'wp-all-import-pro' );
			} // Reales WP Add-On
			elseif ( ! empty( $post['reales_addon'] ) and ! is_plugin_active( 'reales-wp-xml-csv-property-listings-import/reales-add-on.php' ) ) {
				$notifications[] = __( 'The import template you are using requires the Reales WP Add-On. If you continue without it your data may import incorrectly.<br/><br/><a href="https://wordpress.org/plugins/reales-wp-xml-csv-property-listings-import/" target="_blank">Download the Reales WP Add-On</a>.', 'wp-all-import-pro' );
			} // WP Job Manager Add-On
			elseif ( ! empty( $post['wpjm_addon'] ) and ! is_plugin_active( 'wp-job-manager-xml-csv-listings-import/wp-job-manager-add-on.php' ) ) {
				$notifications[] = __( 'The import template you are using requires the WP Job Manager Add-On. If you continue without it your data may import incorrectly.<br/><br/><a href="https://wordpress.org/plugins/wp-job-manager-xml-csv-listings-import/" target="_blank">Download the WP Job Manager Add-On</a>.', 'wp-all-import-pro' );
			} // Yoast SEO Add-On
			elseif ( ! empty( $post['yoast_addon'] ) and ! is_plugin_active( 'yoast-seo-settings-xml-csv-import/yoast-addon.php' ) ) {
				$notifications[] = __( 'The import template you are using requires the Yoast SEO Add-On. If you continue without it your data may import incorrectly.<br/><br/><a href="https://wordpress.org/plugins/yoast-seo-settings-xml-csv-import/" target="_blank">Download the Yoast SEO Add-On</a>.', 'wp-all-import-pro' );
			} // Listable SEO Add-On
			elseif ( ! empty( $post['listable_addon'] ) and ! is_plugin_active( 'import-xml-csv-listings-to-listable-theme/listable-add-on.php' ) ) {
				$notifications[] = __( 'The import template you are using requires the Listable Add-On. If you continue without it your data may import incorrectly.<br/><br/><a href="https://wordpress.org/plugins/import-xml-csv-listings-to-listable-theme/" target="_blank">Download the Listable Add-On</a>.', 'wp-all-import-pro' );
			} // 3rd party Add-On
			elseif ( ! empty( $post['rapid_addon'] ) and ! is_plugin_active( $post['rapid_addon'] ) ) {
				$notification[] = __( 'The import template you are using requires an Add-On for WP All Import. If you continue without using this Add-On your data may import incorrectly.', 'wp-all-import-pro' );
			}
		}

		if ( ! empty( $notifications ) ) {
			foreach ( $notifications as $notification ) {
				if ( $type == 'warning' ) {
					?>
					<div class="error inline">
						<p><?php printf( __( '<strong>Warning:</strong>', 'wp-all-import-pro' ) . ' %s', $notification ); ?></p>
					</div>
					<?php
				} else {
					?>
					<div class="wpallimport-free-edition-notice"
					     style="text-align:center; margin-top:0; margin-bottom: 20px;">
						<p class="upgrade_link"><?php echo $notification; ?></p>
					</div>
					<?php
				}
			}
		}
	}
}	