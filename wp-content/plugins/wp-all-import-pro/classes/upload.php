<?php
if ( ! class_exists( 'PMXI_Upload' ) ) {

	class PMXI_Upload {

		protected $file;
		protected $errors;
		protected $root_element = '';
		protected $is_csv = false;

		protected $uploadsPath;

		function __construct( $file, $errors, $targetDir = false ) {

			$this->file   = $file;
			$this->errors = $errors;

			$uploads = wp_upload_dir();

			$input     = new PMXI_Input();
			$import_id = $input->get( 'id' );
			// Get import ID from cron processing URL.
			if ( empty( $import_id ) ) {
				$import_id = $input->get( 'import_id' );
			}
			// Get import ID from CLI arguments.
			if ( empty( $import_id ) && PMXI_Plugin::getInstance()->isCli() ) {
				$import_id = wp_all_import_get_import_id();
			}
			if ( $uploads['error'] ) {
				$this->uploadsPath = false;
			} else {
				$this->uploadsPath = wp_all_import_get_absolute_path( ( ! $targetDir ) ? wp_all_import_secure_file( $uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::UPLOADS_DIRECTORY, $import_id, true ) : $targetDir );
			}
		}

		public function upload() {

			$this->file = wp_all_import_get_absolute_path( $this->file );

			$templates = false;

			$bundle = array();

			$bundleFiles = array();

			$csv_path = '';

			if ( empty( $this->file ) ) {
				$this->errors->add( 'form-validation', __( 'Please specify a file to import.<br/><br/>If you are uploading the file from your computer, please wait for it to finish uploading (progress bar at 100%), before trying to continue.', 'wp-all-import-pro' ) );
			} elseif ( ! is_file( $this->file ) ) {
				$this->errors->add( 'form-validation', __( 'Uploaded file is empty', 'wp-all-import-pro' ) );
			} elseif ( ! preg_match( '%\W(xml|gzip|zip|csv|tsv|gz|json|txt|dat|psv|sql|xls|xlsx)$%i', trim( basename( $this->file ) ) ) ) {
				$this->errors->add( 'form-validation', __( 'Uploaded file must be XML, CSV, ZIP, GZIP, GZ, JSON, SQL, TXT, DAT or PSV', 'wp-all-import-pro' ) );
			} elseif ( preg_match( '%\W(zip)$%i', trim( basename( $this->file ) ) ) ) {

				if ( ! class_exists( 'WpaiPclZip' ) ) {
					include_once PMXI_Plugin::ROOT_DIR . '/libraries/wpaipclzip.lib.php';
				}

				$archive = new WpaiPclZip( $this->file );

				// Attempt to extract files.
				$v_result_list = $archive->extract( WPAI_PCLZIP_OPT_PATH, $this->uploadsPath, WPAI_PCLZIP_OPT_REPLACE_NEWER, WPAI_PCLZIP_OPT_EXTRACT_DIR_RESTRICTION, $this->uploadsPath, WPAI_PCLZIP_OPT_EXTRACT_EXT_RESTRICTIONS, array(
					'php',
					'phtml',
					'htaccess',
				) );
				if ( empty( $v_result_list ) || ! is_array( $v_result_list ) && $v_result_list < 1 ) {
					$this->errors->add( 'form-validation', __( 'WP All Import couldn\'t find a file to import inside your ZIP.<br/><br/>Either the .ZIP file is broken, or doesn\'t contain a file with an extension of  XML, CSV, PSV, DAT, or TXT. <br/>Please attempt to unzip your .ZIP file on your computer to ensure it is a valid .ZIP file which can actually be unzipped, and that it contains a file which WP All Import can import.', 'wp-all-import-pro' ) );
				} else {
					$filePath         = '';
					$decodedTemplates = array();
					if ( ! empty( $v_result_list ) ) {
						foreach ( $v_result_list as $unzipped_file ) {
							if ( $unzipped_file['status'] == 'ok' and preg_match( '%\W(php)$%i', trim( $unzipped_file['stored_filename'] ) ) ) {
								unlink( $unzipped_file['filename'] );
								continue;
							}
							if ( $unzipped_file['status'] == 'ok' and preg_match( '%\W(xml|csv|txt|dat|psv|json|xls|xlsx|gz)$%i', trim( $unzipped_file['stored_filename'] ) ) and strpos( $unzipped_file['stored_filename'], 'readme.txt' ) === false ) {
								if ( strpos( basename( $unzipped_file['stored_filename'] ), 'WP All Import Template' ) === 0 || strpos( basename( $unzipped_file['stored_filename'] ), 'templates_' ) === 0 ) {
									$templates        = file_get_contents( $unzipped_file['filename'] );
									$decodedTemplates = json_decode( $templates, true );
									$templateOptions  = empty( $decodedTemplates[0] ) ? current( $decodedTemplates ) : $decodedTemplates;
									if ( ! empty( $templateOptions ) and isset( $templateOptions[0]['_import_type'] ) and $templateOptions[0]['_import_type'] == 'url' ) {
										$options = \pmxi_maybe_unserialize( $templateOptions[0]['options'] );

										return array(
											'filePath'             => $templateOptions[0]['_import_url'],
											'bundle'               => $bundle,
											'bundle_xpath'         => $templateOptions[0]['bundle_xpath'] ?? '',
											'template'             => json_encode( $templateOptions ),
											'templates'            => $templates,
											'post_type'            => ( ! empty( $options ) ) ? $options['custom_type'] : false,
											'taxonomy_type'        => ( ! empty( $options['taxonomy_type'] ) ) ? $options['taxonomy_type'] : false,
											'gravity_form_title'   => ( ! empty( $options['gravity_form_title'] ) ) ? $options['gravity_form_title'] : false,
											'is_empty_bundle_file' => true,
										);
									}
								} else {
									if ( $filePath == '' ) {
										$filePath = $unzipped_file['filename'];
									}
									if ( ! in_array( $unzipped_file['filename'], $bundleFiles ) ) {
										$bundleFiles[ basename( $unzipped_file['filename'] ) ] = $unzipped_file['filename'];
									}
								}
							}
						}
					}

					if ( count( $bundleFiles ) > 1 ) {
						if ( ! empty( $decodedTemplates ) ) {
							foreach ( $decodedTemplates as $cpt => $tpl ) {
								$fileFormats    = $this->get_xml_file( $bundleFiles[ basename( $tpl[0]['source_file_name'] ) ] );
								$bundle[ $cpt ] = $fileFormats['xml'];
							}
						}
						if ( ! empty( $bundle ) ) {
							$filePath = current( $bundle );
						}
					}

					if ( $this->uploadsPath === false ) {
						$this->errors->add( 'form-validation', __( 'WP All Import can\'t access your WordPress uploads folder.', 'wp-all-import-pro' ) );
					}

					if ( empty( $filePath ) ) {
						$zip    = new \ZipArchive();
						$result = $zip->open( trim( $this->file ) );
						if ( $result ) {
							for ( $i = 0; $i < $zip->numFiles; $i ++ ) {
								$fileName = $zip->getNameIndex( $i );
								if ( preg_match( '%\W(xml|csv|txt|dat|psv|json|xls|xlsx|gz)$%i', trim( $fileName ) ) ) {
									$filePath = $this->uploadsPath . '/' . $fileName;
									$fp       = fopen( $filePath, 'w' );
									fwrite( $fp, $zip->getFromIndex( $i ) );
									fclose( $fp );
									break;
								}
							}
							$zip->close();
						} else {
							$this->errors->add( 'form-validation', __( 'WP All Import couldn\'t find a file to import inside your ZIP.<br/><br/>Either the .ZIP file is broken, or doesn\'t contain a file with an extension of  XML, CSV, PSV, DAT, or TXT. <br/>Please attempt to unzip your .ZIP file on your computer to ensure it is a valid .ZIP file which can actually be unzipped, and that it contains a file which WP All Import can import.', 'wp-all-import-pro' ) );
						}
					}
					// Detect if file is very large
					$source      = array(
						'name' => basename( $this->file ),
						'type' => 'upload',
						'path' => $this->file,
					);
					$fileFormats = $this->get_xml_file( $filePath );
					$filePath    = $fileFormats['xml'];
					$csv_path    = $fileFormats['csv'];
				}
			} elseif ( preg_match( '%\W(csv|txt|dat|psv|tsv)$%i', trim( $this->file ) ) ) { // If CSV file uploaded

				if ( $this->uploadsPath === false ) {
					$this->errors->add( 'form-validation', __( 'WP All Import can\'t access your WordPress uploads folder.', 'wp-all-import-pro' ) );
				}
				$filePath = $this->file;
				$source   = array(
					'name' => basename( $this->file ),
					'type' => 'upload',
					'path' => $filePath,
				);

				include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportCsvParse.php';

				$csv = new PMXI_CsvParser( array(
						'filename'  => $this->file,
						'targetDir' => $this->uploadsPath,
					) );
				//@unlink($filePath);
				$csv_path           = $filePath;
				$filePath           = $csv->xml_path;
				$this->is_csv       = $csv->is_csv;
				$this->root_element = 'node';

			} elseif ( preg_match( '%\W(gz)$%i', trim( $this->file ) ) ) { // If gz file uploaded
				$fileInfo = wp_all_import_get_gz( $this->file, 0, $this->uploadsPath );
				if ( ! is_wp_error( $fileInfo ) ) {
					$filePath = $fileInfo['localPath'];
					// Detect if file is very large
					$source = array(
						'name' => basename( $this->file ),
						'type' => 'upload',
						'path' => $this->file,
					);
					// detect CSV or XML
					if ( $fileInfo['type'] == 'csv' ) { // it is CSV file

						include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportCsvParse.php';
						$csv = new PMXI_CsvParser( array(
								'filename' => $filePath,
								'targeDir' => $this->uploadsPath,
							) ); // create chunks
						//@unlink($filePath);
						$csv_path           = $filePath;
						$filePath           = $csv->xml_path;
						$this->is_csv       = $csv->is_csv;
						$this->root_element = 'node';
					}
				} else {
					$this->errors->add( 'form-validation', $fileInfo->get_error_message() );
				}
			} elseif ( preg_match( '%\W(json)$%i', trim( $this->file ) ) ) {

				// Detect if file is very large
				$source = array(
					'name' => basename( $this->file ),
					'type' => 'upload',
					'path' => $this->file,
				);

				$json_str = trim( file_get_contents( $this->file ) );
				$json_str = str_replace( "\xEF\xBB\xBF", '', $json_str );
				$is_json  = wp_all_import_is_json( $json_str );

				if ( is_wp_error( $is_json ) ) {
					$this->errors->add( 'form-validation', $is_json->get_error_message(), 'wp-all-import-pro' );
				} else {
					$xml_data = wp_all_import_json_to_xml( json_decode( $json_str, true ) );
					if ( empty( $xml_data ) ) {
						$this->errors->add( 'form-validation', __( 'Can not import this file. JSON to XML conversion failed.', 'wp-all-import-pro' ) );
					} else {
						$jsontmpname = $this->uploadsPath . '/' . wp_all_import_url_title( wp_unique_filename( $this->uploadsPath, str_replace( 'json', 'xml', basename( $this->file ) ) ) );
						//@unlink($this->file);
						file_put_contents( $jsontmpname, $xml_data );
						$filePath = $jsontmpname;

					}
				}
			} elseif ( preg_match( '%\W(sql)$%i', trim( $this->file ) ) ) {
				$source = array(
					'name' => basename( $this->file ),
					'type' => 'upload',
					'path' => $this->file,
				);
				include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportSQLParse.php';
				$sql      = new PMXI_SQLParser( $this->file, $this->uploadsPath );
				$filePath = $sql->parse();
			} elseif ( preg_match( '%\W(xls|xlsx)$%i', trim( $this->file ) ) ) {
				$source = array(
					'name' => basename( $this->file ),
					'type' => 'upload',
					'path' => $this->file,
				);

				include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportXLSParse.php';
				$xls      = new PMXI_XLSParser( $this->file, $this->uploadsPath );
				$filePath = $xls->parse();
			} else { // If XML file uploaded
				$filePath = $this->file;
				$source   = array(
					'name' => basename( $this->file ),
					'type' => 'upload',
					'path' => $filePath,
				);
			}

			if ( $this->errors->get_error_codes() ) {
				return $this->errors;
			}

			$decodedTemplates = empty( $templates ) ? false : json_decode( $templates, true );

			$source['path'] = wp_all_import_get_relative_path( $source['path'] );

			$templateOptions = '';

			if ( is_array( $decodedTemplates ) ) {
				$templateOptions = empty( $decodedTemplates[0] ) ? current( $decodedTemplates ) : $decodedTemplates;
			}

			$options = ( empty( $templateOptions[0]['options'] ) ) ? false : \pmxi_maybe_unserialize( $templateOptions[0]['options'] );

			if ( ! empty( $options['root_element'] ) ) {
				$this->root_element = $options['root_element'];
			}

			return array(
				'filePath'           => $filePath,
				'bundle'             => $bundle, // sub imports [cpt => filepath]
				'source'             => $source,
				'root_element'       => $this->root_element,
				'is_csv'             => $this->is_csv,
				'csv_path'           => $csv_path,
				'template'           => empty( $templateOptions ) ? '' : json_encode( $templateOptions ),
				'templates'          => $templates,
				'post_type'          => ( ! empty( $options ) ) ? $options['custom_type'] : false,
				'taxonomy_type'      => ( ! empty( $options['taxonomy_type'] ) ) ? $options['taxonomy_type'] : false,
				'gravity_form_title' => ( ! empty( $options['gravity_form_title'] ) ) ? $options['gravity_form_title'] : false,
				'bundle_xpath'       => ( ! empty( $templateOptions[0]['bundle_xpath'] ) ) ? $templateOptions[0]['bundle_xpath'] : false,
			);
		}

		public function url( $feed_type = '', $feed_xpath = '', $importTemplate = '' ) {

			$uploads = wp_upload_dir();

			$templates = false;

			$bundle = array();

			$bundleFiles = array();

			if ( empty( $this->file ) ) {
				$this->errors->add( 'form-validation', __( 'Please specify a file to import.', 'wp-all-import-pro' ) );
			} elseif ( ! preg_match( '%^https?://%i', $this->file ) ) {
				$this->errors->add( 'form-validation', __( 'The URL to your file is not valid.<br/><br/>Please make sure the URL starts with http:// or https://. To import from https://, your server must have OpenSSL installed.' ), 'wp-all-import-pro' );
			} elseif ( ! is_writeable( $this->uploadsPath ) ) {
				$this->errors->add( 'form-validation', __( 'Uploads folder ' . $this->uploadsPath . ' is not writable.' ), 'wp-all-import-pro' );
			}

			$this->file = trim( $this->file );

			$csv_path = '';

			if ( empty( $this->errors->errors ) ) {

				if ( '' == $feed_type and ! preg_match( '%\W(xml|csv|zip|gz|xls|xlsx)$%i', trim( $this->file ) ) ) {
					$feed_type = wp_all_import_get_remote_file_name( trim( $this->file ) );
				}

				if ( 'zip' == $feed_type or empty( $feed_type ) and preg_match( '%\W(zip)$%i', trim( $this->file ) ) ) {

					$tmpname = $this->uploadsPath . '/' . wp_unique_filename( $this->uploadsPath, md5( basename( $this->file ) ) . '.zip' );

					@copy( $this->file, $tmpname );

					if ( ! file_exists( $tmpname ) ) {
						$request = get_file_curl( $this->file, $tmpname );
						if ( is_wp_error( $request ) ) {
							$this->errors->add( 'form-validation', $request->get_error_message() );
						}
						if ( ! file_exists( $tmpname ) ) {
							$this->errors->add( 'form-validation', __( 'Failed upload ZIP archive', 'wp-all-import-pro' ) );
						}
					}

					if ( ! class_exists( 'WpaiPclZip' ) ) {
						include_once PMXI_Plugin::ROOT_DIR . '/libraries/wpaipclzip.lib.php';
					}

					$archive = new WpaiPclZip( $tmpname );

					// Attempt to extract files.
					$v_result_list = $archive->extract( WPAI_PCLZIP_OPT_PATH, $this->uploadsPath, WPAI_PCLZIP_OPT_REPLACE_NEWER, WPAI_PCLZIP_OPT_EXTRACT_DIR_RESTRICTION, $this->uploadsPath, WPAI_PCLZIP_OPT_EXTRACT_EXT_RESTRICTIONS, array(
						'php',
						'phtml',
						'htaccess',
					) );
					if ( empty( $v_result_list ) || ! is_array( $v_result_list ) && $v_result_list < 1 ) {
						$this->errors->add( 'form-validation', __( 'WP All Import couldn\'t find a file to import inside your ZIP.<br/><br/>Either the .ZIP file is broken, or doesn\'t contain a file with an extension of  XML, CSV, PSV, DAT, or TXT. <br/>Please attempt to unzip your .ZIP file on your computer to ensure it is a valid .ZIP file which can actually be unzipped, and that it contains a file which WP All Import can import.', 'wp-all-import-pro' ) );
					} else {
						$filePath = '';
						if ( ! empty( $v_result_list ) ) {
							foreach ( $v_result_list as $unzipped_file ) {
								if ( $unzipped_file['status'] == 'ok' and preg_match( '%\W(php)$%i', trim( $unzipped_file['stored_filename'] ) ) ) {
									unlink( $unzipped_file['filename'] );
									continue;
								}
								if ( $unzipped_file['status'] == 'ok' and preg_match( '%\W(xml|csv|txt|dat|psv|json|xls|xlsx|gz)$%i', trim( $unzipped_file['stored_filename'] ) ) and strpos( $unzipped_file['stored_filename'], 'readme.txt' ) === false ) {
									if ( strpos( basename( $unzipped_file['stored_filename'] ), 'WP All Import Template' ) === 0 || strpos( basename( $unzipped_file['stored_filename'] ), 'templates_' ) === 0 ) {
										$templates        = file_get_contents( $unzipped_file['filename'] );
										$decodedTemplates = json_decode( $templates, true );
										$templateOptions  = empty( $decodedTemplates[0] ) ? current( $decodedTemplates ) : $decodedTemplates;
									} else {
										if ( $filePath == '' ) {
											$filePath = $unzipped_file['filename'];
										}
										if ( ! in_array( $unzipped_file['filename'], $bundleFiles ) ) {
											$bundleFiles[ basename( $unzipped_file['filename'] ) ] = $unzipped_file['filename'];
										}
									}
								}
							}
						}

						if ( count( $bundleFiles ) > 1 ) {
							if ( ! empty( $decodedTemplates ) ) {
								foreach ( $decodedTemplates as $cpt => $tpl ) {
									$fileFormats    = $this->get_xml_file( $bundleFiles[ basename( $tpl[0]['source_file_name'] ) ] );
									$bundle[ $cpt ] = $fileFormats['xml'];
								}
							}
							if ( ! empty( $bundle ) ) {
								$filePath = current( $bundle );
							}
						}

						if ( $this->uploadsPath === false ) {
							$this->errors->add( 'form-validation', __( 'WP All Import can\'t access your WordPress uploads folder.', 'wp-all-import-pro' ) );
						}

						if ( empty( $filePath ) ) {
							$zip    = new \ZipArchive();
							$result = $zip->open( trim( $tmpname ) );
							if ( $result ) {
								for ( $i = 0; $i < $zip->numFiles; $i ++ ) {
									$fileName = $zip->getNameIndex( $i );
									if ( preg_match( '%\W(xml|csv|txt|dat|psv|json|xls|xlsx|gz)$%i', trim( $fileName ) ) ) {
										$filePath = $this->uploadsPath . '/' . $fileName;
										$fp       = fopen( $filePath, 'w' );
										fwrite( $fp, $zip->getFromIndex( $i ) );
										fclose( $fp );
										break;
									}
								}
								$zip->close();
							} else {
								$this->errors->add( 'form-validation', __( 'WP All Import couldn\'t find a file to import inside your ZIP.<br/><br/>Either the .ZIP file is broken, or doesn\'t contain a file with an extension of  XML, CSV, PSV, DAT, or TXT. <br/>Please attempt to unzip your .ZIP file on your computer to ensure it is a valid .ZIP file which can actually be unzipped, and that it contains a file which WP All Import can import.', 'wp-all-import-pro' ) );
							}
						}
						// Detect if file is very large
						$source      = array(
							'name' => basename( parse_url( $this->file, PHP_URL_PATH ) ),
							'type' => 'url',
							'path' => $feed_xpath,
						);
						$fileFormats = $this->get_xml_file( $filePath );
						$csv_path    = $fileFormats['csv'];
						$filePath    = $fileFormats['xml'];
					}
					if ( file_exists( $tmpname ) ) {
						wp_all_import_remove_source( $tmpname, false );
					}
				} elseif ( 'csv' == $feed_type or '' == $feed_type and preg_match( '%\W(csv|txt|dat|psv|tsv)$%i', trim( $this->file ) ) ) {

					$source = array(
						'name' => basename( parse_url( $this->file, PHP_URL_PATH ) ),
						'type' => 'url',
						'path' => $feed_xpath,
					);
					// copy remote file in binary mode
					$filePath = wp_all_import_get_url( $this->file, $this->uploadsPath, 'csv' );
					if ( ! is_wp_error( $filePath ) ) {
						if ( ! file_exists( $filePath ) ) {
							$this->errors->add( 'form-validation', __( 'WP All Import was not able to download your file.<br/><br/>Please make sure the URL to your file is valid.<br/>You can test this by pasting it into your browser.<br/>Other reasons for this error can include some server setting on your host restricting access to this particular URL or external URLs in general, or some setting on the server hosting the file you are trying to access preventing your server from accessing it.', 'wp-all-import-pro' ) );
						}
						// Detect if file is very large
						include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportCsvParse.php';
						$csv = new PMXI_CsvParser( array(
								'filename'  => $filePath,
								'targetDir' => $this->uploadsPath,
							) ); // create chunks
						//wp_all_import_remove_source($filePath, false);
						$csv_path           = $filePath;
						$filePath           = $csv->xml_path;
						$this->is_csv       = $csv->is_csv;
						$this->root_element = 'node';
					} else {
						$this->errors->add( 'form-validation', $filePath->get_error_message() );
					}
				} elseif ( 'json' == $feed_type or preg_match( '%\W(json)$%i', trim( $this->file ) ) ) {

					$source = array(
						'name' => basename( parse_url( $this->file, PHP_URL_PATH ) ),
						'type' => 'url',
						'path' => $feed_xpath,
					);
					// copy remote file in binary mode
					$filePath = wp_all_import_get_url( $this->file, $this->uploadsPath, 'json' );
					$json_str = file_get_contents( $filePath );
					$json_str = str_replace( "\xEF\xBB\xBF", '', $json_str );
					$is_json  = wp_all_import_is_json( $json_str );
					if ( is_wp_error( $is_json ) ) {
						$this->errors->add( 'form-validation', $is_json->get_error_message(), 'wp-all-import-pro' );
					} else {
						$xml_data = wp_all_import_json_to_xml( json_decode( $json_str, true ) );
						if ( empty( $xml_data ) ) {
							$this->errors->add( 'form-validation', __( 'Can not import this file. JSON to XML conversion failed.', 'wp-all-import-pro' ) );
						} else {
							$tmpname = $this->uploadsPath . '/' . wp_all_import_url_title( wp_unique_filename( $this->uploadsPath, str_replace( 'json', 'xml', basename( $filePath ) ) ) );
							file_put_contents( $tmpname, $xml_data );
							wp_all_import_remove_source( $filePath, false );
							$filePath = $tmpname;
						}
					}
				} elseif ( 'sql' == $feed_type or preg_match( '%\W(sql)$%i', trim( $this->file ) ) ) {
					$source = array(
						'name' => basename( $this->file ),
						'type' => 'url',
						'path' => $feed_xpath,
					);
					// copy remote file in binary mode
					$localSQLPath = wp_all_import_get_url( $this->file, $this->uploadsPath, 'sql' );
					include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportSQLParse.php';
					$sql      = new PMXI_SQLParser( $localSQLPath, $this->uploadsPath );
					$filePath = $sql->parse();
					wp_all_import_remove_source( $localSQLPath, false );
				} elseif ( preg_match( '%\W(xls|xlsx)$%i', $feed_type ) || preg_match( '%\W(xls|xlsx)$%i', strtok( trim( $this->file ), '?' ) ) || preg_match( '%\W(xls|xlsx)$%i', trim( $this->file ) ) ) {

					$source = array(
						'name' => basename( $this->file ),
						'type' => 'url',
						'path' => $feed_xpath,
					);
					// copy remote file in binary mode
					$localXLSPath = wp_all_import_get_url( $this->file, $this->uploadsPath, 'xls' );
					include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportXLSParse.php';
					$xls      = new PMXI_XLSParser( $localXLSPath, $this->uploadsPath );
					$filePath = $xls->parse();
					wp_all_import_remove_source( $localXLSPath, false );
				} else {
					if ( 'gz' == $feed_type or '' == $feed_type and preg_match( '%\W(gz|gzip)$%i', trim( $this->file ) ) ) {
						$fileInfo = wp_all_import_get_gz( $this->file, 0, $this->uploadsPath );
					} else {
						$headers = wp_all_import_get_feed_type( $this->file );
						if ( $headers['Content-Type'] and in_array( $headers['Content-Type'], array(
								'gz',
								'gzip',
							) ) or $headers['Content-Encoding'] and in_array( $headers['Content-Encoding'], array(
								'gz',
								'gzip',
							) ) ) {
							$fileInfo = wp_all_import_get_gz( $this->file, 0, $this->uploadsPath, $headers );
						} else {
							$fileInfo = wp_all_import_get_url( $this->file, $this->uploadsPath, $headers['Content-Type'], $headers['Content-Encoding'], true );
						}
					}

					if ( ! is_wp_error( $fileInfo ) && false !== $fileInfo ) {
						$filePath = $fileInfo['localPath'];
						if ( ! file_exists( $filePath ) ) {
							$this->errors->add( 'form-validation', __( 'WP All Import was not able to download your file.<br/><br/>Please make sure the URL to your file is valid.<br/>You can test this by pasting it into your browser.<br/>Other reasons for this error can include some server setting on your host restricting access to this particular URL or external URLs in general, or some setting on the server hosting the file you are trying to access preventing your server from accessing it.', 'wp-all-import-pro' ) );
						}
						// Detect if file is very large
						$source           = array(
							'name' => basename( parse_url( $this->file, PHP_URL_PATH ) ),
							'type' => 'url',
							'path' => $feed_xpath,
						);
						$fileInfo['type'] = apply_filters( 'wp_all_import_feed_type', $fileInfo['type'], $this->file );
						// detect CSV or XML
						switch ( $fileInfo['type'] ) {
							case 'csv':
								include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportCsvParse.php';
								$csv      = new PMXI_CsvParser( array(
										'filename'  => $filePath,
										'targetDir' => $this->uploadsPath,
									) ); // create chunks
								$csv_path = $filePath;
								//wp_all_import_remove_source($filePath, false);
								$filePath           = $csv->xml_path;
								$this->is_csv       = $csv->is_csv;
								$this->root_element = 'node';
								break;
							case 'json':
								$json_str = file_get_contents( $filePath );
								$json_str = str_replace( "\xEF\xBB\xBF", '', $json_str );
								$is_json  = wp_all_import_is_json( $json_str );

								if ( is_wp_error( $is_json ) ) {
									$this->errors->add( 'form-validation', $is_json->get_error_message(), 'wp-all-import-pro' );
								} else {
									$xml_data = wp_all_import_json_to_xml( json_decode( $json_str, true ) );
									if ( empty( $xml_data ) ) {
										$this->errors->add( 'form-validation', __( 'Can not import this file. JSON to XML conversion failed.', 'wp-all-import-pro' ) );
									} else {
										$tmpname = $this->uploadsPath . '/' . wp_all_import_url_title( wp_unique_filename( $this->uploadsPath, str_replace( 'json', 'xml', basename( $filePath ) ) ) );
										file_put_contents( $tmpname, $xml_data );
										wp_all_import_remove_source( $filePath, false );
										$filePath = $tmpname;
									}
								}
								break;
							case 'sql':
								include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportSQLParse.php';
								$sql      = new PMXI_SQLParser( $filePath, $this->uploadsPath );
								$filePath = $sql->parse();
								break;
							default:
								# code...
								break;
						}
					} else {
						$error = ( $fileInfo ) ? $fileInfo->get_error_message() : 'No data was returned. This can happen if you try to download from a private IP or if an unknown error has occurred.';

						$this->errors->add( 'form-validation', $error );
					}
				}
			}

			if ( $this->errors->get_error_codes() ) {
				return $this->errors;
			}

			$decodedTemplates = empty( $templates ) ? json_decode( $importTemplate, true ) : json_decode( $templates, true );

			$templateOptions = '';

			if ( is_array( $decodedTemplates ) and ! empty( $decodedTemplates ) ) {
				$templateOptions = empty( $decodedTemplates[0] ) ? current( $decodedTemplates ) : $decodedTemplates;
			}

			$options = ( empty( $templateOptions[0]['options'] ) ) ? false : \pmxi_maybe_unserialize( $templateOptions[0]['options'] );

			if ( ! empty( $options['root_element'] ) ) {
				$this->root_element = $options['root_element'];
			}

			return array(
				'filePath'           => $filePath,
				'bundle'             => $bundle,
				'source'             => $source,
				'root_element'       => $this->root_element,
				'feed_type'          => $feed_type,
				'is_csv'             => $this->is_csv,
				'csv_path'           => $csv_path,
				'template'           => empty( $templateOptions ) ? '' : json_encode( $templateOptions ),
				'templates'          => $templates,
				'post_type'          => ( ! empty( $options ) ) ? $options['custom_type'] : false,
				'taxonomy_type'      => ( ! empty( $options['taxonomy_type'] ) ) ? $options['taxonomy_type'] : false,
				'gravity_form_title' => ( ! empty( $options['gravity_form_title'] ) ) ? $options['gravity_form_title'] : false,
			);
		}

		public function file() {

			$templates = false;

			$bundleFiles = array();

			$bundle = array();

			$wp_uploads = wp_upload_dir();

			$uploads = $wp_uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::FILES_DIRECTORY . DIRECTORY_SEPARATOR;

			$this->file = str_replace( DIRECTORY_SEPARATOR . PMXI_Plugin::FILES_DIRECTORY . DIRECTORY_SEPARATOR, '', $this->file );

			$from = $uploads . trim( $this->file );
			$to   = $this->uploadsPath . DIRECTORY_SEPARATOR . basename( $this->file );

			if ( empty( $this->file ) ) {
				$this->errors->add( 'form-validation', __( 'Please specify a file to import.', 'wp-all-import-pro' ) );
			} elseif ( preg_match( '%\W(zip)$%i', trim( $this->file ) ) ) {

				if ( $this->uploadsPath === false ) {
					$this->errors->add( 'form-validation', __( 'WP All Import can\'t access your WordPress uploads folder.', 'wp-all-import-pro' ) );
				}

				if ( $this->errors->get_error_codes() ) {
					return $this->errors;
				}

				echo '<span style="display:none">';
				copy( $from, $to );
				echo '</span>';

				$zipfilePath = $to;

				if ( ! class_exists( 'WpaiPclZip' ) ) {
					include_once PMXI_Plugin::ROOT_DIR . '/libraries/wpaipclzip.lib.php';
				}

				$archive = new WpaiPclZip( $zipfilePath );

				// Attempt to extract files.
				$v_result_list = $archive->extract( WPAI_PCLZIP_OPT_PATH, $this->uploadsPath, WPAI_PCLZIP_OPT_REPLACE_NEWER, WPAI_PCLZIP_OPT_EXTRACT_DIR_RESTRICTION, $this->uploadsPath, WPAI_PCLZIP_OPT_EXTRACT_EXT_RESTRICTIONS, array(
					'php',
					'phtml',
					'htaccess',
				) );
				if ( empty( $v_result_list ) || ! is_array( $v_result_list ) && $v_result_list < 1 ) {
					$this->errors->add( 'form-validation', __( 'WP All Import couldn\'t find a file to import inside your ZIP.<br/><br/>Either the .ZIP file is broken, or doesn\'t contain a file with an extension of  XML, CSV, PSV, DAT, or TXT. <br/>Please attempt to unzip your .ZIP file on your computer to ensure it is a valid .ZIP file which can actually be unzipped, and that it contains a file which WP All Import can import.', 'wp-all-import-pro' ) );
				} else {
					$filePath = '';
					if ( ! empty( $v_result_list ) ) {
						foreach ( $v_result_list as $unzipped_file ) {
							if ( $unzipped_file['status'] == 'ok' and preg_match( '%\W(php)$%i', trim( $unzipped_file['stored_filename'] ) ) ) {
								unlink( $unzipped_file['filename'] );
								continue;
							}
							if ( $unzipped_file['status'] == 'ok' and preg_match( '%\W(xml|csv|tsv|txt|dat|psv|json|xls|xlsx|gz)$%i', trim( $unzipped_file['stored_filename'] ) ) and strpos( $unzipped_file['stored_filename'], 'readme.txt' ) === false ) {
								if ( strpos( basename( $unzipped_file['stored_filename'] ), 'WP All Import Template' ) === 0 || strpos( basename( $unzipped_file['stored_filename'] ), 'templates_' ) === 0 ) {
									$templates        = file_get_contents( $unzipped_file['filename'] );
									$decodedTemplates = json_decode( $templates, true );
									$templateOptions  = empty( $decodedTemplates[0] ) ? current( $decodedTemplates ) : $decodedTemplates;
								} else {
									if ( $filePath == '' ) {
										$filePath = $unzipped_file['filename'];
									}
									if ( ! in_array( $unzipped_file['filename'], $bundleFiles ) ) {
										$bundleFiles[ basename( $unzipped_file['filename'] ) ] = $unzipped_file['filename'];
									}
								}
							}
						}
					}

					if ( count( $bundleFiles ) > 1 ) {
						if ( ! empty( $decodedTemplates ) ) {
							foreach ( $decodedTemplates as $cpt => $tpl ) {
								$fileFormats    = $this->get_xml_file( $bundleFiles[ basename( $tpl[0]['source_file_name'] ) ] );
								$bundle[ $cpt ] = $fileFormats['xml'];
							}
						}
						if ( ! empty( $bundle ) ) {
							$filePath = current( $bundle );
						}
					}

					if ( $this->uploadsPath === false ) {
						$this->errors->add( 'form-validation', __( 'WP All Import can\'t access your WordPress uploads folder.', 'wp-all-import-pro' ) );
					}

					if ( empty( $filePath ) ) {
						$zip    = new \ZipArchive();
						$result = $zip->open( trim( $zipfilePath ) );
						if ( $result ) {
							for ( $i = 0; $i < $zip->numFiles; $i ++ ) {
								$fileName = $zip->getNameIndex( $i );
								if ( preg_match( '%\W(xml|csv|txt|dat|psv|json|xls|xlsx|gz)$%i', trim( $fileName ) ) ) {
									$filePath = $this->uploadsPath . '/' . $fileName;
									$fp       = fopen( $filePath, 'w' );
									fwrite( $fp, $zip->getFromIndex( $i ) );
									fclose( $fp );
									break;
								}
							}
							$zip->close();
						} else {
							$this->errors->add( 'form-validation', __( 'WP All Import couldn\'t find a file to import inside your ZIP.<br/><br/>Either the .ZIP file is broken, or doesn\'t contain a file with an extension of  XML, CSV, PSV, DAT, or TXT. <br/>Please attempt to unzip your .ZIP file on your computer to ensure it is a valid .ZIP file which can actually be unzipped, and that it contains a file which WP All Import can import.', 'wp-all-import-pro' ) );
						}
					}
					// Detect if file is very large
					$source      = array(
						'name' => trim( $this->file ),
						'type' => 'file',
						'path' => $uploads . $this->file,
					);
					$fileFormats = $this->get_xml_file( $filePath );
					$filePath    = $fileFormats['xml'];
				}

				if ( file_exists( $zipfilePath ) ) {
					wp_all_import_remove_source( $zipfilePath, false );
				}
			} elseif ( preg_match( '%\W(csv|txt|dat|psv|tsv)$%i', trim( $this->file ) ) ) {

				if ( $this->uploadsPath === false ) {
					$this->errors->add( 'form-validation', __( 'WP All Import can\'t access your WordPress uploads folder.', 'wp-all-import-pro' ) );
				}
				if ( ! @file_exists( $from ) ) {
					$this->errors->add( 'form-validation', __( 'File doesn\'t exist.', 'wp-all-import-pro' ) );
				}

				if ( $this->errors->get_error_codes() ) {
					return $this->errors;
				}

				// copy file in temporary folder
				// hide warning message
				echo '<span style="display:none">';
				copy( $from, $to );
				echo '</span>';
				$filePath = $to;
				$source   = array(
					'name' => trim( $this->file ),
					'type' => 'file',
					'path' => $from,
				);
				// Detect if file is very large
				include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportCsvParse.php';
				$csv = new PMXI_CsvParser( array(
						'filename'  => $filePath,
						'targetDir' => $this->uploadsPath,
					) ); // create chunks
				//wp_all_import_remove_source($filePath, false);
				$filePath           = $csv->xml_path;
				$this->is_csv       = $csv->is_csv;
				$this->root_element = 'node';
			} elseif ( preg_match( '%\W(json)$%i', trim( $this->file ) ) ) {
				if ( $this->uploadsPath === false ) {
					$this->errors->add( 'form-validation', __( 'WP All Import can\'t access your WordPress uploads folder.', 'wp-all-import-pro' ) );
				}
				if ( $this->errors->get_error_codes() ) {
					return $this->errors;
				}
				// copy file in temporary folder
				// hide warning message
				echo '<span style="display:none">';
				copy( $from, $to );
				echo '</span>';
				$filePath = $to;
				$source   = array(
					'name' => trim( $this->file ),
					'type' => 'file',
					'path' => $from,
				);
				$json_str = file_get_contents( $filePath );
				$json_str = str_replace( "\xEF\xBB\xBF", '', $json_str );
				$is_json  = wp_all_import_is_json( $json_str );
				if ( is_wp_error( $is_json ) ) {
					$this->errors->add( 'form-validation', $is_json->get_error_message(), 'wp-all-import-pro' );
				} else {
					$xml_data = wp_all_import_json_to_xml( json_decode( $json_str, true ) );
					if ( empty( $xml_data ) ) {
						$this->errors->add( 'form-validation', __( 'Can not import this file. JSON to XML conversion failed.', 'wp-all-import-pro' ) );
					} else {
						$jsontmpname = $this->uploadsPath . '/' . wp_all_import_url_title( wp_unique_filename( $this->uploadsPath, str_replace( 'json', 'xml', basename( $filePath ) ) ) );
						file_put_contents( $jsontmpname, $xml_data );
						wp_all_import_remove_source( $filePath, false );
						$filePath = $jsontmpname;
					}
				}
			} elseif ( preg_match( '%\W(sql)$%i', trim( $this->file ) ) ) {

				if ( $this->uploadsPath === false ) {
					$this->errors->add( 'form-validation', __( 'WP All Import can\'t access your WordPress uploads folder.', 'wp-all-import-pro' ) );
				}
				if ( $this->errors->get_error_codes() ) {
					return $this->errors;
				}

				// copy file in temporary folder
				// hide warning message
				echo '<span style="display:none">';
				copy( $from, $to );
				echo '</span>';

				$localSQLPath = $to;
				$source       = array(
					'name' => trim( $this->file ),
					'type' => 'file',
					'path' => $from,
				);

				include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportSQLParse.php';

				$sql      = new PMXI_SQLParser( $localSQLPath, $this->uploadsPath );
				$filePath = $sql->parse();
				wp_all_import_remove_source( $localSQLPath, false );

			} elseif ( preg_match( '%\W(xls|xlsx)$%i', trim( $this->file ) ) ) {

				if ( $this->uploadsPath === false ) {
					$this->errors->add( 'form-validation', __( 'WP All Import can\'t access your WordPress uploads folder.', 'wp-all-import-pro' ) );
				}
				if ( $this->errors->get_error_codes() ) {
					return $this->errors;
				}
				// copy file in temporary folder
				// hide warning message
				echo '<span style="display:none">';
				copy( $from, $to );
				echo '</span>';
				$localXLSPath = $to;
				$source       = array(
					'name' => trim( $this->file ),
					'type' => 'file',
					'path' => $from,
				);
				include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportXLSParse.php';
				$xls      = new PMXI_XLSParser( $localXLSPath, $this->uploadsPath );
				$filePath = $xls->parse();
				wp_all_import_remove_source( $localXLSPath, false );
			} else {

				if ( $this->uploadsPath === false ) {
					$this->errors->add( 'form-validation', __( 'WP All Import can\'t access your WordPress uploads folder.', 'wp-all-import-pro' ) );
				}
				if ( $this->errors->get_error_codes() ) {
					return $this->errors;
				}

				// copy file in temporary folder
				// hide warning message
				echo '<span style="display:none">';
				copy( $from, $to );
				echo '</span>';

				$source = array(
					'name' => trim( $this->file ),
					'type' => 'file',
					'path' => $from,
				);

				$filePath = $to;

				if ( preg_match( '%\W(gz)$%i', basename( $this->file ) ) ) {
					$fileInfo = wp_all_import_get_gz( $filePath, 0, $this->uploadsPath );
					if ( ! is_wp_error( $fileInfo ) ) {
						wp_all_import_remove_source( $filePath, false );
						$filePath = $fileInfo['localPath'];
					} else {
						$this->errors->add( 'form-validation', $fileInfo->get_error_message() );
					}
				}

				if ( preg_match( '%\W(csv|txt|dat|psv|tsv)$%i', trim( $this->file ) ) or ( ! empty( $fileInfo ) and $fileInfo['type'] == 'csv' ) ) {
					include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportCsvParse.php';
					$csv = new PMXI_CsvParser( array(
							'filename'  => $filePath,
							'targetDir' => $this->uploadsPath,
						) ); // create chunks
					//wp_all_import_remove_source($filePath, false);
					$filePath           = $csv->xml_path;
					$this->is_csv       = $csv->is_csv;
					$this->root_element = 'node';
				}
			}

			if ( $this->errors->get_error_codes() ) {
				return $this->errors;
			}

			$decodedTemplates = empty( $templates ) ? false : json_decode( $templates, true );

			$source['path'] = wp_all_import_get_relative_path( $source['path'] );

			$templateOptions = '';

			if ( is_array( $decodedTemplates ) ) {
				$templateOptions = empty( $decodedTemplates[0] ) ? current( $decodedTemplates ) : $decodedTemplates;
			}

			$options = ( empty( $templateOptions[0]['options'] ) ) ? false : \pmxi_maybe_unserialize( $templateOptions[0]['options'] );

			if ( ! empty( $options['root_element'] ) ) {
				$this->root_element = $options['root_element'];
			}

			return array(
				'filePath'           => $filePath,
				'bundle'             => $bundle,
				'source'             => $source,
				'root_element'       => $this->root_element,
				'is_csv'             => $this->is_csv,
				'template'           => empty( $templateOptions ) ? '' : json_encode( $templateOptions ),
				'templates'          => $templates,
				'post_type'          => ( ! empty( $options ) ) ? $options['custom_type'] : false,
				'taxonomy_type'      => ( ! empty( $options['taxonomy_type'] ) ) ? $options['taxonomy_type'] : false,
				'gravity_form_title' => ( ! empty( $options['gravity_form_title'] ) ) ? $options['gravity_form_title'] : false,
			);
		}

		protected function get_xml_file( $filePath ) {
			$csv_path = '';

			if ( preg_match( '%\W(csv|txt|dat|psv|tsv)$%i', trim( $filePath ) ) ) { // If CSV file found in archieve

				if ( $this->uploadsPath === false ) {
					$this->errors->add( 'form-validation', __( 'WP All Import can\'t access your WordPress uploads folder.', 'wp-all-import-pro' ) );
				}

				include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportCsvParse.php';
				$csv = new PMXI_CsvParser( array(
						'filename'  => $filePath,
						'targetDir' => $this->uploadsPath,
					) ); // create chunks

				$csv_path = $filePath;

				$filePath           = $csv->xml_path;
				$this->is_csv       = $csv->is_csv;
				$this->root_element = 'node';

			} elseif ( preg_match( '%\W(json)$%i', trim( $filePath ) ) ) {

				$json_str = file_get_contents( $filePath );
				$json_str = str_replace( "\xEF\xBB\xBF", '', $json_str );
				$is_json  = wp_all_import_is_json( $json_str );

				if ( is_wp_error( $is_json ) ) {
					$this->errors->add( 'form-validation', $is_json->get_error_message(), 'wp-all-import-pro' );
				} else {

					$xml_data = wp_all_import_json_to_xml( json_decode( $json_str, true ) );

					if ( empty( $xml_data ) ) {
						$this->errors->add( 'form-validation', __( 'Can not import this file. JSON to XML conversion failed.', 'wp-all-import-pro' ) );
					} else {
						$jsontmpname = $this->uploadsPath . '/' . wp_all_import_url_title( wp_unique_filename( $this->uploadsPath, str_replace( 'json', 'xml', basename( $filePath ) ) ) );
						file_put_contents( $jsontmpname, $xml_data );
						wp_all_import_remove_source( $filePath, false );
						$filePath = $jsontmpname;
					}
				}
			} elseif ( preg_match( '%\W(sql)$%i', trim( $filePath ) ) ) {

				include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportSQLParse.php';

				$localSQLPath = $filePath;
				$sql          = new PMXI_SQLParser( $localSQLPath, $this->uploadsPath );
				$filePath     = $sql->parse();
				wp_all_import_remove_source( $localSQLPath, false );
			} elseif ( preg_match( '%\W(xls|xlsx)$%i', trim( $filePath ) ) ) {

				include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportXLSParse.php';

				$localXLSPath = $filePath;
				$xls          = new PMXI_XLSParser( $localXLSPath, $this->uploadsPath );
				$filePath     = $xls->parse();
				wp_all_import_remove_source( $localXLSPath, false );

			} elseif ( preg_match( '%\W(gz)$%i', trim( $filePath ) ) ) { // If gz file uploaded

				$fileInfo = wp_all_import_get_gz( $filePath, 0, $this->uploadsPath );

				if ( ! is_wp_error( $fileInfo ) ) {

					$filePath = $fileInfo['localPath'];

					// detect CSV or XML
					if ( $fileInfo['type'] == 'csv' ) { // it is CSV file

						include_once PMXI_Plugin::ROOT_DIR . '/libraries/XmlImportCsvParse.php';
						$csv_path = $filePath;
						$csv      = new PMXI_CsvParser( array(
								'filename' => $filePath,
								'targeDir' => $this->uploadsPath,
							) ); // create chunks
						//@unlink($filePath);
						$filePath           = $csv->xml_path;
						$this->is_csv       = $csv->is_csv;
						$this->root_element = 'node';

					}
				} else {
					$this->errors->add( 'form-validation', $fileInfo->get_error_message() );
				}
			}

			return array(
				'csv' => $csv_path,
				'xml' => $filePath,
			);
		}
	}
}
