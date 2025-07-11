<?php

function pmxi_wp_loaded_99() {

	// Automatic Scheduling and other WPAI Public API requests.
	if ( isset( $_GET['action'] ) && $_GET['action'] == 'wpai_public_api' ) {
		$router = new \Wpai\Http\Router();
		$router->route( $_GET['q'], false );
	}

	// Clean up *pmxi_posts database table only when requests are likely intended for WPAI.
	if ( isset( $_GET['import_key'] ) || isset( $_GET['action'] ) || isset( $_GET['check_connection'] ) ) {

		global $wpdb;
		$table   = PMXI_Plugin::getInstance()->getTablePrefix() . 'imports';
		$imports = $wpdb->get_results( "SELECT `id`, `name`, `path` FROM $table WHERE `path` IS NULL", ARRAY_A );

		if ( ! empty( $imports ) ) {

			$importRecord = new PMXI_Import_Record();
			$importRecord->clear();
			foreach ( $imports as $imp ) {
				$importRecord->getById( $imp['id'] );
				if ( ! $importRecord->isEmpty() ) {
					$importRecord->delete( true );
				}
				$importRecord->clear();
			}
		}
	}

	// Check connection
	if ( ! empty( $_GET['check_connection'] ) ) {
		exit( json_encode( array( 'success' => true ) ) );
	}

	// Manual Scheduling/Cron
	if ( ! empty( $_GET['action'] ) && ! empty( $_GET['import_key'] ) && in_array( $_GET['action'], array(
			'processing',
			'trigger',
			'pipe',
			'cancel',
			'cleanup',
		) ) ) {

		/* Confirm cron import key, then execute import */
		$cron_job_key = PMXI_Plugin::getInstance()->getOption( 'cron_job_key' );

		if ( ! empty( $cron_job_key ) && $_GET['import_key'] == $cron_job_key ) {

			$logger = function ( $m ) {
				print( "<p>[" . date( "H:i:s" ) . "] " . wp_all_import_filter_html_kses( $m ) . "</p>\n" );
			};
			$logger = apply_filters( 'wp_all_import_logger', $logger );

			if ( empty( $_GET['import_id'] ) ) {
				if ( $_GET['action'] == 'cleanup' ) {
					$settings = new PMXI_Admin_Settings();
					$settings->cleanup( true );
					pmxi_send_json( array(
						'status'  => 200,
						'message' => __( 'Cleanup completed.', 'wp-all-import-pro' ),
					) );

					return;
				}
				pmxi_send_json( array(
					'status'  => 403,
					'message' => __( 'Missing import ID.', 'wp-all-import-pro' ),
				) );

				return;
			}

			$import = new PMXI_Import_Record();

			$ids = explode( ',', $_GET['import_id'] );

			if ( ! empty( $ids ) and is_array( $ids ) ) {

				foreach ( $ids as $id ) {
					if ( empty( $id ) ) {
						continue;
					}

					$import->getById( $id );

					if ( ! $import->isEmpty() ) {

						if ( ! empty( $_GET['sync'] ) ) {
							$imports = $wpdb->get_results( "SELECT `id`, `name`, `path` FROM $table WHERE `processing` = 1", ARRAY_A );
							if ( ! empty( $imports ) ) {
								$processing_ids = array();
								foreach ( $imports as $imp ) {
									$processing_ids[] = $imp['id'];
								}
								pmxi_send_json( array(
									'status'  => 403,
									'message' => sprintf( __( 'Other imports are currently in process [%s].', 'wp-all-import-pro' ), implode( ",", $processing_ids ) ),
								) );
								break;
							}
						}

						if ( ! in_array( $import->type, array( 'url', 'ftp', 'file' ) ) ) {
							pmxi_send_json( array(
								'status'  => 500,
								'message' => sprintf( __( 'Scheduling update is not working with "upload" import type. Import #%s.', 'wp-all-import-pro' ), $id ),
							) );
						}

						switch ( $_GET['action'] ) {

							case 'trigger':

								if ( (int) $import->executing ) {

									pmxi_send_json( array(
										'status'  => 403,
										'message' => sprintf( __( 'Import #%s is currently in manually process. Request skipped.', 'wp-all-import-pro' ), $id ),
									) );
								} elseif ( ! $import->processing and ! $import->triggered ) {

									$scheduledImport = new \Wpai\Scheduling\Import();

									$history_log = $scheduledImport->trigger( $import );

									pmxi_send_json( array(
										'status'  => 200,
										'message' => sprintf( __( '#%s Cron job triggered.', 'wp-all-import-pro' ), $id ),
									) );

								} elseif ( $import->processing and ! $import->triggered ) {
									pmxi_send_json( array(
										'status'  => 403,
										'message' => sprintf( __( 'Import #%s currently in process. Request skipped.', 'wp-all-import-pro' ), $id ),
									) );
								} elseif ( ! $import->processing and $import->triggered ) {

									pmxi_send_json( array(
										'status'  => 403,
										'message' => sprintf( __( 'Import #%s already triggered. Request skipped.', 'wp-all-import-pro' ), $id ),
									) );
								}

								break;

							case 'processing':

								// check the maximum amount of time we should wait before assuming the iteration failed
								$max_wait = ini_get( 'max_execution_time' );

								$max_wait = $max_wait > 0 ? $max_wait : 1200; // failsafe max_wait should be high enough to avoid falsely marking as failed

								if ( $import->processing == 1 and ( time() - strtotime( $import->registered_on ) ) > $max_wait ) { // it means processor crashed, so it will reset processing to false, and terminate. Then next run it will work normally.
									$import->set( array(
										'processing' => 0,
									) )->update();
								}

								// start execution imports that is in the cron process
								if ( ! (int) $import->triggered ) {
									pmxi_send_json( array(
										'status'  => 403,
										'message' => sprintf( __( 'Import #%s is not triggered. Request skipped.', 'wp-all-import-pro' ), $id ),
									) );
								} elseif ( (int) $import->executing ) {
									pmxi_send_json( array(
										'status'  => 403,
										'message' => sprintf( __( 'Import #%s is currently in manually process. Request skipped.', 'wp-all-import-pro' ), $id ),
									) );
								} elseif ( (int) $import->triggered and ! (int) $import->processing ) {

									$scheduledImport = new \Wpai\Scheduling\Import();

									$response = $scheduledImport->process( $import, $logger );

									if ( ! empty( $response ) and is_array( $response ) ) {
										pmxi_send_json( $response );
									} elseif ( ! (int) $import->queue_chunk_number ) {

										pmxi_send_json( array(
											'status'  => 200,
											'message' => sprintf( __( 'Import #%s complete', 'wp-all-import-pro' ), $import->id ),
										) );
									} else {
										pmxi_send_json( array(
											'status'  => 200,
											'message' => sprintf( __( 'Records Processed %s. Records Count %s.', 'wp-all-import-pro' ), (int) $import->queue_chunk_number, (int) $import->count ),
										) );
									}

								} else {
									pmxi_send_json( array(
										'status'  => 403,
										'message' => sprintf( __( 'Import #%s already processing. Request skipped.', 'wp-all-import-pro' ), $id ),
									) );
								}

								break;
							case 'pipe':

								$import->execute( $logger );

								break;

							case 'cancel':

								$import->set( array(
									'triggered'   => 0,
									'processing'  => 0,
									'executing'   => 0,
									'canceled'    => 1,
									'canceled_on' => date( 'Y-m-d H:i:s' ),
								) )->update();

								pmxi_send_json( array(
									'status'  => 200,
									'message' => sprintf( __( 'Import #%s canceled', 'wp-all-import-pro' ), $import->id ),
								) );

								break;
						}
					}
				}
			}
		}
	}

}

function pmxi_send_json( $response, $status_code = null, $options = 0 ) {
	header( "Content-Type: application/json; charset=" . get_option( 'blog_charset' ) );
	header( "Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0, no-transform" );
	header( "CDN-Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0, no-transform" );
	header( "Cloudflare-CDN-Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0, no-transform" );
	header( "Cache-Control: post-check=0, pre-check=0", false );
	header( "Pragma: no-cache" );

	if ( null !== $status_code ) {
		status_header( $status_code );
	}

	echo wp_json_encode( $response, $options );

	if ( wp_doing_ajax() ) {
		wp_die( '', '', array(
				'response' => null,
			) );
	} else {
		die;
	}
}