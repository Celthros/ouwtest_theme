<?php

/**
 * Manage Imports
 *
 * @author Pavel Kulbakin <p.kulbakin@gmail.com>
 */
class PMXE_Admin_Manage extends PMXE_Controller_Admin {

	public function init() {
		parent::init();

		if ( 'update_action' == PMXE_Plugin::getInstance()->getAdminCurrentScreen()->action ) {
			$this->isInline = true;
		}
	}

	/**
	 * Previous Imports list
	 */
	public function index_action() {

		$get = $this->input->get( array(
			's'        => '',
			'order_by' => 'id',
			'order'    => 'DESC',
			'pagenum'  => 1,
			'perPage'  => 25,
		) );

		$get['pagenum'] = absint( $get['pagenum'] );
		extract( $get );
		$this->data += $get;

		if ( ! in_array( $order_by, array( 'registered_on', 'id', 'friendly_name' ) ) ) {
			$order_by = 'registered_on';
		}

		if ( ! in_array( $order, array( 'DESC', 'ASC' ) ) ) {
			$order = 'DESC';
		}

		$list = new PMXE_Export_List();
		$by   = array(
			'parent_id' => 0,
		);

		if ( ! current_user_can( PMXE_Plugin::$capabilities ) && current_user_can( PMXE_Plugin::CLIENT_MODE_CAP ) ) {
			$by['client_mode_enabled'] = 1;
		}

		if ( '' != $s ) {
			$like = '%' . preg_replace( '%\s+%', '%', preg_replace( '/[%?]/', '\\\\$0', $s ) ) . '%';
			$by[] = array( array( 'friendly_name LIKE' => $like, 'registered_on LIKE' => $like ), 'OR' );
		}

		$exportList = $list->setColumns( $list->getTable() . '.*' )->getBy( $by, "$order_by $order", $pagenum, $perPage, $list->getTable() . '.id' );


		$this->data['list'] = $exportList;

		$this->data['page_links'] = paginate_links( array(
			'base'      => esc_url_raw( add_query_arg( 'pagenum', '%#%', $this->baseUrl ) ),
			'add_args'  => array( 'page' => 'pmxe-admin-manage' ),
			'format'    => '',
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'total'     => ceil( $list->total() / $perPage ),
			'current'   => $pagenum,
		) );

		PMXE_Plugin::$session->clean_session();

		$this->render();
	}

	/**
	 * Edit Options
	 */
	public function options_action() {
		$this->onlyAllowAdmin();

		// deligate operation to other controller
		$controller = new PMXE_Admin_Export();
		$controller->set( 'isTemplateEdit', true );
		$controller->options_action();
	}

	/**
	 * Edit Template
	 */
	public function template_action() {

		$this->onlyAllowAdmin();

		// deligate operation to other controller
		$controller = new PMXE_Admin_Export();
		$controller->set( 'isTemplateEdit', true );
		$controller->template_action();
	}

	/**
	 * Cron Scheduling
	 */
	public function scheduling_action() {
		$this->onlyAllowAdmin();

		$this->data['id']           = $id = $this->input->get( 'id' );
		$this->data['cron_job_key'] = PMXE_Plugin::getInstance()->getOption( 'cron_job_key' );
		$this->data['item']         = $item = new PMXE_Export_Record();
		if ( ! $id or $item->getById( $id )->isEmpty() ) {
			wp_redirect( $this->baseUrl );
			die();
		}

		$wp_uploads = wp_upload_dir();

		$this->data['file_path'] = site_url() . '/wp-load.php?security_token=' . substr( md5( $this->data['cron_job_key'] . $item['id'] ), 0, 16 ) . '&export_id=' . $item['id'] . '&action=get_data';

		$this->data['bundle_url'] = '';

		if ( ! empty( $item['options']['bundlepath'] ) ) {
			$this->data['bundle_url'] = site_url() . '/wp-load.php?security_token=' . substr( md5( $this->data['cron_job_key'] . $item['id'] ), 0, 16 ) . '&export_id=' . $item['id'] . '&action=get_bundle&t=zip';
		}

		$this->render();
	}

	/**
	 * Google merchants info
	 */
	public function google_merchants_info_action() {

		$this->onlyAllowAdmin();

		$this->data['id']           = $id = $this->input->get( 'id' );
		$this->data['cron_job_key'] = PMXE_Plugin::getInstance()->getOption( 'cron_job_key' );
		$this->data['item']         = $item = new PMXE_Export_Record();
		if ( ! $id or $item->getById( $id )->isEmpty() ) {
			wp_redirect( $this->baseUrl );
			die();
		}

		$this->data['file_path'] = site_url() . '/wp-load.php?security_token=' . substr( md5( $this->data['cron_job_key'] . $item['id'] ), 0, 16 ) . '&export_id=' . $item['id'] . '&action=get_data';

		$this->render();
	}

	/**
	 * Download import templates
	 */
	public function templates_action() {

		$this->onlyAllowAdmin();

		$this->data['id']   = $id = $this->input->get( 'id' );
		$this->data['item'] = $item = new PMXE_Export_Record();
		if ( ! $id or $item->getById( $id )->isEmpty() ) {
			wp_redirect( $this->baseUrl );
			die();
		}

		$this->render();
	}

	/**
	 * Cancel import processing
	 */
	public function cancel_action() {

		$id = $this->input->get( 'id' );

		PMXE_Plugin::$session->clean_session( $id );

		$item = new PMXE_Export_Record();

		if ( ! $id or $item->getById( $id )->isEmpty() ) {
			wp_redirect( $this->baseUrl );
			die();
		}

		$this->userHasAccessToItem( $item );

		$item->set( array(
			'triggered'   => 0,
			'processing'  => 0,
			'executing'   => 0,
			'canceled'    => 1,
			'canceled_on' => date( 'Y-m-d H:i:s' ),
		) )->update();

		wp_redirect( esc_url_raw( add_query_arg( 'pmxe_nt', urlencode( __( 'Export canceled', 'wp_all_import_plugin' ) ), $this->baseUrl ) ) );

		die();
	}

	/**
	 * Reexport
	 */
	public function update_action() {

		$id = $this->input->get( 'id' );

		PMXE_Plugin::$session->clean_session( $id );

		$action_type = $this->input->get( 'type' );

		$this->data['item'] = $item = new PMXE_Export_Record();
		if ( ! $id or $item->getById( $id )->isEmpty() ) {
			wp_redirect( $this->baseUrl );
			die();
		}

		$this->userHasAccessToItem( $item );

		$item->fix_template_options();

		$default        = PMXE_Plugin::get_default_import_options();
		$defaultOptions = $item->options + $default;
		if ( empty( $item->options['export_variations'] ) ) {
			$defaultOptions['export_variations'] = XmlExportEngine::VARIABLE_PRODUCTS_EXPORT_PARENT_AND_VARIATION;
		}
		if ( empty( $item->options['export_variations_title'] ) ) {
			$defaultOptions['export_variations_title'] = XmlExportEngine::VARIATION_USE_DEFAULT_TITLE;
		}


		if ( current_user_can( PMXE_Plugin::$capabilities ) ) {
			// Allow administrators to modify any options.
			$this->data['post'] = $post = $this->input->post( $defaultOptions );

		} else {
			// Restrict options that can be modified for client mode runs.
			// We provide the current default values so that the run screen displays properly.
			$allowedUserProvidedOptions = [
				'export_only_new_stuff'               => $defaultOptions['export_only_new_stuff'],
				'export_only_modified_stuff'          => $defaultOptions['export_only_modified_stuff'],
				'include_bom'                         => $defaultOptions['include_bom'],
				'creata_a_new_export_file'            => $defaultOptions['creata_a_new_export_file'],
				'do_not_generate_file_on_new_records' => $defaultOptions['do_not_generate_file_on_new_records'],
				'split_large_exports'                 => $defaultOptions['split_large_exports'],
				'split_large_exports_count'           => $defaultOptions['split_large_exports_count'],
				'records_per_iteration'               => $defaultOptions['records_per_iteration'],
			];

			$post = $this->input->post( $allowedUserProvidedOptions );

			// Add the non-client mode configurable options.
			$this->data['post'] = $post = array_merge( $defaultOptions, $post );
		}

		$this->data['iteration'] = $item->iteration;

		if ( $this->input->post( 'is_confirmed' ) ) {

			check_admin_referer( 'update-export', '_wpnonce_update-export' );

			$iteration = ( empty( $item->options['creata_a_new_export_file'] ) && ! empty( $post['creata_a_new_export_file'] ) ) ? 0 : $item->iteration;

			$item->set( array( 'options' => $post, 'iteration' => $iteration ) )->save();
			if ( ! empty( $post['friendly_name'] ) ) {
				if ( current_user_can( PMXE_Plugin::$capabilities ) ) {
					$item->set( array(
						'friendly_name' => $post['friendly_name'],
						'scheduled'     => ( ( $post['is_scheduled'] ) ? $post['scheduled_period'] : '' ),
					) )->save();
				}
			}

			// compose data to look like result of wizard steps
			$sesson_data = $post + array( 'update_previous' => $item->id ) + $default;

			foreach ( $sesson_data as $key => $value ) {
				PMXE_Plugin::$session->set( $key, $value );
			}

			$this->data['engine'] = new XmlExportEngine( $sesson_data, $this->errors );
			$this->data['engine']->init_additional_data();
			$this->data['engine']->init_available_data();

			PMXE_Plugin::$session->save_data();

			if ( ! $this->errors->get_error_codes() && $this->input->post( 'record-count' ) ) {

				// deligate operation to other controller
				$controller                          = new PMXE_Admin_Export();
				$controller->data['update_previous'] = $item;
				$controller->process_action();

				return;

			}

			$this->errors->remove( 'count-validation' );
			if ( ! $this->errors->get_error_codes() ) {
				wp_redirect( esc_url_raw( add_query_arg( 'pmxe_nt', urlencode( __( 'Options updated', 'wp_all_export_plugin' ) ), $this->baseUrl ) ) );
				die();
			}

		}

		$this->data['isWizard'] = false;
		$this->data['engine']   = new XmlExportEngine( $post, $this->errors );
		$this->data['engine']->init_available_data();

		$this->render();
	}

	/**
	 * Delete an export
	 */
	public function delete_action() {

		$this->onlyAllowAdmin();

		$id                 = $this->input->get( 'id' );
		$this->data['item'] = $item = new PMXE_Export_Record();

		if ( ! $id or $item->getById( $id )->isEmpty() ) {
			wp_redirect( $this->baseUrl );
			die();
		}

		if ( $this->input->post( 'is_confirmed' ) ) {
			check_admin_referer( 'delete-export', '_wpnonce_delete-export' );
			$item->delete();

			$scheduling = \Wpae\Scheduling\Scheduling::create();
			$scheduling->deleteScheduleIfExists( $id );

			wp_redirect( esc_url_raw( add_query_arg( 'pmxe_nt', urlencode( __( 'Export deleted', 'wp_all_export_plugin' ) ), $this->baseUrl ) ) );
			die();
		}

		$this->render();
	}

	/**
	 * Bulk actions
	 */
	public function bulk_action() {
		$this->onlyAllowAdmin();

		check_admin_referer( 'bulk-exports', '_wpnonce_bulk-exports' );
		if ( $this->input->post( 'doaction2' ) ) {
			$this->data['action'] = $action = $this->input->post( 'bulk-action2' );
		} else {
			$this->data['action'] = $action = $this->input->post( 'bulk-action' );
		}
		$this->data['ids']   = $ids = $this->input->post( 'items' );
		$this->data['items'] = $items = new PMXE_Export_List();
		if ( empty( $action ) or ! in_array( $action, array(
				'delete',
				'allow_client_mode',
			) ) or empty( $ids ) or $items->getBy( 'id', $ids )->isEmpty() ) {
			wp_redirect( $this->baseUrl );
			die();
		}

		if ( $this->input->post( 'bulk_action', 'delete' ) == 'delete' && $this->input->post( 'is_confirmed' ) ) {
			if ( $this->input->post( 'bulk_action', 'delete' ) == 'delete' ) {
				foreach ( $items->convertRecords() as $item ) {

					if ( $item->attch_id ) {
						wp_delete_attachment( $item->attch_id, true );
					}

					$item->delete();

					$scheduling = \Wpae\Scheduling\Scheduling::create();
					$scheduling->deleteScheduleIfExists( $item->id );
				}
			}
			wp_redirect( esc_url_raw( add_query_arg( 'pmxe_nt', urlencode( sprintf( __( '%d %s deleted', 'wp_all_export_plugin' ), $items->count(), _n( 'export', 'exports', $items->count(), 'wp_all_export_plugin' ) ) ), $this->baseUrl ) ) );
			die();
		}

		if ( $this->input->post( 'bulk-action' ) == 'allow_client_mode' || $this->input->post( 'bulk-action2' ) == 'allow_client_mode' ) {

			foreach ( $items->convertRecords() as $item ) {
				if ( $item->client_mode_enabled ) {
					$item->set( array( 'client_mode_enabled' => 0 ) )->save();
				} else {
					$item->set( array( 'client_mode_enabled' => 1 ) )->save();
				}
			}
			wp_redirect( esc_url_raw( add_query_arg( 'pmxe_nt', urlencode( sprintf( __( 'Client mode enabled for %d %s', 'wp_all_export_plugin' ), $items->count(), _n( 'export', 'exports', $items->count(), 'wp_all_export_plugin' ) ) ), $this->baseUrl ) ) );
			die();
		}

		$this->render();
	}

	public function get_template_action() {

		$this->onlyAllowAdmin();

		$nonce = ( ! empty( $_REQUEST['_wpnonce'] ) ) ? $_REQUEST['_wpnonce'] : '';
		if ( ! wp_verify_nonce( $nonce, '_wpnonce-download_template' ) ) {
			die( __( 'Security check', 'wp_all_export_plugin' ) );
		} else {

			$id = $this->input->get( 'id' );

			$export = new PMXE_Export_Record();

			$filepath = '';

			$export_data = array();

			if ( ! $export->getById( $id )->isEmpty() ) {

				$export_data[] = $export->options['tpl_data'];
				$uploads       = wp_upload_dir();
				$targetDir     = $uploads['basedir'] . DIRECTORY_SEPARATOR . PMXE_Plugin::TEMP_DIRECTORY;

				$export_file_name = "WP All Import Template - " . sanitize_file_name( $export->friendly_name ) . ".txt";

				file_put_contents( $targetDir . DIRECTORY_SEPARATOR . $export_file_name, json_encode( $export_data ) );

				PMXE_download::csv( $targetDir . DIRECTORY_SEPARATOR . $export_file_name );

			}
		}
	}

	/*
	 * Download bundle for WP All Import
	 *
	 */
	public function bundle_action() {
		$this->onlyAllowAdmin();

		$nonce = ( ! empty( $_REQUEST['_wpnonce'] ) ) ? $_REQUEST['_wpnonce'] : '';
		if ( ! wp_verify_nonce( $nonce, '_wpnonce-download_bundle' ) ) {
			die( __( 'Security check', 'wp_all_export_plugin' ) );
		} else {

			$uploads = wp_upload_dir();

			$id = $this->input->get( 'id' );

			$export = new PMXE_Export_Record();

			if ( ! $export->getById( $id )->isEmpty() ) {
				if ( ! empty( $export->options['bundlepath'] ) ) {
					$bundle_path = wp_all_export_get_absolute_path( $export->options['bundlepath'] );

					if ( @file_exists( $bundle_path ) ) {
						$bundle_url = $uploads['baseurl'] . str_replace( $uploads['basedir'], '', $bundle_path );

						PMXE_download::zip( $bundle_path );
					}
				} else {
					wp_redirect( esc_url_raw( add_query_arg( 'pmxe_nt', urlencode( __( 'The exported bundle is missing and can\'t be downloaded. Please re-run your export to re-generate it.', 'wp_all_export_plugin' ) ), $this->baseUrl ) ) );
					die();
				}
			} else {
				wp_redirect( esc_url_raw( add_query_arg( 'pmxe_nt', urlencode( __( 'This export doesn\'t exist.', 'wp_all_export_plugin' ) ), $this->baseUrl ) ) );
				die();
			}
		}
	}

	public function split_bundle_action() {
		$this->onlyAllowAdmin();

		$nonce = ( ! empty( $_REQUEST['_wpnonce'] ) ) ? $_REQUEST['_wpnonce'] : '';
		if ( ! wp_verify_nonce( $nonce, '_wpnonce-download_split_bundle' ) ) {
			die( __( 'Security check', 'wp_all_export_plugin' ) );
		} else {

			$uploads = wp_upload_dir();

			$id = PMXE_Plugin::$session->update_previous;

			if ( empty( $id ) ) {
				$id = $this->input->get( 'id' );
			}

			$export = new PMXE_Export_Record();

			if ( ! $export->getById( $id )->isEmpty() ) {
				if ( ! empty( $export->options['split_files_list'] ) ) {
					$tmp_dir    = $uploads['basedir'] . DIRECTORY_SEPARATOR . PMXE_Plugin::TEMP_DIRECTORY . DIRECTORY_SEPARATOR . md5( $export->id ) . DIRECTORY_SEPARATOR;
					$bundle_dir = $tmp_dir . 'split_files' . DIRECTORY_SEPARATOR;

					wp_all_export_rrmdir( $tmp_dir );

					@mkdir( $tmp_dir );
					@mkdir( $bundle_dir );

					foreach ( $export->options['split_files_list'] as $file ) {
						@copy( $file, $bundle_dir . basename( $file ) );
					}

					$friendly_name = sanitize_file_name( $export->friendly_name );

					$bundle_path = $tmp_dir . $friendly_name . '-split-files.zip';

					PMXE_Zip::zipDir( $bundle_dir, $bundle_path );

					if ( file_exists( $bundle_path ) ) {
						$bundle_url = $uploads['baseurl'] . str_replace( $uploads['basedir'], '', $bundle_path );

						PMXE_download::zip( $bundle_path );
					}
				}
			}
		}
	}

	/*
	 * Download import log file
	 *
	 */
	public function get_file_action() {

		$nonce = ( ! empty( $_REQUEST['_wpnonce'] ) ) ? $_REQUEST['_wpnonce'] : '';
		if ( ! wp_verify_nonce( $nonce, '_wpnonce-download_feed' ) ) {
			die( __( 'Security check', 'wp_all_export_plugin' ) );
		} else {

			$is_secure_import = PMXE_Plugin::getInstance()->getOption( 'secure' );

			$id = $this->input->get( 'id' );

			$export = new PMXE_Export_Record();

			$filepath = '';

			if ( ! $export->getById( $id )->isEmpty() ) {

				$this->userHasAccessToItem( $export );

				if ( ! $is_secure_import ) {
					$filepath = get_attached_file( $export->attch_id );
				} else {
					$filepath = wp_all_export_get_absolute_path( $export->options['filepath'] );
				}

				if ( @file_exists( $filepath ) ) {
					switch ( $export->options['export_to'] ) {
						case 'xml':
							if ( $export['options']['xml_template_type'] == XmlExportEngine::EXPORT_TYPE_GOOLE_MERCHANTS ) {
								PMXE_Download::txt( $filepath );
							} else {
								PMXE_download::xml( $filepath );
							}

							break;
						case 'csv':
							if ( empty( $export->options['export_to_sheet'] ) or $export->options['export_to_sheet'] == 'csv' ) {
								PMXE_download::csv( $filepath );
							} else {
								PMXE_download::xls( $filepath );
							}
							break;
						default:
							wp_redirect( esc_url_raw( add_query_arg( 'pmxe_nt', urlencode( __( 'File format not supported', 'wp_all_export_plugin' ) ), $this->baseUrl ) ) );
							die();
							break;
					}
				} else {
					wp_redirect( esc_url_raw( add_query_arg( 'pmxe_nt', urlencode( __( "The exported file is missing and can't be downloaded. Please re-run your export to re-generate it.", 'wp_all_export_plugin' ) ), $this->baseUrl ) ) );
					die();
				}
			} else {
				wp_redirect( esc_url_raw( add_query_arg( 'pmxe_nt', urlencode( __( "The exported file is missing and can't be downloaded. Please re-run your export to re-generate it.", 'wp_all_export_plugin' ) ), $this->baseUrl ) ) );
				die();
			}
		}
	}

	public function download_action() {


		$nonce = ( ! empty( $_REQUEST['_wpnonce'] ) ) ? $_REQUEST['_wpnonce'] : '';
		if ( ! wp_verify_nonce( $nonce, '_wpnonce-download_feed' ) ) {
			die( __( 'Security check', 'wp_all_export_plugin' ) );
		} else {

			$is_secure_import = PMXE_Plugin::getInstance()->getOption( 'secure' );

			$id = $this->input->get( 'id' );

			$export = new PMXE_Export_Record();

			$filepath = '';

			if ( ! $export->getById( $id )->isEmpty() ) {
				$this->userHasAccessToItem( $export );

				if ( $export->options['export_to'] != XmlExportEngine::EXPORT_TYPE_GOOLE_MERCHANTS && isset( $_GET['google_feed'] ) ) {
					die( 'Unauthorized' );
				}
				if ( ! $is_secure_import ) {
					$filepath = get_attached_file( $export->attch_id );
				} else {
					$filepath = wp_all_export_get_absolute_path( $export->options['filepath'] );
				}
				if ( @file_exists( $filepath ) ) {
					switch ( $export['options']['export_to'] ) {
						case XmlExportEngine::EXPORT_TYPE_XML:

							if ( $export['options']['xml_template_type'] == XmlExportEngine::EXPORT_TYPE_GOOLE_MERCHANTS ) {
								PMXE_download::txt( $filepath );
							} else {
								PMXE_download::xml( $filepath );
							}

							break;
						case XmlExportEngine::EXPORT_TYPE_CSV:
							if ( empty( $export->options['export_to_sheet'] ) or $export->options['export_to_sheet'] == 'csv' ) {
								PMXE_download::csv( $filepath );
							} else {
								switch ( $export->options['export_to_sheet'] ) {
									case 'xls':
										PMXE_download::xls( $filepath );
										break;
									case 'xlsx':
										PMXE_download::xlsx( $filepath );
										break;
								}
							}
							break;

						default:

							break;
					}
				}
			}
		}
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
}