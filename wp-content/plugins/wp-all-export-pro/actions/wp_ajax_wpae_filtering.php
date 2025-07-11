<?php

function pmxe_wp_ajax_wpae_filtering() {

	if ( ! check_ajax_referer( 'wp_all_export_secure', 'security', false ) ) {
		exit( json_encode( array( 'html' => esc_html__( 'Security check', 'wp_all_export_plugin' ) ) ) );
	}

	if ( ! current_user_can( PMXE_Plugin::$capabilities ) ) {
		exit( json_encode( array( 'html' => esc_html__( 'Security check', 'wp_all_export_plugin' ) ) ) );
	}

	$response = array(
		'html' => '',
		'btns' => '',
	);

	ob_start();

	$errors = new WP_Error();

	$input = new PMXE_Input();
	
	$post = $input->post( 'data', array() );

	if ( empty( $post['cpt'] ) ) {
		$postTypes           = [];
		$exportqueryPostType = [];

		if ( isset( $post['exportquery'] ) && ! empty( $post['exportquery']->query['post_type'] ) ) {
			$exportqueryPostType = [ $post['exportquery']->query['post_type'] ];
		}

		if ( empty( $postTypes ) ) {
			$postTypes = $exportqueryPostType;
		}

		$post['cpt'] = $postTypes;
	}

	if ( ! empty( $post['cpt'] ) ):

		$engine = new XmlExportEngine( $post, $errors );

		$engine->init_available_data();

		?>
		<div class="wpallexport-content-section">
			<div class="wpallexport-collapsed-header">
				<h3><?php esc_html_e( 'Add Filtering Options', 'wp_all_export_plugin' ); ?></h3>
			</div>

			<div class="wpallexport-collapsed-content">
				<?php include_once PMXE_ROOT_DIR . '/views/admin/export/blocks/filters.php'; ?>
			</div>

		</div>

	<?php

	endif;

	$response['html'] = ob_get_clean();
	
	ob_start();


	if ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() ) {
		?>
		<input type="hidden" id="woocommerce_add_on_pro_installed" value="1" />
		<?php
	}

	if ( XmlExportEngine::$is_auto_generate_enabled ):
		?>
		<div class="wpallexport-free-edition-notice" id="migrate-orders-notice"
		     style="padding: 20px; margin-bottom: 10px; display: none; width:auto;">
			<p><?php esc_html_e( 'The WooCoommerce Export Add-On Pro is Required to Migrate Orders.', 'wp_all_export_plugin' ); ?></p>
			<br />
			<a class="upgrade_link" target="_blank"
			   href="http://www.wpallimport.com/portal/discounts?utm_source=export-plugin-pro&utm_medium=upgrade-notice&utm_campaign=migrate-orders"><?php esc_html_e( 'Purchase the WooCommerce Export Add-On Pro', 'wp_all_export_plugin' ); ?></a>
		</div>

		<div class="wpallexport-free-edition-notice" id="migrate-products-notice"
		     style="padding: 20px; margin-bottom: 10px; display: none; width:auto;">
			<p><?php esc_html_e( 'The WooCoommerce Export Add-On Pro is Required to Migrate Products.', 'wp_all_export_plugin' ); ?></p>
			<br />
			<a class="upgrade_link" target="_blank"
			   href="http://www.wpallimport.com/portal/discounts?utm_source=export-plugin-pro&utm_medium=upgrade-notice&utm_campaign=migrate-products"><?php esc_html_e( 'Purchase the WooCommerce Export Add-On Pro', 'wp_all_export_plugin' ); ?></a>
		</div>

		<span class="wp_all_export_btn_with_note">
		<a href="javascript:void(0);" class="back rad3 auto-generate-template"
		   style="float:none; background: #425f9a; padding: 0 50px; margin-right: 10px; color: #fff; font-weight: normal;"><?php printf( esc_html__( 'Migrate %s', 'wp_all_export_plugin' ), wp_all_export_get_cpt_name( array( $post['cpt'] ), 2, $post ) ); ?></a>
		<span class="auto-generate-template">&nbsp;</span>
	</span>
		<span class="wp_all_export_btn_with_note">
		<input type="submit" class="button button-primary button-hero wpallexport-large-button"
		       value="<?php esc_html_e( 'Customize Export File', 'wp_all_export_plugin' ) ?>" />
		<span class="auto-generate-template">&nbsp;</span>
	</span>
	<?php
	else:
		?>
		<span class="wp_all_export_btn_with_note">
		<input type="submit" class="button button-primary button-hero wpallexport-large-button"
		       value="<?php esc_html_e( 'Customize Export File', 'wp_all_export_plugin' ) ?>" />
	</span>
	<?php
	endif;
	$response['btns'] = ob_get_clean();
	
	exit( json_encode( $response ) );
	die;

}