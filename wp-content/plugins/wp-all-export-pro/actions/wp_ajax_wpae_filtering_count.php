<?php

function pmxe_wp_ajax_wpae_filtering_count() {

	if ( ! check_ajax_referer( 'wp_all_export_secure', 'security', false ) ) {
		exit( json_encode( array( 'html' => esc_html__( 'Security check', 'wp_all_export_plugin' ) ) ) );
	}

	if ( ! current_user_can( PMXE_Plugin::$capabilities ) && ! current_user_can( PMXE_Plugin::CLIENT_MODE_CAP ) ) {
		exit( json_encode( array( 'html' => esc_html__( 'Security check', 'wp_all_export_plugin' ) ) ) );
	}

	ob_start();

	$hasVariations = false;

	$input = new PMXE_Input();

	$post = $input->post( 'data', array() );

	$filter_args = array(
		'filter_rules_hierarhy'   => empty( $post['filter_rules_hierarhy'] ) ? array() : $post['filter_rules_hierarhy'],
		'product_matching_mode'   => empty( $post['product_matching_mode'] ) ? 'strict' : $post['product_matching_mode'],
		'taxonomy_to_export'      => empty( $post['taxonomy_to_export'] ) ? '' : $post['taxonomy_to_export'],
		'sub_post_type_to_export' => empty( $post['sub_post_type_to_export'] ) ? '' : $post['sub_post_type_to_export'],
	);

	$input     = new PMXE_Input();
	$export_id = $input->get( 'id', 0 );
	if ( empty( $export_id ) ) {
		$export_id = ( ! empty( PMXE_Plugin::$session->update_previous ) ) ? PMXE_Plugin::$session->update_previous : 0;
	}

	$export = new PMXE_Export_Record();
	$export->getById( $export_id );

	if ( ! current_user_can( PMXE_Plugin::$capabilities ) && ! $export['client_mode_enabled'] && ! current_user_can( PMXE_Plugin::CLIENT_MODE_CAP ) ) {
		die( 'Security check' );
	}

	if ( ! $export->isEmpty() ) {
		XmlExportEngine::$exportRecord                                = $export;
		XmlExportEngine::$exportOptions                               = $export->options + PMXE_Plugin::get_default_import_options();
		XmlExportEngine::$exportOptions['export_only_new_stuff']      = $post['export_only_new_stuff'];
		XmlExportEngine::$exportOptions['export_only_modified_stuff'] = $post['export_only_modified_stuff'];
		if ( ! empty( $post['wpml_lang'] ) ) {
			XmlExportEngine::$exportOptions['wpml_lang'] = $post['wpml_lang'];
			$export->set( array( 'options' => XmlExportEngine::$exportOptions ) )->save();
		}
	} else {
		$sessionLang                                 = empty( PMXE_Plugin::$session->wpml_lang ) ? 'all' : PMXE_Plugin::$session->wpml_lang;
		XmlExportEngine::$exportOptions['wpml_lang'] = empty( $post['wpml_lang'] ) ? $sessionLang : $post['wpml_lang'];
	}

	if ( class_exists( 'SitePress' ) && ! empty( XmlExportEngine::$exportOptions['wpml_lang'] ) ) {
		PMXE_Plugin::$session->set( 'wpml_lang', XmlExportEngine::$exportOptions['wpml_lang'] );
		do_action( 'wpml_switch_language', XmlExportEngine::$exportOptions['wpml_lang'] );
	}

	XmlExportEngine::$is_user_export                                             = ( 'users' == $post['cpt'] ) ? true : false;
	XmlExportEngine::$is_woo_customer_export                                     = ( 'shop_customer' == $post['cpt'] ) ? true : false;
	XmlExportEngine::$is_comment_export                                          = ( 'comments' == $post['cpt'] ) ? true : false;
	XmlExportEngine::$is_woo_review_export                                       = ( 'shop_review' == $post['cpt'] ) ? true : false;
	XmlExportEngine::$is_taxonomy_export                                         = ( 'taxonomies' == $post['cpt'] ) ? true : false;
	XmlExportEngine::$is_custom_addon_export                                     = ( 'custom_wpae_gf_export_add_on' == $post['cpt'] ) ? true : false;
	XmlExportEngine::$post_types                                                 = array( $post['cpt'] );
	XmlExportEngine::$exportOptions['export_variations']                         = empty( $post['export_variations'] ) ? XmlExportEngine::VARIABLE_PRODUCTS_EXPORT_PARENT_AND_VARIATION : $post['export_variations'];
	XmlExportEngine::$exportOptions['export_only_customers_that_made_purchases'] = empty( $post['export_only_customers_that_made_purchases'] ) ? 0 : $post['export_only_customers_that_made_purchases'];

	if ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() ) {
		XmlExportEngine::$exportOptions['export_variations'] = empty( $post['export_variations'] ) ? XmlExportEngine::VARIABLE_PRODUCTS_EXPORT_PARENT_AND_VARIATION : $post['export_variations'];
	} else {
		XmlExportEngine::$exportOptions['export_variations'] = XmlExportEngine::VARIABLE_PRODUCTS_EXPORT_PARENT;
	}

	try {
		$filters = \Wpae\Pro\Filtering\FilteringFactory::getFilterEngine();
		$filters->init( $filter_args );
		$filters->parse();
	} catch ( \Wpae\App\Service\Addons\AddonNotFoundException $e ) {
		die( $e->getMessage() );
	}

	PMXE_Plugin::$session->set( 'whereclause', $filters->get( 'queryWhere' ) );
	PMXE_Plugin::$session->set( 'joinclause', $filters->get( 'queryJoin' ) );
	PMXE_Plugin::$session->save_data();

	$foundRecords  = 0;
	$total_records = 0;

	$cpt = array( $post['cpt'] );

	$is_products_export = ( $post['cpt'] == 'product' and class_exists( 'WooCommerce' ) );
	$is_orders_export   = ( $post['cpt'] == 'shop_order' and class_exists( 'WooCommerce' ) );

	if ( $post['export_type'] == 'advanced' ) {

		// Remove trailing comma from the query.
		PMXE_Plugin::$session->set( 'wp_query', rtrim( PMXE_Plugin::$session->get( 'wp_query' ), ',' ) );

		if ( XmlExportEngine::$is_user_export ) {

			// get total users
			$totalQuery = eval( 'return new WP_User_Query(array(' . PMXE_Plugin::$session->get( 'wp_query' ) . ', \'offset\' => 0, \'number\' => 10 ));' );
			if ( ! empty( $totalQuery->results ) ) {
				$total_records = $totalQuery->total_users;
			}

			ob_start();
			// get users depends on filters
			add_action( 'pre_user_query', 'wp_all_export_pre_user_query', 10, 1 );

			$queryCode       = 'return new WP_User_Query(array(' . PMXE_Plugin::$session->get( 'wp_query' ) . ', \'offset\' => 0, \'number\' => 10 ));';
			$userExportQuery = eval( $queryCode );

			if ( ! empty( $userExportQuery->total_users ) ) {
				$foundRecords = $userExportQuery->total_users;
			}
			remove_action( 'pre_user_query', 'wp_all_export_pre_user_query' );
			ob_get_clean();

		} elseif ( XmlExportEngine::$is_comment_export ) {
			// get total comments
			$totalQuery = eval( 'return new WP_Comment_Query(array(' . PMXE_Plugin::$session->get( 'wp_query' ) . ', \'number\' => 10, \'count\' => true ));' );
			// get_comments() will return the count since the count parameter above is true.
			$total_records = $totalQuery->get_comments();

			ob_start();
			// get comments depends on filters
			add_action( 'comments_clauses', 'wp_all_export_comments_clauses', 10, 1 );
			$exportQuery  = eval( 'return new WP_Comment_Query(array(' . PMXE_Plugin::$session->get( 'wp_query' ) . '));' );
			$foundRecords = count( $exportQuery->get_comments() );
			remove_action( 'comments_clauses', 'wp_all_export_comments_clauses' );
			ob_get_clean();
		} elseif ( XmlExportEngine::$is_woo_review_export ) {
			// get total comments
			$totalQuery    = eval( 'return new WP_Comment_Query(array(' . PMXE_Plugin::$session->get( 'wp_query' ) . ', \'number\' => 10, \'count\' => true ));' );
			$total_records = count( $totalQuery->get_comments() );

			ob_start();
			// get comments depends on filters
			add_action( 'comments_clauses', 'wp_all_export_comments_clauses', 10, 1 );
			$exportQuery  = eval( 'return new WP_Comment_Query(array(' . PMXE_Plugin::$session->get( 'wp_query' ) . '));' );
			$foundRecords = count( $exportQuery->get_comments() );
			remove_action( 'comments_clauses', 'wp_all_export_comments_clauses' );
			ob_get_clean();
		} else {

			remove_all_actions( 'parse_query' );
			remove_all_filters( 'posts_clauses' );
			wp_all_export_remove_before_post_except_toolset_actions();

			// get total custom post type records
			$totalQuery = eval( 'return new WP_Query(array(' . PMXE_Plugin::$session->get( 'wp_query' ) . ', \'offset\' => 0, \'posts_per_page\' => 10 ));' );
			if ( ! empty( $totalQuery->found_posts ) ) {
				$total_records = $totalQuery->found_posts;
			}

			wp_reset_postdata();

			ob_start();
			// get custom post type records depends on filters
			add_filter( 'posts_where', 'wp_all_export_posts_where', 10, 1 );

			add_filter( 'posts_join', 'wp_all_export_posts_join', 10, 1 );
			
			$exportQuery = eval( 'return new WP_Query(array(' . PMXE_Plugin::$session->get( 'wp_query' ) . ', \'offset\' => 0, \'posts_per_page\' => 10 ));' );

			/*
			 * We have to add this hack to cope with the percent encoding WP added in 4.8.3 [https://make.wordpress.org/core/2017/10/31/changed-behaviour-of-esc_sql-in-wordpress-4-8-3/]
			 * When it's present in the object's 'request' at this point it can't be properly decoded after the object is
			 * saved to the database. We use the remove_placeholder_escape() method to revert it to literal percent symbols.
			 * That allows things to work as expected. It's likely necessary so long as we're saving the WP_Query object
			 * to the database.
			 */ global $wpdb;
			$exportQuery->request = $wpdb->remove_placeholder_escape( $exportQuery->request );

			// We need to remove the placeholders from these values as well.
			if ( ! empty( $exportQuery->query_vars['search_orderby_title'] ) ) {
				foreach ( $exportQuery->query_vars['search_orderby_title'] as $key => $value ) {
					$exportQuery->query_vars['search_orderby_title'][ $key ] = $wpdb->remove_placeholder_escape( $value );
				}
			}

			if ( ! empty( $exportQuery->found_posts ) ) {
				$foundRecords = $exportQuery->found_posts;
			}
			remove_filter( 'posts_join', 'wp_all_export_posts_join' );
			remove_filter( 'posts_where', 'wp_all_export_posts_where' );
			ob_get_clean();
		}
	} else {
		if ( 'users' == $post['cpt'] or 'shop_customer' == $post['cpt'] ) {

			if ( 'shop_customer' == $post['cpt'] ) {

				// get total customers
				$totalQuery = new WP_User_Query( array(
					'role'    => 'customer',
					'orderby' => 'ID',
					'order'   => 'ASC',
					'number'  => 10,
				) );
				if ( ! empty( $totalQuery->results ) ) {
					$total_records = $totalQuery->get_total();
				}
			} else {
				// get total users
				$totalQuery = new WP_User_Query( array( 'orderby' => 'ID', 'order' => 'ASC', 'number' => 10 ) );
				if ( ! empty( $totalQuery->results ) ) {
					$total_records = $totalQuery->get_total();
				}
			}

			ob_start();
			// get users depends on filters
			add_action( 'pre_user_query', 'wp_all_export_pre_user_query', 10, 1 );
			$exportQuery = new WP_User_Query( array( 'orderby' => 'ID', 'order' => 'ASC', 'number' => 10 ) );
			if ( ! empty( $exportQuery->results ) ) {
				$foundRecords = $exportQuery->get_total();
			}
			remove_action( 'pre_user_query', 'wp_all_export_pre_user_query' );
			ob_get_clean();
		} elseif ( 'comments' == $post['cpt'] ) {

			$products = new WP_Query( array(
				'post_type' => 'product',
				'fields'    => 'ids',
			) );

			// get total comments
			global $wp_version;

			if ( version_compare( $wp_version, '4.2.0', '>=' ) ) {
				$totalQuery    = new WP_Comment_Query( array(
					'post__not_in' => $products->posts,
					'orderby'      => 'comment_ID',
					'order'        => 'ASC',
					'number'       => 10,
					'count'        => true,
				) );
				$total_records = $totalQuery->get_comments();
			} else {
				$total_records = get_comments( array(
					'post__not_in' => $products->posts,
					'orderby'      => 'comment_ID',
					'order'        => 'ASC',
					'number'       => 10,
					'count'        => true,
				) );
			}

			ob_start();
			// get comments depends on filters
			add_action( 'comments_clauses', 'wp_all_export_comments_clauses', 10, 1 );

			if ( version_compare( $wp_version, '4.2.0', '>=' ) ) {
				$exportQuery  = new WP_Comment_Query( array(
					'post__not_in' => $products->posts,
					'orderby'      => 'comment_ID',
					'order'        => 'ASC',
				) );
				$foundRecords = count( $exportQuery->get_comments() );
			} else {
				$foundRecords = count( get_comments( array(
					'post__not_in' => $products->posts,
					'orderby'      => 'comment_ID',
					'order'        => 'ASC',
				) ) );
			}
			remove_action( 'comments_clauses', 'wp_all_export_comments_clauses' );
			ob_get_clean();
		} elseif ( 'shop_review' == $post['cpt'] ) {
			// get total comments
			global $wp_version;

			if ( version_compare( $wp_version, '4.2.0', '>=' ) ) {
				$totalQuery    = new WP_Comment_Query( array(
					'post_type' => 'product',
					'orderby'   => 'comment_ID',
					'order'     => 'ASC',
					'number'    => 10,
					'count'     => true,
				) );
				$total_records = $totalQuery->get_comments();
			} else {
				$total_records = get_comments( array(
					'post_type' => 'product',
					'orderby'   => 'comment_ID',
					'order'     => 'ASC',
					'number'    => 10,
					'count'     => true,
				) );
			}

			ob_start();
			// get comments depends on filters
			add_action( 'comments_clauses', 'wp_all_export_comments_clauses', 10, 1 );

			if ( version_compare( $wp_version, '4.2.0', '>=' ) ) {
				$exportQuery  = new WP_Comment_Query( array(
					'post_type' => 'product',
					'orderby'   => 'comment_ID',
					'order'     => 'ASC',
				) );
				$foundRecords = count( $exportQuery->get_comments() );
			} else {
				$foundRecords = count( get_comments( array(
					'post_type' => 'product',
					'orderby'   => 'comment_ID',
					'order'     => 'ASC',
				) ) );
			}
			remove_action( 'comments_clauses', 'wp_all_export_comments_clauses' );
			ob_get_clean();
		} elseif ( 'taxonomies' == $post['cpt'] ) {
			global $wp_version;

			if ( version_compare( $wp_version, '4.6.0', '>=' ) ) {
				$totalQuery    = new WP_Term_Query( array(
					'taxonomy'   => $post['taxonomy_to_export'],
					'orderby'    => 'name',
					'order'      => 'ASC',
					'number'     => 10,
					'hide_empty' => false,
				) );
				$total_records = count( $totalQuery->get_terms() );

				ob_start();
				// get comments depends on filters
				add_filter( 'terms_clauses', 'wp_all_export_terms_clauses', 10, 3 );
				$exportQuery  = new WP_Term_Query( array(
					'taxonomy'   => $post['taxonomy_to_export'],
					'orderby'    => 'name',
					'order'      => 'ASC',
					'hide_empty' => false,
				) );
				$foundRecords = count( $exportQuery->get_terms() );
				remove_filter( 'terms_clauses', 'wp_all_export_terms_clauses' );
				ob_get_clean();
			} else {
				?>
				<div class="founded_records">
					<h3><?php esc_html_e( 'Unable to Export', 'wp_all_export_plugin' ); ?></h3>
					<h4><?php printf( esc_html__( "Exporting taxonomies requires WordPress 4.6 or greater", "wp_all_export_plugin" ), wp_all_export_get_cpt_name( $cpt, 2, $post ) ); ?></h4>
				</div>
				<?php
				exit( json_encode( array(
					'html'          => ob_get_clean(),
					'found_records' => 0,
					'hasVariations' => $hasVariations,
				) ) );
				die;
			}

		} else {

			if ( strpos( $post['cpt'], 'custom_' ) === 0 ) {

				$addon = GF_Export_Add_On::get_instance();

				$exportQuery = $addon->add_on->get_query( 0, 0, $filter_args );

				PMXE_Plugin::$session->set( 'exportQuery', $exportQuery );

				if ( is_object( $exportQuery ) ) {
					$foundRecords = $exportQuery->found_posts;
				} else {
					$foundRecords = 0;
				}

			} else {
				remove_all_actions( 'parse_query' );
				remove_all_filters( 'posts_clauses' );
				wp_all_export_remove_before_post_except_toolset_actions();

				if ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() ) {
					$custom_posts = [ 'product', 'product_variation' ];
				} else {
					$custom_posts = [ 'product' ];
				}

				$cpt = ( $is_products_export ) ? $custom_posts : array( $post['cpt'] );


				// get total custom post type records
				$totalQuery = new WP_Query( array(
					'post_type'      => $cpt,
					'post_status'    => 'any',
					'orderby'        => 'ID',
					'order'          => 'ASC',
					'posts_per_page' => 10,
				) );

				if ( ! empty( $totalQuery->found_posts ) ) {
					$total_records = $totalQuery->found_posts;
				}

				wp_reset_postdata();

				ob_start();
				// get custom post type records depends on filters
				add_filter( 'posts_where', 'wp_all_export_posts_where', 10, 1 );
				add_filter( 'posts_join', 'wp_all_export_posts_join', 10, 1 );


				if ( $is_products_export ) {

					add_filter( 'posts_where', 'wp_all_export_numbering_where', 15, 1 );

					if ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() ) {
						$productsQuery   = new WP_Query( array(
							'post_type'      => array(
								'product',
								'product_variation',
							),
							'post_status'    => 'any',
							'orderby'        => 'ID',
							'order'          => 'ASC',
							'posts_per_page' => 10,
						) );
						$variationsQuery = new WP_Query( array(
							'post_type'      => 'product_variation',
							'post_status'    => 'any',
							'orderby'        => 'ID',
							'order'          => 'ASC',
							'posts_per_page' => 10,
						) );

						$foundProducts   = $productsQuery->found_posts;
						$foundVariations = $variationsQuery->found_posts;

						$foundRecords  = $foundProducts;
						$hasVariations = ! ! $foundVariations;

					} else if ( XmlExportEngine::get_addons_service()->isWooCommerceProductAddonActive() ) {
						$productsQuery = new WP_Query( array(
							'post_type'      => array( 'product' ),
							'post_status'    => 'any',
							'orderby'        => 'ID',
							'order'          => 'ASC',
							'posts_per_page' => 10,
						) );
						$foundProducts = $productsQuery->found_posts;

						$foundRecords  = $foundProducts;
						$hasVariations = false;
					}

					remove_filter( 'posts_where', 'wp_all_export_numbering_where' );

				} else if ( $is_orders_export && PMXE_Plugin::hposEnabled() ) {


					add_filter( 'posts_where', 'wp_all_export_numbering_where', 15, 1 );


					if ( XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() || XmlExportEngine::get_addons_service()->isWooCommerceOrderAddonActive() ) {
						$ordersQuery = new \Wpae\WordPress\OrderQuery();

						$foundRecords = count( $ordersQuery->getOrders() );

						PMXE_Plugin::$session->set( 'exportQuery', $ordersQuery );
						PMXE_Plugin::$session->save_data();

					} else if ( XmlExportEngine::get_addons_service()->isWooCommerceProductAddonActive() ) {
						$productsQuery = new WP_Query( array(
							'post_type'      => array( 'product' ),
							'post_status'    => 'any',
							'orderby'        => 'ID',
							'order'          => 'ASC',
							'posts_per_page' => 10,
						) );
						$foundProducts = $productsQuery->found_posts;


						$foundRecords  = $foundProducts;
						$hasVariations = false;
					}

					remove_filter( 'posts_where', 'wp_all_export_numbering_where' );


				} else {
					$exportQuery = new WP_Query( array(
						'post_type'      => $cpt,
						'post_status'    => 'any',
						'orderby'        => 'ID',
						'order'          => 'ASC',
						'posts_per_page' => 10,
					) );
					if ( ! empty( $exportQuery->found_posts ) ) {
						$foundRecords = $exportQuery->found_posts;
					}
				}

				remove_filter( 'posts_where', 'wp_all_export_posts_where' );

				remove_filter( 'posts_join', 'wp_all_export_posts_join' );

				ob_end_clean();
			}

		}
	}

	if ( class_exists( 'SitePress' ) && ! empty( XmlExportEngine::$exportOptions['wpml_lang'] ) && defined( 'ICL_LANGUAGE_CODE' ) ) {
		PMXE_Plugin::$session->set( 'wpml_lang', XmlExportEngine::$exportOptions['wpml_lang'] );
		do_action( 'wpml_switch_language', ICL_LANGUAGE_CODE );
	}

	if ( XmlExportTaxonomy::$is_active ) {
		if ( isset( $exportQuery ) ) {
			PMXE_Plugin::$session->set( 'exportQuery', $exportQuery );
			PMXE_Plugin::$session->save_data();
		}
	}

	$addons = new \Wpae\App\Service\Addons\AddonService();
	if ( $addons->isWooCommerceAddonActive() ) {
		if ( XmlExportWooCommerceReview::$is_active ) {
			if ( isset( $exportQuery ) ) {
				PMXE_Plugin::$session->set( 'exportQuery', $exportQuery );
				PMXE_Plugin::$session->save_data();
			}
		}
	}

	if ( $post['is_confirm_screen'] ) {
		if ( ( isset( $post['enable_real_time_exports'] ) && $post['enable_real_time_exports'] ) ) {
			?>
			<h3><?php esc_html_e( 'Your export is ready to run.', 'wp_all_export_plugin' ); ?></h3>
			<h4><?php printf( esc_html__( 'WP All Export will export the most recent %s.', 'wp_all_export_plugin' ), strtolower( wp_all_export_get_cpt_name( $cpt, 1, $post ) ) ); ?></h4>
			<?php
		} else {

			?>

			<?php if ( $foundRecords > 0 ) : ?>
				<h3><?php esc_html_e( 'Your export is ready to run.', 'wp_all_export_plugin' ); ?></h3>
				<h4><?php printf( esc_html__( 'WP All Export will export %d %s.', 'wp_all_export_plugin' ), $foundRecords, wp_all_export_get_cpt_name( $cpt, $foundRecords, $post ) ); ?></h4>
			<?php else: ?>
				<?php if ( ! $export->isEmpty() and ( $export->options['export_only_new_stuff'] or $export->options['export_only_modified_stuff'] ) ): ?>
					<h3><?php esc_html_e( 'Nothing to export.', 'wp_all_export_plugin' ); ?></h3>
					<h4><?php printf( esc_html__( "All %s have already been exported.", "wp_all_export_plugin" ), wp_all_export_get_cpt_name( $cpt, 2, $post ) ); ?></h4>
				<?php elseif ( $total_records > 0 ): ?>
					<h3><?php esc_html_e( 'Nothing to export.', 'wp_all_export_plugin' ); ?></h3>
					<h4><?php printf( esc_html__( "No matching %s found for selected filter rules.", "wp_all_export_plugin" ), wp_all_export_get_cpt_name( $cpt, 2, $post ) ); ?></h4>
				<?php else: ?>
					<h3><?php esc_html_e( 'Nothing to export.', 'wp_all_export_plugin' ); ?></h3>
					<h4><?php printf( esc_html__( "There aren't any %s to export.", "wp_all_export_plugin" ), wp_all_export_get_cpt_name( $cpt, 2, $post ) ); ?></h4>
				<?php endif; ?>
			<?php endif; ?>

			<?php

		}
	} elseif ( $post['is_template_screen'] ) {

		if ( isset( XMLExportEngine::$exportOptions['enable_real_time_exports'] ) && XMLExportEngine::$exportOptions['enable_real_time_exports'] ) {
			?>
			<h3><?php esc_html_e( 'Your export is ready to run.', 'wp_all_export_plugin' ); ?></h3>
			<h4><?php printf( esc_html__( 'WP All Export will export the most recent %s.', 'wp_all_export_plugin' ), strtolower( wp_all_export_get_cpt_name( $cpt, 1, $post ) ) ); ?></h4>
			<?php
		} else {
			?>

			<?php if ( $foundRecords > 0 ) : ?>
				<h3><span class="matches_count"><?php echo $foundRecords; ?></span>
					<strong><?php echo wp_all_export_get_cpt_name( $cpt, $foundRecords, $post ); ?></strong> will be
					exported</h3>
				<h4><?php esc_html_e( "Drag &amp; drop data to include in the export file.", "wp_all_export_plugin" ); ?></h4>
			<?php else: ?>
				<?php if ( ! $export->isEmpty() and ( $export->options['export_only_new_stuff'] or $export->options['export_only_modified_stuff'] ) ): ?>
					<h3><?php esc_html_e( 'Nothing to export.', 'wp_all_export_plugin' ); ?></h3>
					<h4><?php printf( esc_html__( "All %s have already been exported.", "wp_all_export_plugin" ), wp_all_export_get_cpt_name( $cpt, 2, $post ) ); ?></h4>
				<?php elseif ( $total_records > 0 ): ?>
					<h3><?php esc_html_e( 'Nothing to export.', 'wp_all_export_plugin' ); ?></h3>
					<h4><?php printf( esc_html__( "No matching %s found for selected filter rules.", "wp_all_export_plugin" ), wp_all_export_get_cpt_name( $cpt, 2, $post ) ); ?></h4>
				<?php else: ?>
					<h3><?php esc_html_e( 'Nothing to export.', 'wp_all_export_plugin' ); ?></h3>
					<h4><?php printf( esc_html__( "There aren't any %s to export.", "wp_all_export_plugin" ), wp_all_export_get_cpt_name( $cpt, 2, $post ) ); ?></h4>
				<?php endif; ?>
			<?php endif; ?>

		<?php }
		?>

		<?php
	} else {
		?>

		<div class="founded_records">
			<?php if ( $foundRecords > 0 ) : ?>
				<h3><span class="matches_count"><?php echo $foundRecords; ?></span>
					<strong><?php echo wp_all_export_get_cpt_name( $cpt, $foundRecords, $post ); ?></strong> will be
					exported</h3>
				<h4><?php esc_html_e( "Continue to configure and run your export.", "wp_all_export_plugin" ); ?></h4>
			<?php elseif ( $total_records > 0 ): ?>
				<h4 style="line-height:60px;"><?php printf( esc_html__( "No matching %s found for selected filter rules.", "wp_all_export_plugin" ), wp_all_export_get_cpt_name( $cpt, 2, $post ) ); ?></h4>
			<?php else: ?>
				<h4 style="line-height:60px;"><?php printf( esc_html__( "There aren't any %s to export.", "wp_all_export_plugin" ), wp_all_export_get_cpt_name( $cpt, 2, $post ) ); ?></h4>
			<?php endif; ?>
		</div>
		<?php
	}

	exit( json_encode( array(
		'html'          => ob_get_clean(),
		'found_records' => $foundRecords,
		'hasVariations' => $hasVariations,
	) ) );
	die;

}

function wp_all_export_numbering_where( $where ) {
	global $wpdb;

	$excludeVariationsSql = " AND $wpdb->posts.ID NOT IN (SELECT o.ID FROM $wpdb->posts o
                            LEFT OUTER JOIN $wpdb->posts r ON o.post_parent = r.ID WHERE ((r.post_status = 'trash' OR r.ID IS NULL) AND o.post_type = 'product_variation'))";

	$groupSql = "GROUP BY $wpdb->posts.ID";
	if ( strpos( $where, $groupSql ) !== false ) {
		$where = str_replace( $groupSql, $excludeVariationsSql . " " . $groupSql, $where );
	} else {
		$where = $where . $excludeVariationsSql;
	}

	return $where;
}