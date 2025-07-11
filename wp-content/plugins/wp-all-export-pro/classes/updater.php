<?php

if ( ! class_exists( 'PMXE_Updater' ) ) {

	class PMXE_Updater {
		private $api_url = '';
		private $api_data = array();
		private $name = '';
		private $slug = '';
		private $_plugin_file = '';
		private $did_check = false;
		private $version;

		/**
		 * Class constructor.
		 *
		 * @param string $_api_url The URL pointing to the custom API endpoint.
		 * @param string $_plugin_file Path to the plugin file.
		 * @param array $_api_data Optional data to send with API calls.
		 *
		 * @uses hook()
		 *
		 * @uses plugin_basename()
		 */
		function __construct( $_api_url, $_plugin_file, $_api_data = null ) {
			$this->api_url  = trailingslashit( $_api_url );
			$this->api_data = urlencode_deep( $_api_data );
			$this->name     = plugin_basename( $_plugin_file );
			$this->slug     = basename( $_plugin_file, '.php' );

			$this->version      = $_api_data['version'];
			$this->_plugin_file = $_plugin_file;

			// Set up hooks.
			$this->init();
			add_action( 'admin_init', array( $this, 'show_changelog' ) );
		}

		/**
		 * Set up WordPress filters to hook into WP's update process.
		 *
		 * @return void
		 * @uses add_filter()
		 *
		 */
		public function init() {

			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ), 20 );
			add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );

			add_action( 'after_plugin_row_' . $this->name, array( $this, 'show_update_notification' ), 11, 2 );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

			add_action( 'in_plugin_update_message-' . $this->name, [ $this, 'custom_update_note' ], 10, 2 );
		}

		public function custom_update_note( $data, $response ) {

			// Only show a custom note if one was included in the update data.
			if ( is_object( $response ) && ! empty( $response->custom_update_note ) && ! empty( $response->update_note_version ) ) {
				// Ensure that this version is the same or older than the note's target version.
				if ( version_compare( $this->version, $response->update_note_version, '<=' ) ) {
					echo wp_kses( $response->custom_update_note, 'post' );
				}
			}
		}

		/**
		 * Show row meta on the plugin screen.
		 *
		 * @param mixed $links Plugin Row Meta
		 * @param mixed $file Plugin Base file
		 *
		 * @return    array
		 */
		public function plugin_row_meta( $links, $file ) {
			if ( $file == $this->name ) {
				$row_meta = array(
					'changelog' => '<a href="' . admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $this->slug . '&section=changelog&TB_iframe=true&width=600&height=800' ) . '" class="thickbox open-plugin-details-modal" title="' . esc_attr( 'View ' . urldecode( $this->api_data['item_name'] ) . ' Changelog' ) . '">' . __( 'Changelog', 'wp_all_export_plugin' ) . '</a>',
				);

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}

		/**
		 * Check for Updates at the defined API endpoint and modify the update array.
		 *
		 * This function dives into the update API just when WordPress creates its update array,
		 * then adds a custom API call and injects the custom plugin data retrieved from the API.
		 * It is reassembled from parts of the native WordPress plugin update code.
		 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
		 *
		 * @param array $_transient_data Update array build by WordPress.
		 *
		 * @return array Modified update array with custom plugin data.
		 * @uses api_request()
		 *
		 */
		function check_update( $_transient_data ) {

			global $pagenow;
			global $wpdb;

			if ( ! is_object( $_transient_data ) ) {
				$_transient_data = new stdClass;
			}

			if ( 'plugins.php' == $pagenow && is_multisite() ) {
				return $_transient_data;
			}

			if ( empty( $_transient_data ) ) {
				return $_transient_data;
			}

			if ( empty( $_transient_data->response ) || empty( $_transient_data->response[ $this->name ] ) ) {

				$cache_key    = md5( 'edd_plugin_' . sanitize_key( $this->name ) . '_version_info' );
				$version_info = get_transient( $cache_key );

				if ( false === $version_info ) {

					$timeout = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $this->slug . '_timeout_' . $cache_key ) );

					// Has to be get_row instead of get_var because of funkiness with 0, false, null values
					if ( is_object( $timeout ) ) {
						$value = $timeout->option_value;
						// cache time is not expired
						if ( $value >= strtotime( "now" ) ) {
							$cache_value = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $this->slug . '_' . $cache_key ) );
							if ( is_object( $cache_value ) and ! empty( $cache_value->option_value ) ) {
								$version_info = maybe_unserialize( $cache_value->option_value );
							}
						}
					}

					if ( false === $version_info ) {
						$version_info     = $this->api_request( 'check_update', array( 'slug' => $this->slug ) );
						$transient_result = set_transient( $cache_key, $version_info, 3600 * 24 );

						$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name = %s", $this->slug . '_' . $cache_key ) );
						$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name = %s", $this->slug . '_timeout_' . $cache_key ) );

						$wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->options ( option_value, option_name, autoload ) VALUES ( %s, %s, 'no' )", maybe_serialize( $version_info ), $this->slug . '_' . $cache_key ) );
						$wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->options ( option_value, option_name ) VALUES ( %s, %s )", strtotime( "+1 hour" ), $this->slug . '_timeout_' . $cache_key ) );

					}

				}

				if ( ! is_object( $version_info ) ) {
					return $_transient_data;
				}

				if ( false !== $version_info && is_object( $version_info ) && isset( $version_info->new_version ) ) {

					$this->did_check = true;

					if ( version_compare( $this->version, $version_info->new_version, '<' ) ) {

						$_transient_data->response[ $this->name ] = $version_info;

					}

					$_transient_data->last_checked           = time();
					$_transient_data->checked[ $this->name ] = $this->version;

				}

			}

			return $_transient_data;
		}

		/**
		 * show update nofication row -- needed for multisite subsites, because WP won't tell you otherwise!
		 *
		 * @param string $file
		 * @param array $plugin
		 */
		public function show_update_notification( $file, $plugin ) {

			if ( ! current_user_can( 'update_plugins' ) ) {
				return;
			}

			if ( $this->name != $file ) {
				return;
			}

			// Only show this enhanced notification for WP All Export Pro itself.
			if ( $this->slug != 'wp-all-export-pro' ) {
				return;
			}

			// Remove our filter on the site transient
			remove_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ), 21 );

			$update_cache = get_site_transient( 'update_plugins' );

			if ( ! is_object( $update_cache ) || empty( $update_cache->response ) || empty( $update_cache->response[ $this->name ] ) ) {

				global $wpdb;

				if ( ! is_object( $update_cache ) ) {
					$update_cache = new stdClass();
				}

				$cache_key = md5( 'edd_plugin_' . sanitize_key( $this->name ) . '_version_info' );

				$version_info = get_transient( $cache_key );

				if ( false === $version_info ) {

					$timeout = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $this->slug . '_timeout_' . $cache_key ) );

					// Has to be get_row instead of get_var because of funkiness with 0, false, null values
					if ( is_object( $timeout ) ) {
						$value = $timeout->option_value;
						// cache time is not expired
						if ( $value >= strtotime( "now" ) ) {
							$cache_value = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $this->slug . '_' . $cache_key ) );
							if ( is_object( $cache_value ) and ! empty( $cache_value->option_value ) ) {
								$version_info = maybe_unserialize( $cache_value->option_value );
							}
						}
					}

					if ( false === $version_info ) {

						$version_info = $this->api_request( 'plugin_latest_version', array( 'slug' => $this->slug ) );

						$transient_result = set_transient( $cache_key, $version_info, 3600 * 24 );

						$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name = %s", $this->slug . '_' . $cache_key ) );
						$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name = %s", $this->slug . '_timeout_' . $cache_key ) );

						$wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->options ( option_value, option_name, autoload ) VALUES ( %s, %s, 'no' )", maybe_serialize( $version_info ), $this->slug . '_' . $cache_key ) );
						$wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->options ( option_value, option_name ) VALUES ( %s, %s )", strtotime( "+1 hour" ), $this->slug . '_timeout_' . $cache_key ) );

					}
				}

				if ( ! is_object( $version_info ) ) {
					return;
				}

				if ( version_compare( $this->version, $version_info->new_version, '<' ) ) {

					$update_cache->response[ $this->name ] = $version_info;

				}

				$update_cache->last_checked           = time();
				$update_cache->checked[ $this->name ] = $this->version;

				set_site_transient( 'update_plugins', $update_cache );

			} else {

				$version_info = $update_cache->response[ $this->name ];

			}

			// Restore our filter
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ), 20 );

			$plugin_title       = $plugin['Name'];
			$plugin_slug        = sanitize_title( $plugin_title );
			$active             = is_plugin_active( $file ) ? 'active' : '';
			$shiny_updates      = version_compare( get_bloginfo( 'version' ), '1.4.7-beta1-37926', '>=' );
			$update_msg_classes = $shiny_updates ? 'notice inline notice-warning notice-alt' : 'pre-shiny-updates';
			$new_version        = '';

			if ( ! empty( $update_cache->response[ $this->name ] ) && version_compare( $this->version, $version_info->new_version, '<' ) ) {

				// build a plugin list row, with update notification
				$changelog_link = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $this->slug . '&section=changelog&TB_iframe=true&width=772&height=412' );
				if ( empty( $version_info->download_link ) ) {
					if ( $shiny_updates ) {
						$update_msg_classes .= ' post-shiny-updates';
					}
					$new_version = "<span class=\"wp-all-export-pro-new-version-notice\">" . sprintf( __( 'A new version of WP All Export Pro is available. <strong>A valid license is required to enable updates - enter your license key on the <a href="%1$s">Settings</a> page.</strong>', 'wp_all_export_plugin' ), esc_url( admin_url( 'admin.php?page=pmxe-admin-settings' ) ) ) . "</span>";
					$new_version .= "<span class=\"wp-all-export-pro-licence-error-notice\">" . sprintf( __( 'If you don\'t have a license key, please see the <a href="%1$s" target="_blank">details & pricing</a> page. If you do have a license key, you can access it at the <a href="%2$s" target="_blank">customer portal</a>.', 'wp_all_export_plugin' ), esc_url( 'http://www.wpallimport.com/order-now/' ), esc_url( 'http://www.wpallimport.com/portal/' ) ) . "</span>";
				} else {
					$new_version = "<span class=\"wp-all-export-pro-new-version-notice\">" . sprintf( __( 'There is a new version of WP All Export Pro available. <a target="_blank" class="thickbox" href="%1$s">View version %2$s details</a> or <a href="%3$s" class="update-link">update now</a>.', 'wp_all_export_plugin' ), esc_url( $changelog_link ), esc_html( $version_info->new_version ), esc_url( wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $this->name, 'upgrade-plugin_' . $this->name ) ) ) . "</span>";
				}
			}


			if ( empty( $new_version ) ) {
				return;
			}
			?>
			<tr class="plugin-update-tr <?php echo $active; ?> wp-all-export-pro-custom">
				<td colspan="4" class="plugin-update colspanchange">
					<div class="update-message <?php echo $update_msg_classes; ?>">
						<p>
							<?php echo $new_version; ?>
							<?php do_action( 'in_plugin_update_message-' . $this->name, [], $version_info ); ?>
						</p>
					</div>
				</td>
			</tr>
			<?php if ( $new_version ) { // removes the built-in plugin update message ?>
				<script type="text/javascript">
					( function ( $ ) {
						var wp_all_export_row = jQuery( '[data-slug=<?php echo $plugin_slug; ?>]:first' );

						// Fallback for earlier versions of WordPress.
						if ( ! wp_all_export_row.length ) {
							wp_all_export_row = jQuery( '#<?php echo $plugin_slug; ?>' );
						}

						wp_all_export_row.addClass( 'update' );

						var next_row = wp_all_export_row.next();

						// If there's a plugin update row - need to keep the original update row available so we can switch it out
						// if the user has a successful response from the 'check my license again' link
						if ( next_row.hasClass( 'plugin-update-tr' ) && ! next_row.hasClass( 'wp-all-export-pro-custom' ) ) {
							var original = next_row.clone();
							// original.add;
							next_row.html( next_row.next().html() ).addClass( 'wp-all-export-pro-custom-visible' );
							next_row.next().remove();
							next_row.after( original );
							original.addClass( 'wp-all-export-original-update-row' );
						}
					} )( jQuery );
				</script>
				<?php
			}
		}

		/**
		 * Updates information on the "View version x.x details" page with custom data.
		 *
		 * @param mixed $_data
		 * @param string $_action
		 * @param object $_args
		 *
		 * @return object $_data
		 * @uses api_request()
		 *
		 */
		function plugins_api_filter( $_data, $_action = '', $_args = null ) {

			if ( $_action != 'plugin_information' ) {

				return $_data;

			}

			if ( ! isset( $_args->slug ) || ( $_args->slug != $this->slug ) ) {

				return $_data;

			}

			global $wpdb;

			$cache_key = md5( 'edd_plugin_' . sanitize_key( $this->name ) . '_version_info' );
			$_data     = get_transient( $cache_key );

			if ( false === $_data ) {

				$timeout = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $this->slug . '_timeout_' . $cache_key ) );

				// Has to be get_row instead of get_var because of funkiness with 0, false, null values
				if ( is_object( $timeout ) ) {
					$value = $timeout->option_value;
					// cache time is not expired
					if ( $value >= strtotime( "now" ) ) {
						$cache_value = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $this->slug . '_' . $cache_key ) );
						if ( is_object( $cache_value ) and ! empty( $cache_value->option_value ) ) {
							$_data = maybe_unserialize( $cache_value->option_value );
						}
					}
				}

				if ( false === $_data ) {
					$to_send = array(
						'slug'   => $this->slug,
						'is_ssl' => is_ssl(),
						'fields' => array(
							'banners' => false, // These will be supported soon hopefully
							'reviews' => false,
						),
					);

					$_data = $this->api_request( 'plugin_information', $to_send );

					if ( false !== $_data ) {

						$transient_result = set_transient( $cache_key, $_data, 3600 * 24 );

						$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name = %s", $this->slug . '_' . $cache_key ) );
						$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name = %s", $this->slug . '_timeout_' . $cache_key ) );

						$wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->options ( option_value, option_name, autoload ) VALUES ( %s, %s, 'no' )", maybe_serialize( $_data ), $this->slug . '_' . $cache_key ) );
						$wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->options ( option_value, option_name ) VALUES ( %s, %s )", strtotime( "+1 hour" ), $this->slug . '_timeout_' . $cache_key ) );

					}
				}
			}

			return $_data;
		}


		/**
		 * Disable SSL verification in order to prevent download update failures
		 *
		 * @param array $args
		 * @param string $url
		 *
		 * @return array|object
		 */
		function http_request_args( $args, $url ) {

			if ( defined( 'WPALLEXPORT_SIGNATURE' ) ) {
				$args['body'] = array( 'signature' => WPALLEXPORT_SIGNATURE );
			}

			// If it is an https request and we are performing a package download, disable ssl verification
			if ( strpos( $url, 'https://' ) !== false && strpos( $url, 'edd_action=package_download' ) ) {
				$args['sslverify'] = true;
			}

			return $args;
		}

		/**
		 * Calls the API and, if successfull, returns the object delivered by the API.
		 *
		 * @param string $_action The requested action.
		 * @param array $_data Parameters for the API action.
		 *
		 * @return false||object
		 * @uses get_bloginfo()
		 * @uses wp_remote_post()
		 * @uses is_wp_error()
		 *
		 */
		private function api_request( $_action, $_data ) {

			global $wp_version;

			$data = array_merge( $this->api_data, $_data );

			if ( $data['slug'] != $this->slug ) {
				return false;
			}

			/*if ( empty( $data['license'] ) )
				return;*/

			if ( $this->api_url == home_url() ) {
				return false; // Don't allow a plugin to ping itself
			}

			$api_params = array(
				'edd_action' => 'get_version',
				'license'    => $data['license'],
				'item_name'  => isset( $data['item_name'] ) ? $data['item_name'] : false,
				'item_id'    => isset( $data['item_id'] ) ? $data['item_id'] : false,
				'slug'       => $data['slug'],
				'plugin'     => $this->_plugin_file,
				'author'     => $data['author'],
				'url'        => home_url(),
				'version'    => $this->version,
				'signature'  => defined( 'WPALLEXPORT_SIGNATURE' ) ? WPALLEXPORT_SIGNATURE : '',
			);

			// if ( defined('WP_DEBUG') and WP_DEBUG )
			// {
			//     $uploads = wp_upload_dir();
			//     file_put_contents($uploads['basedir'] . "/log.txt", date("d-m-Y H:i:s") . ' - ' .json_encode($api_params) . "\n", FILE_APPEND);
			// }

			$request = wp_remote_get( esc_url_raw( add_query_arg( $api_params, $this->api_url . 'check_version/?' ) ), array( 'timeout'   => 15,
			                                                                                                                  'sslverify' => true,
			) );

			if ( ! is_wp_error( $request ) ) {
				$request = json_decode( wp_remote_retrieve_body( $request ) );
			}

			if ( $request && isset( $request->contributors ) ) {
				$request->contributors = maybe_unserialize( $request->contributors );
			}

			if ( $request && isset( $request->banners ) ) {
				$request->banners = maybe_unserialize( $request->banners );
			}

			if ( $request && isset( $request->sections ) ) {
				$request->sections = maybe_unserialize( $request->sections );
			} else {
				$request = false;
			}

			return $request;
		}

		public function show_changelog() {


			if ( empty( $_REQUEST['edd_sl_action'] ) || 'view_plugin_changelog' != $_REQUEST['edd_sl_action'] ) {
				return;
			}

			if ( empty( $_REQUEST['plugin'] ) ) {
				return;
			}

			if ( empty( $_REQUEST['slug'] ) ) {
				return;
			}

			if ( ! current_user_can( 'update_plugins' ) ) {
				wp_die( esc_html__( 'You do not have permission to install plugin updates', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
			}

			$response = $this->api_request( 'show_changelog', array( 'slug' => $_REQUEST['slug'] ) );

			if ( $response && isset( $response->sections['changelog'] ) ) {
				echo '<div style="background:#fff;padding:10px;">' . $response->sections['changelog'] . '</div>';
			}


			exit;
		}
	}

}