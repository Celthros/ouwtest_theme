<?php

/**
 * Export configuration wizard
 *
 * @author Max Tsiplyakov <makstsiplyakov@gmail.com>
 */
class PMXE_Admin_Export extends PMXE_Controller_Admin {

	protected $isWizard = true; // indicates whether controller is in wizard mode (otherwise it called to be delegated an edit action)

	protected $isTemplateEdit = false;

	protected function init() {

		parent::init();

		if ( 'PMXE_Admin_Manage' == PMXE_Plugin::getInstance()->getAdminCurrentScreen()->base ) { // prereqisites are not checked when flow control is deligated
			$id                   = $this->input->get( 'id' );
			$this->data['export'] = $export = new PMXE_Export_Record();
			if ( ! $id or $export->getById( $id )->isEmpty() ) { // specified import is not found
				wp_redirect( esc_url_raw( add_query_arg( 'page', 'pmxe-admin-manage', admin_url( 'admin.php' ) ) ) );
				die();
			}
			$this->isWizard = false;
			$export->fix_template_options();
		} else {
			$action = PMXE_Plugin::getInstance()->getAdminCurrentScreen()->action;
			$this->_step_ready( $action );
		}

		// preserve id parameter as part of baseUrl
		$id = $this->input->get( 'id' ) and $this->baseUrl = esc_url_raw( add_query_arg( 'id', $id, $this->baseUrl ) );

	}

	public function set( $var, $val ) {
		$this->{$var} = $val;
	}

	public function get( $var ) {
		return $this->{$var};
	}

	/**
	 * Checks whether corresponding step of wizard is complete
	 *
	 * @param string $action
	 *
	 * @return bool
	 */
	protected function _step_ready( $action ) {

		// step #1: xml selction - has no prerequisites
		if ( 'index_action' == $action ) {
			return true;
		}

		if ( 'element_action' == $action ) {
			return true;
		}

		$this->data['update_previous'] = $update_previous = new PMXE_Export_Record();

		$update_previous->getById( PMXE_Plugin::$session->update_previous );

		if ( ! $update_previous->isEmpty() ) {
			$update_previous->fix_template_options();
		}

		if ( 'options_action' == $action ) {
			return true;
		}

		if ( ! PMXE_Plugin::$session->has_session() ) {
			wp_redirect_or_javascript( $this->baseUrl );
			die();
		}

		if ( 'process_action' == $action ) {
			return true;
		}

	}

	/**
	 * Step #1: Choose CPT
	 */
	public function index_action() {
		if ( $this->input->post( 'is_submitted' ) ) {
			check_admin_referer( 'choose-cpt', '_wpnonce_choose-cpt' );
		}

		$this->onlyAllowAdmin();

		$action = $this->input->get( 'action' );

		$DefaultOptions = array(
			'cpt'                     => '',
			'export_to'               => 'xml',
			'export_type'             => 'specific',
			'wp_query'                => '',
			'filter_rules_hierarhy'   => '',
			'product_matching_mode'   => 'strict',
			'wp_query_selector'       => 'wp_query',
			'auto_generate'           => 0,
			'taxonomy_to_export'      => '',
			'sub_post_type_to_export' => '',
			'created_at_version'      => PMXE_VERSION,
		);

		if ( ! in_array( $action, array( 'index' ) ) ) {
			PMXE_Plugin::$session->clean_session();
			$this->data['preload'] = false;
		} else {
			$DefaultOptions        = ( PMXE_Plugin::$session->has_session() ? PMXE_Plugin::$session->get_clear_session_data() : array() ) + $DefaultOptions;
			$this->data['preload'] = true;
		}

		$this->data['post'] = $post = $this->input->post( $DefaultOptions );

		if ( is_array( $this->data['post']['cpt'] ) ) {
			$this->data['post']['cpt'] = $this->data['post']['cpt'][0];
		}

		if ( is_dir( PMXE_ROOT_DIR . '/history/' ) ) {
			// Delete history
			$history_files = PMXE_Helper::safe_glob( PMXE_ROOT_DIR . '/history/*', PMXE_Helper::GLOB_RECURSE | PMXE_Helper::GLOB_PATH );
			if ( ! empty( $history_files ) ) {
				foreach ( $history_files as $filePath ) {
					@file_exists( $filePath ) and @unlink( $filePath );
				}
			}
		}

		if ( ! class_exists( 'XMLReader' ) or ! class_exists( 'XMLWriter' ) ) {
			$this->errors->add( 'form-validation', __( 'Required PHP components are missing.<br/><br/>WP All Export requires XMLReader, and XMLWriter PHP modules to be installed.<br/>These are standard features of PHP, and are necessary for WP All Export to write the files you are trying to export.<br/>Please contact your web hosting provider and ask them to install and activate the DOMDocument, XMLReader, and XMLWriter PHP modules.', 'wp_all_export_plugin' ) );
		}

		if ( $this->input->post( 'is_submitted' ) ) {

			PMXE_Plugin::$session->set( 'export_type', $post['export_type'] );
			PMXE_Plugin::$session->set( 'filter_rules_hierarhy', $post['filter_rules_hierarhy'] );
			PMXE_Plugin::$session->set( 'product_matching_mode', $post['product_matching_mode'] );
			PMXE_Plugin::$session->set( 'wp_query_selector', $post['wp_query_selector'] );
			PMXE_Plugin::$session->set( 'taxonomy_to_export', $post['taxonomy_to_export'] );
			PMXE_Plugin::$session->set( 'sub_post_type_to_export', $post['sub_post_type_to_export'] );
			PMXE_Plugin::$session->set( 'created_at_version', $post['created_at_version'] );

			if ( ! empty( $post['auto_generate'] ) ) {
				PMXE_Plugin::$session->set( 'auto_generate', 1 );
				$auto_generate = XmlCsvExport::auto_genetate_export_fields( $post, $this->errors );

				foreach ( $auto_generate as $key => $value ) {
					PMXE_Plugin::$session->set( $key, $value );
				}

				PMXE_Plugin::$session->save_data();
			} else {
				$engine = new XmlExportEngine( $post, $this->errors );
				$engine->init_additional_data();
			}
		}

		if ( $this->input->post( 'is_submitted' ) and ! $this->errors->get_error_codes() ) {

			PMXE_Plugin::$session->save_data();

			if ( ! empty( $post['auto_generate'] ) ) {
				wp_redirect( esc_url_raw( add_query_arg( [
					'action'           => 'options',
					'_wpnonce_options' => wp_create_nonce( 'options' ),
				], $this->baseUrl ) ) );
				die();
			} else {
				wp_redirect( esc_url_raw( add_query_arg( [
					'action'            => 'template',
					'_wpnonce_template' => wp_create_nonce( 'template' ),
				], $this->baseUrl ) ) );
				die();
			}

		}

		$this->render();
	}

	/**
	 * Step #2: Export Template
	 */
	public function template_action() {

		check_admin_referer( 'template', '_wpnonce_template' );


		$this->onlyAllowAdmin();

		$template = new PMXE_Template_Record();

		$default = PMXE_Plugin::get_default_import_options();

		$this->data['dismiss_warnings'] = 0;

		if ( $this->isWizard ) {
			// New export
			$DefaultOptions = ( PMXE_Plugin::$session->has_session() ? PMXE_Plugin::$session->get_clear_session_data() : array() ) + $default;
			$post           = $this->input->post( $DefaultOptions );
		} else {
			// Edit export
			$DefaultOptions = $this->data['export']->options + $default;

			if ( empty( $this->data['export']->options['export_variations'] ) ) {
				$DefaultOptions['export_variations'] = XmlExportEngine::VARIABLE_PRODUCTS_EXPORT_PARENT_AND_VARIATION;
			}
			if ( empty( $this->data['export']->options['export_variations_title'] ) ) {
				$DefaultOptions['export_variations_title'] = XmlExportEngine::VARIATION_USE_DEFAULT_TITLE;
			}
			$post              = $this->input->post( $DefaultOptions );
			$post['scheduled'] = $this->data['export']->scheduled;

			foreach ( $post as $key => $value ) {
				PMXE_Plugin::$session->set( $key, $value );
			}
			$this->data['dismiss_warnings'] = get_option( 'wpae_dismiss_warnings_' . $this->data['export']->id, 0 );
		}


		$max_input_vars = @ini_get( 'max_input_vars' );

		if ( ctype_digit( $max_input_vars ) && count( $_POST, COUNT_RECURSIVE ) >= $max_input_vars ) {
			$this->errors->add( 'form-validation', sprintf( __( 'You\'ve reached your max_input_vars limit of %d. Please contact your web host to increase it.', 'wp_all_export_plugin' ), $max_input_vars ) );
		}

		PMXE_Plugin::$session->save_data();

		$this->data['post'] =& $post;

		PMXE_Plugin::$session->set( 'is_loaded_template', '' );

		$this->data['engine'] = null;

		XmlExportEngine::$exportQuery = empty( PMXE_Plugin::$session->get( 'exportQuery' ) ) && ! empty( $this->data['export']->id ) ? \Wpae\WordPress\ExportQuery::getInstance()->generate( $DefaultOptions, $this->data['export']->id ) : PMXE_Plugin::$session->get( 'exportQuery' );

		if ( ( $load_template = $this->input->post( 'load_template' ) ) ) { // init form with template selected
			if ( ! $template->getById( $load_template )->isEmpty() ) {
				$template_options = $template->options;
				unset( $template_options['cpt'] );
				unset( $template_options['wp_query'] );
				unset( $template_options['filter_rules_hierarhy'] );
				unset( $template_options['product_matching_mode'] );
				unset( $template_options['wp_query_selector'] );
				$this->data['post'] = array_merge( $post, $template_options );
				PMXE_Plugin::$session->set( 'is_loaded_template', $load_template );
			}

		} elseif ( $this->input->post( 'is_submitted' ) ) {

			if ( empty( $post['cc_type'][0] ) && ! in_array( $post['xml_template_type'], array(
					'custom',
					'XmlGoogleMerchants',
				) ) ) {
				$this->errors->add( 'form-validation', esc_html__( 'You haven\'t selected any columns for export.', 'wp_all_export_plugin' ) );
			}

			if ( 'csv' == $post['export_to'] and '' == $post['delimiter'] ) {
				$this->errors->add( 'form-validation', esc_html__( 'CSV delimiter must be specified.', 'wp_all_export_plugin' ) );
			}

			if ( 'xml' == $post['export_to'] && ! in_array( $post['xml_template_type'], array(
					'custom',
					'XmlGoogleMerchants',
				) ) ) {
				$post['main_xml_tag'] = preg_replace( '/[^a-z0-9_]/i', '', $post['main_xml_tag'] );
				if ( empty( $post['main_xml_tag'] ) ) {
					$this->errors->add( 'form-validation', esc_html__( 'Main XML Tag is required.', 'wp_all_export_plugin' ) );
				}

				$post['record_xml_tag'] = preg_replace( '/[^a-z0-9_]/i', '', $post['record_xml_tag'] );
				if ( empty( $post['record_xml_tag'] ) ) {
					$this->errors->add( 'form-validation', esc_html__( 'Single Record XML Tag is required.', 'wp_all_export_plugin' ) );
				}

				if ( $post['main_xml_tag'] == $post['record_xml_tag'] ) {
					$this->errors->add( 'form-validation', esc_html__( 'Main XML Tag equals to Single Record XML Tag.', 'wp_all_export_plugin' ) );
				}
			}

			if ( ( $post['export_to'] == XmlExportEngine::EXPORT_TYPE_XML ) && in_array( $post['xml_template_type'], array(
					'custom',
					'XmlGoogleMerchants',
				) ) ) {

				if ( empty( $post['custom_xml_template'] ) ) {
					$this->errors->add( 'form-validation', esc_html__( 'XML template is empty.', 'wp_all_export_plugin' ) );
				}

				// Convert Custom XML template to default
				if ( ! empty( $post['custom_xml_template'] ) ) {

					$post['custom_xml_template'] = str_replace( "<!-- BEGIN POST LOOP -->", "<!-- BEGIN LOOP -->", $post['custom_xml_template'] );
					$post['custom_xml_template'] = str_replace( "<!-- END POST LOOP -->", "<!-- END LOOP -->", $post['custom_xml_template'] );

					$this->data['engine'] = new XmlExportEngine( $post, $this->errors );

					$this->data['engine']->init_additional_data();

					$this->data = array_merge( $this->data, $this->data['engine']->init_available_data() );

					$result = $this->data['engine']->parse_custom_xml_template();

					if ( ! $this->errors->get_error_codes() ) {
						$post = array_merge( $post, $result );
					}
				}
			}

			if ( ! $this->errors->get_error_codes() ) {

				if ( ! empty( $post['name'] ) and ! empty( $post['save_template_as'] ) ) { // save template in database
					$template->getByName( $post['name'] )->set( array(
						'name'    => $post['name'],
						'options' => $post,
					) )->save();
					PMXE_Plugin::$session->set( 'saved_template', $template->id );
				}

				if ( $this->isWizard ) {
					foreach ( $this->data['post'] as $key => $value ) {
						PMXE_Plugin::$session->set( $key, $value );
					}
					PMXE_Plugin::$session->save_data();
					wp_redirect( esc_url_raw( add_query_arg( [
						'action'           => 'options',
						'_wpnonce_options' => wp_create_nonce( 'options' ),
					], $this->baseUrl ) ) );
					die();
				} else {
					$this->data['export']->set( array(
						'options'            => $post,
						'settings_update_on' => date( 'Y-m-d H:i:s' ),
					) )->save();

					if ( ! empty( $post['friendly_name'] ) ) {
						if ( current_user_can( PMXE_Plugin::$capabilities ) ) {
							$this->data['export']->set( array(
								'friendly_name' => $post['friendly_name'],
								'scheduled'     => ( ( $post['is_scheduled'] ) ? $post['scheduled_period'] : '' ),
							) )->save();
						}
					}

					if ( isset( $this->data['export']['options']['enable_real_time_exports'] ) && $this->data['export']['options']['enable_real_time_exports'] ) {
						wp_redirect( esc_url_raw( add_query_arg( array(
							                                         'page'   => 'pmxe-admin-export',
							                                         'id'     => $this->data['export']->id,
							                                         'action' => 'process',
						                                         ) + array_intersect_key( $_GET, array_flip( $this->baseUrlParamNames ) ), admin_url( 'admin.php' ) ) ) );

					} else {
						wp_redirect( esc_url_raw( add_query_arg( array(
							                                         'page'             => 'pmxe-admin-manage',
							                                         'pmxe_nt'          => urlencode( __( 'Options updated', 'wp_all_export_plugin' ) ),
							                                         '_wpnonce_options' => wp_create_nonce( 'options' ),
						                                         ) + array_intersect_key( $_GET, array_flip( $this->baseUrlParamNames ) ), admin_url( 'admin.php' ) ) ) );
					}
					die();
				}
			}
		}

		if ( empty( $this->data['engine'] ) ) {

			$this->data['engine'] = new XmlExportEngine( $post, $this->errors );

			$this->data['engine']->init_additional_data();

			$this->data = array_merge( $this->data, $this->data['engine']->init_available_data() );
		}

		$this->data['available_data_view'] = $this->data['engine']->render();

		$this->data['available_fields_view'] = $this->data['engine']->render_new_field();

		if ( class_exists( 'SitePress' ) ) {
			global $sitepress;
			$langs = $sitepress->get_active_languages();
			if ( ! empty( $langs ) ) {
				// prepare active languages list
				$language_list = array( 'all' => 'All' );
				foreach ( $langs as $code => $langInfo ) {
					$language_list[ $code ] = "<img width='18' height='12' src='" . $sitepress->get_flag_url( $code ) . "' style='position:relative; top: 2px;'/> " . $langInfo['display_name'];
					if ( isset( $this->default_language ) ) {
						if ( $code == $this->default_language ) {
							$language_list[ $code ] .= ' ( <strong>default</strong> )';
						}
					}

				}
			}
			$this->data['wpml_options'] = $language_list;
		}

		$this->render();
	}

	/**
	 * Step #3: Export Options
	 */
	public function options_action() {
		check_admin_referer( 'options', '_wpnonce_options' );

		$this->onlyAllowAdmin();

		$default = PMXE_Plugin::get_default_import_options();

		if ( $this->isWizard ) {

			$DefaultOptions = ( PMXE_Plugin::$session->has_session() ? PMXE_Plugin::$session->get_clear_session_data() : array() ) + $default;
			$post           = $this->input->post( $DefaultOptions );

			if ( isset( $post['update_previous'] ) ) {
				$exportId = $post['update_previous'];
			} else {
				$exportId = false;
			}

			if ( $post['enable_real_time_exports'] ) {
				$post['creata_a_new_export_file']         = 1;
				$post['enable_real_time_exports_running'] = 1;
			}

			if ( ! $exportId ) {
				$export = $this->data['update_previous'];
				$export->set( array(
						'triggered'           => 0,
						'processing'          => 0,
						'exported'            => 0,
						'executing'           => 0,
						'canceled'            => 0,
						'options'             => $post,
						'client_mode_enabled' => $post['allow_client_mode'],
						'friendly_name'       => $this->getFriendlyName( $post ),
						'last_activity'       => date( 'Y-m-d H:i:s' ),
						'created_at_gmt'      => date( 'Y-m-d H:i:s' ),
					) )->save();

				PMXE_Plugin::$session->set( 'update_previous', $export->id );
				PMXE_Plugin::$session->set( 'friendly_name', $this->getFriendlyName( $post ) );
				PMXE_Plugin::$session->save_data();
				$exportId = $export->id;
			}
			$this->data['export_id'] = $exportId;
			$this->data['export']    = new PMXE_Export_Record();
			$this->data['export']    = $this->data['export']->getBy( 'id', $exportId );

			if ( empty( $post['friendly_name'] ) ) {
				$post['friendly_name'] = $this->getFriendlyName( $post );
			}
		} else {
			$DefaultOptions = $this->data['export']->options + $default;
			if ( empty( $this->data['export']->options['export_variations'] ) ) {
				$DefaultOptions['export_variations'] = XmlExportEngine::VARIABLE_PRODUCTS_EXPORT_PARENT_AND_VARIATION;
			}
			if ( empty( $this->data['export']->options['export_variations_title'] ) ) {
				$DefaultOptions['export_variations_title'] = XmlExportEngine::VARIATION_USE_DEFAULT_TITLE;
			}
			$post              = $this->input->post( $DefaultOptions );
			$post['scheduled'] = $this->data['export']->scheduled;

			foreach ( $post as $key => $value ) {
				PMXE_Plugin::$session->set( $key, $value );
			}
			PMXE_Plugin::$session->save_data();
			$this->data['export_id'] = $this->data['export']->id;
		}

		$this->data['engine'] = new XmlExportEngine( $post, $this->errors );

		$this->data['engine']->init_available_data();

		$this->data['post'] =& $post;

		if ( $this->input->post( 'is_submitted' ) ) {

			$auto_generate = PMXE_Plugin::$session->get( 'auto_generate', false );
			if ( $auto_generate ) {
				$post['order_item_per_row'] = 0;
			}

			if ( $post['is_generate_templates'] and '' == $post['template_name'] ) {
				$friendly_name         = $this->getFriendlyName( $post );
				$post['template_name'] = $friendly_name;
			}

			if ( $this->isWizard ) {
				if ( ! $this->errors->get_error_codes() ) {

					foreach ( $this->data['post'] as $key => $value ) {
						PMXE_Plugin::$session->set( $key, $value );
					}

					PMXE_Plugin::$session->save_data();
					wp_redirect( esc_url_raw( add_query_arg( 'action', 'process', $this->baseUrl ) ) );
					die();
				}
			} else {
				$this->errors->remove( 'count-validation' );
				if ( ! $this->errors->get_error_codes() ) {

					if ( $post['enable_real_time_exports'] ) {
						$post['creata_a_new_export_file']         = 1;
						$post['enable_real_time_exports_running'] = 1;
					}

					if ( $this->data['post']['enable_real_time_exports'] ) {
						$this->data['post']['creata_a_new_export_file']         = 1;
						$this->data['post']['enable_real_time_exports_running'] = 1;
					}


					$this->data['export']->set( array(
						'options'             => $post,
						'settings_update_on'  => date( 'Y-m-d H:i:s' ),
						'client_mode_enabled' => $post['allow_client_mode'],
					) )->save();

					if ( ! empty( $post['friendly_name'] ) ) {
						$this->data['export']->set( array(
							'friendly_name'       => $post['friendly_name'],
							'scheduled'           => ( ( $post['is_scheduled'] ) ? $post['scheduled_period'] : '' ),
							'client_mode_enabled' => $post['allow_client_mode'],
						) )->save();
					}
					if ( isset( $post['enable_real_time_exports'] ) && $post['enable_real_time_exports'] ) {
						wp_redirect( esc_url_raw( add_query_arg( array(
							                                         'page'   => 'pmxe-admin-export',
							                                         'id'     => $this->data['export']->id,
							                                         'action' => 'process',
						                                         ) + array_intersect_key( $_GET, array_flip( $this->baseUrlParamNames ) ), admin_url( 'admin.php' ) ) ) );
					} else {
						wp_redirect( add_query_arg( array(
							                            'page'    => 'pmxe-admin-manage',
							                            'pmxe_nt' => urlencode( __( 'Options updated', 'wp_all_export_plugin' ) ),
						                            ) + array_intersect_key( $_GET, array_flip( $this->baseUrlParamNames ) ), admin_url( 'admin.php' ) ) );
					}
					die();
				}
			}
		}

		$this->render();
	}

	/**
	 * Step #4: Export Processing
	 */
	public function process_action() {
		@set_time_limit( 0 );

		$export = $this->data['update_previous'];

		$this->userHasAccessToItem( $export );

		if ( ! PMXE_Plugin::is_ajax() ) {

			if ( "" == PMXE_Plugin::$session->friendly_name ) {

				$post_types = PMXE_Plugin::$session->get( 'cpt' );
				if ( ! empty( $post_types ) ) {
					if ( in_array( 'users', $post_types ) ) {
						$friendly_name = 'Users Export - ' . date( "Y F d H:i" );
					} elseif ( in_array( 'shop_customer', $post_types ) ) {
						$friendly_name = 'Customers Export - ' . date( "Y F d H:i" );
					} elseif ( in_array( 'comments', $post_types ) ) {
						$friendly_name = 'Comments Export - ' . date( "Y F d H:i" );
					} elseif ( in_array( 'taxonomies', $post_types ) ) {
						$tx = get_taxonomy( PMXE_Plugin::$session->get( 'taxonomy_to_export' ) );
						if ( ! empty( $tx->labels->name ) ) {
							$friendly_name = $tx->labels->name . ' Export - ' . date( "Y F d H:i" );
						} else {
							$friendly_name = 'Taxonomy Terms Export - ' . date( "Y F d H:i" );
						}
					} else {
						$post_type_details = get_post_type_object( array_shift( $post_types ) );
						$friendly_name     = $post_type_details->labels->name . ' Export - ' . date( "Y F d H:i" );
					}
				} else {
					$friendly_name = 'WP_Query Export - ' . date( "Y F d H:i" );
				}

				PMXE_Plugin::$session->set( 'friendly_name', $friendly_name );
			}

			PMXE_Plugin::$session->set( 'file', '' );
			PMXE_Plugin::$session->save_data();

			$options = PMXE_Plugin::$session->get_clear_session_data();

			if ( isset( $options['enable_real_time_exports'] ) && $options['enable_real_time_exports'] ) {
				$options['creata_a_new_export_file']         = 1;
				$options['enable_real_time_exports_running'] = 1;
			}
			$exportParams = array(
				'triggered'     => 0,
				'processing'    => 0,
				'exported'      => 0,
				'executing'     => 1,
				'canceled'      => 0,
				'options'       => $options,
				'friendly_name' => PMXE_Plugin::$session->friendly_name,
				'scheduled'     => ( PMXE_Plugin::$session->is_scheduled ) ? PMXE_Plugin::$session->scheduled_period : '',
				'last_activity' => date( 'Y-m-d H:i:s' ),
			);


			if ( current_user_can( PMXE_Plugin::$capabilities ) ) {
				$exportParams['client_mode_enabled'] = PMXE_Plugin::$session->allow_client_mode;
			}

			$export->set( $exportParams )->save();

			// create an import for this export
			if ( $export->options['export_to'] == 'csv' || ! in_array( $export->options['xml_template_type'], array(
					'custom',
					'XmlGoogleMerchants',
				) ) ) {
				PMXE_Wpallimport::create_an_import( $export );
			}
			PMXE_Plugin::$session->set( 'update_previous', $export->id );
			PMXE_Plugin::$session->save_data();

		}
		do_action( 'pmxe_before_export', $export->id );


		$this->render();
	}

	/**
	 * @param $post
	 *
	 * @return string
	 */
	protected function getFriendlyName( $post ) {
		$friendly_name = '';
		$post_types    = PMXE_Plugin::$session->get( 'cpt' );
		if ( ! empty( $post_types ) ) {
			if ( in_array( 'users', $post_types ) ) {
				$friendly_name = 'Users Export - ' . date( "Y F d H:i" );

				return $friendly_name;
			} elseif ( in_array( 'shop_customer', $post_types ) ) {
				$friendly_name = 'Customers Export - ' . date( "Y F d H:i" );

				return $friendly_name;
			} elseif ( in_array( 'comments', $post_types ) ) {
				$friendly_name = 'Comments Export - ' . date( "Y F d H:i" );

				return $friendly_name;
			} elseif ( in_array( 'taxonomies', $post_types ) ) {
				$tx = get_taxonomy( $post['taxonomy_to_export'] );
				if ( ! empty( $tx->labels->name ) ) {
					$friendly_name = $tx->labels->name . ' Export - ' . date( "Y F d H:i" );

					return $friendly_name;
				} else {
					$friendly_name = 'Taxonomy Terms Export - ' . date( "Y F d H:i" );

					return $friendly_name;
				}
			} else {
				$is_rapid_add_on_export = PMXE_Helper::is_rapid_export_addon( $post_types );
				if ( $is_rapid_add_on_export ) {
					return 'Gravity Forms Entries Export - ' . date( "Y F d H:i" );
				}

				$post_type_details = get_post_type_object( array_shift( $post_types ) );
				$friendly_name     = $post_type_details->labels->name . ' Export - ' . date( "Y F d H:i" );

				return $friendly_name;
			}
		} else {
			$friendly_name = 'WP_Query Export - ' . date( "Y F d H:i" );

			return $friendly_name;
		}
	}

	function insertAfter( $input, $index, $newKey, $element ) {
		if ( ! array_key_exists( $index, $input ) ) {
			throw new Exception( "Index not found" );
		}
		$tmpArray = array();
		foreach ( $input as $key => $value ) {
			$tmpArray[ $key ] = $value;
			if ( $key === $index ) {
				$tmpArray[ $newKey ] = $element;
			}
		}

		return $tmpArray;
	}
}