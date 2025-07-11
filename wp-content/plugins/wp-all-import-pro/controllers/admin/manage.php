<?php

/**
 * Manage Imports
 *
 * @author Pavel Kulbakin <p.kulbakin@gmail.com>
 */
class PMXI_Admin_Manage extends PMXI_Controller_Admin {

	public function init() {
		parent::init();

		if ( 'update' == PMXI_Plugin::getInstance()->getAdminCurrentScreen()->action ) {
			$this->isInline = true;
		}
	}

	/**
	 * Previous Imports list
	 */
	public function index() {

		$get            = $this->input->get( array(
			's'        => '',
			'order_by' => 'registered_on',
			'order'    => 'DESC',
			'pagenum'  => 1,
			'perPage'  => 25,
		) );
		$get['pagenum'] = absint( $get['pagenum'] );
		extract( $get );
		$this->data += $get;

		if ( ! in_array( $order_by, array( 'registered_on', 'id', 'name' ) ) ) {
			$order_by = 'registered_on';
		}

		if ( ! in_array( $order, array( 'DESC', 'ASC' ) ) ) {
			$order = 'DESC';
		}

		if ( in_array( $order_by, array( 'name' ) ) ) {
			$order_by = 'friendly_name ' . $order . ', name';
		}

		$list = new PMXI_Import_List();
		$by   = array( 'parent_import_id' => 0 );
		if ( '' != $s ) {
			$like = '%' . preg_replace( '%\s+%', '%', preg_replace( '/[%?]/', '\\\\$0', $s ) ) . '%';
			$by[] = array( array( 'name LIKE'          => $like,
			                      'type LIKE'          => $like,
			                      'path LIKE'          => $like,
			                      'friendly_name LIKE' => $like,
			),
				'OR',
			);
		}

		$this->data['list'] = $list->setColumns( $list->getTable() . '.*' )->getBy( $by, "$order_by $order", $pagenum, $perPage, $list->getTable() . '.id' );

		$this->data['page_links'] = paginate_links( array(
			'base'      => add_query_arg( 'pagenum', '%#%', $this->baseUrl ),
			'add_args'  => array( 'page' => 'pmxi-admin-manage' ),
			'format'    => '',
			'prev_text' => __( '&laquo;', 'wp-all-import-pro' ),
			'next_text' => __( '&raquo;', 'wp-all-import-pro' ),
			'total'     => ceil( $list->total() / $perPage ),
			'current'   => $pagenum,
		) );

		PMXI_Plugin::$session->clean_session();

		$this->render();
	}

	/**
	 * delete all posts, media, files, and whatever value was in the 'Unique ID' field
	 */
	public function delete_and_edit() {
		if ( ! wp_verify_nonce( ( $_REQUEST['_wpnonce_delete-and-edit'] ?? '' ), 'delete-and-edit' ) ) {
			die( __( 'Security check', 'wp-all-import-pro' ) );
		}
		$get = $this->input->get( array(
			'id' => '',
		) );
		if ( ! empty( $get['id'] ) ) {
			$import = new PMXI_Import_Record();
			$import->getById( $get['id'] );
			if ( ! $import->isEmpty() ) {
				$import->deletePosts( false );
				$options = $import->options;
				if ( empty( $import->options['custom_type'] ) || $import->options['custom_type'] != 'shop_order' ) {
					$options['unique_key'] = '';
				}
				$import->set( array(
					'options'         => $options,
					'imported'        => 0,
					'created'         => 0,
					'updated'         => 0,
					'skipped'         => 0,
					'deleted'         => 0,
					'changed_missing' => 0,
				) )->save();
			}
		}
		if ( ! empty( $import->options['custom_type'] ) && $import->options['custom_type'] == 'shop_order' ) {
			wp_redirect( esc_url_raw( add_query_arg( array( 'id'     => $import->id,
			                                                'action' => 'edit',
			), $this->baseUrl ) ) );
			die();
		} else {
			wp_redirect( esc_url_raw( add_query_arg( array( 'id'     => $import->id,
			                                                'action' => 'options',
			), $this->baseUrl ) ) );
			die();
		}
	}

	/**
	 * Disable `Skip posts if their data in your file has not changed` option.
	 */
	public function disable_skip_posts() {
		$get = $this->input->get( array(
			'id' => '',
		) );
		if ( ! empty( $get['id'] ) ) {
			$import = new PMXI_Import_Record();
			$import->getById( $get['id'] );
			if ( ! $import->isEmpty() ) {
				$options                         = $import->options;
				$options['is_selective_hashing'] = 0;
				$import->set( array(
					'options'         => $options,
					'imported'        => 0,
					'created'         => 0,
					'updated'         => 0,
					'skipped'         => 0,
					'deleted'         => 0,
					'changed_missing' => 0,
				) )->save();
			}
			wp_redirect( esc_url_raw( add_query_arg( array( 'id'     => $import->id,
			                                                'action' => 'update',
			), $this->baseUrl ) ) );
			die();
		}
	}

	/**
	 * Edit Template
	 */
	public function edit() {

		// deligate operation to other controller
		$controller = new PMXI_Admin_Import();
		$controller->set( 'isTemplateEdit', true );
		$controller->template();
	}

	/**
	 * Edit Options
	 */
	public function options() {

		// deligate operation to other controller
		$controller = new PMXI_Admin_Import();
		$controller->set( 'isTemplateEdit', true );
		$controller->options();
	}

	public function get_template() {
		$nonce = ( ! empty( $_REQUEST['_wpnonce'] ) ) ? $_REQUEST['_wpnonce'] : '';
		if ( ! wp_verify_nonce( $nonce, '_wpnonce-download_template' ) ) {
			die( __( 'Security check', 'wp-all-import-pro' ) );
		} else {

			$id = $this->input->get( 'id' );

			$item = new PMXI_Import_Record();

			$filepath = '';

			$export_data = array();

			if ( ! $item->getById( $id )->isEmpty() ) {
				$tpl_name = empty( $item->friendly_name ) ? $item->name : $item->friendly_name;
				$tpl_data = array(
					'name'               => $tpl_name,
					'is_keep_linebreaks' => 0,
					'is_leave_html'      => 0,
					'fix_characters'     => 0,
					'options'            => $item->options,
				);

				$template_data[] = $tpl_data;
				$uploads         = wp_upload_dir();
				$targetDir       = $uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::TEMP_DIRECTORY;

				$export_file_name = "WP All Import Template - " . sanitize_file_name( $tpl_name ) . ".txt";

				file_put_contents( $targetDir . DIRECTORY_SEPARATOR . $export_file_name, json_encode( $template_data ) );

				PMXI_download::csv( $targetDir . DIRECTORY_SEPARATOR . $export_file_name );

			}
		}
	}

	/*
	 * Download bundle for WP All Import
	 *
	 */
	public function bundle() {

		$nonce = ( ! empty( $_REQUEST['_wpnonce'] ) ) ? $_REQUEST['_wpnonce'] : '';
		if ( ! wp_verify_nonce( $nonce, '_wpnonce-download_bundle' ) ) {
			die( __( 'Security check', 'wp-all-import-pro' ) );
		} else {

			$uploads = wp_upload_dir();

			$id = $this->input->get( 'id' );

			$bundle_path = $this->create_bundle( $id, $nonce );

			if ( file_exists( $bundle_path ) ) {
				$bundle_url = $uploads['baseurl'] . str_replace( $uploads['basedir'], '', $bundle_path );

				PMXI_download::zip( $bundle_path );
			}

		}
	}

	protected function create_bundle( $id, $nonce ) {
		$uploads = wp_upload_dir();

		//generate temporary folder
		$tmp_dir    = $uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::TEMP_DIRECTORY . DIRECTORY_SEPARATOR . md5( $nonce ) . DIRECTORY_SEPARATOR;
		$bundle_dir = $tmp_dir . 'bundle' . DIRECTORY_SEPARATOR;

		// clear tmp dir
		wp_all_import_rmdir( $tmp_dir );

		@mkdir( $tmp_dir );

		$import = new PMXI_Import_Record();

		if ( ! $import->getById( $id )->isEmpty() ) {

			$friendly_name = sanitize_file_name( $import->friendly_name );

			@mkdir( $bundle_dir );

			$tpl_name = empty( $import->friendly_name ) ? $import->name : $import->friendly_name;
			if ( empty( $tpl_name ) ) {
				$tpl_name = 'Import_' . $import->id;
			}
			$tpl_data = array(
				'name'               => $tpl_name,
				'bundle_xpath'       => $import->xpath,
				'is_keep_linebreaks' => 0,
				'is_leave_html'      => 0,
				'fix_characters'     => 0,
				'options'            => $import->options,
			);

			$template_data = array( $tpl_data );
			$template      = "WP All Import Template - " . $tpl_name . ".txt";

			$filepath = '';
			if ( in_array( $import->type, array( 'url' ) ) ) {
				$template_data[0]['_import_type'] = 'url';
				$template_data[0]['_import_url']  = $import->path;
				// $history = new PMXI_File_List();
				// $history->setColumns('id', 'name', 'registered_on', 'path')->getBy(array('import_id' => $import->id), 'id DESC');
				// if ($history->count()){
				// 	foreach ($history as $file){
				// 		$filepath = wp_all_import_get_absolute_path($file['path']);
				// 		if (@file_exists($filepath)) break;
				// 	}
				// }
			} else {
				$filepath = wp_all_import_get_absolute_path( $import->path );
			}

			file_put_contents( $bundle_dir . $template, json_encode( $template_data ) );

			$readme = __( "The other two files in this zip are the export file containing all of your data and the import template for WP All Import. \n\nTo import this data, create a new import with WP All Import and upload this zip file.", "wp-all-import-pro" );

			file_put_contents( $bundle_dir . 'readme.txt', $readme );

			@copy( $filepath, $bundle_dir . basename( $filepath ) );

			$bundle_path = $tmp_dir . $tpl_name . '.zip';

			PMXI_Zip::zipDir( $bundle_dir, $bundle_path );

			return $bundle_path;

		}
	}

	/**
	 * Cron Scheduling
	 */
	public function scheduling() {
		$this->data['id']           = $id = $this->input->get( 'id' );
		$this->data['cron_job_key'] = PMXI_Plugin::getInstance()->getOption( 'cron_job_key' );
		$this->data['item']         = $item = new PMXI_Import_Record();
		if ( ! $id or $item->getById( $id )->isEmpty() ) {
			wp_redirect( esc_url_raw( $this->baseUrl ) );
			die();
		}

		$this->render();
	}

	/**
	 * Cancel import processing
	 */
	public function cancel() {

		$nonce = ( ! empty( $_REQUEST['_wpnonce'] ) ) ? $_REQUEST['_wpnonce'] : '';
		if ( ! wp_verify_nonce( $nonce, '_wpnonce-cancel_import' ) ) {
			die( __( 'Security check', 'wp-all-import-pro' ) );
		} else {

			$id = $this->input->get( 'id' );

			PMXI_Plugin::$session->clean_session( $id );

			$item = new PMXI_Import_Record();
			if ( ! $id or $item->getById( $id )->isEmpty() ) {
				wp_redirect( esc_url_raw( $this->baseUrl ) );
				die();
			}
			$item->set( array(
				'triggered'   => 0,
				'processing'  => 0,
				'executing'   => 0,
				'canceled'    => 1,
				'canceled_on' => date( 'Y-m-d H:i:s' ),
			) )->update();

			wp_redirect( esc_url_raw( add_query_arg( 'pmxi_nt', urlencode( __( 'Import canceled', 'wp-all-import-pro' ) ), $this->baseUrl ) ) );
			die();
		}
	}

	/**
	 * Re-run import
	 */
	public function update() {

		$id = $this->input->get( 'id' );

		PMXI_Plugin::$session->clean_session( $id );

		$action_type = false;

		$this->data['import'] = $item = new PMXI_Import_Record();
		if ( ! $id or $item->getById( $id )->isEmpty() ) {
			wp_redirect( esc_url_raw( $this->baseUrl ) );
			die();
		}

		$this->data['isWizard'] = false;

		$default = PMXI_Plugin::get_default_import_options();

		$DefaultOptions = $item->options + $default;
		foreach ( PMXI_Admin_Addons::get_active_addons() as $class ) {
			if ( class_exists( $class ) ) {
				$DefaultOptions += call_user_func( array( $class, "get_default_import_options" ) );
			}
		}

		$this->data['post'] =& $DefaultOptions;

		$this->data['source'] = array(
			'path'         => $item->path,
			'root_element' => $item->root_element,
		);

		$this->data['xpath'] = $item->xpath;
		$this->data['count'] = $item->count;

		$history = new PMXI_File_List();
		$history->setColumns( 'id', 'name', 'registered_on', 'path' )->getBy( array( 'import_id' => $item->id ), 'id DESC' );
		if ( $history->count() ) {
			foreach ( $history as $file ) {
				if ( @file_exists( wp_all_import_get_absolute_path( $file['path'] ) ) ) {
					$this->data['locfilePath'] = wp_all_import_get_absolute_path( $file['path'] );
					break;
				}
			}
		}

		$chunks = 0;

		if ( ( $this->input->post( 'is_confirmed' ) and check_admin_referer( 'confirm', '_wpnonce_confirm' ) ) ) {

			$continue = $this->input->post( 'is_continue', 'no' );

			// mark action type ad continue
			if ( $continue == 'yes' ) {
				$action_type = 'continue';
			}

			$filePath = '';

			// upload new file in case when import is not continue
			if ( empty( PMXI_Plugin::$session->chunk_number ) ) {
				$upload_result = false;
				if ( $item->type == 'ftp' ) {
					try {
						$files         = PMXI_FTPFetcher::fetch( $item->options );
						$uploader      = new PMXI_Upload( $files[0], $this->errors, rtrim( str_replace( basename( $files[0] ), '', $files[0] ), '/' ) );
						$upload_result = $uploader->upload();
					} catch ( Exception $e ) {
						$this->errors->add( 'form-validation', $e->getMessage() );
					}
				} elseif ( $item->type == 'url' ) { // up to date the file from URL
					$filesXML    = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<data><node></node></data>";
					$files       = XmlImportParser::factory( $filesXML, '/data/node', $item->path, $file )->parse();
					$tmp_files[] = $file;
					foreach ( $tmp_files as $tmp_file ) { // remove all temporary files created
						@unlink( $tmp_file );
					}
					$file_to_import = $item->path;
					if ( ! empty( $files ) and is_array( $files ) ) {
						$file_to_import = array_shift( $files );
					}
					$uploader      = new PMXI_Upload( trim( $file_to_import ), $this->errors );
					$upload_result = $uploader->url( $item->feed_type, $item->path );
				} elseif ( $item->type == 'file' ) { // copy file from WP All Import uploads folder
					$uploader      = new PMXI_Upload( trim( $item->path ), $this->errors );
					$upload_result = $uploader->file();
				} else { // retrieve already uploaded file
					$uploader      = new PMXI_Upload( trim( $item->path ), $this->errors, rtrim( str_replace( basename( $item->path ), '', wp_all_import_get_absolute_path( $item->path ) ), '/' ) );
					$upload_result = $uploader->upload();
				}

				if ( ! count( $this->errors->errors ) ) {
					$filePath = $upload_result['filePath'];
				}

				if ( empty( $item->options['encoding'] ) ) {
					$currentOptions             = $item->options;
					$currentOptions['encoding'] = 'UTF-8';
					$item->set( array(
						'options' => $currentOptions,
					) )->update();
				}

				if ( ! empty( $filePath ) && @file_exists( $filePath ) ) {
					@set_time_limit( 0 );
					$local_paths = ( ! empty( $local_paths ) ) ? $local_paths : array( $filePath );
					foreach ( $local_paths as $key => $path ) {
						if ( ! empty( $action_type ) and $action_type == 'continue' ) {
							$chunks = $item->count;
						} else {
							$file                 = new PMXI_Chunk( $path, array(
								'element'  => $item->root_element,
								'encoding' => $item->options['encoding'],
							) );
							$this->data['is_404'] = $file->is_404;
							while ( $xml = $file->read() ) {
								if ( ! empty( $xml ) ) {
									//PMXI_Import_Record::preprocessXml($xml);
									$xml = "<?xml version=\"1.0\" encoding=\"" . $item->options['encoding'] . "\"?>" . "\n" . $xml;
									$dom = new DOMDocument( '1.0', ( ! empty( $item->options['encoding'] ) ) ? $item->options['encoding'] : 'UTF-8' );
									$old = libxml_use_internal_errors( true );
									$dom->loadXML( $xml ); // FIX: libxml xpath doesn't handle default namespace properly, so remove it upon XML load
									libxml_use_internal_errors( $old );
									$xpath = new DOMXPath( $dom );
									if ( ( $elements = @$xpath->query( $item->xpath ) ) and ! empty( $elements ) and ! empty( $elements->length ) ) {
										$chunks += $elements->length;
									}
									unset( $dom, $xpath, $elements );
								}
							}
							unset( $file );
						}
						! $key and $filePath = $path;
					}
				}

				if ( empty( $chunks ) ) {
					if ( $item->options['is_delete_missing'] ) {
						$chunks = 1;
					} else {
						$item->set( array(
							'registered_on' => date( 'Y-m-d H:i:s' ),
						) )->update();
						$this->errors->add( 'root-element-validation', __( 'No matching elements found for Root element and XPath expression specified', 'wp-all-import-pro' ) );
					}
				}
			}

			if ( $chunks ) { // xml is valid
				if ( ! PMXI_Plugin::is_ajax() and empty( PMXI_Plugin::$session->chunk_number ) ) {
					// compose data to look like result of wizard steps
					$sesson_data = array(
						'filePath'         => $filePath,
						'source'           => array(
							'name'         => $item->name,
							'type'         => $item->type,
							'path'         => wp_all_import_get_relative_path( $item->path ),
							'root_element' => $item->root_element,
						),
						'feed_type'        => $item->feed_type,
						'update_previous'  => $item->id,
						'parent_import_id' => $item->parent_import_id,
						'xpath'            => $item->xpath,
						'options'          => $item->options,
						'encoding'         => ( ! empty( $item->options['encoding'] ) ) ? $item->options['encoding'] : 'UTF-8',
						'is_csv'           => ( ! empty( $item->options['delimiter'] ) ) ? $item->options['delimiter'] : PMXI_Plugin::$is_csv,
						'csv_path'         => PMXI_Plugin::$csv_path,
						'chunk_number'     => 1,
						'log'              => '',
						'warnings'         => 0,
						'errors'           => 0,
						'start_time'       => 0,
						'pointer'          => 1,
						'count'            => ( isset( $chunks ) ) ? $chunks : 0,
						'local_paths'      => ( ! empty( $local_paths ) ) ? $local_paths : array(),
						// ftp import local copies of remote files
						'action'           => ( ! empty( $action_type ) and $action_type == 'continue' ) ? 'continue' : 'update',
						'nonce'            => wp_create_nonce( 'import' ),
						'deligate'         => false,
					);
					foreach ( $sesson_data as $key => $value ) {
						PMXI_Plugin::$session->set( $key, $value );
					}
					PMXI_Plugin::$session->save_data();
				}
				$item->set( array( 'canceled' => 0, 'failed' => 0 ) )->update();
				// deligate operation to other controller
				$controller                          = new PMXI_Admin_Import();
				$controller->data['update_previous'] = $item;
				$controller->process();

				return;
			}
		}
		$this->render( 'admin/import/confirm' );
	}

	/*
	 * Download import file
	 *
	 */
	public function feed() {

		$nonce = ( ! empty( $_REQUEST['_wpnonce'] ) ) ? $_REQUEST['_wpnonce'] : '';
		if ( ! wp_verify_nonce( $nonce, '_wpnonce-download_feed' ) ) {
			die( __( 'Security check', 'wp-all-import-pro' ) );
		} else {

			$import_id = $this->input->get( 'id' );

			$path = '';

			$import = new PMXI_Import_Record();
			$import->getbyId( $import_id );
			if ( ! $import->isEmpty() ) {
				$path = wp_all_import_get_absolute_path( $import->path );

			}

			if ( file_exists( $path ) ) {
				if ( preg_match( '%\W(zip)$%i', trim( basename( $path ) ) ) ) {
					PMXI_download::zip( $path );
				} elseif ( preg_match( '%\W(xml)$%i', trim( basename( $path ) ) ) ) {
					PMXI_download::xml( $path );
				} else {
					PMXI_download::csv( $path );
				}
			} else {
				wp_redirect( esc_url_raw( add_query_arg( array( 'pmxi_nt' => urlencode( __( 'File does not exist.', 'wp-all-import-pro' ) ) ), $this->baseUrl ) ) );
				die();
			}
		}
	}

	/**
	 * Delete an import
	 */
	public function delete() {
		$id                 = $this->input->get( 'id' );
		$this->data['item'] = $item = new PMXI_Import_Record();
		if ( ! $id or $item->getById( $id )->isEmpty() ) {
			wp_redirect( esc_url_raw( $this->baseUrl ) );
			die();
		}

		if ( $this->input->post( 'is_confirmed' ) ) {
			check_admin_referer( 'delete-import', '_wpnonce_delete-import' );

			$is_deleted_images     = $this->input->post( 'is_delete_images' );
			$is_delete_attachments = $this->input->post( 'is_delete_attachments' );
			$is_delete_import      = $this->input->post( 'is_delete_import', false );
			$is_delete_posts       = $this->input->post( 'is_delete_posts', false );

			if ( ! $is_delete_import and ! $is_delete_posts ) {
				wp_redirect( esc_url_raw( add_query_arg( 'pmxi_nt', urlencode( __( 'Nothing to delete.', 'wp-all-import-pro' ) ), $this->baseUrl ) ) );
				die();
			}

			do_action( 'pmxi_before_import_delete', $item, $is_delete_posts );

			$item->delete( ! $is_delete_posts, $is_deleted_images, $is_delete_attachments, $is_delete_import );

			$scheduling = \Wpai\Scheduling\Scheduling::create();
			$scheduling->deleteScheduleIfExists( $id );

			$redirect_msg = '';

			if ( $is_delete_import and ! $is_delete_posts ) {
				$redirect_msg = __( 'Import deleted', 'wp-all-import-pro' );
			} elseif ( ! $is_delete_import and $is_delete_posts ) {
				$redirect_msg = __( 'All associated posts deleted.', 'wp-all-import-pro' );
			} elseif ( $is_delete_import and $is_delete_posts ) {
				$redirect_msg = __( 'Import and all associated posts deleted.', 'wp-all-import-pro' );
			}

			wp_redirect( esc_url_raw( add_query_arg( 'pmxi_nt', urlencode( $redirect_msg ), $this->baseUrl ) ) );
			die();
		}

		$postList                       = new PMXI_Post_List();
		$this->data['associated_posts'] = count( $postList->getBy( array( 'import_id' => $item->id ) ) );

		$this->render();
	}

	/**
	 * Bulk actions
	 */
	public function bulk() {
		check_admin_referer( 'bulk-imports', '_wpnonce_bulk-imports' );
		if ( $this->input->post( 'doaction2' ) ) {
			$this->data['action'] = $action = $this->input->post( 'bulk-action2' );
		} else {
			$this->data['action'] = $action = $this->input->post( 'bulk-action' );
		}
		$this->data['ids']   = $ids = $this->input->post( 'items' );
		$this->data['items'] = $items = new PMXI_Import_List();
		if ( empty( $action ) or ! in_array( $action, array( 'delete' ) ) or empty( $ids ) or $items->getBy( 'id', $ids )->isEmpty() ) {
			wp_redirect( esc_url_raw( $this->baseUrl ) );
			die();
		}

		if ( $this->input->post( 'is_confirmed' ) ) {
			$is_delete_posts       = $this->input->post( 'is_delete_posts', false );
			$is_deleted_images     = $this->input->post( 'is_delete_images' );
			$is_delete_attachments = $this->input->post( 'is_delete_attachments' );

			$scheduling = \Wpai\Scheduling\Scheduling::create();

			foreach ( $items->convertRecords() as $item ) {
				$item->delete( ! $is_delete_posts, $is_deleted_images, $is_delete_attachments );
				$scheduling->deleteScheduleIfExists( $item->id );
			}

			wp_redirect( esc_url_raw( add_query_arg( 'pmxi_nt', urlencode( sprintf( __( '%d %s deleted', 'wp-all-import-pro' ), $items->count(), _n( 'import', 'imports', $items->count(), 'wp-all-import-pro' ) ) ), $this->baseUrl ) ) );
			die();
		}

		$this->render();
	}

}
