<?php
if ( ! function_exists( 'wpai_wp_enqueue_code_editor' ) ) {
	function wpai_wp_enqueue_code_editor( $args ) {

		// We need syntax highlighting to work in the plugin regardless of user setting.
		// Function matches https://developer.wordpress.org/reference/functions/wp_enqueue_code_editor/ otherwise.
		/*if ( is_user_logged_in() && 'false' === wp_get_current_user()->syntax_highlighting ) {
			return false;
		}*/

		$settings = wp_get_code_editor_settings( $args );

		if ( empty( $settings ) || empty( $settings['codemirror'] ) ) {
			return false;
		}

		wp_enqueue_script( 'code-editor' );
		wp_enqueue_style( 'code-editor' );

		if ( isset( $settings['codemirror']['mode'] ) ) {
			$mode = $settings['codemirror']['mode'];
			if ( is_string( $mode ) ) {
				$mode = array(
					'name' => $mode,
				);
			}

			if ( ! empty( $settings['codemirror']['lint'] ) ) {
				switch ( $mode['name'] ) {
					case 'css':
					case 'text/css':
					case 'text/x-scss':
					case 'text/x-less':
						wp_enqueue_script( 'csslint' );
						break;
					case 'htmlmixed':
					case 'text/html':
					case 'php':
					case 'application/x-httpd-php':
					case 'text/x-php':
						wp_enqueue_script( 'htmlhint' );
						wp_enqueue_script( 'csslint' );
						wp_enqueue_script( 'jshint' );
						if ( ! current_user_can( 'unfiltered_html' ) ) {
							wp_enqueue_script( 'htmlhint-kses' );
						}
						break;
					case 'javascript':
					case 'application/ecmascript':
					case 'application/json':
					case 'application/javascript':
					case 'application/ld+json':
					case 'text/typescript':
					case 'application/typescript':
						wp_enqueue_script( 'jshint' );
						wp_enqueue_script( 'jsonlint' );
						break;
				}
			}
		}

		wp_add_inline_script( 'code-editor', sprintf( 'jQuery.extend( wp.codeEditor.defaultSettings, %s );', wp_json_encode( $settings ) ) );

		/**
		 * Fires when scripts and styles are enqueued for the code editor.
		 *
		 * @param array $settings Settings for the enqueued code editor.
		 *
		 * @since 4.9.0
		 *
		 */
		do_action( 'wp_enqueue_code_editor', $settings );

		return $settings;
	}
}

if ( ! function_exists( 'pmxi_if' ) ) {
	function pmxi_if( $left_condition, $operand, $right_condition, $then, $else = '' ) {
		$str = trim( implode( ' ', array( $left_condition, html_entity_decode( $operand ), $right_condition ) ) );

		return ( eval ( "return ($str);" ) ) ? $then : $else;
	}
}

if ( ! function_exists( 'is_empty' ) ) {
	function is_empty( $var ) {
		return empty( $var );
	}
}

if ( ! function_exists( 'pmxi_human_filesize' ) ) {
	function pmxi_human_filesize( $bytes, $decimals = 2 ) {
		$sz     = 'BKMGTP';
		$factor = (int) floor( ( strlen( $bytes ) - 1 ) / 3 );

		return sprintf( "%.{$decimals}f", $bytes / pow( 1024, $factor ) ) . ( isset( $sz[ $factor ] ) ? $sz[ $factor ] : '' );
	}
}

if ( ! function_exists( 'pmxi_get_remote_image_ext' ) ) {
	function pmxi_get_remote_image_ext( $filePath ) {
		$response     = wp_remote_get( $filePath );
		$headers      = wp_remote_retrieve_headers( $response );
		$content_type = ( ! empty( $headers['content-type'] ) ) ? explode( '/', $headers['content-type'] ) : false;
		if ( ! empty( $content_type[1] ) ) {
			$extensions = wp_all_import_supported_image_extensions();
			$extensions = explode( "|", $extensions );
			foreach ( $extensions as $extension ) {
				if ( preg_match( '%' . $extension . '%i', $content_type[1] ) ) {
					return $extension;
				}
			}
			if ( preg_match( '%pdf%i', $content_type[1] ) ) {
				return 'pdf';
			}

			return ( $content_type[1] == "unknown" ) ? "" : $content_type[1];
		}

		return '';
	}
}

if ( ! function_exists( 'pmxi_getExtension' ) ) {
	function pmxi_getExtension( $str ) {
		$i = strrpos( $str, "." );
		if ( ! $i ) {
			return "";
		}
		$l   = strlen( $str ) - $i;
		$ext = substr( $str, $i + 1, $l );

		return ( strlen( $ext ) <= 4 ) ? $ext : "";
	}
}

if ( ! function_exists( 'pmxi_getExtensionFromStr' ) ) {
	function pmxi_getExtensionFromStr( $str ) {
		$filetype = wp_check_filetype( $str );
		if ( empty( $filetype['ext'] ) ) {
			$filetype = wp_check_filetype( strtok( $str, "?" ) );
		}

		return ( $filetype['ext'] == "unknown" ) ? "" : $filetype['ext'];
	}
}

if ( ! function_exists( 'pmxi_convert_encoding' ) ) {
	function pmxi_convert_encoding( $source, $target_encoding = 'ASCII' ) {
		if ( function_exists( 'mb_detect_encoding' ) ) {
			// detect the character encoding of the incoming file
			$encoding = mb_detect_encoding( $source, "auto" );
			// escape all of the question marks so we can remove artifacts from
			// the unicode conversion process
			$target = str_replace( "?", "[question_mark]", $source );
			// convert the string to the target encoding
			$target = mb_convert_encoding( $target, $target_encoding, $encoding );
			// remove any question marks that have been introduced because of illegal characters
			$target = str_replace( "?", "", $target );
			// replace the token string "[question_mark]" with the symbol "?"
			$target = str_replace( "[question_mark]", "?", $target );

			return html_entity_decode( $target, ENT_COMPAT, 'UTF-8' );
		}

		return $source;
	}
}

if ( ! function_exists( 'wp_all_import_get_remote_file_name' ) ) {
	function wp_all_import_get_remote_file_name( $filePath ) {
		$bn   = wp_all_import_basename( $filePath );
		$type = ( preg_match( '%\W(csv|txt|dat|psv)$%i', $bn ) ) ? 'csv' : false;
		if ( ! $type ) {
			$type = ( preg_match( '%\W(xml)$%i', $bn ) ) ? 'xml' : false;
		}
		if ( ! $type ) {
			$type = ( preg_match( '%\W(zip)$%i', $bn ) ) || ( preg_match( '%\W(get_bundle)$%i', $bn ) && strpos( $bn, 'export_id' ) !== false && strpos( $bn, 'security_token' ) !== false ) ? 'zip' : false;
		}
		if ( ! $type ) {
			$type = ( preg_match( '%\W(gz)$%i', $bn ) ) ? 'gz' : false;
		}

		if ( ! $type ) {
			$filePath = strtok( $filePath, "?" );
			$bn       = wp_all_import_basename( $filePath );
			$type     = ( preg_match( '%\W(csv|txt|dat|psv)$%i', $bn ) ) ? 'csv' : false;
			if ( ! $type ) {
				$type = ( preg_match( '%\W(xml)$%i', $bn ) ) ? 'xml' : false;
			}
			if ( ! $type ) {
				$type = ( preg_match( '%\W(zip)$%i', $bn ) ) ? 'zip' : false;
			}
			if ( ! $type ) {
				$type = ( preg_match( '%\W(gz)$%i', $bn ) ) ? 'gz' : false;
			}
		}

		return ( $type ) ? $type : false;
	}
}

if ( ! function_exists( 'wp_all_import_translate_uri' ) ) {
	function wp_all_import_translate_uri( $uri ) {
		$parts = explode( '/', $uri );
		for ( $i = 1; $i < count( $parts ); $i ++ ) {
			$parts[ $i ] = rawurlencode( $parts[ $i ] );
		}

		return implode( '/', $parts );
	}
}

if ( ! function_exists( 'wp_all_import_cdata_filter' ) ) {
	function wp_all_import_cdata_filter( $matches ) {
		PMXI_Import_Record::$cdata[] = $matches[0];

		return '{{CPLACE_' . count( PMXI_Import_Record::$cdata ) . '}}';
	}
}

if ( ! function_exists( 'wp_all_import_amp_filter' ) ) {
	function wp_all_import_amp_filter( $matches ) {
		if ( empty( $matches[1] ) && ! empty( $matches[0] ) ) {
			return "&amp;";
		}

		return in_array( $matches[1], array( "amp;", "lt;", "gt;" ) ) ? "&" . $matches[1] : "&amp;" . $matches[1];
	}
}

if ( ! function_exists( 'wp_all_import_isValidMd5' ) ) {
	function wp_all_import_isValidMd5( $md5 = '' ) {
		return preg_match( '/^[a-f0-9]{32}$/', $md5 );
	}
}

if ( ! function_exists( 'wp_all_import_get_relative_path' ) ) {
	function wp_all_import_get_relative_path( $path ) {
		$uploads = wp_upload_dir();

		return str_replace( $uploads['basedir'], '', $path );
	}
}

if ( ! function_exists( 'wp_all_import_get_absolute_path' ) ) {
	function wp_all_import_get_absolute_path( $path ) {
		$uploads = wp_upload_dir();

		return ( strpos( $path, $uploads['basedir'] ) === false and ! preg_match( '%^https?://%i', $path ) ) ? $uploads['basedir'] . $path : $path;
	}
}

if ( ! function_exists( 'wp_all_import_clear_xss' ) ) {
	function wp_all_import_clear_xss( $str ) {
		return stripslashes( esc_sql( htmlspecialchars( strip_tags( $str ) ) ) );
	}
}

if ( ! function_exists( 'wp_all_import_get_taxonomies' ) ) {
	function wp_all_import_get_taxonomies() {
		// get all taxonomies
		$taxonomies = get_taxonomies( false, 'objects' );
		$ignore     = array( 'nav_menu', 'link_category' );
		$r          = array();
		// populate $r
		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy->name, $ignore ) ) {
				continue;
			}
			if ( ! empty( $taxonomy->labels->name ) && strpos( $taxonomy->labels->name, "_" ) === false ) {
				$r[ $taxonomy->name ] = $taxonomy->labels->name;
			} else {
				$r[ $taxonomy->name ] = empty( $taxonomy->labels->singular_name ) ? $taxonomy->name : $taxonomy->labels->singular_name;
			}
		}
		asort( $r, SORT_FLAG_CASE | SORT_STRING );

		// return
		return $r;
	}
}

if ( ! function_exists( 'wp_all_import_is_password_protected_feed' ) ) {
	function wp_all_import_is_password_protected_feed( $url ) {
		$url_data = parse_url( $url );

		return ( ! empty( $url_data['user'] ) and ! empty( $url_data['pass'] ) ) ? true : false;
	}
}

if ( ! function_exists( 'wp_all_import_cmp_custom_types' ) ) {
	function wp_all_import_cmp_custom_types( $a, $b ) {
		return strcmp( $a->labels->name, $b->labels->name );
	}
}

if ( ! function_exists( 'wp_all_import_basename' ) ) {
	function wp_all_import_basename( $file ) {
		$a = explode( '/', $file );

		return end( $a );
	}
}

if ( ! function_exists( 'wp_all_import_update_post_count' ) ) {
	function wp_all_import_update_post_count() {
		global $wpdb;
		update_option( 'post_count', (int) $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_status = 'publish' and post_type = 'post'" ), false );
	}
}

if ( ! function_exists( 'wp_all_import_supported_image_types' ) ) {
	function wp_all_import_supported_image_types() {
		$supported_image_types = array( IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP );
		if ( defined( 'IMAGETYPE_WEBP' ) ) {
			$supported_image_types[] = IMAGETYPE_WEBP;
		}
		if ( defined( 'IMAGETYPE_AVIF' ) ) {
			$supported_image_types[] = IMAGETYPE_AVIF;
		}

		return $supported_image_types;
	}
}

if ( ! function_exists( 'wp_all_import_generate_functions_hash' ) ) {
	function wp_all_import_generate_functions_hash() {
		$uploads        = wp_upload_dir();
		$functions_hash = false;
		$functions_file = $uploads['basedir'] . DIRECTORY_SEPARATOR . WP_ALL_IMPORT_UPLOADS_BASE_DIRECTORY . DIRECTORY_SEPARATOR . 'functions.php';
		if ( @file_exists( $functions_file ) ) {
			$functions_hash = hash_file( 'md5', $functions_file );
			// Check functions file from current theme.
			$theme_functions_file = get_template_directory() . '/functions.php';
			if ( @file_exists( $theme_functions_file ) ) {
				$functions_hash .= hash_file( 'md5', $theme_functions_file );
			}
		}

		return $functions_hash;
	}
}

if ( ! function_exists( 'wp_all_import_is_update_custom_field' ) ) {
	function wp_all_import_is_update_custom_field( $meta_key, $options ) {
		if ( $options['update_all_data'] == 'yes' ) {
			return true;
		}
		if ( ! $options['is_update_custom_fields'] ) {
			return false;
		}
		if ( $options['update_custom_fields_logic'] == "full_update" ) {
			return true;
		}
		if ( $options['update_custom_fields_logic'] == "only" && ! empty( $options['custom_fields_list'] ) && is_array( $options['custom_fields_list'] ) && in_array( $meta_key, $options['custom_fields_list'] ) ) {
			return true;
		}
		if ( $options['update_custom_fields_logic'] == "all_except" && ( empty( $options['custom_fields_list'] ) || ! in_array( $meta_key, $options['custom_fields_list'] ) ) ) {
			return true;
		}

		return false;
	}
}
if ( ! function_exists( 'wp_all_import_delete_missing_notice' ) ) {
	function wp_all_import_delete_missing_notice( $options ) {

		$options += PMXI_Plugin::get_default_import_options();

		$notice = false;
		if ( ! empty( $options['is_delete_missing'] ) ) {

			switch ( $options['custom_type'] ) {
				case 'taxonomies':
					$custom_type = get_taxonomy( $options['taxonomy_type'] );
					if ( empty( $custom_type ) ) {
						$custom_type                        = new stdClass();
						$custom_type->labels                = new stdClass();
						$custom_type->labels->name          = __( 'Taxonomy Terms', 'wp-all-import-pro' );
						$custom_type->labels->singular_name = __( 'Taxonomy Term', 'wp-all-import-pro' );
					}
					break;
				case 'comments':
					$custom_type                        = new stdClass();
					$custom_type->labels                = new stdClass();
					$custom_type->labels->name          = __( 'Comments', 'wp-all-import-pro' );
					$custom_type->labels->singular_name = __( 'Comment', 'wp-all-import-pro' );
					break;
				case 'woo_reviews':
					$custom_type                        = new stdClass();
					$custom_type->labels                = new stdClass();
					$custom_type->labels->name          = __( 'Reviews', 'wp-all-import-pro' );
					$custom_type->labels->singular_name = __( 'Review', 'wp-all-import-pro' );
					break;
				default:
					$custom_type = wp_all_import_custom_type( $options['custom_type'] );
					break;
			}

			if ( $options['delete_missing_logic'] == 'all' && $options['delete_missing_action'] == 'keep' && ! empty( $options['is_change_post_status_of_removed'] ) ) {
				if ( ! empty( $options['is_send_removed_to_trash'] ) ) {
					$notice = sprintf( __( '<span class="important-warning">Warning</span>: Any %s not in your import file will be sent to the trash, even those not created by this import.', 'wp-all-import-pro' ), $custom_type->labels->name );
				} else {
					$notice = sprintf( __( '<span class="important-warning">Warning</span>: Any %s not in your import file will be marked as %s, even those not created by this import.', 'wp-all-import-pro' ), $custom_type->labels->name, $options['status_of_removed'] );
				}
			}
			if ( $options['delete_missing_logic'] == 'import' && $options['delete_missing_action'] == 'keep' && ! empty( $options['is_send_removed_to_trash'] ) ) {
				$notice = sprintf( __( '<span class="important-warning">Warning</span>: %s created by this import and no longer present in the import file will be sent to the trash.', 'wp-all-import-pro' ), $custom_type->labels->name );
			}
			if ( $options['delete_missing_logic'] == 'import' && $options['delete_missing_action'] == 'keep' && ! empty( $options['is_change_post_status_of_removed'] ) ) {
				if ( ! empty( $options['is_send_removed_to_trash'] ) ) {
					$notice = sprintf( __( '<span class="important-warning">Warning</span>: %s created by this import and no longer present in the import file will be sent to the trash.', 'wp-all-import-pro' ), $custom_type->labels->name );
				} else {
					$notice = sprintf( __( '<span class="important-warning">Warning</span>: %s created by this import and no longer present in the import file will be marked as %s.', 'wp-all-import-pro' ), $custom_type->labels->name, $options['status_of_removed'] );
				}
			}
			if ( $options['delete_missing_logic'] == 'import' && $options['delete_missing_action'] == 'remove' ) {
				$notice = sprintf( __( '<span class="important-warning">Warning</span>: %s created by this import and no longer present in the import file will be <b>permanently deleted</b>.', 'wp-all-import-pro' ), $custom_type->labels->name );
			}
			if ( $options['delete_missing_logic'] == 'all' && $options['delete_missing_action'] == 'keep' && ! empty( $options['is_send_removed_to_trash'] ) ) {
				$notice = sprintf( __( '<span class="important-warning">Warning</span>: Any %s not in your import file will be sent to the trash when this import runs. This includes %s that weren\'t created by this import.', 'wp-all-import-pro' ), $custom_type->labels->name, $custom_type->labels->name );
			}
			if ( $options['delete_missing_logic'] == 'all' && $options['delete_missing_action'] == 'remove' ) {
				$notice = sprintf( __( '<span class="important-warning">Warning</span>: Any %s not in your import file will be <b>permanently deleted</b>, even those not created by this import.', 'wp-all-import-pro' ), $custom_type->labels->name );
			}
		}

		return $notice;
	}
}

if ( ! function_exists( 'wp_all_import_custom_type_labels' ) ) {
	function wp_all_import_custom_type_labels( $post_type, $taxonomy_type = false ) {
		switch ( $post_type ) {
			case 'taxonomies':
				if ( ! empty( $taxonomy_type ) ) {
					$tx = get_taxonomy( $taxonomy_type );
				}
				$custom_type                        = new stdClass();
				$custom_type->name                  = $post_type;
				$custom_type->label                 = empty( $tx->labels->name ) ? __( 'Taxonomy Terms', 'wp-all-import-pro' ) : $tx->labels->name;
				$custom_type->labels                = new stdClass();
				$custom_type->labels->name          = empty( $tx->labels->name ) ? __( 'Taxonomy Terms', 'wp-all-import-pro' ) : $tx->labels->name;
				$custom_type->labels->singular_name = empty( $tx->labels->singular_name ) ? __( 'Taxonomy Term', 'wp-all-import-pro' ) : $tx->labels->singular_name;
				break;
			case 'comments':
				$custom_type                        = new stdClass();
				$custom_type->name                  = $post_type;
				$custom_type->label                 = __( 'Comments', 'wp-all-import-pro' );
				$custom_type->labels                = new stdClass();
				$custom_type->labels->name          = __( 'Comments', 'wp-all-import-pro' );
				$custom_type->labels->singular_name = __( 'Comment', 'wp-all-import-pro' );
				break;
			case 'woo_reviews':
				$custom_type                        = new stdClass();
				$custom_type->name                  = $post_type;
				$custom_type->label                 = __( 'WooCommerce Reviews', 'wp-all-import-pro' );
				$custom_type->labels                = new stdClass();
				$custom_type->labels->name          = __( 'WooCommerce Reviews', 'wp-all-import-pro' );
				$custom_type->labels->singular_name = __( 'Review', 'wp-all-import-pro' );
				break;
			case 'import_users':
				$custom_type                        = new stdClass();
				$custom_type->name                  = $post_type;
				$custom_type->label                 = __( 'Users', 'wp-all-import-pro' );
				$custom_type->labels                = new stdClass();
				$custom_type->labels->name          = __( 'Users', 'wp-all-import-pro' );
				$custom_type->labels->singular_name = __( 'User', 'wp-all-import-pro' );
				break;
			case 'gf_entries':
				$custom_type                        = new stdClass();
				$custom_type->name                  = $post_type;
				$custom_type->label                 = __( 'Gravity Forms Entry', 'wp-all-import-pro' );
				$custom_type->labels                = new stdClass();
				$custom_type->labels->name          = __( 'Gravity Forms Entry', 'wp-all-import-pro' );
				$custom_type->labels->singular_name = __( 'Gravity Forms Entry', 'wp-all-import-pro' );
				break;
			case 'shop_customer':
				$custom_type                        = new stdClass();
				$custom_type->name                  = $post_type;
				$custom_type->label                 = __( 'WooCommerce Customers', 'wp-all-import-pro' );
				$custom_type->labels                = new stdClass();
				$custom_type->labels->name          = __( 'WooCommerce Customers', 'wp-all-import-pro' );
				$custom_type->labels->singular_name = __( 'WooCommerce Customer', 'wp-all-import-pro' );
				break;
			default:
				$custom_type = wp_all_import_custom_type( $post_type );
				break;
		}

		return $custom_type;
	}
}

if ( ! function_exists( 'wp_all_import_get_product_id_by_sku' ) ) {
	function wp_all_import_get_product_id_by_sku( $sku ) {
		global $wpdb;

		// phpcs:ignore WordPress.VIP.DirectDatabaseQuery.DirectQuery
		$id = $wpdb->get_var( $wpdb->prepare( "
                    SELECT posts.ID
                    FROM {$wpdb->posts} as posts
                    INNER JOIN {$wpdb->wc_product_meta_lookup} AS lookup ON posts.ID = lookup.product_id
                    WHERE
                    posts.post_type IN ( 'product', 'product_variation' )			
                    AND lookup.sku = %s
                    LIMIT 1
                    ", $sku ) );

		return (int) apply_filters( 'wp_all_import_get_product_id_by_sku', $id, $sku );
	}
}

if ( ! function_exists( 'wp_all_import_supported_image_extensions' ) ) {
	function wp_all_import_supported_image_extensions() {
		$types      = [ 'svg' ]; //
		$mime_types = get_allowed_mime_types();
		if ( ! empty( $mime_types ) ) {
			foreach ( $mime_types as $ext => $mime_type ) {
				if ( strpos( $mime_type, 'image/' ) !== false ) {
					$types[] = $ext;
				}
			}
		}

		return implode( "|", apply_filters( 'pmxi_supported_image_extensions', $types ) );
	}
}

if ( ! function_exists( 'pmxi_encode_parenthesis' ) ) {
	function pmxi_encode_parenthesis( $input ) {
		return strtr( $input, [
			'(' => '&lpar;',
			')' => '&rpar;',
			'[' => '&lsqb;',
			']' => '&rsqb;',
		] );
	}
}

if ( ! function_exists( 'pmxi_decode_parenthesis' ) ) {
	function pmxi_decode_parenthesis( $input ) {
		return strtr( $input, [
			'&lpar;' => '(',
			'&rpar;' => ')',
			'&lsqb;' => '[',
			'&rsqb;' => ']',
		] );
	}
}

if ( ! function_exists( 'pmxi_encode_parenthesis_within_strings' ) ) {
	function pmxi_encode_parenthesis_within_strings( string $code ): string {
		$tokens = token_get_all( $code );
		$result = '';

		foreach ( $tokens as $token ) {
			if ( is_array( $token ) && $token[0] === T_CONSTANT_ENCAPSED_STRING ) {
				$token[1] = pmxi_encode_parenthesis( $token[1] );
			}
			$result .= is_array( $token ) ? $token[1] : $token;
		}

		return $result;
	}
}

if ( ! function_exists( 'pmxi_decode_parenthesis_within_strings' ) ) {
	function pmxi_decode_parenthesis_within_strings( string $code ): string {
		$tokens = token_get_all( $code );
		$result = '';

		foreach ( $tokens as $token ) {
			if ( is_array( $token ) && $token[0] === T_CONSTANT_ENCAPSED_STRING ) {
				$token[1] = pmxi_decode_parenthesis( $token[1] );
			}
			$result .= is_array( $token ) ? $token[1] : $token;
		}

		return $result;
	}
}

if ( ! function_exists( 'pmxi_truncate_term_slug' ) ) {
	function pmxi_truncate_term_slug( $string, $limit = 200 ) {
		// Function to check if the string is URL-encoded.
		$is_url_encoded = ( urldecode( $string ) !== $string );

		// Function to truncate URL-encoded strings.
		if ( $is_url_encoded ) {
			$decoded        = rawurldecode( $string );
			$encoded_string = '';
			$encoded_length = 0;

			for ( $i = 0; $i < mb_strlen( $decoded ); $i ++ ) {
				$char         = mb_substr( $decoded, $i, 1 );
				$encoded_char = rawurlencode( $char );

				if ( ( $encoded_length + strlen( $encoded_char ) ) > $limit ) {
					break;
				}

				$encoded_string .= $encoded_char;
				$encoded_length += strlen( $encoded_char );
			}

			return $encoded_string;
		} else {
			// If not URL-encoded, simply truncate the string.
			return mb_substr( $string, 0, $limit );
		}
	}
}

if ( ! function_exists( 'pmxi_maybe_unserialize' ) ) {
	function pmxi_maybe_unserialize( $value ) {
		if ( is_serialized( $value ) ) {
			$value = @unserialize( trim( $value ), [ 'allowed_classes' => false ] );
		}

		return $value;
	}
}
