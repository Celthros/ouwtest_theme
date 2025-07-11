<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
?>
<?php
do_action( 'pmxe_addons_html' );
?>
<?php
$addons                = new \Wpae\App\Service\Addons\AddonService();
$is_rapid_addon_export = PMXE_Helper::is_rapid_export_addon( $post['cpt'] );
?>
<?php
if ( $is_rapid_addon_export ) {
	?>
	<input type="hidden" id="pmxe_dismiss_import_warnings_by_default" value="1" />
	<?php
}
?>
<h2 class="wpallexport-wp-notices"></h2>
<div class="wpallexport-wrapper">
	<div class="wpallexport-header">
		<div class="wpallexport-logo"></div>
		<div class="wpallexport-title">
			<h2><?php esc_html_e( 'Drag & Drop', 'wp_all_export_plugin' ); ?></h2>
		</div>
		<div class="wpallexport-links">
			<a href="http://www.wpallimport.com/support/"
			   target="_blank"><?php esc_html_e( 'Support', 'wp_all_export_plugin' ); ?></a> |
			<a
				href="http://www.wpallimport.com/documentation/"
				target="_blank"><?php esc_html_e( 'Documentation', 'wp_all_export_plugin' ); ?></a>
		</div>
	</div>
	<div class="clear"></div>
</div>

<div class="clear"></div>

<div class="wpallexport-content-section wpallexport-console" style="display: block; margin-bottom: 10px;">
	<div class="ajax-console">
		<div class="founded_records">
			<div class="wp_all_export_preloader"></div>
			<h4><?php esc_html_e( "Drag &amp; drop data to include in the export file." ); ?></h4>
		</div>
	</div>
</div>

<?php \Wpae\Pro\Filtering\FilteringFactory::render_filtering_block( $engine, $this->isWizard, $post, true ); ?>

<table class="wpallexport-layout wpallexport-export-template">
	<tr>
		<td class="left">

			<?php do_action( 'pmxe_template_header', $this->isWizard, $post ); ?>

			<?php if ( $this->errors->get_error_codes() ): ?>
				<?php $this->error(); ?>
			<?php endif ?>

			<form class="wpallexport-template <?php echo ! $this->isWizard ? 'edit' : '' ?> wpallexport-step-3"
			      method="post" style="display:none;" id="templateForm">
				
				<input type="hidden" class="hierarhy-output" name="filter_rules_hierarhy"
				       value="<?php echo esc_html( $post['filter_rules_hierarhy'] ); ?>" />
				<input type="hidden" name="taxonomy_to_export" value="<?php echo $post['taxonomy_to_export']; ?>">
				<input type="hidden" name="sub_post_type_to_export"
				       value="<?php echo $post['sub_post_type_to_export']; ?>">
				<input type="hidden" name="export_only_modified_stuff"
				       value="<?php echo $post['export_only_modified_stuff']; ?>" />
				<input type="hidden" name="export_only_new_stuff"
				       value="<?php echo $post['export_only_new_stuff']; ?>" />
				<?php if ( isset( $post['enable_real_time_exports'] ) && $post['enable_real_time_exports'] ) { ?>
					<input type="hidden" name="enable_real_time_exports" id="enable_real_time_exports" value="1" />
				<?php } ?>

				<?php
				$selected_post_type = '';
				if ( $addons->isUserAddonActiveAndIsUserExport() ):
					$selected_post_type = empty( $post['cpt'][0] ) ? 'users' : $post['cpt'][0];
				endif;
				if ( XmlExportComment::$is_active ):
					$selected_post_type = 'comments';
				elseif ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() && XmlExportWooCommerceReview::$is_active ):
					$selected_post_type = 'shop_review';
				endif;
				if ( empty( $selected_post_type ) and ! empty( $post['cpt'][0] ) ) {
					$selected_post_type = $post['cpt'][0];
				}
				?>

				<input type="hidden" name="selected_post_type" value="<?php echo esc_attr( $selected_post_type ); ?>" />
				<input type="hidden" name="export_type" value="<?php echo esc_attr( $post['export_type'] ); ?>" />
				<div class="wpallexport-collapsed wpallexport-section wpallexport-simple-xml-template">
					<div class="wpallexport-content-section" style="margin-bottom: 10px;">
						<div class="wpallexport-collapsed-content">
							<fieldset class="optionsset" style="padding: 10px 20px 0px;">
								<div id="columns_to_export">
									<div class="columns-to-export-content" style="padding-right: 8px;">
										<ol id="columns" class="rad4" style="margin-bottom:0;">
											<?php
											$i          = 0;
											$new_export = false;
											if ( ! empty( $post['ids'] ) ) {
												foreach ( $post['ids'] as $ID => $value ) {
													if ( is_numeric( $ID ) ) {
														if ( empty( $post['cc_name'][ $ID ] ) ) {
															continue;
														}
														?>
														<li>
															<div class="custom_column"
															     rel="<?php echo intval( ( $i + 1 ) ); ?>">
																<?php
																$field_label   = ( ! empty( $post['cc_name'][ $ID ] ) ) ? $post['cc_name'][ $ID ] : $post['cc_label'][ $ID ];
																$field_name    = ( ! empty( $post['cc_name'][ $ID ] ) ) ? $post['cc_name'][ $ID ] : trim( str_replace( " ", "_", $post['cc_label'][ $ID ] ) );
																$field_type    = $post['cc_type'][ $ID ];
																$field_options = esc_html( $post['cc_options'][ $ID ] );
																?>
																<label
																	class="wpallexport-xml-element"><?php echo ( strtolower( $field_label ) == "id" ) ? "ID" : esc_html( $field_label ); ?></label>
																<input type="hidden" name="ids[]" value="1" />
																<input type="hidden" name="cc_label[]"
																       value="<?php echo ( ! empty( $post['cc_label'][ $ID ] ) ) ? esc_attr( $post['cc_label'][ $ID ] ) : ''; ?>" />
																<input type="hidden" name="cc_php[]"
																       value="<?php echo ( ! empty( $post['cc_php'][ $ID ] ) ) ? esc_attr( $post['cc_php'][ $ID ] ) : 0; ?>" />
																<input type="hidden" name="cc_code[]"
																       value="<?php echo ( ! empty( $post['cc_code'][ $ID ] ) ) ? esc_attr( $post['cc_code'][ $ID ] ) : ''; ?>" />
																<input type="hidden" name="cc_sql[]"
																       value="<?php echo ( ! empty( $post['cc_sql'][ $ID ] ) ) ? esc_attr( $post['cc_sql'][ $ID ] ) : 0; ?>" />
																<input type="hidden" name="cc_type[]"
																       value="<?php echo $field_type; ?>" />
																<input type="hidden" name="cc_options[]"
																       value="<?php echo ( ! empty( $field_options ) ) ? esc_attr( $field_options ) : 0; ?>" />
																<input type="hidden" name="cc_value[]"
																       value="<?php echo esc_attr( $post['cc_value'][ $ID ] ); ?>" />
																<input type="hidden" name="cc_name[]"
																       value="<?php echo XmlExportEngine::sanitizeFieldName( esc_attr( $field_name ) ); ?>" />
																<input type="hidden" name="cc_settings[]"
																       value="<?php echo ( ! empty( $post['cc_settings'][ $ID ] ) ) ? esc_attr( $post['cc_settings'][ $ID ] ) : 0; ?>" />
																<input type="hidden" name="cc_combine_multiple_fields[]"
																       value="<?php echo ( ! empty( $post['cc_combine_multiple_fields'][ $ID ] ) ) ? esc_attr( $post['cc_combine_multiple_fields'][ $ID ] ) : ''; ?>" />
																<input type="hidden"
																       name="cc_combine_multiple_fields_value[]"
																       value="<?php echo ( ! empty( $post['cc_combine_multiple_fields_value'][ $ID ] ) ) ? esc_attr( $post['cc_combine_multiple_fields_value'][ $ID ] ) : ''; ?>" />
															</div>
														</li>
														<?php
														$i ++;
													}
												}
											} elseif ( $this->isWizard ) {
												$new_export = true;
												if ( empty( $post['cpt'] ) and $addons->isWooCommerceAddonActive() and ! XmlExportWooCommerceOrder::$is_active and ! $addons->isUserAddonActiveAndIsUserExport() and ! XmlExportComment::$is_active and ! XmlExportWooCommerceReview::$is_active ) {
													$init_fields[] = array(
														'label' => 'post_type',
														'name'  => 'post_type',
														'type'  => 'post_type',
													);
												}
												foreach ( $init_fields as $k => $field ) {
													?>
													<li>
														<div class="custom_column" rel="<?php echo( $i + 1 ); ?>">
															<label
																class="wpallexport-xml-element"><?php echo esc_html( XmlExportEngine::sanitizeFieldName( $field['name'] ) ); ?></label>
															<input type="hidden" name="ids[]" value="1" />
															<input type="hidden" name="cc_label[]"
															       value="<?php echo esc_attr( $field['label'] ); ?>" />
															<input type="hidden" name="cc_php[]" value="0" />
															<input type="hidden" name="cc_code[]" value="" />
															<input type="hidden" name="cc_sql[]" value="0" />
															<input type="hidden" name="cc_options[]"
															       value="<?php echo ( empty( $field['options'] ) ) ? 0 : esc_attr( $field['options'] ); ?>" />
															<input type="hidden" name="cc_type[]"
															       value="<?php echo esc_attr( $field['type'] ); ?>" />
															<input type="hidden" name="cc_value[]"
															       value="<?php echo esc_attr( $field['label'] ); ?>" />
															<input type="hidden" name="cc_name[]"
															       value="<?php echo ( strtoupper( $field['name'] ) == 'ID' && ( XmlExportEngine::$exportOptions['export_to_sheet'] === 'xlsx' || XmlExportEngine::$exportOptions['export_to_sheet'] === 'xls' ) ) ? 'id' : esc_attr( $field['name'] ); ?>" />
															<input type="hidden" name="cc_settings[]" value="0" />
															<input type="hidden" name="cc_combine_multiple_fields[]"
															       value="" />
															<input type="hidden"
															       name="cc_combine_multiple_fields_value[]" value="" />
														</div>
													</li>
													<?php
													$i ++;
												}
											}
											?>
											<li class="placeholder" <?php if ( ! empty( $post['ids'] ) and count( $post['ids'] ) > 1 or $new_export ) {
												echo 'style="display:none;"';
											} ?>><?php esc_html_e( "Drag & drop data from \"Available Data\" on the right to include it in the export or click \"Add Field To Export\" below.", "wp_all_export_plugin" ); ?></li>
											<?php
											?>
										</ol>
									</div>
								</div>

								<div class="custom_column template">
									<label class="wpallexport-xml-element"></label>
									<input type="hidden" name="ids[]" value="1" />
									<input type="hidden" name="cc_label[]" value="" />
									<input type="hidden" name="cc_php[]" value="0" />
									<input type="hidden" name="cc_code[]" value="" />
									<input type="hidden" name="cc_sql[]" value="0" />
									<input type="hidden" name="cc_type[]" value="" />
									<input type="hidden" name="cc_options[]" value="0" />
									<input type="hidden" name="cc_value[]" value="" />
									<input type="hidden" name="cc_name[]" value="" />
									<input type="hidden" name="cc_settings[]" value="0" />
									<input type="hidden" name="cc_combine_multiple_fields[]" value="" />
									<input type="hidden" name="cc_combine_multiple_fields_value[]" value="" />
								</div>

								<!-- Warning Messages -->
								<?php if ( ( ( $addons->isWooCommerceAddonActive() || $addons->isWooCommerceOrderAddonActive() ) && ! XmlExportWooCommerceOrder::$is_active ) && ! XmlExportComment::$is_active && ! XmlExportTaxonomy::$is_active ) : ?>
									<div
										class="wp-all-export-warning" <?php if ( empty( $post['ids'] ) or count( $post['ids'] ) > 1 ) {
										echo 'style="display:none;"';
									} ?>>
										<p></p>
										<input type="hidden" id="warning_template"
										       value="<?php esc_html_e( "Warning: without %s you won't be able to re-import this data back to this site using WP All Import.", "wp_all_export_plugin" ); ?>" />
										<button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span>
										</button>
									</div>
								<?php endif; ?>

								<?php if ( ( $addons->isWooCommerceAddonActive() || $addons->isWooCommerceProductAddonActive() ) && in_array( 'product', XmlExportEngine::$post_types ) ) : ?>
									<input type="hidden" id="is_product_export" value="1" />
								<?php endif; ?>

								<?php if ( ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() || XMLExportEngine::get_addons_service()->isWooCommerceOrderAddonActive() ) && empty( $post['cpt'] ) && ! XmlExportWooCommerceOrder::$is_active && ! $addons->isUserAddonActiveAndIsUserExport() && ! XmlExportComment::$is_active && ! XmlExportWooCommerceReview::$is_active && ! XmlExportTaxonomy::$is_active ) : ?>
									<input type="hidden" id="is_wp_query" value="1" />
								<?php endif; ?>

							</fieldset>

							<!-- Add New Field Button -->
							<div class="input" style="display:inline-block; margin: 20px 0 10px 20px;">
								<input type="button" value="<?php esc_html_e( 'Add Field', 'wp_all_export_plugin' ); ?>"
								       class="add_column" style="float:left;">
								<input type="button" value="<?php esc_html_e( 'Add All', 'wp_all_export_plugin' ); ?>"
								       class="wp_all_export_auto_generate_data">
								<input type="button" value="<?php esc_html_e( 'Clear All', 'wp_all_export_plugin' ); ?>"
								       class="wp_all_export_clear_all_data">
							</div>

							<!-- Preview a Row Button -->
							<div class="input" style="float:right; margin: 20px 20px 10px 0;">
								<input type="button" value="<?php esc_html_e( 'Preview', 'wp_all_export_plugin' ); ?>"
								       class="preview_a_row">
							</div>
						</div>

						<?php include( 'variation_options_common.php' ); ?>

						<div
							class="wpallexport-collapsed closed wpallexport-section wpallexport-xml-advanced-options" <?php if ( $post['export_to'] !== 'xml' ) { ?> style="display: none;" <?php } ?> >
							<div class="wpallexport-content-section rad0"
							     style="margin:0; border-top:1px solid #ddd; border-bottom: none; border-right: none; border-left: none; background: #f1f2f2; padding-bottom: 15px; margin-top: 5px;">
								<div class="wpallexport-collapsed-header">
									<h3 style="color:#40acad;"><?php esc_html_e( 'Advanced Options', 'wp_all_export_plugin' ); ?></h3>
									<hr style="display:none; margin-right:25px;" />
								</div>
								<div class="wpallexport-collapsed-content" style="padding:0 0 0 5px;">
									<div class="wpallexport-collapsed-content-inner">
										<div class="simple_xml_template_options" style="margin-top:20px;">
											<div class="input"
											     style="display: inline-block; max-width: 360px; width: 40%; margin-right: 10px;">
												<label for="main_xml_tag"
												       style="float: left;"><?php esc_html_e( 'Root XML Element', 'wp_all_export_plugin' ); ?></label>
												<div class="input">
													<input type="text" name="main_xml_tag"
													       style="vertical-align:middle; background:#fff !important; width: 100%; margin-left:0;"
													       value="<?php echo esc_attr( $post['main_xml_tag'] ) ?>" />
												</div>
											</div>
											<div class="input"
											     style="display: inline-block; max-width: 360px; width: 40%; ">
												<?php
												$post_type_details = ( ! empty( $post['cpt'] ) ) ? get_post_type_object( $post['cpt'][0] ) : '';
												?>
												<label for="record_xml_tag"
												       style="float: left;"><?php printf( __( 'Single %s XML Element', 'wp_all_export_plugin' ), empty( $post_type_details ) ? 'Record' : $post_type_details->labels->singular_name ); ?></label>
												<div class="input">
													<input type="text" name="record_xml_tag"
													       style="vertical-align:middle; background:#fff !important; width: 100%; margin-left:0;"
													       value="<?php echo esc_attr( $post['record_xml_tag'] ) ?>" />
												</div>
											</div>
										</div>
										<input type="hidden" id="custom_xml_cdata_logic"
										       value="<?php echo esc_attr( $post['custom_xml_cdata_logic'] ); ?>"
										       name="custom_xml_cdata_logic" />
										<input type="hidden" id="show_cdata_in_preview"
										       value="<?php echo esc_attr( $post['show_cdata_in_preview'] ); ?>"
										       name="show_cdata_in_preview" />
										<div>
											<?php if ( ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() || XmlExportEngine::get_addons_service()->isWooCommerceProductAddonActive() ) && XmlExportWooCommerce::$is_active ) {
												include( 'variation_options.php' );
											}
											?>
										</div>

										<div
											class="wp-all-export-product-bundle-warning warning-only-export-parent-products"
											style="display:none;">
											<p><?php esc_html_e( "You will not be able to reimport data to the product variations, and you will not be able to import these products to another site.", 'wp_all_export_plugin' ); ?></p>
										</div>
										<div
											class="wp-all-export-product-bundle-warning warning-only-export-product-variations"
											style="display:none;">
											<p><?php esc_html_e( "You will not be able to reimport data to the parent products, and you will not be able to import these products to another site.", 'wp_all_export_plugin' ); ?></p>
										</div>
										<div class="input">
											<h4 style="margin-top: 24px;">CDATA</h4>
											<p style="font-size: 12px;margin-top: 10px;"><?php echo sprintf( __( "There are certain characters that cannot be included in an XML file unless they are wrapped in CDATA tags.<br/>Read more about CDATA tags: <a target='_blank' style='font-size: 12px;' href='%s'>%s</a>.", 'wp_all_export_plugin' ), 'https://en.wikipedia.org/wiki/CDATA', 'https://en.wikipedia.org/wiki/CDATA' ); ?></p>
											<div class="input" style="margin: 10px 0;">
												<input type="radio" id="simple_custom_xml_cdata_logic_auto"
												       name="simple_custom_xml_cdata_logic" value="auto"
												       checked="checked" <?php echo ( "auto" == $post['custom_xml_cdata_logic'] ) ? 'checked="checked"' : '' ?>
												       class="switcher cdata" />
												<label
													for="simple_custom_xml_cdata_logic_auto"><?php esc_attr_e( 'Automatically wrap data in CDATA tags when it contains illegal characters', 'wp_all_export_plugin' ) ?></label>
											</div>
											<div class="input" style="margin: 10px 0;">
												<input type="radio" id="simple_custom_xml_cdata_logic_all"
												       name="simple_custom_xml_cdata_logic"
												       value="all" <?php echo ( "all" == $post['custom_xml_cdata_logic'] ) ? 'checked="checked"' : '' ?>
												       class="switcher cdata" />
												<label
													for="simple_custom_xml_cdata_logic_all"><?php esc_attr_e( 'Always wrap data in CDATA tags', 'wp_all_export_plugin' ) ?></label>
											</div>
											<div class="input" style="margin: 10px 0;">
												<input type="radio" id="simple_custom_xml_cdata_logic_never"
												       name="simple_custom_xml_cdata_logic"
												       value="never" <?php echo ( "never" == $post['custom_xml_cdata_logic'] ) ? 'checked="checked"' : '' ?>
												       class="switcher cdata" />
												<label
													for="simple_custom_xml_cdata_logic_never"><?php esc_attr_e( 'Never wrap data in CDATA tags', 'wp_all_export_plugin' ) ?></label>
												<div class="switcher-target-simple_custom_xml_cdata_logic_never"
												     style="padding-left:28px;">
													<p style="font-size: 12px;margin-top: 10px;"><?php esc_html_e( 'Warning: This may result in an invalid XML file', 'wp_all_export_plugin' ); ?></p>
												</div>
											</div>
											<div class="input" style="margin: 16px 4px;">
												<input type="checkbox" value="1"
												       id="simple_show_cdata_in_preview" <?php echo ( 1 == $post['show_cdata_in_preview'] ) ? 'checked="checked"' : '' ?>
												       class="show_cdata_in_preview" />
												<label for="simple_show_cdata_in_preview">Show CDATA tags in XML
													preview</label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- ExportToCsvBegin -->
						<div
							class="wpallexport-collapsed closed wpallexport-section wpallexport-csv-advanced-options export_to_csv" <?php if ( $post['export_to'] == 'xml' ) : ?> style="display: none;" <?php endif; ?> >
							<div class="wpallexport-content-section rad0"
							     style="margin:0; border-top:1px solid #ddd; border-bottom: none; border-right: none; border-left: none; background: #f1f2f2; padding-bottom: 15px; margin-top: 5px;">
								<div class="wpallexport-collapsed-header">
									<h3 style="color:#40acad;"><?php esc_html_e( 'Advanced Options', 'wp_all_export_plugin' ); ?></h3>
									<hr style="display:none; margin-right:25px;" />
								</div>
								<div class="wpallexport-collapsed-content" style="padding:0 0 0 5px;">
									<div class="wpallexport-collapsed-content-inner" style="padding-left: 5px;">
										<div class="simple_xml_template_options csv_delimiter" style="margin-top:20px;">
											<div class="input"
											     style="display: inline-block; max-width: 360px; width: 40%; margin-right: 10px;">
												<label
													style="width: 80px; margin-left: 20px;"><?php esc_html_e( 'Separator:', 'wp_all_export_plugin' ); ?></label>
												<input type="text" name="delimiter"
												       value="<?php echo esc_attr( $post['delimiter'] ) ?>"
												       style="width: 40px; height: 30px; top: 0px; text-align: center;" />
											</div>
											<div class="wp-all-export-additional-csv-options">
												<h4><?php esc_html_e( 'CSV Header Row', 'wp_all_export_plugin' ); ?></h4>
												<div class="input">
													<input type="hidden" name="include_header_row" value="0" />
													<input type="checkbox" id="include_header_row"
													       name="include_header_row" value="1"
													       style="margin-bottom: -4px"
													       <?php if ( $post['include_header_row'] ): ?>checked="checked"<?php endif; ?>
													       class="switcher" />
													<label
														for="include_header_row"><?php esc_html_e( "Include header row and column titles in export", "wp_all_export_plugin" ); ?></label>
												</div>

											</div>
										</div>

										<div style="margin-left: 20px;">
											<?php if ( ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() || XmlExportEngine::get_addons_service()->isWooCommerceProductAddonActive() ) && XmlExportWooCommerce::$is_active ) {
												include( 'variation_options.php' );
											}
											?>
										</div>
										<div
											class="wp-all-export-product-bundle-warning warning-only-export-parent-products"
											style="display:none;">
											<p><?php esc_html_e( "You will not be able to reimport data to the product variations, and you will not be able to import these products to another site.", 'wp_all_export_plugin' ); ?></p>
										</div>
										<div
											class="wp-all-export-product-bundle-warning warning-only-export-product-variations"
											style="display:none;">
											<p><?php esc_html_e( "You will not be able to reimport data to the parent products, and you will not be able to import these products to another site.", 'wp_all_export_plugin' ); ?></p>
										</div>
										<!-- Display each product in its own row -->
										<?php if ( ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() || XmlExportEngine::get_addons_service()->isWooCommerceOrderAddonActive() ) && XmlExportWooCommerceOrder::$is_active ): ?>
											<div class="input" style="float: left; margin-top: 15px; margin-left:20px;"
											     id="woo_commerce_order">
												<input type="hidden" name="order_item_per_row" value="0" />
												<input type="checkbox" id="order_item_per_row" name="order_item_per_row"
												       value="1"
												       <?php if ( $post['order_item_per_row'] ): ?>checked="checked"<?php endif; ?>
												       class="switcher" />
												<label
													for="order_item_per_row"><?php esc_html_e( "Display each product in its own row", "wp_all_export_plugin" ); ?></label>
												<span>
                                                    <a href="#help" class="wpallexport-help"
                                                       style="position: relative; top: 0px;"
                                                       title="<?php esc_html_e( 'If an order contains multiple products, each product will have its own row. If disabled, each product will have its own column.', 'wp_all_export_plugin' ); ?>">?</a>
												</span>
												<div class="input switcher-target-order_item_per_row"
												     style="margin-top: 10px; text-align:left;">
													<input type="hidden" name="order_item_fill_empty_columns"
													       value="0" />
													<input type="checkbox" id="order_item_fill_empty_columns"
													       name="order_item_fill_empty_columns" value="1"
													       <?php if ( $post['order_item_fill_empty_columns'] ): ?>checked="checked"<?php endif; ?>/>
													<label
														for="order_item_fill_empty_columns"><?php esc_html_e( "Fill in empty columns", "wp_all_export_plugin" ); ?></label>
													<span>
                                                        <a href="#help" class="wpallexport-help"
                                                           style="position: relative; top: 0px;"
                                                           title="<?php esc_html_e( 'If enabled, each order item will appear as its own row with all order info filled in for every column. If disabled, order info will only display on one row with only the order item info displaying in additional rows.', 'wp_all_export_plugin' ); ?>">?</a>
												    </span>
												</div>
											</div>
											<div class="clear"></div>
										<?php endif; ?>

										<div class="input" style="float: left; margin-top: 15px; margin-left:20px;"
										     id="csv_omit_empty_columns">
											<input type="hidden" name="csv_omit_empty_columns" value="0" />
											<input type="checkbox" id="csv_omit_empty_columns"
											       name="csv_omit_empty_columns"
											       value="1"
											       <?php if ( $post['csv_omit_empty_columns'] ): ?>checked="checked"<?php endif; ?>
											/>
											<label
												for="csv_omit_empty_columns"><?php esc_html_e( "Remove empty columns from export file", "wp_all_export_plugin" ); ?></label>
											<span>
                                                    <a href="#help" class="wpallexport-help"
                                                       style="position: relative; top: 0px;"
                                                       title="<?php esc_html_e( 'When enabled, any column without data for at least one row will be removed from the generated export file.', 'wp_all_export_plugin' ); ?>">?</a>
												</span>
										</div>
										<div class="clear"></div>
									</div>
								</div>
							</div>
						</div>
						<!-- ExporToCsvEnd -->
					</div>
				</div>

				<?php if ( class_exists( 'SitePress' ) ): ?>
					<div class="wpallexport-collapsed wpallexport-section wpallexport-file-options closed"
					     style="margin-top: 0px;">
						<div class="wpallexport-content-section" style="padding-bottom: 15px; margin-bottom: 10px;">
							<div class="wpallexport-collapsed-header" style="padding-left: 25px;">
								<h3><?php esc_html_e( 'WPML', 'wp_all_export_plugin' ); ?></h3>
							</div>
							<?php
							/** @var $post */
							/** @var string $random */
							$random = uniqid();
							?>
							<div class="wpallexport-collapsed-content"
							     style="padding: 0; overflow: hidden; min-height: 180px;">
								<div class="wpallexport-collapsed-content-inner">
									<div class="wp-all-export-wpml-options">
										<h4><?php esc_html_e( 'Language', 'wp_all_export_plugin' ); ?></h4>
										<div class="input">
											<?php foreach ( $wpml_options as $key => $value ): ?>
												<div class="input">
													<input type="radio" class="wpml_lang"
													       id="<?php echo esc_attr( $random ); ?>_wpml_lang_<?php echo esc_attr( $key ); ?>"
													       name="<?php echo esc_attr( $random ); ?>_wpml_lang"
													       value="<?php echo esc_attr( $key ); ?>"
													       <?php if ( $post['wpml_lang'] == $key ): ?>checked="checked"<?php endif; ?>
													       class="switcher" />
													<label
														for="<?php echo esc_attr( $random ); ?>_wpml_lang_<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $value ); ?></label>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" id="wpml_lang" name="wpml_lang"
					       value="<?php echo esc_attr( $post['wpml_lang'] ); ?>" />
				<?php endif; ?>
				
				<div class="wpallexport-collapsed wpallexport-section wpallexport-file-options closed"
				     style="margin-top: 0px;">
					<div class="wpallexport-content-section" style="padding-bottom: 15px; margin-bottom: 10px;">
						<div class="wpallexport-collapsed-header" style="padding-left: 25px;">
							<h3><?php esc_html_e( 'Export Type', 'wp_all_export_plugin' ); ?></h3>
						</div>
						<div class="wpallexport-collapsed-content" style="padding: 0; overflow: hidden; height: 305px;">
							<div class="wpallexport-collapsed-content-inner">
								<div class="wpallexport-choose-data-type">
									<h3 style="margin-top: 10px; margin-bottom: 40px;"><?php esc_html_e( 'Choose your export type', 'wp_all_export_plugin' ); ?></h3>
									<a href="javascript:void(0);"
									   class="wpallexport-import-to-format rad4 wpallexport-csv-type <?php if ( $post['export_to'] != XmlExportEngine::EXPORT_TYPE_XML ) {
										   echo 'selected';
									   } ?>">
										<span
											class="wpallexport-import-to-title"><?php esc_html_e( 'Spreadsheet', 'wp_all_export_plugin' ); ?></span>
										<span class="wpallexport-import-to-arrow"></span>
									</a>
									<a href="javascript:void(0);"
									   class="wpallexport-import-to-format rad4 wpallexport-xml-type <?php if ( $post['export_to'] == XmlExportEngine::EXPORT_TYPE_XML ) {
										   echo 'selected';
									   } ?>" style="margin-right:0;">
										<span
											class="wpallexport-import-to-title"><?php esc_html_e( 'Feed', 'wp_all_export_plugin' ); ?></span>
										<span class="wpallexport-import-to-arrow"></span>
									</a>
								</div>

								<div class="wpallexport-all-options">
									<input type="hidden" name="export_to"
									       value="<?php echo esc_attr( $post['export_to'] ); ?>" />

									<div class="wpallexport-file-format-options">

										<div class="wpallexport-csv-options"
										     style="<?php if ( $post['export_to'] == XmlExportEngine::EXPORT_TYPE_XML || $post['export_to'] == XmlExportEngine::EXPORT_TYPE_GOOLE_MERCHANTS ) {
											     echo 'display:none;';
										     } ?>">
											<!-- Export File Format -->
											<div class="input">
												<select name="export_to_sheet" id="export_to_sheet">
													<option value="csv" <?php if ( $post['export_to_sheet'] == 'csv' ) {
														echo 'selected="selected"';
													} ?>><?php esc_html_e( 'CSV File', 'wp_all_export_plugin' ); ?></option>
													<option value="xls" <?php if ( $post['export_to_sheet'] == 'xls' ) {
														echo 'selected="selected"';
													} ?>><?php esc_html_e( 'Excel File (XLS)', 'wp_all_export_plugin' ); ?></option>
													<option
														value="xlsx" <?php if ( $post['export_to_sheet'] == 'xlsx' ) {
														echo 'selected="selected"';
													} ?>><?php esc_html_e( 'Excel File (XLSX)', 'wp_all_export_plugin' ); ?></option>
												</select>
											</div>
											<div class="clear"></div>
										</div>

										<div
											class="wpallexport-xml-options" <?php if ( $post['export_to'] != XmlExportEngine::EXPORT_TYPE_XML ) {
											echo 'style="display:none;"';
										} ?>>
											<div class="input">
												<select name="xml_template_type" class="xml_template_type">
													<option
														value="simple" <?php if ( $post['xml_template_type'] == 'simple' ) {
														echo 'selected="selected"';
													} ?>><?php esc_html_e( 'Simple XML Feed', 'wp_all_export_plugin' ); ?></option>
													<option
														value="custom" <?php if ( $post['export_to'] === 'xml' && $post['xml_template_type'] == 'custom' ) {
														echo 'selected="selected"';
													} ?>><?php esc_html_e( 'Custom XML Feed', 'wp_all_export_plugin' ); ?></option>
													<?php
													if ( in_array( 'product', $post['cpt'] ) ) {
														?>
														<option
															value="<?php echo esc_attr( XmlExportEngine::EXPORT_TYPE_GOOLE_MERCHANTS ); ?>" <?php if ( $post['xml_template_type'] == XmlExportEngine::EXPORT_TYPE_GOOLE_MERCHANTS ) {
															echo 'selected="selected"';
														} ?>><?php esc_html_e( 'Google Merchant Center Product Feed', 'wp_all_export_plugin' ); ?></option>
														<?php
													}
													?>
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Google Merchants -->
				<?php include( __DIR__ . '/google.php' ); ?>

				<div class="error inline" id="validationError" style="display: none;">
					<p>

					</p>
				</div>

				<div class="wpallexport-collapsed wpallexport-section wpallexport-custom-xml-template">
					<div class="wpallexport-content-section" style="padding-bottom: 0; margin-bottom: 10px;">
						<div class="wpallexport-collapsed-header" style="margin-bottom: 15px;">
							<h3><?php esc_html_e( 'XML Editor', 'wp_all_export_plugin' ); ?></h3>
						</div>
						<div class="wpallexport-collapsed-content" style="padding: 0;">
							<div class="wpallexport-collapsed-content-inner" style="padding-top: 5px;">

								<?php $default_template = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<data>\n\t<!-- BEGIN LOOP -->\n\t<post>\n\n\t</post>\n\t<!-- END LOOP -->\n</data>"; ?>

								<textarea id="wp_all_export_custom_xml_template"
								          name="custom_xml_template"><?php echo ( empty( $post['custom_xml_template'] ) ) ? esc_textarea( $default_template ) : esc_textarea( $post['custom_xml_template'] ); ?></textarea>

								<div class="input" style="overflow: hidden; margin-top: 10px; margin-bottom: -20px;">
									<!-- Help Button -->
									<div class="input" style="float: left;">
										<input type="button"
										       value="<?php esc_html_e( 'Help', 'wp_all_export_plugin' ); ?>"
										       class="help_custom_xml">
									</div>
									<!-- Preview a Row Button -->
									<div class="input" style="float: right;">
										<input type="button"
										       value="<?php esc_html_e( 'Preview', 'wp_all_export_plugin' ); ?>"
										       class="preview_a_custom_xml_row">
									</div>
								</div>
							</div>
							<div class="wpallexport-collapsed closed wpallexport-section">
								<div class="wpallexport-content-section rad0"
								     style="margin:0; border-top:1px solid #ddd; border-bottom: none; border-right: none; border-left: none; background: #f1f2f2; padding-bottom: 15px; margin-top: 5px;">
									<div class="wpallexport-collapsed-header">
										<h3 style="color:#40acad;"><?php esc_html_e( 'Advanced Options', 'wp_all_export_plugin' ); ?></h3>
										<hr style="display: none; margin-right: 25px;" />
									</div>
									<div class="wpallexport-collapsed-content" style="padding: 0 0 0 5px;">
										<div class="wpallexport-collapsed-content-inner">
											<?php if ( ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() || XmlExportEngine::get_addons_service()->isWooCommerceProductAddonActive() ) && XmlExportWooCommerce::$is_active ) {
												include( 'variation_options.php' );
											} ?>
											<div
												class="wp-all-export-product-bundle-warning warning-only-export-parent-products"
												style="display:none;">
												<p><?php esc_html_e( "You will not be able to reimport data to the product variations, and you will not be able to import these products to another site.", 'wp_all_export_plugin' ); ?></p>
											</div>
											<div
												class="wp-all-export-product-bundle-warning warning-only-export-product-variations"
												style="display:none;">
												<p><?php esc_html_e( "You will not be able to reimport data to the parent products, and you will not be able to import these products to another site.", 'wp_all_export_plugin' ); ?></p>
											</div>
											<div class="input cdata">
												<h4 style="margin-top: 20px;">CDATA</h4>
												<p style="font-size: 12px;margin-top: 8px;"><?php echo sprintf( __( "There are certain characters that cannot be included in an XML file unless they are wrapped in CDATA tags.<br/>Read more about CDATA tags: <a target='_blank' href='%s' style='font-size: 12px;'>%s</a>.", 'wp_all_export_plugin' ), 'https://en.wikipedia.org/wiki/CDATA', 'https://en.wikipedia.org/wiki/CDATA' ); ?></p>
												<div class="input" style="margin: 10px 0;">
													<input type="radio" id="custom_xml_cdata_logic_auto"
													       name="custom_custom_xml_cdata_logic"
													       value="auto" <?php echo ( "auto" == $post['custom_xml_cdata_logic'] ) ? 'checked="checked"' : '' ?>
													       class="switcher" />
													<label
														for="custom_xml_cdata_logic_auto"><?php esc_html_e( 'Automatically wrap data in CDATA tags when it contains illegal characters', 'wp_all_export_plugin' ) ?></label>
												</div>
												<div class="input" style="margin: 10px 0;">
													<input type="radio" id="custom_custom_xml_cdata_logic_all"
													       name="custom_custom_xml_cdata_logic"
													       value="all" <?php echo ( "all" == $post['custom_xml_cdata_logic'] ) ? 'checked="checked"' : '' ?>
													       class="switcher cdata" />
													<label
														for="custom_custom_xml_cdata_logic_all"><?php esc_html_e( 'Always wrap data in CDATA tags', 'wp_all_export_plugin' ) ?></label>
												</div>
												<div class="input" style="margin: 10px 0;">
													<input type="radio" id="custom_custom_xml_cdata_logic_never"
													       name="custom_custom_xml_cdata_logic"
													       value="never" <?php echo ( "never" == $post['custom_xml_cdata_logic'] ) ? 'checked="checked"' : '' ?>
													       class="switcher cdata" />
													<label
														for="custom_custom_xml_cdata_logic_never"><?php esc_html_e( 'Never wrap data in CDATA tags', 'wp_all_export_plugin' ) ?></label>
													<div class="switcher-target-simple_custom_xml_cdata_logic_never"
													     style="padding-left:28px;">
														<p style="font-size: 12px;margin-top: 10px;"><?php esc_html_e( 'Warning: This may result in an invalid XML file', 'wp_all_export_plugin' ); ?></p>
													</div>
												</div>
												<div class="input" style="margin: 16px 4px;">
													<input type="checkbox" value="1" name="custom_show_cdata_in_preview"
													       id="custom_show_cdata_in_preview" <?php echo ( 1 == $post['show_cdata_in_preview'] ) ? 'checked="checked"' : '' ?>
													       class="show_cdata_in_preview" />
													<label for="custom_show_cdata_in_preview">Show CDATA tags in XML
														preview</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<?php
				$uploads           = wp_upload_dir();
				$functions         = $uploads['basedir'] . DIRECTORY_SEPARATOR . WP_ALL_EXPORT_UPLOADS_BASE_DIRECTORY . DIRECTORY_SEPARATOR . 'functions.php';
				$functions         = apply_filters( 'wp_all_export_functions_file_path', $functions );
				$functions_content = file_get_contents( $functions );
				?>

				<div class="wpallexport-collapsed closed wpallexport-section wpallexport-function-editor">
					<div class="wpallexport-content-section" style="padding-bottom: 15px; margin-bottom: 10px;">
						<div class="wpallexport-collapsed-header">
							<h3><?php esc_html_e( 'Function Editor', 'wp_all_export_plugin' ); ?></h3>
						</div>
						<div class="wpallexport-collapsed-content" style="padding: 0;">
							<div class="wpallexport-collapsed-content-inner">

								<?php
								require( PMXE_Plugin::ROOT_DIR . '/views/admin/shared/function_editor.php' );
								?>
							</div>
						</div>
					</div>
				</div>

				<hr>

				<div class="input wpallexport-section load-template-container" style="padding-left: 8px;">

					<p style="margin: 11px; float: left;">
						<input type="checkbox" id="save_template_as" name="save_template_as"
						       class="switcher-horizontal fix_checkbox"
						       value="1" <?php echo ( ! empty( $post['save_template_as'] ) ) ? 'checked="checked"' : '' ?> />
						<label
							for="save_template_as"><?php esc_html_e( 'Save settings as a template', 'wp_all_export_plugin' ); ?></label>
					</p>
					<div class="switcher-target-save_template_as" style="float: left; overflow: hidden;">
						<input type="text" name="name"
						       placeholder="<?php esc_html_e( 'Template name...', 'wp_all_export_plugin' ) ?>"
						       style="vertical-align:middle; line-height: 26px;"
						       value="<?php echo esc_attr( $post['name'] ) ?>" />
					</div>
					<?php $templates = new PMXE_Template_List(); ?>
					<div class="load-template">
						<select name="load_template" id="load_template">
							<option value=""><?php esc_html_e( 'Load Template...', 'wp_all_export_plugin' ) ?></option>
							<?php foreach ( $templates->getBy()->convertRecords() as $t ): ?>
								<?php
								// When creating a new export you should be able to select existing saved export templates that were created for the same post type.
								if ( $t->options['cpt'] != $post['cpt'] ) {
									continue;
								}
								?>
								<option
									value="<?php echo esc_attr( $t->id ) ?>"><?php echo esc_html( wp_all_export_clear_xss( $t->name ) ); ?></option>
							<?php endforeach ?>
						</select>
					</div>

				</div>
				
				<hr>
				<div
					class="input custom_xml_upgrade_notice wpallexport-free-edition-notice wpallexport-google-merchants-template"
					style="vertical-align:middle; position: relative; top: -5px;">
					<span class="" style="margin: 0 0 10px;">
						<p><?php esc_html_e( 'The WooCommerce Export Add-On Pro Is Required to Export to Google Merchant Center.', 'wp_all_export_plugin' ); ?></p>
						<a class="upgrade_link" target="_blank"
						   href="https://www.wpallimport.com/portal/discounts/?utm_source=export-plugin-pro&utm_medium=upgrade-notice&utm_campaign=export-google-merchants"><?php esc_html_e( 'Click here to purchase the WooCommerce Export Add-On', 'wp_all_export_plugin' ); ?></a>
					</span>
				</div>
				<div class="wpallexport-submit-buttons">

					<div style="text-align:center; width:100%;">
						<?php wp_nonce_field( 'template', '_wpnonce_template' ); ?>
						<input type="hidden" name="is_submitted" value="1" />
						<input type="hidden" id="dismiss_warnings"
						       value="<?php echo esc_attr( $dismiss_warnings ); ?>" />
						<?php if ( ! $this->isWizard ): ?>
							<a href="<?php echo esc_url( remove_query_arg( 'id', remove_query_arg( 'action', $this->baseUrl ) ) ); ?>"
							   class="back rad3"
							   style="float:none;"><?php esc_html_e( 'Back to Manage Exports', 'wp_all_export_plugin' ) ?></a>
						<?php else: ?>
							<a href="<?php echo esc_url_raw( add_query_arg( 'action', 'index', $this->baseUrl ) ); ?>"
							   class="back rad3"><?php esc_html_e( 'Back', 'wp_all_export_plugin' ) ?></a>
						<?php endif; ?>
						<input type="submit" class="button button-primary button-hero wpallexport-large-button"
						       value="<?php esc_html_e( ( $this->isWizard ) ? 'Continue' : ( isset( $post['enable_real_time_exports'] ) && $post['enable_real_time_exports'] ? 'Save &amp; Run Export' : 'Update Template' ), 'wp_all_export_plugin' ) ?>" />
					</div>

				</div>
				<a href="http://soflyy.com/" target="_blank"
				   class="wpallexport-created-by"><?php esc_html_e( 'Created by', 'wp_all_export_plugin' ); ?>
					<span></span></a>

			</form>

		</td>

		<td class="right template-sidebar" style="position: relative; width: 18%; right: 0px; padding: 0;">

			<fieldset id="available_data" class="optionsset rad4 wpae_available_data">

				<div class="title"><?php esc_html_e( 'Available Data', 'wp_all_export_plugin' ); ?></div>

				<div class="wpallexport-xml resetable">

					<ul>

						<?php
						// The contents are sanitized in the view
						echo $available_data_view;
						?>

					</ul>

				</div>

			</fieldset>
		</td>
	</tr>

</table>

<fieldset class="optionsset column rad4 wp-all-export-edit-column">

	<div class="title"><span
			class="wpallexport-add-row-title"
			style="font-size: 14px;"><?php esc_html_e( 'Add Field To Export', 'wp_all_export_plugin' ); ?></span><span
			class="wpallexport-edit-row-title"
			style="font-size: 14px;"><?php esc_html_e( 'Edit Export Field', 'wp_all_export_plugin' ); ?></span></div>

	<?php include_once 'template/add_new_field.php'; ?>

</fieldset>

<fieldset class="optionsset column rad4 wp-all-export-custom-xml-help">

	<div class="title"><span style="font-size:1.5em;"
	                         class="wpallexport-add-row-title"><?php esc_html_e( 'Custom XML Feeds', 'wp_all_export_plugin' ); ?></span><span
			class="wpallexport-edit-row-title"><?php esc_html_e( 'Edit Export Field', 'wp_all_export_plugin' ); ?></span>
	</div>

	<?php include_once 'template/custom_xml_help.php'; ?>

</fieldset>

<div class="wpallexport-overlay"></div>
