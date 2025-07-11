<?php

final class PMXE_Wpallimport {
	/**
	 * Singletone instance
	 * @var PMXE_Wpallimport
	 */
	protected static $instance;

	/**
	 * Return singletone instance
	 * @return PMXE_Wpallimport
	 */
	static public function getInstance() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
	}

	public static function create_an_import( &$export ) {

		if ( $export->options['is_generate_import'] && wp_all_export_is_compatible() && ( ! isset( $export->options['enable_real_time_exports'] ) || ! $export->options['enable_real_time_exports'] ) ) {

			$import = new PMXI_Import_Record();

			if ( ! empty( $export->options['import_id'] ) ) {
				$import->getById( $export->options['import_id'] );
			}

			if ( $import->isEmpty() ) {
				$import->set( array(
					'parent_import_id' => 99999,
					'xpath'            => '/',
					'type'             => 'upload',
					'options'          => array( 'empty' ),
					'root_element'     => 'root',
					'path'             => 'path',
					'imported'         => 0,
					'created'          => 0,
					'updated'          => 0,
					'skipped'          => 0,
					'deleted'          => 0,
					'iteration'        => 1,
				) )->save();

				if ( ! empty( PMXE_Plugin::$session ) and PMXE_Plugin::$session->has_session() ) {
					PMXE_Plugin::$session->set( 'import_id', $import->id );
				}
				$options              = $export->options;
				$options['import_id'] = $import->id;

				$export->set( array(
					'options' => $options,
				) )->save();
			} else {
				if ( $import->parent_import_id != 99999 ) {
					$newImport = new PMXI_Import_Record();

					$newImport->set( array(
						'parent_import_id' => 99999,
						'xpath'            => '/',
						'type'             => 'upload',
						'options'          => array( 'empty' ),
						'root_element'     => 'root',
						'path'             => 'path',
						'imported'         => 0,
						'created'          => 0,
						'updated'          => 0,
						'skipped'          => 0,
						'deleted'          => 0,
						'iteration'        => 1,
					) )->save();

					if ( ! empty( PMXE_Plugin::$session ) and PMXE_Plugin::$session->has_session() ) {
						PMXE_Plugin::$session->set( 'import_id', $newImport->id );
					}

					$options              = $export->options;
					$options['import_id'] = $newImport->id;

					$export->set( array(
						'options' => $options,
					) )->save();
				} else {
					global $wpdb;
					$post = new PMXI_Post_List();
					$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $post->getTable() . ' WHERE import_id = %s', $import->id ) );
				}
			}
		}
	}

	public static $templateOptions = array();

	public static function generateImportTemplate( &$export, $file_path = '', $foundPosts = 0, $link_to_import = true ) {
		$exportOptions = $export->options;

		$custom_type = ( empty( $exportOptions['cpt'] ) ) ? 'post' : $exportOptions['cpt'][0];

		if ( $custom_type == 'shop_review' ) {
			$custom_type = 'woo_reviews';
		}

		if ( XmlExportEngine::$is_custom_addon_export ) {
			$custom_type = 'gf_entries';
		}

		// Do not create an import template for WooCommerce Refunds
		if ( $export->options['export_to'] == 'xml' && in_array( $export->options['xml_template_type'], array(
				'custom',
				'XmlGoogleMerchants',
			) ) ) {
			return false;
		}

		// Generate template for WP All Import	
		if ( $exportOptions['is_generate_templates'] ) {
			self::$templateOptions = array(
				'type'                           => ( ! empty( $exportOptions['cpt'] ) and $exportOptions['cpt'][0] == 'page' ) ? 'page' : 'post',
				'wizard_type'                    => 'new',
				'deligate'                       => 'wpallexport',
				'custom_type'                    => ( XmlExportEngine::$is_user_export ) ? 'import_users' : $custom_type,
				'status'                         => 'xpath',
				'is_multiple_page_parent'        => 'no',
				'unique_key'                     => '',
				'acf'                            => array(),
				'fields'                         => array(),
				'is_multiple_field_value'        => array(),
				'multiple_value'                 => array(),
				'fields_delimiter'               => array(),
				'update_all_data'                => 'no',
				'is_update_status'               => 0,
				'is_update_title'                => 0,
				'is_update_author'               => 0,
				'is_update_slug'                 => 0,
				'is_update_content'              => 0,
				'is_update_excerpt'              => 0,
				'is_update_dates'                => 0,
				'is_update_menu_order'           => 0,
				'is_update_parent'               => 0,
				'is_update_attachments'          => 0,
				'is_update_acf'                  => 0,
				'is_update_comment_status'       => 0,
				'is_update_comment_post_id'      => 0,
				'is_update_comment_author'       => 0,
				'is_update_comment_author_email' => 0,
				'is_update_comment_author_url'   => 0,
				'is_update_comment_author_IP'    => 0,
				'is_update_comment_karma'        => 0,
				'is_update_comment_approved'     => 0,
				'is_update_comment_verified'     => 0,
				'is_update_comment_rating'       => 0,
				'is_update_comment_agent'        => 0,
				'is_update_comment_user_id'      => 0,
				'is_update_comment_type'         => 0,
				'import_img_tags'                => 1,
				'update_acf_logic'               => 'only',
				'acf_list'                       => '',
				'is_update_product_type'         => 1,
				'is_update_attributes'           => 0,
				'update_attributes_logic'        => 'only',
				'attributes_list'                => '',
				'is_update_images'               => 0,
				'is_update_custom_fields'        => 0,
				'update_custom_fields_logic'     => 'only',
				'custom_fields_list'             => '',
				'is_update_categories'           => 0,
				'update_categories_logic'        => 'only',
				'taxonomies_list'                => '',
				'export_id'                      => $export->id,
			);

			$addons = \XmlExportEngine::get_addons();
			foreach ( $addons as $addon ) {
				self::$templateOptions[ $addon ]                = [];
				self::$templateOptions[ $addon . '_groups' ]    = [];
				self::$templateOptions[ $addon . '_switchers' ] = [];
				self::$templateOptions[ $addon . '_multiple' ]  = [];
			}

			if ( XmlExportEngine::$is_custom_addon_export ) {

				$gf_addon      = \GF_Export_Add_On::get_instance();
				$sub_post_type = $gf_addon->add_on->get_sub_post_type();

				if ( class_exists( 'GFAPI' ) ) {
					$form                                        = GFAPI::get_form( $sub_post_type );
					self::$templateOptions['gravity_form_title'] = $form['title'];
				}
			}

			if ( in_array( 'product', $exportOptions['cpt'] ) ) {
				$default = array(
					'is_multiple_product_type'                    => 'yes',
					'multiple_product_type'                       => 'simple',
					'is_product_virtual'                          => 'no',
					'is_product_downloadable'                     => 'no',
					'is_product_enabled'                          => 'yes',
					'is_variation_enabled'                        => 'yes',
					'is_product_featured'                         => 'no',
					'is_product_visibility'                       => 'visible',
					'is_multiple_product_tax_status'              => 'yes',
					'multiple_product_tax_status'                 => 'none',
					'is_multiple_product_tax_class'               => 'yes',
					'is_product_manage_stock'                     => 'no',
					'product_stock_status'                        => 'auto',
					'product_allow_backorders'                    => 'no',
					'product_sold_individually'                   => 'no',
					'is_multiple_product_shipping_class'          => 'yes',
					'is_multiple_grouping_product'                => 'yes',
					'is_product_enable_reviews'                   => 'no',
					'single_sale_price_dates_from'                => 'now',
					'single_sale_price_dates_to'                  => 'now',
					'product_files_delim'                         => ',',
					'product_files_names_delim'                   => ',',
					'matching_parent'                             => 'auto',
					'parent_indicator'                            => 'custom field',
					'missing_records_stock_status'                => 0,
					'is_variable_sale_price_shedule'              => 0,
					'is_variable_product_virtual'                 => 'no',
					'is_variable_product_manage_stock'            => 'no',
					'is_multiple_variable_product_shipping_class' => 'yes',
					'is_multiple_variable_product_tax_class'      => 'yes',
					'multiple_variable_product_tax_class'         => 'parent',
					'variable_stock_status'                       => 'instock',
					'variable_allow_backorders'                   => 'no',
					'is_variable_product_downloadable'            => 'no',
					'variable_product_files_delim'                => ',',
					'variable_product_files_names_delim'          => ',',
					'is_variable_product_enabled'                 => 'yes',
					'first_is_parent'                             => 'yes',
					'default_attributes_type'                     => 'first',
					'disable_sku_matching'                        => 1,
					'disable_prepare_price'                       => 1,
					'convert_decimal_separator'                   => 1,
					'grouping_indicator'                          => 'xpath',
					'is_update_product_type'                      => 1,
					'make_simple_product'                         => 0,
					'single_product_regular_price_adjust_type'    => '%',
					'single_product_sale_price_adjust_type'       => '%',
					'is_variation_product_manage_stock'           => 'no',
					'variation_stock_status'                      => 'auto',
				);

				self::$templateOptions = array_replace_recursive( self::$templateOptions, $default );

				self::$templateOptions['_virtual']                       = 1;
				self::$templateOptions['_downloadable']                  = 1;
				self::$templateOptions['put_variation_image_to_gallery'] = 1;
				self::$templateOptions['disable_auto_sku_generation']    = 1;
			}

			if ( in_array( 'shop_order', $exportOptions['cpt'] ) ) {
				self::$templateOptions['update_all_data']   = 'no';
				self::$templateOptions['is_update_status']  = 0;
				self::$templateOptions['is_update_dates']   = 0;
				self::$templateOptions['is_update_excerpt'] = 0;

				// $default = PMWI_Plugin::get_default_import_options();
				// self::$templateOptions['pmwi_order'] = $default['pmwi_order'];		
				self::$templateOptions['pmwi_order']                                     = array();
				self::$templateOptions['pmwi_order']['is_update_billing_details']        = 0;
				self::$templateOptions['pmwi_order']['is_update_shipping_details']       = 0;
				self::$templateOptions['pmwi_order']['is_update_payment']                = 0;
				self::$templateOptions['pmwi_order']['is_update_notes']                  = 0;
				self::$templateOptions['pmwi_order']['is_update_products']               = 0;
				self::$templateOptions['pmwi_order']['is_update_fees']                   = 0;
				self::$templateOptions['pmwi_order']['is_update_coupons']                = 0;
				self::$templateOptions['pmwi_order']['is_update_shipping']               = 0;
				self::$templateOptions['pmwi_order']['is_update_taxes']                  = 0;
				self::$templateOptions['pmwi_order']['is_update_refunds']                = 0;
				self::$templateOptions['pmwi_order']['is_update_total']                  = 0;
				self::$templateOptions['pmwi_order']['is_guest_matching']                = 1;
				self::$templateOptions['pmwi_order']['status']                           = 'wc-pending';
				self::$templateOptions['pmwi_order']['billing_source']                   = 'existing';
				self::$templateOptions['pmwi_order']['billing_source_match_by']          = 'username';
				self::$templateOptions['pmwi_order']['shipping_source']                  = 'guest';
				self::$templateOptions['pmwi_order']['copy_from_billing']                = 1;
				self::$templateOptions['pmwi_order']['products_repeater_mode']           = 'csv';
				self::$templateOptions['pmwi_order']['products_repeater_mode_separator'] = '|';
				self::$templateOptions['pmwi_order']['products_source']                  = 'existing';
				self::$templateOptions['pmwi_order']['fees_repeater_mode']               = 'csv';
				self::$templateOptions['pmwi_order']['fees_repeater_mode_separator']     = '|';
				self::$templateOptions['pmwi_order']['coupons_repeater_mode']            = 'csv';
				self::$templateOptions['pmwi_order']['coupons_repeater_mode_separator']  = '|';
				self::$templateOptions['pmwi_order']['shipping_repeater_mode']           = 'csv';
				self::$templateOptions['pmwi_order']['shipping_repeater_mode_separator'] = '|';
				self::$templateOptions['pmwi_order']['taxes_repeater_mode']              = 'csv';
				self::$templateOptions['pmwi_order']['taxes_repeater_mode_separator']    = '|';
				self::$templateOptions['pmwi_order']['order_total_logic']                = 'auto';
				self::$templateOptions['pmwi_order']['order_refund_date']                = 'now';
				self::$templateOptions['pmwi_order']['order_refund_issued_source']       = 'existing';
				self::$templateOptions['pmwi_order']['order_refund_issued_match_by']     = 'username';
				self::$templateOptions['pmwi_order']['notes_repeater_mode']              = 'csv';
				self::$templateOptions['pmwi_order']['notes_repeater_mode_separator']    = '|';
			}

			if ( XmlExportEngine::$is_user_export ) {
				self::$templateOptions['is_update_first_name']   = 0;
				self::$templateOptions['is_update_last_name']    = 0;
				self::$templateOptions['is_update_role']         = 0;
				self::$templateOptions['is_update_nickname']     = 0;
				self::$templateOptions['is_update_description']  = 0;
				self::$templateOptions['is_update_login']        = 0;
				self::$templateOptions['is_update_password']     = 0;
				self::$templateOptions['is_update_nicename']     = 0;
				self::$templateOptions['is_update_email']        = 0;
				self::$templateOptions['is_update_registered']   = 0;
				self::$templateOptions['is_update_display_name'] = 0;
				self::$templateOptions['is_update_url']          = 0;
			}

			if ( XmlExportEngine::$is_woo_review_export || XmlExportEngine::$is_comment_export ) {
				self::$templateOptions['is_update_comment_type'] = 1;
			}

			if ( XmlExportEngine::$is_taxonomy_export ) {
				self::$templateOptions['taxonomy_type'] = $exportOptions['taxonomy_to_export'];
			}

			self::prepare_import_template( $exportOptions );

			if ( in_array( 'product', $exportOptions['cpt'] ) ) {
				self::$templateOptions['single_page_parent'] = '';
				if ( ! empty( $exportOptions['export_variations'] ) && $exportOptions['export_variations'] == XmlExportEngine::VARIABLE_PRODUCTS_EXPORT_VARIATION ) {
					if ( $exportOptions['export_variations_title'] == XmlExportEngine::VARIATION_USE_PARENT_TITLE ) {
						self::$templateOptions['matching_parent'] = 'first_is_variation';
					}
					if ( $exportOptions['export_variations_title'] == XmlExportEngine::VARIATION_USE_DEFAULT_TITLE ) {
						self::$templateOptions['matching_parent'] = 'first_is_parent_id';
					}
					self::$templateOptions['create_new_records']     = 0;
					self::$templateOptions['is_update_product_type'] = 0;
				}
			}

			$tpl_options = self::$templateOptions;

			if ( 'csv' == $exportOptions['export_to'] ) {
				$tpl_options['delimiter']    = $exportOptions['delimiter'];
				$tpl_options['root_element'] = 'node';
			} else {
				$tpl_options['root_element'] = $exportOptions['record_xml_tag'];
			}

			$tpl_options['update_all_data']            = 'yes';
			$tpl_options['is_update_status']           = 1;
			$tpl_options['is_update_title']            = 1;
			$tpl_options['is_update_author']           = 1;
			$tpl_options['is_update_slug']             = 1;
			$tpl_options['is_update_content']          = 1;
			$tpl_options['is_update_excerpt']          = 1;
			$tpl_options['is_update_dates']            = 1;
			$tpl_options['is_update_menu_order']       = 1;
			$tpl_options['is_update_parent']           = 1;
			$tpl_options['is_update_attachments']      = 1;
			$tpl_options['is_update_acf']              = 1;
			$tpl_options['update_acf_logic']           = 'full_update';
			$tpl_options['acf_list']                   = '';
			$tpl_options['is_update_product_type']     = 1;
			$tpl_options['is_update_attributes']       = 1;
			$tpl_options['update_attributes_logic']    = 'full_update';
			$tpl_options['attributes_list']            = '';
			$tpl_options['is_update_images']           = 1;
			$tpl_options['is_update_custom_fields']    = 1;
			$tpl_options['update_custom_fields_logic'] = 'full_update';
			$tpl_options['custom_fields_list']         = '';
			$tpl_options['is_update_categories']       = 1;
			$tpl_options['update_categories_logic']    = 'full_update';
			$tpl_options['taxonomies_list']            = '';

			$tpl_data = array(
				'name'               => $exportOptions['template_name'],
				'is_keep_linebreaks' => 1,
				'is_leave_html'      => 0,
				'fix_characters'     => 0,
				'options'            => $tpl_options,
			);

			$exportOptions['tpl_data'] = $tpl_data;

			$export->set( array(
				'options' => $exportOptions,
			) )->save();

		}

		if ( $link_to_import && $export->options['is_generate_import'] && ( ! isset( $export->options['enable_real_time_exports'] ) || ! $export->options['enable_real_time_exports'] ) ) {
			self::link_template_to_import( $export, $file_path, $foundPosts );
		}
	}

	public static function link_template_to_import( &$export, $file_path, $foundPosts ) {

		$exportOptions = $export->options;

		// associate exported posts with new import
		if ( wp_all_export_is_compatible() ) {
			$options = self::$templateOptions + PMXI_Plugin::get_default_import_options();

			$import = new PMXI_Import_Record();

			$import->getById( $exportOptions['import_id'] );

			if ( ! $import->isEmpty() and $import->parent_import_id == 99999 ) {

				$xmlPath = $file_path;

				$root_element = '';

				$historyPath = $file_path;

				if ( 'csv' == $exportOptions['export_to'] ) {
					$is_secure_import = PMXE_Plugin::getInstance()->getOption( 'secure' );

					$options['delimiter'] = $exportOptions['delimiter'];

					include_once( PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportCsvParse.php' );

					$path_info = pathinfo( $xmlPath );

					$path_parts = explode( DIRECTORY_SEPARATOR, $path_info['dirname'] );

					$security_folder = array_pop( $path_parts );

					$wp_uploads = wp_upload_dir();

					$target = $is_secure_import ? $wp_uploads['basedir'] . DIRECTORY_SEPARATOR . PMXE_Plugin::UPLOADS_DIRECTORY . DIRECTORY_SEPARATOR . $security_folder : $wp_uploads['path'];

					$csv = new PMXI_CsvParser( array(
						'filename'  => $xmlPath,
						'targetDir' => $target,
						'delimiter' => $options['delimiter'],
					) );

					if ( ! in_array( $xmlPath, $exportOptions['attachment_list'] ) ) {
						$exportOptions['attachment_list'][] = $csv->xml_path;
					}

					$historyPath = $csv->xml_path;

					$root_element = 'node';

				} else {
					$root_element = apply_filters( 'wp_all_export_record_xml_tag', $exportOptions['record_xml_tag'], $export->id );
				}

				$import->set( array(
					'xpath'        => '/' . $root_element,
					'type'         => 'upload',
					'options'      => $options,
					'root_element' => $root_element,
					'path'         => $xmlPath,
					'name'         => basename( $xmlPath ),
					'imported'     => 0,
					'created'      => 0,
					'updated'      => 0,
					'skipped'      => 0,
					'deleted'      => 0,
					'iteration'    => 1,
					'count'        => $foundPosts,
				) )->save();

				// Get a list of all files linked to the related import.
				$history_file_list = new PMXI_File_List();
				$history_file_list->getBy( array( 'import_id' => $import->id ), 'id DESC' );

				// If there is more than one file linked, delete them all as something is wrong.
				// However, do not delete the linked files themselves as there should only be one file
				// and it should be the export file.
				if ( $history_file_list->total() > 1 ) {
					foreach ( $history_file_list->convertRecords() as $hs_file ) {
						// Delete each of the existing history files since we have more than expected.
						$hs_file->delete( false ); // Passing false is required otherwise the export file we just generated could be deleted.
					}
				}

				// Create a new file record for the new export file.
				// Try to get the current record if one exists.
				$history_file = new PMXI_File_Record();
				$history_file->getBy( array( 'import_id' => $import->id ), 'id DESC' );

				$history_file_data = array(
					'name'          => $import->name,
					'import_id'     => $import->id,
					'path'          => $historyPath,
					'registered_on' => date( 'Y-m-d H:i:s' ),
				);

				// Update the history file record if it exists, insert it otherwise.
				if ( ! $history_file->isEmpty() ) {
					$history_file->set( $history_file_data )->update();
				} else {
					$history_file->set( $history_file_data )->insert();
				}

				$exportOptions['import_id'] = $import->id;

				$export->set( array(
					'options' => $exportOptions,
				) )->save();
			}
		}
	}

	public static function prepare_import_template( $exportOptions ) {

		$options = $exportOptions;

		$is_xml_template = $options['export_to'] == 'xml';

		$required_add_ons = array();

		$cf_list   = array();
		$attr_list = array();
		$taxs_list = array();
		$acf_list  = array();

		$implode_delimiter = ( $options['delimiter'] == ',' ) ? '|' : ',';

		if ( ! empty( $options['is_user_export'] ) ) {
			self::$templateOptions['pmui']['import_users'] = 1;
		}

		foreach ( $options['ids'] as $ID => $value ) {
			if ( empty( $options['cc_type'][ $ID ] ) ) {
				continue;
			}

			if ( $is_xml_template ) {
				$element_name = ( ! empty( $options['cc_name'][ $ID ] ) ) ? str_replace( ':', '_', preg_replace( '/[^a-z0-9_:-]/i', '', $options['cc_name'][ $ID ] ) ) : 'untitled_' . $ID;
			} else {
				$element_name = strtolower( ( ! empty( $options['cc_name'][ $ID ] ) ) ? preg_replace( '/[^a-z0-9_]/i', '', $options['cc_name'][ $ID ] ) : 'untitled_' . $ID );
			}

			if ( empty( $element_name ) ) {
				$element_name = 'undefined' . $ID;
			}

			$element_type = $options['cc_type'][ $ID ];

			switch ( $element_type ) {
				case 'woo':

					if ( ! empty( $options['cc_value'][ $ID ] ) ) {
						if ( empty( $required_add_ons['PMWI_Plugin'] ) ) {
							$required_add_ons['PMWI_Plugin'] = array(
								'name' => 'WooCommerce Add-On Pro',
								'paid' => true,
								'url'  => 'http://www.wpallimport.com/woocommerce-product-import/',
							);
						}

						if ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() || XmlExportEngine::get_addons_service()->isWooCommerceProductAddonActive() ) {
							XmlExportWooCommerce::prepare_import_template( $options, self::$templateOptions, $cf_list, $attr_list, $element_name, $options['cc_label'][ $ID ] );
						}
					}

					break;

				case 'acf':
					if ( empty( $required_add_ons['PMAI_Plugin'] ) ) {
						$required_add_ons['PMAI_Plugin'] = array(
							'name' => 'ACF Add-On Pro',
							'paid' => true,
							'url'  => 'http://www.wpallimport.com/advanced-custom-fields/?utm_source=wordpress.org&utm_medium=wpai-import-template&utm_campaign=free+wp+all+export+plugin',
						);
					}

					$field_options = unserialize( $options['cc_options'][ $ID ] );

					// add ACF group ID to the template options
					if ( ! in_array( $field_options['group_id'], self::$templateOptions['acf'] ) ) {
						$group = get_post( $field_options['group_id'] );
						if ( ! empty( $group ) ) {
							self::$templateOptions['acf'][ $group->post_excerpt ] = 1;
						}
					}

					if ( XmlExportEngine::get_addons_service()->isAcfAddonActive() ) {
						self::$templateOptions['fields'][ $field_options['key'] ] = XmlExportACF::prepare_import_template( $options, self::$templateOptions, $acf_list, $element_name, $field_options );
					}

					break;


				default:

					$addons = new \Wpae\App\Service\Addons\AddonService();

					XmlExportCpt::prepare_import_template( $options, self::$templateOptions, $cf_list, $attr_list, $taxs_list, $element_name, $ID );

					XmlExportMediaGallery::prepare_import_template( $options, self::$templateOptions, $element_name, $ID );

					if ( $addons->isUserAddonActive() ) {
						if ( XmlExportEngine::$is_user_export ) {
							XmlExportUser::prepare_import_template( $options, self::$templateOptions, $element_name, $ID, $cf_list );
						}

						if ( XmlExportEngine::$is_woo_customer_export ) {
							XmlExportWooCommerceCustomer::prepare_import_template( $options, self::$templateOptions, $bill_list, $ship_list, $element_name, $ID );
						}

						XmlExportUser::prepare_import_template( $options, self::$templateOptions, $element_name, $ID, $cf_list );
					}


					if ( XmlExportEngine::$is_custom_addon_export ) {
						XmlExportCustomRecord::prepare_import_template( $options, self::$templateOptions, $element_name, $ID );
						if ( empty( $required_add_ons['PMAI_Plugin'] ) ) {
							$required_add_ons['PMGI_Plugin'] = array(
								'name' => 'Gravity Forms Add-On',
								'paid' => true,

								'url' => 'http://www.wpallimport.com/advanced-custom-fields/?utm_source=wordpress.org&utm_medium=wpai-import-template&utm_campaign=free+wp+all+export+plugin',
							);
						}
					}

					if ( XmlExportEngine::$is_comment_export ) {
						XmlExportComment::prepare_import_template( $options, self::$templateOptions, $element_name, $ID );
					}
					XmlExportTaxonomy::prepare_import_template( $options, self::$templateOptions, $element_name, $ID );

					if ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() ) {
						if ( XmlExportEngine::$is_woo_review_export ) {
							XmlExportWooCommerceReview::prepare_import_template( $options, self::$templateOptions, $element_name, $ID );
						}
					}
					if ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() || XmlExportEngine::get_addons_service()->isWooCommerceOrderAddonActive() ) {

						XmlExportWooCommerceOrder::prepare_import_template( $options, self::$templateOptions, $element_name, $ID );
					}

					// Run addon hooks
					foreach ( \XmlExportEngine::get_addons() as $addon ) {
						apply_filters( "pmxe_{$addon}_addon_prepare_import_template", $options, self::$templateOptions, $element_name, $ID );
					}

					$field_options = maybe_unserialize( $options['cc_options'][ $ID ] );

					foreach ( \XmlExportEngine::get_addons() as $addon ) {
						apply_filters( "pmxe_{$addon}_addon_prepare_import_template", $options, self::$templateOptions, $element_name, $ID );

						if ( isset( $field_options['addon'] ) && $field_options['addon'] == $addon ) {
							self::$templateOptions = apply_filters( "pmxe_{$addon}_addon_override_import_template", self::$templateOptions, $options, $element_name, $field_options );
						}
					}

					break;
			}
		}

		if ( ! empty( $cf_list ) ) {
			self::$templateOptions['is_update_custom_fields'] = 1;
			self::$templateOptions['custom_fields_list']      = $cf_list;
		}
		if ( ! empty( $attr_list ) ) {
			self::$templateOptions['is_update_attributes']    = 1;
			self::$templateOptions['update_attributes_logic'] = 'only';
			self::$templateOptions['attributes_list']         = $attr_list;
			self::$templateOptions['attributes_only_list']    = implode( ',', $attr_list );
		} else {
			self::$templateOptions['is_update_attributes'] = 0;
		}
		if ( ! empty( $taxs_list ) ) {
			self::$templateOptions['is_update_categories'] = 1;
			self::$templateOptions['taxonomies_list']      = $taxs_list;
		}
		if ( ! empty( $acf_list ) ) {
			self::$templateOptions['is_update_acf'] = 1;
			self::$templateOptions['acf_list']      = $acf_list;
		}

		self::$templateOptions['required_add_ons'] = $required_add_ons;
	}
}

PMXE_Wpallimport::getInstance();