<?php

use Wpae\App\Service\ExportGoogleMerchantsFactory;

final class XmlCsvExport {
	public static $main_xml_tag = '';

	public static $node_xml_tag = '';

	/** @var  \Wpae\Csv\CsvWriter */
	public static $csvWriter;

	public static function export() {
		switch ( XmlExportEngine::$exportOptions['export_to'] ) {
			case XmlExportEngine::EXPORT_TYPE_XML:

				if ( XmlExportEngine::$exportOptions['xml_template_type'] == XmlExportEngine::EXPORT_TYPE_GOOLE_MERCHANTS ) {
					$exportGoogleMerchantsFactory = new ExportGoogleMerchantsFactory();
					$exportGoogleMerchants        = $exportGoogleMerchantsFactory->createService();
					$exportGoogleMerchants->export( false, '', XmlExportEngine::$exportRecord->exported );
				} else {
					self::export_xml();
				}
				break;

			case XmlExportEngine::EXPORT_TYPE_CSV:
				self::export_csv();
				break;
			default:
				# code...
				break;
		}
	}

	public static function export_csv( $preview = false, $is_cron = false, $file_path = false, $exported_by_cron = 0 ) {

		if ( XmlExportEngine::$exportOptions['delimiter'] == '\t' ) {
			XmlExportEngine::$exportOptions['delimiter'] = "\t";
		}

		ob_start();

		$stream = fopen( "php://output", 'w' );

		$headers   = array();
		$woo       = array();
		$woo_order = array();
		$acfs      = array();
		$articles  = array();

		// [ Exporting requested data ]

		if ( XmlExportEngine::$is_user_export ) { // exporting WordPress users

			foreach ( XmlExportEngine::$exportQuery->results as $user ) {
				$articles[] = XmlExportUser::prepare_data( $user, XmlExportEngine::$exportOptions, false, $acfs, XmlExportEngine::$implode, $preview );
				$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );
				if ( ! $preview ) {
					do_action( 'pmxe_exported_post', $user->ID, XmlExportEngine::$exportRecord );
				}
			}
		} elseif ( XmlExportEngine::$is_woo_customer_export ) { // exporting WooCommerce Customers

			foreach ( XmlExportEngine::$exportQuery->results as $customer ) {
				$articles[] = XmlExportWooCommerceCustomer::prepare_data( $customer, XmlExportEngine::$exportOptions, false, $acfs, XmlExportEngine::$implode, $preview );
				$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );
				if ( ! $preview ) {
					do_action( 'pmxe_exported_post', $customer->ID, XmlExportEngine::$exportRecord );
				}
			}
		} elseif ( XmlExportEngine::$is_comment_export ) {  // exporting comments
			global $wp_version;

			if ( version_compare( $wp_version, '4.2.0', '>=' ) ) {
				$comments = XmlExportEngine::$exportQuery->get_comments();
			} else {
				$comments = XmlExportEngine::$exportQuery;
			}

			foreach ( $comments as $comment ) {
				$articles[] = XmlExportComment::prepare_data( $comment, XmlExportEngine::$exportOptions, false, XmlExportEngine::$implode, $preview );
				$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );
				if ( ! $preview ) {
					do_action( 'pmxe_exported_post', $comment->comment_ID, XmlExportEngine::$exportRecord );
				}
			}
		} elseif ( XmlExportEngine::$is_woo_review_export ) {  // exporting comments

			global $wp_version;

			if ( version_compare( $wp_version, '4.2.0', '>=' ) ) {
				$comments = XmlExportEngine::$exportQuery->get_comments();
			} else {
				$comments = XmlExportEngine::$exportQuery;
			}

			foreach ( $comments as $comment ) {
				$articles[] = XmlExportWooCommerceReview::prepare_data( $comment, XmlExportEngine::$exportOptions, false, XmlExportEngine::$implode, $preview );
				$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );
				if ( ! $preview ) {
					do_action( 'pmxe_exported_post', $comment->comment_ID, XmlExportEngine::$exportRecord );
				}
			}
		} elseif ( XmlExportEngine::$is_taxonomy_export ) { // exporting WordPress taxonomy terms

			add_filter( 'terms_clauses', 'wp_all_export_terms_clauses', 10, 3 );
			$terms = XmlExportEngine::$exportQuery->get_terms();
			remove_filter( 'terms_clauses', 'wp_all_export_terms_clauses' );
			foreach ( $terms as $term ) {
				$articles[] = XmlExportTaxonomy::prepare_data( $term, XmlExportEngine::$exportOptions, false, $acfs, XmlExportEngine::$implode, $preview );
				$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );
				if ( ! $preview ) {
					do_action( 'pmxe_exported_post', $term->term_id, XmlExportEngine::$exportRecord );
				}
			}
		} elseif ( XmlExportEngine::$is_custom_addon_export ) {

			foreach ( XmlExportEngine::$exportQuery->results as $record ) {

				$articles[] = XmlExportCustomRecord::prepare_data( $record, XmlExportEngine::$exportOptions, false, XmlExportEngine::$implode, $preview );
				$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );
				if ( ! $preview ) {
					do_action( 'pmxe_exported_post', $record->id, XmlExportEngine::$exportRecord );
				}
			}
		} else { // exporting custom post types

			// Get custom field snippets
			$snippets      = array();
			$exportOptions = XmlExportEngine::$exportOptions;
			$snippetParser = new \Wpae\App\Service\SnippetParser();
			$wpaeString    = new WpaeString();

			if ( ( XmlExportEngine::$exportOptions['export_to'] == XmlExportEngine::EXPORT_TYPE_CSV ) && isset( XmlExportEngine::$exportOptions['ids'] ) && is_array( XmlExportEngine::$exportOptions['ids'] ) ) {

				$snippets = array();
				foreach ( XmlExportEngine::$exportOptions['ids'] as $ID => $value ) {
					if ( isset( $exportOptions['cc_combine_multiple_fields'][ $ID ] ) && $exportOptions['cc_combine_multiple_fields'][ $ID ] ) {
						$combineMultipleFieldsValue = $exportOptions['cc_combine_multiple_fields_value'][ $ID ];
						$combineMultipleFieldsValue = stripslashes( $combineMultipleFieldsValue );
						$parsedSnippets             = $snippetParser->parseSnippets( $combineMultipleFieldsValue );
						foreach ( $parsedSnippets as $v ) {
							if ( ! in_array( $v, $snippets ) ) {
								$snippets[] = $v;
							}
						}
					}
				}

				$engine = new XmlExportEngine( XmlExportEngine::$exportOptions );
				$engine->init_available_data();
				$engine->init_additional_data();
				$finalSnippets = $engine->get_fields_options( $snippets );

			}

			if ( ! empty( $finalSnippets ) ) {
				foreach ( $finalSnippets as $key => $val ) {
					foreach ( $val as $k => $v ) {
						$exportOptions[ $key ][] = $v;
					}
				}
			}

			if ( $exportOptions['export_type'] != 'advanced' && in_array( 'shop_order', $exportOptions['cpt'] ) && PMXE_Plugin::hposEnabled() ) {


				$exported = 0;
				if ( is_object( XmlExportEngine::$exportRecord ) ) {
					$exported = XmlExportEngine::$exportRecord->exported;
				}

				$orders = XmlExportEngine::$exportQuery->getOrders( $exported, $exportOptions['records_per_iteration'] );

				foreach ( $orders as $record ) {

					if ( ! isset( $record->ID ) ) {
						$recordId = $record->order_id ?? $record->id;
					} else {
						$recordId = $record->order_id ?? $record->ID;
					}
					$articles[] = XmlExportCpt::prepare_data( $record, $exportOptions, false, $acfs, $woo, $woo_order, XmlExportEngine::$implode, $preview );
					$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );
					if ( ! $preview ) {
						do_action( 'pmxe_exported_post', $recordId, XmlExportEngine::$exportRecord );
					}
				}

			} else {
				// End get custom field snippets

				while ( XmlExportEngine::$exportQuery->have_posts() ) {

					XmlExportEngine::$exportQuery->the_post();

					$record     = get_post( get_the_ID() );
					$articles[] = XmlExportCpt::prepare_data( $record, $exportOptions, false, $acfs, $woo, $woo_order, XmlExportEngine::$implode, $preview );
					$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );
					if ( ! $preview ) {
						do_action( 'pmxe_exported_post', $record->ID, XmlExportEngine::$exportRecord );
					}
				}

			}

			// Process multiplefieldsvalue for combine multiple fields if needed.
			\Wpae\App\Service\CombineFields::prepareMultipleFieldsValue( $articles, false, false, $preview );

			wp_reset_postdata();
		}
		// [ \Exporting requested data ]
		// [ Prepare CSV headers ]
		if ( XmlExportEngine::$exportOptions['ids'] ):

			foreach ( XmlExportEngine::$exportOptions['ids'] as $ID => $value ) {
				if ( empty( XmlExportEngine::$exportOptions['cc_name'][ $ID ] ) or empty( XmlExportEngine::$exportOptions['cc_type'][ $ID ] ) or ! is_numeric( $ID ) ) {
					continue;
				}

				self::prepare_csv_headers( $headers, $ID, $acfs );
			}
		endif;

		$headers = apply_filters( 'wp_all_export_csv_headers', $headers, XmlExportEngine::$exportID );

		if ( $is_cron ) {
			if ( ! $exported_by_cron ) {
				self::getCsvWriter()->writeCsv( $stream, array_map( array(
					'XmlCsvExport',
					'_get_valid_header_name',
				), $headers ), XmlExportEngine::$exportOptions['delimiter'] );
				apply_filters( 'wp_all_export_after_csv_line', $stream, XmlExportEngine::$exportID );
			} else {
				if ( isset( XmlExportEngine::$exportOptions['enable_real_time_exports'] ) && XmlExportEngine::$exportOptions['enable_real_time_exports'] ) {
					self::getCsvWriter()->writeCsv( $stream, array_map( array(
						'XmlCsvExport',
						'_get_valid_header_name',
					), $headers ), XmlExportEngine::$exportOptions['delimiter'] );
					apply_filters( 'wp_all_export_after_csv_line', $stream, XmlExportEngine::$exportID );
				} else {
					self::merge_headers( $file_path, $headers );

				}
			}
		} else {
			if ( $preview or empty( PMXE_Plugin::$session->file ) ) {
				self::getCsvWriter()->writeCsv( $stream, array_map( array(
					'XmlCsvExport',
					'_get_valid_header_name',
				), $headers ), XmlExportEngine::$exportOptions['delimiter'] );
				apply_filters( 'wp_all_export_after_csv_line', $stream, XmlExportEngine::$exportID );
			} else {
				self::merge_headers( PMXE_Plugin::$session->file, $headers );
			}
		}

		// [ \Prepare CSV headers ]
		if ( ! empty( $articles ) ) {
			foreach ( $articles as $article ) {
				if ( empty( $article ) ) {
					continue;
				}
				$line = array();
				foreach ( $headers as $header ) {
					$line[ $header ] = ( isset( $article[ $header ] ) ) ? $article[ $header ] : '';
				}
				self::getCsvWriter()->writeCsv( $stream, $line, XmlExportEngine::$exportOptions['delimiter'] );
				apply_filters( 'wp_all_export_after_csv_line', $stream, XmlExportEngine::$exportID );
			}
		}

		if ( $preview ) {
			return ob_get_clean();
		}

		return self::save_csv_to_file( $file_path, $is_cron, $exported_by_cron );

	}

	public static function export_xml( $preview = false, $is_cron = false, $file_path = false, $exported_by_cron = 0 ) {
		$is_custom_xml = ( ! empty( XmlExportEngine::$exportOptions['xml_template_type'] ) && in_array( XmlExportEngine::$exportOptions['xml_template_type'], array(
				'custom',
				'XmlGoogleMerchants',
			) ) ) ? true : false;

		if ( ! empty( XmlExportEngine::$exportOptions['xml_template_type'] ) ) {

			switch ( XmlExportEngine::$exportOptions['xml_template_type'] ) {
				case 'custom':
				case 'XmlGoogleMerchants':
					if ( isset( XmlExportEngine::$exportOptions['custom_xml_template_options']['ids'] ) ) {
						XmlExportEngine::$exportOptions['ids'] = XmlExportEngine::$exportOptions['custom_xml_template_options']['ids'];
					}

					if ( isset( XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_label'] ) ) {
						XmlExportEngine::$exportOptions['cc_label'] = XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_label'];
					}
					if ( isset( XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_type'] ) ) {
						XmlExportEngine::$exportOptions['cc_type'] = XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_type'];
					}
					if ( isset( XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_value'] ) ) {
						XmlExportEngine::$exportOptions['cc_value'] = XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_value'];
					}
					if ( isset( XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_name'] ) ) {
						XmlExportEngine::$exportOptions['cc_name'] = XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_name'];
					}
					if ( isset( XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_php'] ) ) {
						XmlExportEngine::$exportOptions['cc_php'] = XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_php'];
					}
					if ( isset( XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_code'] ) ) {
						XmlExportEngine::$exportOptions['cc_code'] = XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_code'];
					}
					if ( isset( XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_sql'] ) ) {
						XmlExportEngine::$exportOptions['cc_sql'] = XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_sql'];
					}
					if ( isset( XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_options'] ) ) {
						XmlExportEngine::$exportOptions['cc_options'] = XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_options'];
					}
					if ( isset( XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_settings'] ) ) {
						XmlExportEngine::$exportOptions['cc_settings'] = XmlExportEngine::$exportOptions['custom_xml_template_options']['cc_settings'];
					}
					break;
				default:
					# code...
					break;
			}
		}

		if ( XmlExportEngine::$exportOptions['delimiter'] == '\t' ) {
			XmlExportEngine::$exportOptions['delimiter'] = "\t";
		}

		require_once PMXE_ROOT_DIR . '/classes/XMLWriter.php';

		$woo       = array();
		$woo_order = array();
		$acfs      = array();

		self::$main_xml_tag = apply_filters( 'wp_all_export_main_xml_tag', XmlExportEngine::$exportOptions['main_xml_tag'], XmlExportEngine::$exportID );
		self::$node_xml_tag = apply_filters( 'wp_all_export_record_xml_tag', XmlExportEngine::$exportOptions['record_xml_tag'], XmlExportEngine::$exportID );

		$xmlWriter = new PMXE_XMLWriter();

		if ( ! $is_custom_xml ) {

			$xmlWriter->openMemory();
			$xmlWriter->setIndent( true );
			$xmlWriter->setIndentString( "\t" );
			$xmlWriter->startDocument( '1.0', XmlExportEngine::$exportOptions['encoding'] );
			$xmlWriter->startElement( self::$main_xml_tag );
			// add additional data after XML root element
			self::xml_header( $xmlWriter, $is_cron, $exported_by_cron );
		}

		// [ Exporting requested data ]

		if ( XmlExportEngine::$is_user_export ) // exporting WordPress users
		{
			foreach ( XmlExportEngine::$exportQuery->results as $user ) :

				$is_export_record = apply_filters( 'wp_all_export_xml_rows', true, $user, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

				if ( ! $is_export_record ) {
					continue;
				}

				if ( ! $is_custom_xml ) {
					// add additional information before each node
					self::before_xml_node( $xmlWriter, $user->ID );

					$xmlWriter->startElement( self::$node_xml_tag );

					XmlExportUser::prepare_data( $user, XmlExportEngine::$exportOptions, $xmlWriter, $acfs, XmlExportEngine::$implode, $preview );

					$xmlWriter->closeElement(); // end post

					// add additional information after each node
					self::after_xml_node( $xmlWriter, $user->ID );
				} else {
					$articles   = array();
					$articles[] = XmlExportUser::prepare_data( $user, XmlExportEngine::$exportOptions, $xmlWriter, $acfs, XmlExportEngine::$implode, $preview );
					$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

					$xmlWriter->writeArticle( $articles );
				}

				if ( ! $preview ) {
					do_action( 'pmxe_exported_post', $user->ID, XmlExportEngine::$exportRecord );
				}

			endforeach;

		}
		if ( XmlExportEngine::$is_woo_customer_export ) // exporting WordPress users
		{
			foreach ( XmlExportEngine::$exportQuery->results as $customer ) :

				$is_export_record = apply_filters( 'wp_all_export_xml_rows', true, $customer, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

				if ( ! $is_export_record ) {
					continue;
				}

				if ( ! $is_custom_xml ) {
					// add additional information before each node
					self::before_xml_node( $xmlWriter, $customer->ID );

					$xmlWriter->startElement( self::$node_xml_tag );

					XmlExportWooCommerceCustomer::prepare_data( $customer, XmlExportEngine::$exportOptions, $xmlWriter, $acfs, XmlExportEngine::$implode, $preview );

					$xmlWriter->closeElement(); // end post

					// add additional information after each node
					self::after_xml_node( $xmlWriter, $customer->ID );
				} else {
					$articles   = array();
					$articles[] = XmlExportUser::prepare_data( $customer, XmlExportEngine::$exportOptions, $xmlWriter, $acfs, XmlExportEngine::$implode, $preview );
					$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

					$xmlWriter->writeArticle( $articles );
				}

				if ( ! $preview ) {
					do_action( 'pmxe_exported_post', $customer->ID, XmlExportEngine::$exportRecord );
				}

			endforeach;

		} elseif ( XmlExportEngine::$is_taxonomy_export ) // exporting WordPress taxonomy terms
		{
			add_filter( 'terms_clauses', 'wp_all_export_terms_clauses', 10, 3 );
			$terms = XmlExportEngine::$exportQuery->get_terms();
			remove_filter( 'terms_clauses', 'wp_all_export_terms_clauses' );
			foreach ( $terms as $term ) {

				$is_export_record = apply_filters( 'wp_all_export_xml_rows', true, $term, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

				if ( ! $is_export_record ) {
					continue;
				}

				if ( ! $is_custom_xml ) {
					// add additional information before each node
					self::before_xml_node( $xmlWriter, $term->term_id );

					$xmlWriter->startElement( self::$node_xml_tag );

					XmlExportTaxonomy::prepare_data( $term, XmlExportEngine::$exportOptions, $xmlWriter, $acfs, XmlExportEngine::$implode, $preview );

					$xmlWriter->closeElement(); // end post

					// add additional information after each node
					self::after_xml_node( $xmlWriter, $term->term_id );
				} else {
					$articles   = array();
					$articles[] = XmlExportTaxonomy::prepare_data( $term, XmlExportEngine::$exportOptions, $xmlWriter, $acfs, XmlExportEngine::$implode, $preview );
					$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

					$xmlWriter->writeArticle( $articles );
				}

				if ( ! $preview ) {
					do_action( 'pmxe_exported_post', $term->term_id, XmlExportEngine::$exportRecord );
				}

			}

		} elseif ( XmlExportEngine::$is_comment_export ) // exporting comments
		{
			global $wp_version;

			if ( version_compare( $wp_version, '4.2.0', '>=' ) ) {
				$comments = XmlExportEngine::$exportQuery->get_comments();
			} else {
				$comments = XmlExportEngine::$exportQuery;
			}

			foreach ( $comments as $comment ) {

				$is_export_record = apply_filters( 'wp_all_export_xml_rows', true, $comment, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

				if ( ! $is_export_record ) {
					continue;
				}

				if ( ! $is_custom_xml ) {
					// add additional information before each node
					self::before_xml_node( $xmlWriter, $comment->comment_ID );

					$xmlWriter->startElement( self::$node_xml_tag );

					XmlExportComment::prepare_data( $comment, XmlExportEngine::$exportOptions, $xmlWriter, XmlExportEngine::$implode, $preview );

					$xmlWriter->closeElement(); // end post

					// add additional information after each node
					self::after_xml_node( $xmlWriter, $comment->comment_ID );
				} else {
					$articles   = array();
					$articles[] = XmlExportComment::prepare_data( $comment, XmlExportEngine::$exportOptions, $xmlWriter, XmlExportEngine::$implode, $preview );
					$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

					$xmlWriter->writeArticle( $articles );
				}

				if ( ! $preview ) {
					do_action( 'pmxe_exported_post', $comment->comment_ID, XmlExportEngine::$exportRecord );
				}

			}
		} elseif ( XmlExportEngine::$is_woo_review_export ) // exporting comments
		{
			global $wp_version;

			if ( version_compare( $wp_version, '4.2.0', '>=' ) ) {
				$reviews = XmlExportEngine::$exportQuery->get_comments();
			} else {
				$reviews = XmlExportEngine::$exportQuery;
			}

			foreach ( $reviews as $review ) {

				$is_export_record = apply_filters( 'wp_all_export_xml_rows', true, $review, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

				if ( ! $is_export_record ) {
					continue;
				}

				if ( ! $is_custom_xml ) {
					// add additional information before each node
					self::before_xml_node( $xmlWriter, $review->comment_ID );

					$xmlWriter->startElement( self::$node_xml_tag );

					XmlExportWooCommerceReview::prepare_data( $review, XmlExportEngine::$exportOptions, $xmlWriter, XmlExportEngine::$implode, $preview );

					$xmlWriter->closeElement(); // end post

					// add additional information after each node
					self::after_xml_node( $xmlWriter, $review->comment_ID );
				} else {
					$articles   = array();
					$articles[] = XmlExportWooCommerceReview::prepare_data( $review, XmlExportEngine::$exportOptions, $xmlWriter, XmlExportEngine::$implode, $preview );
					$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

					$xmlWriter->writeArticle( $articles );
				}

				if ( ! $preview ) {
					do_action( 'pmxe_exported_post', $review->comment_ID, XmlExportEngine::$exportRecord );
				}

			}
		} else if ( XmlExportEngine::$exportOptions['export_type'] != 'advanced' && XmlExportEngine::$is_woo_order_export && PMXE_Plugin::hposEnabled() ) {
			add_filter( 'posts_where', 'wp_all_export_numbering_where', 15, 1 );


			$exported = 0;
			if ( is_object( XmlExportEngine::$exportRecord ) ) {
				$exported = XmlExportEngine::$exportRecord->exported;
			}
			$orders = XmlExportEngine::$exportQuery->getOrders( $exported, XmlExportEngine::$exportOptions['records_per_iteration'] );

			foreach ( $orders as $record ) {

				if ( ! isset( $record->ID ) ) {
					$recordId = $record->order_id ?? $record->id;
				} else {
					$recordId = $record->order_id ?? $record->ID;
				}

				if ( ! $is_custom_xml ) {
					// add additional information before each node
					self::before_xml_node( $xmlWriter, $recordId );
					$xmlWriter->startElement( self::$node_xml_tag );

					XmlExportCpt::prepare_data( $record, XmlExportEngine::$exportOptions, $xmlWriter, $acfs, $woo, $woo_order, XmlExportEngine::$implode, $preview );

					$xmlWriter->closeElement(); // end post

					// add additional information after each node
					self::after_xml_node( $xmlWriter, $recordId );
				} else {
					$articles   = [];
					$articles[] = XmlExportCpt::prepare_data( $record, XmlExportEngine::$exportOptions, false, $acfs, $woo, $woo_order, XmlExportEngine::$implode, $preview );
					$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );
					$xmlWriter->writeArticle( $articles );
				}

				if ( ! $preview ) {
					do_action( 'pmxe_exported_post', $recordId, XmlExportEngine::$exportRecord );
				}
			}
		} elseif ( XmlExportEngine::$is_custom_addon_export ) {

			foreach ( XmlExportEngine::$exportQuery->results as $record ) {

				$is_export_record = apply_filters( 'wp_all_export_xml_rows', true, $record, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

				if ( ! $is_export_record ) {
					continue;
				}

				if ( ! $is_custom_xml ) {
					// add additional information before each node
					self::before_xml_node( $xmlWriter, $record->id );
					$xmlWriter->startElement( self::$node_xml_tag );

					XmlExportCustomRecord::prepare_data( $record, XmlExportEngine::$exportOptions, $xmlWriter, XmlExportEngine::$implode, $preview );

					$xmlWriter->closeElement(); // end post

					// add additional information after each node
					self::after_xml_node( $xmlWriter, $record->id );
				} else {
					$articles   = array();
					$articles[] = XmlExportCustomRecord::prepare_data( $record, XmlExportEngine::$exportOptions, $xmlWriter, XmlExportEngine::$implode, $preview );
					$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

					$xmlWriter->writeArticle( $articles );
				}

				if ( ! $preview ) {
					do_action( 'pmxe_exported_post', $record->id, XmlExportEngine::$exportRecord );
				}
			}
		} else {// exporting custom post types
			while ( XmlExportEngine::$exportQuery->have_posts() ) {
				XmlExportEngine::$exportQuery->the_post();
				$record = get_post( get_the_ID() );

				$is_export_record = apply_filters( 'wp_all_export_xml_rows', true, $record, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

				if ( ! $is_export_record ) {
					continue;
				}

				if ( ! $is_custom_xml ) {
					// add additional information before each node
					self::before_xml_node( $xmlWriter, $record->ID );
					$xmlWriter->startElement( self::$node_xml_tag );

					XmlExportCpt::prepare_data( $record, XmlExportEngine::$exportOptions, $xmlWriter, $acfs, $woo, $woo_order, XmlExportEngine::$implode, $preview );

					$xmlWriter->closeElement(); // end post

					// add additional information after each node
					self::after_xml_node( $xmlWriter, $record->ID );
				} else {
					$articles   = array();
					$articles[] = XmlExportCpt::prepare_data( $record, XmlExportEngine::$exportOptions, $xmlWriter, $acfs, $woo, $woo_order, XmlExportEngine::$implode, $preview );
					$articles   = apply_filters( 'wp_all_export_csv_rows', $articles, XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

					$xmlWriter->writeArticle( $articles );
				}

				if ( ! $preview ) {
					do_action( 'pmxe_exported_post', $record->ID, XmlExportEngine::$exportRecord );
				}
			}
			wp_reset_postdata();
		}
		// [ \Exporting requested data ]

		if ( ! $is_custom_xml ) {
			$xmlWriter->closeElement();
		} // close root XML element

		if ( $preview ) {
			return $xmlWriter->wpae_flush();
		}

		return self::save_xml_to_file( $xmlWriter, $file_path, $is_cron, $exported_by_cron );

	}

	// [ XML Export Helpers ]
	private static function xml_header( $xmlWriter, $is_cron, $exported_by_cron ) {
		if ( $is_cron ) {
			if ( ! $exported_by_cron ) {

				$additional_data = apply_filters( 'wp_all_export_additional_data', array(), XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

				if ( ! empty( $additional_data ) ) {
					foreach ( $additional_data as $key => $value ) {
						self::addElement( $xmlWriter, $key, $value );
					}
				}
			}
		} else {

			if ( empty( PMXE_Plugin::$session->file ) ) {

				$additional_data = apply_filters( 'wp_all_export_additional_data', array(), XmlExportEngine::$exportOptions, XmlExportEngine::$exportID );

				if ( ! empty( $additional_data ) ) {
					foreach ( $additional_data as $key => $value ) {
						self::addElement( $xmlWriter, $key, $value );
					}
				}
			}
		}
	}

	private static function before_xml_node( $xmlWriter, $pid ) {
		$add_before_node = apply_filters( 'wp_all_export_add_before_node', array(), XmlExportEngine::$exportOptions, XmlExportEngine::$exportID, $pid );

		if ( ! empty( $add_before_node ) ) {
			foreach ( $add_before_node as $key => $value ) {
				self::addElement( $xmlWriter, $key, $value );
			}
		}
	}

	private static function after_xml_node( $xmlWriter, $pid ) {
		$add_after_node = apply_filters( 'wp_all_export_add_after_node', array(), XmlExportEngine::$exportOptions, XmlExportEngine::$exportID, $pid );

		if ( ! empty( $add_after_node ) ) {
			foreach ( $add_after_node as $key => $value ) {
				self::addElement( $xmlWriter, $key, $value );
			}
		}
	}

	private static function save_xml_to_file( $xmlWriter, $file_path, $is_cron, $exported_by_cron ) {
		$is_custom_xml = in_array( XmlExportEngine::$exportOptions['xml_template_type'], array(
			'custom',
			'XmlGoogleMerchants',
		) );

		if ( $is_cron ) {
			if ( ! $is_custom_xml ) {
				$xml_header = apply_filters( 'wp_all_export_xml_header', '<?xml version="1.0" encoding="UTF-8"?>', XmlExportEngine::$exportID );

				$xml = str_replace( '<?xml version="1.0" encoding="UTF-8"?>', $xml_header, $xmlWriter->wpae_flush() );
			} else {
				$xml_header = XmlExportEngine::$exportOptions['custom_xml_template_header'];

				$xml = ( ! $exported_by_cron || self::isRteExport( XmlExportEngine::$exportOptions ) ) ? PMXE_XMLWriter::preprocess_xml( $xml_header ) . $xmlWriter->wpae_flush() : $xmlWriter->wpae_flush();

			}

			if ( ! $exported_by_cron || self::isRteExport( XmlExportEngine::$exportOptions ) ) {

				if ( ! $is_custom_xml ) {
					$xml = substr( $xml, 0, ( strlen( self::$main_xml_tag ) + 4 ) * ( - 1 ) );
				}

				// The BOM will help some programs like Microsoft Excel read your export file if it includes non-English characters.
				if ( XmlExportEngine::$exportOptions['include_bom'] ) {
					file_put_contents( $file_path, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) . $xml );
				} else {
					file_put_contents( $file_path, $xml );
				}
			} else {
				$xml = ( ! $is_custom_xml ) ? substr( substr( $xml, 41 + strlen( self::$main_xml_tag ) ), 0, ( strlen( self::$main_xml_tag ) + 4 ) * ( - 1 ) ) : $xml;
				file_put_contents( $file_path, $xml, FILE_APPEND );
			}

			return $file_path;

		} else {

			if ( empty( PMXE_Plugin::$session->file ) ) {

				// generate export file name
				$export_file = wp_all_export_generate_export_file( XmlExportEngine::$exportID );

				if ( ! $is_custom_xml ) {
					$xml_header = apply_filters( 'wp_all_export_xml_header', '<?xml version="1.0" encoding="UTF-8"?>', XmlExportEngine::$exportID );

					$xml = str_replace( '<?xml version="1.0" encoding="UTF-8"?>', $xml_header, $xmlWriter->wpae_flush() );
				} else {
					$xml_header = XmlExportEngine::$exportOptions['custom_xml_template_header'];

					$xml = PMXE_XMLWriter::preprocess_xml( $xml_header ) . $xmlWriter->wpae_flush();
				}

				if ( ! $is_custom_xml ) {
					$xml = substr( $xml, 0, ( strlen( self::$main_xml_tag ) + 4 ) * ( - 1 ) );
				}

				// The BOM will help some programs like Microsoft Excel read your export file if it includes non-English characters.
				if ( XmlExportEngine::$exportOptions['include_bom'] ) {
					file_put_contents( $export_file, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) . $xml );
				} else {
					file_put_contents( $export_file, $xml );
				}

				PMXE_Plugin::$session->set( 'file', $export_file );
				PMXE_Plugin::$session->save_data();

			} else {

				$xml = ( ! $is_custom_xml ) ? substr( substr( $xmlWriter->wpae_flush(), 41 + strlen( self::$main_xml_tag ) ), 0, ( strlen( self::$main_xml_tag ) + 4 ) * ( - 1 ) ) : $xmlWriter->wpae_flush();

				// Don't write empty lines to file
				if ( ! empty( trim( $xml, " \n" ) ) ) {
					file_put_contents( PMXE_Plugin::$session->file, $xml, FILE_APPEND );
				}
			}

			return true;

		}
	}
	// [ \XML Export Helpers ]

	// [ CSV Export Helpers ]
	public static function prepare_csv_headers( &$headers, $ID, &$acfs ) {
		$element_name = ( ! empty( XmlExportEngine::$exportOptions['cc_name'][ $ID ] ) ) ? XmlExportEngine::$exportOptions['cc_name'][ $ID ] : 'untitled_' . $ID;
		$element_name = apply_filters( 'wp_all_export_field_name', wp_all_export_parse_field_name( $element_name ), XmlExportEngine::$exportID );
		$field_type   = XmlExportEngine::$exportOptions['cc_type'][ $ID ];

		if ( strpos( XmlExportEngine::$exportOptions['cc_label'][ $ID ], "item_data__" ) !== false ) {
			if ( XmlExportEngine::$woo_order_export ) {
				XmlExportEngine::$woo_order_export->get_element_header( $headers, XmlExportEngine::$exportOptions, $ID );
			}

			return;
		}

		switch ( XmlExportEngine::$exportOptions['cc_type'][ $ID ] ) {
			case 'woo':
				XmlExportEngine::$woo_export->get_element_header( $headers, XmlExportEngine::$exportOptions, $ID );
				break;

			case 'woo_order':
				if ( XmlExportEngine::$woo_order_export ) {
					XmlExportEngine::$woo_order_export->get_element_header( $headers, XmlExportEngine::$exportOptions, $ID );
				}
				break;

			case 'acf':
				if ( ! empty( $acfs ) ) {
					$single_acf_field = array_shift( $acfs );

					if ( is_array( $single_acf_field ) ) {
						foreach ( $single_acf_field as $acf_header ) {
							if ( ! in_array( $acf_header, $headers ) ) {
								$headers[] = $acf_header;
							}
						}
					} else {
						if ( ! in_array( $single_acf_field, $headers ) ) {
							$headers[] = $single_acf_field;
						}
					}
				}
				break;

			default:

				// For ID columns make first element in lowercase for Excel export
				if ( $element_name == 'ID' && ! $ID && isset( XmlExportEngine::$exportOptions['export_to_sheet'] ) && XmlExportEngine::$exportOptions['export_to_sheet'] != 'csv' ) {
					$element_name = 'id';
				}

				if ( ! in_array( $element_name, $headers ) ) {
					$headers[] = $element_name;
				} else {
					$is_added = false;
					$i        = 0;
					do {
						$new_element_name = $element_name . '_' . md5( $i );

						if ( ! in_array( $new_element_name, $headers ) ) {
							$headers[] = $new_element_name;
							$is_added  = true;
						}

						$i ++;
					} while ( ! $is_added );
				}


				break;
		}

		$addons = XmlExportEngine::get_addons();

		if ( in_array( $field_type, $addons ) ) {
			$ccOptions    = XmlExportEngine::$exportOptions['cc_options'][ $ID ];
			$fieldOptions = maybe_unserialize( $ccOptions );

			$headers = apply_filters( "pmxe_{$field_type}_addon_get_headers", $headers, $fieldOptions, XmlExportEngine::$exportOptions, $ID, $element_name );
		}

	}

	public static function _get_valid_header_name( $element_name ) {
		$element_name_parts = explode( "_", $element_name );

		$elementIndex = array_pop( $element_name_parts );

		if ( wp_all_export_isValidMd5( $elementIndex ) ) {
			$element_name_in_file = str_replace( "_" . $elementIndex, "", $element_name );
		} else {
			$element_name_in_file = $element_name;
		}

		$element_name_in_file = XmlExportEngine::sanitizeFieldName( $element_name_in_file );

		return $element_name_in_file;
	}


	// Add missing ACF headers
	public static function merge_headers( $file, &$headers ) {

		$in = fopen( $file, 'r' );

		$clear_old_headers = fgetcsv( $in, 0, XmlExportEngine::$exportOptions['delimiter'] );

		fclose( $in );

		$old_headers                  = array();
		$sanitized_headers            = array();
		$urldecoded_sanitized_headers = array();

		foreach ( $headers as $header ) {
			$sanitizedHeaderValue           = str_replace( "'", "", str_replace( '"', "", str_replace( chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ), "", $header ) ) );
			$sanitized_headers[]            = $sanitizedHeaderValue;
			$urldecoded_sanitized_headers[] = urldecode( $sanitizedHeaderValue );

		}
		foreach ( $clear_old_headers as $i => $header ) {
			$header = str_replace( "'", "", str_replace( '"', "", str_replace( chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ), "", $header ) ) );

			if ( ! in_array( $header, $old_headers ) ) {
				$old_headers[] = $header;
			} else {
				$is_added = false;
				$i        = 0;
				do {
					$new_element_name = $header . '_' . md5( $i );

					if ( ! in_array( $new_element_name, $old_headers ) ) {
						$old_headers[] = str_replace( "'", "", str_replace( '"', "", str_replace( chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ), "", $new_element_name ) ) );
						$is_added      = true;
					}

					$i ++;
				} while ( ! $is_added );
			}
		}

		$is_update_headers = false;

		foreach ( $sanitized_headers as $key => $header ) {
			if ( ! in_array( XmlExportEngine::sanitizeFieldName( $header ), $old_headers ) || XmlExportEngine::sanitizeFieldName( $header ) != $old_headers[ $key ] ) {
				$is_update_headers = true;
				break;
			}
		}

		foreach ( $old_headers as $key => $old_header ) {
			if ( ! in_array( XmlExportEngine::sanitizeFieldName( $old_header ), $urldecoded_sanitized_headers ) || XmlExportEngine::sanitizeFieldName( $old_header ) != $urldecoded_sanitized_headers[ $key ] ) {
				$is_update_headers = true;
				break;
			}
		}

		if ( $is_update_headers ) {
			$tmp_headers = $headers;
			$headers     = $old_headers;
			foreach ( $tmp_headers as $theader ) {
				if ( ! in_array( XmlExportEngine::sanitizeFieldName( $theader ), $headers ) ) {
					$headers[] = $theader;
				}
			}
			$tmp_file = str_replace( basename( $file ), 'iteration_' . basename( $file ), $file );
			copy( $file, $tmp_file );
			$in      = fopen( $tmp_file, 'r' );
			$out     = fopen( $file, 'w' );
			$headers = apply_filters( 'wp_all_export_csv_headers', $headers, XmlExportEngine::$exportID );

			if ( XmlExportEngine::$exportOptions['include_bom'] ) {
				fwrite( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
				self::getCsvWriter()->writeCsv( $out, array_map( array(
					'XmlCsvExport',
					'_get_valid_header_name',
				), $headers ), XmlExportEngine::$exportOptions['delimiter'] );
			} else {
				self::getCsvWriter()->writeCsv( $out, array_map( array(
					'XmlCsvExport',
					'_get_valid_header_name',
				), $headers ), XmlExportEngine::$exportOptions['delimiter'] );
			}

			apply_filters( 'wp_all_export_after_csv_line', $out, XmlExportEngine::$exportID );

			$exclude_old_headers = fgetcsv( $in );

			if ( is_resource( $in ) ) {
				while ( ! feof( $in ) ) {
					$data = fgetcsv( $in, 0, XmlExportEngine::$exportOptions['delimiter'] );
					if ( empty( $data ) ) {
						continue;
					}

					if ( count( array_values( $data ) ) < count( $old_headers ) ) {
						$difference = count( $old_headers ) - count( array_values( $data ) );
						for ( $i = 0; $i < $difference; $i ++ ) {
							$data[] = "";
						}
					}

					$data_assoc = array_combine( $old_headers, array_values( $data ) );
					$line       = array();
					foreach ( $headers as $header ) {
						$line[ $header ] = ( isset( $data_assoc[ $header ] ) ) ? $data_assoc[ $header ] : '';
					}
					self::getCsvWriter()->writeCsv( $out, $line, XmlExportEngine::$exportOptions['delimiter'] );
					apply_filters( 'wp_all_export_after_csv_line', $out, XmlExportEngine::$exportID );
				}
				fclose( $in );
			}
			fclose( $out );
			@unlink( $tmp_file );
		}
	}

	private static function save_csv_to_file( $file_path, $is_cron, $exported_by_cron ) {
		if ( $is_cron ) {
			if ( ! $exported_by_cron ) {
				// The BOM will help some programs like Microsoft Excel read your export file if it includes non-English characters.
				if ( XmlExportEngine::$exportOptions['include_bom'] ) {
					file_put_contents( $file_path, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) . ob_get_clean() );
				} else {
					file_put_contents( $file_path, ob_get_clean() );
				}
			} else {
				file_put_contents( $file_path, ob_get_clean(), FILE_APPEND );
			}

			return $file_path;

		} else {
			if ( empty( PMXE_Plugin::$session->file ) ) {

				// generate export file name
				$export_file = wp_all_export_generate_export_file( XmlExportEngine::$exportID );

				// The BOM will help some programs like Microsoft Excel read your export file if it includes non-English characters.
				if ( XmlExportEngine::$exportOptions['include_bom'] ) {
					file_put_contents( $export_file, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) . ob_get_clean() );
				} else {
					file_put_contents( $export_file, ob_get_clean() );
				}

				PMXE_Plugin::$session->set( 'file', $export_file );
				PMXE_Plugin::$session->save_data();

			} else {
				file_put_contents( PMXE_Plugin::$session->file, ob_get_clean(), FILE_APPEND );
			}

			return true;
		}
	}

	// [ \CSV Export Helpers ]

	public static function auto_genetate_export_fields( $post, $errors = false ) {
		$errors or $errors = new WP_Error();

		remove_all_filters( "wp_all_export_init_fields", 10 );
		remove_all_filters( "wp_all_export_default_fields", 10 );
		remove_all_filters( "wp_all_export_other_fields", 10 );
		remove_all_filters( "wp_all_export_available_sections", 10 );
		remove_all_filters( "wp_all_export_available_data", 10 );

		$engine = new XmlExportEngine( $post, $errors );
		$engine->init_additional_data();

		$auto_generate = array(
			'ids'        => array(),
			'cc_label'   => array(),
			'cc_php'     => array(),
			'cc_code'    => array(),
			'cc_sql'     => array(),
			'cc_type'    => array(),
			'cc_options' => array(),
			'cc_value'   => array(),
			'cc_name'    => array(),
		);

		$available_data = $engine->init_available_data();

		$available_sections = apply_filters( "wp_all_export_available_sections", $engine->get( 'available_sections' ) );

		foreach ( $available_sections as $slug => $section ) {
			if ( ! empty( $section['content'] ) and ! empty( $available_data[ $section['content'] ] ) ) {
				foreach ( $available_data[ $section['content'] ] as $field ) {
					if ( isset( $field['auto'] ) or ! \class_exists( 'WooCommerce' ) || 'product' != $post['cpt'] or ( is_array( $post['cpt'] ) && ! in_array( 'product', $post['cpt'] ) ) ) {
						$auto_generate['ids'][]         = 1;
						$auto_generate['cc_label'][]    = is_array( $field ) ? $field['label'] : $field;
						$auto_generate['cc_php'][]      = 0;
						$auto_generate['cc_code'][]     = '';
						$auto_generate['cc_sql'][]      = '';
						$auto_generate['cc_settings'][] = '';
						$auto_generate['cc_type'][]     = is_array( $field ) ? $field['type'] : $slug;
						$auto_generate['cc_options'][]  = '';
						$auto_generate['cc_value'][]    = is_array( $field ) ? $field['label'] : $field;
						$auto_generate['cc_name'][]     = is_array( $field ) ? $field['name'] : $field;
					}
				}
			}

			if ( ! empty( $section['additional'] ) ) {
				foreach ( $section['additional'] as $sub_slug => $sub_section ) {
					foreach ( $sub_section['meta'] as $field ) {
						$field_options = ( in_array( $sub_slug, array(
							'images',
							'attachments',
						) ) ) ? esc_attr( '{"is_export_featured":true,"is_export_attached":true,"image_separator":"|"}' ) : '0';
						$field_name    = '';
						switch ( $sub_slug ) {
							case 'images':
								$field_name = 'Image ' . $field['name'];
								break;
							case 'attachments':
								$field_name = 'Attachment ' . $field['name'];
								break;
							default:
								if ( isset( $field['name'] ) ) {
									$field_name = $field['name'];
								} else {
									$field_name = '';
								}

								break;
						}

						if ( is_array( $field ) and isset( $field['auto'] ) ) {
							$auto_generate['ids'][]         = 1;
							$auto_generate['cc_label'][]    = is_array( $field ) ? $field['label'] : $field;
							$auto_generate['cc_php'][]      = 0;
							$auto_generate['cc_code'][]     = '';
							$auto_generate['cc_sql'][]      = '';
							$auto_generate['cc_settings'][] = '';
							$auto_generate['cc_type'][]     = is_array( $field ) ? $field['type'] : $sub_slug;
							$auto_generate['cc_options'][]  = $field_options;
							$auto_generate['cc_value'][]    = is_array( $field ) ? $field['label'] : $field;
							$auto_generate['cc_name'][]     = $field_name;
						}
					}
				}
			}
		}

		if ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() || XmlExportEngine::get_addons_service()->isWooCommerceOrderAddonActive() && XmlExportWooCommerceOrder::$is_active ) {
			foreach ( XmlExportWooCommerceOrder::$order_sections as $slug => $section ) {
				if ( ! empty( $section['meta'] ) ) {
					foreach ( $section['meta'] as $cur_meta_key => $field ) {
						$auto_generate['ids'][]         = 1;
						$auto_generate['cc_label'][]    = is_array( $field ) ? $field['label'] : $cur_meta_key;
						$auto_generate['cc_php'][]      = 0;
						$auto_generate['cc_code'][]     = '';
						$auto_generate['cc_sql'][]      = '';
						$auto_generate['cc_settings'][] = '';
						$auto_generate['cc_type'][]     = is_array( $field ) ? $field['type'] : 'woo_order';
						$auto_generate['cc_options'][]  = is_array( $field ) ? $field['options'] : $slug;
						$auto_generate['cc_value'][]    = is_array( $field ) ? $field['label'] : $cur_meta_key;
						$auto_generate['cc_name'][]     = is_array( $field ) ? $field['name'] : $field;
					}
				}
			}
		}

		if ( XmlExportEngine::get_addons_service()->isAcfAddonActive() && ! XmlExportEngine::$is_comment_export && ! XmlExportEngine::$is_woo_review_export ) {
			XmlExportEngine::$acf_export->auto_generate_export_fields( $auto_generate );
		}

		if ( XmlExportEngine::$is_custom_addon_export ) {

			$auto_generate = [];

			$addon = GF_Export_Add_On::get_instance();
			$addon->run();
			$available_data = $addon->add_on->init_available_data( [] );

			foreach ( $available_data as $section ) {
				foreach ( $section as $field ) {
					if ( $field['auto'] ) {
						$auto_generate['ids'][]         = 1;
						$auto_generate['cc_label'][]    = $field['label'];
						$auto_generate['cc_php'][]      = 0;
						$auto_generate['cc_code'][]     = '';
						$auto_generate['cc_sql'][]      = '';
						$auto_generate['cc_settings'][] = '';
						$auto_generate['cc_type'][]     = $field['type'];
						$auto_generate['cc_value'][]    = $field['label'];
						$auto_generate['cc_name'][]     = $field['name'];
					}
				}
			}
		}

		foreach ( \XmlExportEngine::get_addons() as $addon ) {
			$auto_generate = apply_filters( "pmxe_get_{$addon}_addon_auto_generate_fields", $auto_generate, 10, 1 );
		}

		return $auto_generate;
	}

	/**
	 * @param $xmlWriter
	 * @param $key
	 * @param $value
	 */
	private static function addElement( $xmlWriter, $key, $value ) {
		$xmlWriter->startElement( preg_replace( '/[^a-z0-9_-]/i', '', $key ) );
		$xmlWriter->writeData( $value, preg_replace( '/[^a-z0-9_-]/i', '', $key ) );
		$xmlWriter->closeElement();
	}

	/**
	 * @return \Wpae\Csv\CsvWriter
	 */
	public static function getCsvWriter() {
		if ( is_null( self::$csvWriter ) ) {

			$csvStrategy                = apply_filters( 'wp_all_export_csv_strategy', \Wpae\Csv\CsvWriter::CSV_STRATEGY_DEFAULT );
			$useRcfCompliantLineEndings = apply_filters( 'wp_all_export_use_csv_compliant_line_endings', false );

			if ( $useRcfCompliantLineEndings ) {
				\Wpae\Csv\CsvRfcUtils::setDefaultWriteEol( \Wpae\Csv\CsvRfcUtils::EOL_WRITE_RFC );
			}

			self::$csvWriter = new \Wpae\Csv\CsvWriter( $csvStrategy );
		}

		return self::$csvWriter;
	}

	private static function isRteExport( $item ) {
		return isset( $item['enable_real_time_exports'] ) && $item['enable_real_time_exports'];
	}
}
