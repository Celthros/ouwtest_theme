<?php
/*
Plugin Name: WP All Export Pro
Plugin URI: http://www.wpallimport.com/export/
Description: Export any post type to a CSV or XML file. Edit the exported data, and then re-import it later using WP All Import.
Version: 1.9.8
Author: Soflyy
*/

// Enable error reporting in development
if ( getenv( 'WPAE_DEV' ) ) {
	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);
	//xdebug_disable();
}

if ( ! defined( 'PMXE_SESSION_COOKIE' ) ) {
	define( 'PMXE_SESSION_COOKIE', '_pmxe_session' );
}
/**
 * Plugin root dir with forward slashes as directory separator regardless of actuall DIRECTORY_SEPARATOR value
 * @var string
 */
define( 'PMXE_ROOT_DIR', str_replace( '\\', '/', dirname( __FILE__ ) ) );
/**
 * Plugin root url for referencing static content
 * @var string
 */
define( 'PMXE_ROOT_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

if ( class_exists( 'PMXE_Plugin' ) and PMXE_EDITION == "free" ) {

	include_once __DIR__ . '/src/WordPress/AdminNotice.php';
	include_once __DIR__ . '/src/WordPress/AdminErrorNotice.php';
	$notice = new \Wpae\WordPress\AdminErrorNotice( printf( esc_html__( 'Please de-activate and remove the free version of the WP All Export before activating the paid version.', 'wp_all_export_plugin' ) ) );
	$notice->render();

	deactivate_plugins( str_replace( '\\', '/', dirname( __FILE__ ) ) . '/wp-all-export-pro.php' );
} else {

	/**
	 * Plugin prefix for making names unique (be aware that this variable is used in conjuction with naming convention,
	 * i.e. in order to change it one must not only modify this constant but also rename all constants, classes and functions which
	 * names composed using this prefix)
	 * @var string
	 */
	define( 'PMXE_PREFIX', 'pmxe_' );

	define( 'PMXE_VERSION', '1.9.8' );

	define( 'PMXE_EDITION', 'paid' );

	/**
	 * Plugin root uploads folder name
	 * @var string
	 */
	define( 'WP_ALL_EXPORT_UPLOADS_BASE_DIRECTORY', 'wpallexport' );
	/**
	 * Plugin uploads folder name
	 * @var string
	 */
	define( 'WP_ALL_EXPORT_UPLOADS_DIRECTORY', WP_ALL_EXPORT_UPLOADS_BASE_DIRECTORY . DIRECTORY_SEPARATOR . 'exports' );

	/**
	 * Plugin temp folder name
	 * @var string
	 */
	define( 'WP_ALL_EXPORT_TEMP_DIRECTORY', WP_ALL_EXPORT_UPLOADS_BASE_DIRECTORY . DIRECTORY_SEPARATOR . 'temp' );

	/**
	 * Plugin temp folder name
	 * @var string
	 */
	define( 'WP_ALL_EXPORT_CRON_DIRECTORY', WP_ALL_EXPORT_UPLOADS_BASE_DIRECTORY . DIRECTORY_SEPARATOR . 'exports' );

	/**
	 * Main plugin file, Introduces MVC pattern
	 *
	 * @singletone
	 * @author Pavel Kulbakin <p.kulbakin@gmail.com>
	 */
	final class PMXE_Plugin {
		/**
		 * Singletone instance
		 * @var PMXE_Plugin
		 */
		protected static $instance;

		/**
		 * Plugin options
		 * @var array
		 */
		protected $options = array();

		/**
		 * Plugin root dir
		 * @var string
		 */
		const ROOT_DIR = PMXE_ROOT_DIR;
		/**
		 * Plugin root URL
		 * @var string
		 */
		const ROOT_URL = PMXE_ROOT_URL;
		/**
		 * Prefix used for names of shortcodes, action handlers, filter functions etc.
		 * @var string
		 */
		const PREFIX = PMXE_PREFIX;
		/**
		 * Plugin file path
		 * @var string
		 */
		const FILE = __FILE__;
		/**
		 * Max allowed file size (bytes) to import in default mode
		 * @var int
		 */
		const LARGE_SIZE = 0; // all files will importing in large import mode

		/**
		 * WP All Import temp folder
		 * @var string
		 */
		const TEMP_DIRECTORY = WP_ALL_EXPORT_TEMP_DIRECTORY;
		/**
		 * WP All Import uploads folder
		 * @var string
		 */
		const UPLOADS_DIRECTORY = WP_ALL_EXPORT_UPLOADS_DIRECTORY;
		/**
		 * WP All Import uploads folder
		 * @var string
		 */
		const CRON_DIRECTORY = WP_ALL_EXPORT_CRON_DIRECTORY;

		const LANGUAGE_DOMAIN = 'wp_all_export_plugin';

		const CLIENT_MODE_CAP = 'wpae_run_exports';

		public static $session = null;

		public static $capabilities = 'setup_network';

		public static $cache_key = '';

		private static $hasActiveSchedulingLicense = null;

		/** @var  \Wpae\App\Service\Addons\AddonService */
		private $addons;

		/**
		 * Return singletone instance
		 * @return PMXE_Plugin
		 */
		static public function getInstance() {
			if ( self::$instance == null ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		static public function getEddName() {
			return 'WP All Export';
		}

		static public function getSchedulingName() {
			return 'Automatic Scheduling';
		}

		static public function hasActiveSchedulingLicense() {

			if ( is_null( self::$hasActiveSchedulingLicense ) ) {
				$scheduling                       = \Wpae\Scheduling\Scheduling::create();
				$hasActiveSchedulingLicense       = $scheduling->checkLicense()['success'] ?? false;
				self::$hasActiveSchedulingLicense = $hasActiveSchedulingLicense;
			}

			return self::$hasActiveSchedulingLicense;
		}

		/**
		 * Common logic for requestin plugin info fields
		 */
		public function __call( $method, $args ) {
			if ( preg_match( '%^get(.+)%i', $method, $mtch ) ) {
				$info = get_plugin_data( self::FILE );
				if ( isset( $info[ $mtch[1] ] ) ) {
					return $info[ $mtch[1] ];
				}
			}
			throw new Exception( "Requested method " . get_class( $this ) . "::$method doesn't exist." );
		}

		/**
		 * Get path to plagin dir relative to wordpress root
		 *
		 * @param bool [optional] $noForwardSlash Whether path should be returned withot forwarding slash
		 *
		 * @return string
		 */
		public function getRelativePath( $noForwardSlash = false ) {
			$wp_root = str_replace( '\\', '/', ABSPATH );

			return ( $noForwardSlash ? '' : '/' ) . str_replace( $wp_root, '', self::ROOT_DIR );
		}

		/**
		 * Check whether plugin is activated as network one
		 * @return bool
		 */
		public function isNetwork() {
			if ( ! is_multisite() ) {
				return false;
			}

			$plugins = get_site_option( 'active_sitewide_plugins' );
			if ( isset( $plugins[ plugin_basename( self::FILE ) ] ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Check whether permalinks is enabled
		 * @return bool
		 */
		public function isPermalinks() {
			global $wp_rewrite;

			return $wp_rewrite->using_permalinks();
		}

		/**
		 * Return prefix for plugin database tables
		 * @return string
		 */
		public function getTablePrefix() {
			global $wpdb;

			//return ($this->isNetwork() ? $wpdb->base_prefix : $wpdb->prefix) . self::PREFIX;
			return $wpdb->prefix . self::PREFIX;
		}

		/**
		 * Return prefix for wordpress database tables
		 * @return string
		 */
		public function getWPPrefix() {
			global $wpdb;

			return ( $this->isNetwork() ) ? $wpdb->base_prefix : $wpdb->prefix;
		}

		/**
		 * Class constructor containing dispatching logic
		 *
		 * @param string $rootDir Plugin root dir
		 * @param string $pluginFilePath Plugin main file
		 */
		protected function __construct() {
			if ( defined( 'WPAI_WPAE_ALLOW_INSECURE_MULTISITE' ) && 1 === WPAI_WPAE_ALLOW_INSECURE_MULTISITE ) {
				self::$capabilities = 'manage_options';
			}

			require_once( self::ROOT_DIR . '/classes/installer.php' );

			$installer = new PMXE_Installer();
			$installer->checkActivationConditions();

			// register autoloading method
			spl_autoload_register( array( $this, 'autoload' ) );

			require_once 'vendor/autoload.php';

			require_once self::ROOT_DIR . '/addon-api/autoload.php';

			// register helpers
			if ( is_dir( self::ROOT_DIR . '/helpers' ) ) {
				foreach ( PMXE_Helper::safe_glob( self::ROOT_DIR . '/helpers/*.php', PMXE_Helper::GLOB_RECURSE | PMXE_Helper::GLOB_PATH ) as $filePath ) {
					require_once $filePath;
				}
			}

			$this->addons = new \Wpae\App\Service\Addons\AddonService();

			$plugin_basename = plugin_basename( __FILE__ );

			self::$cache_key = md5( 'edd_plugin_' . sanitize_key( $plugin_basename ) . '_version_info' );

			// init plugin options
			$option_name     = get_class( $this ) . '_Options';
			$options_default = PMXE_Config::createFromFile( self::ROOT_DIR . '/config/options.php' )->toArray();
			$current_options = get_option( $option_name, array() );
			$current_options = is_array( $current_options ) ? $current_options : array();
			$this->options   = array_intersect_key( $current_options, $options_default ) + $options_default;
			$this->options   = array_intersect_key( $options_default, array_flip( array(
					'info_api_url',
					'info_api_url_new',
				) ) ) + $this->options; // make sure hidden options apply upon plugin reactivation
			if ( '' == $this->options['cron_job_key'] ) {
				$this->options['cron_job_key'] = wp_all_export_url_title( wp_all_export_rand_char( 12 ) );
			}
			if ( '' == $this->options['zapier_api_key'] ) {
				$this->options['zapier_api_key'] = wp_all_export_rand_char( 32 );
			}

			if ( $current_options !== $this->options ) {
				update_option( $option_name, $this->options );
			}
			register_activation_hook( self::FILE, array( $this, 'activation' ) );

			// register action handlers
			if ( is_dir( self::ROOT_DIR . '/actions' ) ) {
				if ( is_dir( self::ROOT_DIR . '/actions' ) ) {
					foreach ( PMXE_Helper::safe_glob( self::ROOT_DIR . '/actions/*.php', PMXE_Helper::GLOB_RECURSE | PMXE_Helper::GLOB_PATH ) as $filePath ) {
						require_once $filePath;
						$function = $actionName = basename( $filePath, '.php' );
						if ( preg_match( '%^(.+?)[_-](\d+)$%', $actionName, $m ) ) {
							$actionName = $m[1];
							$priority   = intval( $m[2] );
						} else {
							$priority = 10;
						}
						add_action( $actionName, self::PREFIX . str_replace( '-', '_', $function ), $priority, 99 ); // since we don't know at this point how many parameters each plugin expects, we make sure they will be provided with all of them (it's unlikely any developer will specify more than 99 parameters in a function)
					}
				}
			}

			// register filter handlers
			if ( is_dir( self::ROOT_DIR . '/filters' ) ) {
				foreach ( PMXE_Helper::safe_glob( self::ROOT_DIR . '/filters/*.php', PMXE_Helper::GLOB_RECURSE | PMXE_Helper::GLOB_PATH ) as $filePath ) {
					require_once $filePath;
					$function = $actionName = basename( $filePath, '.php' );
					if ( preg_match( '%^(.+?)[_-](\d+)$%', $actionName, $m ) ) {
						$actionName = $m[1];
						$priority   = intval( $m[2] );
					} else {
						$priority = 10;
					}
					add_filter( $actionName, self::PREFIX . str_replace( '-', '_', $function ), $priority, 99 ); // since we don't know at this point how many parameters each plugin expects, we make sure they will be provided with all of them (it's unlikely any developer will specify more than 99 parameters in a function)
				}
			}

			// register shortcodes handlers
			if ( is_dir( self::ROOT_DIR . '/shortcodes' ) ) {
				foreach ( PMXE_Helper::safe_glob( self::ROOT_DIR . '/shortcodes/*.php', PMXE_Helper::GLOB_RECURSE | PMXE_Helper::GLOB_PATH ) as $filePath ) {
					$tag = strtolower( str_replace( '/', '_', preg_replace( '%^' . preg_quote( self::ROOT_DIR . '/shortcodes/', '%' ) . '|\.php$%', '', $filePath ) ) );
					add_shortcode( $tag, array( $this, 'shortcodeDispatcher' ) );
				}
			}

			// register admin page pre-dispatcher
			add_action( 'admin_init', array( $this, 'fix_db_schema' ), 9 );
			add_action( 'admin_init', array( $this, 'adminInit' ), 10 );
			add_action( 'init', array( $this, 'init' ) );

		}

		public function init() {
			$this->load_plugin_textdomain();
		}

		public function showNoticeAndDisablePlugin( $message ) {
			$this->showNotice( $message );
			deactivate_plugins( str_replace( '\\', '/', dirname( __FILE__ ) ) . '/wp-all-export-pro.php' );
		}

		public function showNotice( $message ) {
			$notice = new \Wpae\WordPress\AdminErrorNotice( $message );
			$notice->render();
		}

		public function showDismissibleNotice( $message, $noticeId ) {
			$notice = new \Wpae\WordPress\AdminDismissibleNotice( $message, $noticeId );
			if ( ! $notice->isDismissed() ) {
				$notice->render();
			}
		}

		/**
		 * pre-dispatching logic for admin page controllers
		 */
		public function adminInit() {
			// Initialize session.
			self::$session = new PMXE_Handler();

			if ( ! wp_doing_ajax() ) {

				$addons_not_included = get_option( 'wp_all_export_pro_addons_not_included', false );

				$addons = new \Wpae\App\Service\Addons\AddonService();
				if ( ! $addons_not_included && ! defined( 'PMAE_VERSION' ) ) {

					$this->showDismissibleNotice( '<strong>WP All Export add-ons are now required to export ACF and WooCommerce data</strong> <br/><br/>
Some of the features you used in WP All Export Pro now require paid add-ons. If you had a WP All Export license before this change you are eligible to receive these add-ons free of charge. In that case, they have been added to the account where you originally purchased WP All Export Pro.
<br/><br/>
<a href="https://wpallimport.com/portal/downloads/?utm_source=export-plugin-pro&utm_medium=wpae-addons-notice&utm_campaign=export-add-ons-added-to-acount" target="_blank">Click here to download your new export add-ons.</a>', 'wpae_woocommerce_required_notice' );

				}

				if ( defined( 'PMWE_VERSION' ) && version_compare( PMWE_VERSION, '1.0.8-beta-1.1', '<' ) ) {
					$this->showDismissibleNotice( '<strong>WP All Export:</strong> The latest version of the WP All Export WooCommerce Add-On (1.0.8+) is required. Any exports that require this add-on will not run correctly until you update the WP All Export WooCommerce Add-On.', 'wpae_woocommerce_required_version_notice' );
				}

				wp_enqueue_style( 'wp-all-export-updater', PMXE_ROOT_URL . '/static/css/plugin-update-styles.css', array(), PMXE_VERSION );

				wp_enqueue_script( 'wp-all-export-notice-dismiss', PMXE_ROOT_URL . '/static/js/pmxe_notice_dismiss.js', array( 'jquery' ), PMXE_VERSION );

				wp_localize_script( 'wp-all-export-notice-dismiss', 'wp_all_export_object', array(
					'security' => wp_create_nonce( "wp_all_export_secure" ),
					'ajaxurl'  => admin_url( 'admin-ajax.php' ),
				) );

				// create history folder
				$uploads = wp_upload_dir();

				$wpallimportDirs = array(
					WP_ALL_EXPORT_UPLOADS_BASE_DIRECTORY,
					self::TEMP_DIRECTORY,
					self::UPLOADS_DIRECTORY,
					self::CRON_DIRECTORY,
				);

				foreach ( $wpallimportDirs as $destination ) {

					$dir = $uploads['basedir'] . DIRECTORY_SEPARATOR . $destination;

					if ( ! is_dir( $dir ) ) {
						wp_mkdir_p( $dir );
					}

					if ( ! @file_exists( $dir . DIRECTORY_SEPARATOR . 'index.php' ) ) {
						@touch( $dir . DIRECTORY_SEPARATOR . 'index.php' );
					}

				}

				if ( ! is_dir( $uploads['basedir'] . DIRECTORY_SEPARATOR . WP_ALL_EXPORT_UPLOADS_BASE_DIRECTORY ) or ! pmxe_is_writable( $uploads['basedir'] . DIRECTORY_SEPARATOR . WP_ALL_EXPORT_UPLOADS_BASE_DIRECTORY ) ) {
					$this->showNoticeAndDisablePlugin( sprintf( esc_html__( 'Uploads folder %s must be writable', 'wp_all_export_plugin' ), $uploads['basedir'] . DIRECTORY_SEPARATOR . WP_ALL_EXPORT_UPLOADS_BASE_DIRECTORY ) );
				}

				if ( ! is_dir( $uploads['basedir'] . DIRECTORY_SEPARATOR . self::UPLOADS_DIRECTORY ) or ! pmxe_is_writable( $uploads['basedir'] . DIRECTORY_SEPARATOR . self::UPLOADS_DIRECTORY ) ) {
					$this->showNoticeAndDisablePlugin( sprintf( esc_html__( 'Uploads folder %s must be writable', 'wp_all_export_plugin' ), $uploads['basedir'] . DIRECTORY_SEPARATOR . self::UPLOADS_DIRECTORY ) );
				}

				if ( ! $addons_not_included && $this->addons->userExportsExistAndAddonNotInstalled() && current_user_can( PMXE_Plugin::$capabilities ) ) {
					$this->showDismissibleNotice( __( '<strong style="font-size:16px">WP All Export Pro requires the User Export Add-On Pro</strong><p>Your User and Customer exports will not be able to run until you install the User Export Add-On Pro for WP All Export Pro. That add-on has been added to all customer accounts which had a WP All Export license before this requirement, free of charge.</p>', 'wp_all_export_plugin' ) . '<p><a class="button button-primary" href="https://wpallimport.com/portal/downloads" target="_blank">' . __( 'Download Add-On', 'wp_all_export_plugin' ) . '</a></p>', 'wpae_user_addon_not_installed_notice' );
				}

				if ( ! $addons_not_included && $this->addons->wooCommerceExportsExistAndAddonNotInstalled() && current_user_can( PMXE_Plugin::$capabilities ) && \class_exists( 'WooCommerce' ) ) {
					$this->showDismissibleNotice( __( '<strong style="font-size:16px">WP All Export Pro requires the WooCommerce Export Add-On Pro</strong><p>Your Products, Orders, Reviews and Coupons exports will not be able to run until you install the WooCommerce Export Add-On Pro for WP All Export Pro. That add-on has been added to all customer accounts which had a WP All Export license before this requirement, free of charge.</p>', 'wp_all_export_plugin' ) . '<p><a class="button button-primary" href="https://wpallimport.com/portal/downloads" target="_blank">' . __( 'Download Add-On', 'wp_all_export_plugin' ) . '</a></p>', 'wpae_woocommerce_addon_not_installed_notice' );
				}

				if ( ! $addons_not_included && $this->addons->acfExportsExistAndNotInstalled() && current_user_can( PMXE_Plugin::$capabilities ) ) {
					$this->showDismissibleNotice( __( '<strong style="font-size:16px">WP All Export Pro requires the ACF Export Add-On Pro</strong><p>Exports that contain ACF fields will not be able to run until you install the ACF Export Add-On Pro for WP All Export Pro. That add-on has been added to all customer accounts which had a WP All Export license before this requirement, free of charge.</p>', 'wp_all_export_plugin' ) . '<p><a class="button button-primary" href="https://wpallimport.com/portal/downloads" target="_blank">' . __( 'Download Add-On', 'wp_all_export_plugin' ) . '</a></p>', 'wpae_acf_addon_not_installed_notice' );
				}

				if ( $this->addons->wooCommerceRealTimeExportsExistAndAddonNotInstalled() && current_user_can( PMXE_Plugin::$capabilities ) ) {
					$this->showDismissibleNotice( __( '<strong>WP All Export Pro:</strong> An export configured to run in real time requires the WooCommerce Export Add-On and will not export newly created records while the add-on is deactivated.</p>' ), 'wpae_real_time_woocommerce_addon_not_installed_notice' );
				}

				if ( $this->addons->userRealTimeExportsExistAndAddonNotInstalled() && current_user_can( PMXE_Plugin::$capabilities ) ) {
					$this->showDismissibleNotice( __( '<strong>WP All Export Pro:</strong> An export configured to run in real time requires the User Export Add-On and will not export newly created users while the add-on is deactivated.</p>' ), 'wpae_real_time_woocommerce_addon_not_installed_notice' );
				}

				if ( $this->addons->acfRealTimeExportsExistAndNotInstalled() && current_user_can( PMXE_Plugin::$capabilities ) ) {
					$this->showDismissibleNotice( __( '<strong>WP All Export Pro:</strong> An export configured to run in real time requires the ACF Export Add-On and will not export newly created records while the add-on is deactivated.</p>' ), 'wpae_real_time_woocommerce_addon_not_installed_notice' );
				}

				$functions = $uploads['basedir'] . DIRECTORY_SEPARATOR . WP_ALL_EXPORT_UPLOADS_BASE_DIRECTORY . DIRECTORY_SEPARATOR . 'functions.php';
				$functions = apply_filters( 'wp_all_export_functions_file_path', $functions );
				if ( ! @file_exists( $functions ) ) {
					@touch( $functions );
				}

				$input = new PMXE_Input();
				$page  = strtolower( $input->getpost( 'page', '' ) );

				if ( preg_match( '%^' . preg_quote( str_replace( '_', '-', self::PREFIX ), '%' ) . '([\w-]+)$%', $page ) ) {

					$action = strtolower( $input->getpost( 'action', 'index' ) ) . '_action';

					// capitalize prefix and first letters of class name parts
					$controllerName = preg_replace_callback( '%(^' . preg_quote( self::PREFIX, '%' ) . '|_).%', array(
						$this,
						"replace_callback",
					), str_replace( '-', '_', $page ) );
					$actionName     = str_replace( '-', '_', $action );

					if ( strpos( $controllerName, 'PMXE_Admin' ) !== 0 ) {
						die( 'Security Check' );
					}

					if ( method_exists( $controllerName, $actionName ) ) {

						if ( ! get_current_user_id() || ( ! current_user_can( self::$capabilities ) && ! current_user_can( PMXE_Plugin::CLIENT_MODE_CAP ) ) ) {
							// This nonce is not valid.
							die( 'Security check' );
						}

						$this->_admin_current_screen = (object) array(
							'id'         => $controllerName,
							'base'       => $controllerName,
							'action'     => $actionName,
							'is_ajax'    => strpos( $_SERVER["HTTP_ACCEPT"], 'json' ) !== false,
							'is_network' => is_network_admin(),
							'is_user'    => is_user_admin(),
						);

						add_filter( 'current_screen', array( $this, 'getAdminCurrentScreen' ) );
						add_filter( 'admin_body_class', function ( $admin_body_class ) {
							return $admin_body_class . ' wpallexport-plugin';
						} );

						$controller = new $controllerName();
						if ( ! $controller instanceof PMXE_Controller_Admin ) {
							throw new Exception( "Administration page `$page` matches to a wrong controller type." );
						}

						$reviewsUI = new \Wpae\Reviews\ReviewsUI();
						add_action( 'admin_notices', [ $reviewsUI, 'render' ] );

						if ( $controller instanceof PMXE_Admin_Manage && $action == 'update_action' && isset( $_GET['id'] ) ) {
							$addons   = new \Wpae\App\Service\Addons\AddonService();
							$exportId = intval( $_GET['id'] );

							$export = new \PMXE_Export_Record();
							$export->getById( $exportId );

							$cpt = $export->options['cpt'];
							if ( ! is_array( $cpt ) ) {
								$cpt = array( $cpt );
							}

							if ( isset( $export->options['export_type'] ) && $export->options['export_type'] === 'advanced' ) {

								if ( ! $addons->isWooCommerceAddonActive() && ! $addons->isWooCommerceProductAddonActive() && strpos( $export->options['wp_query'], 'product' ) !== false && \class_exists( 'WooCommerce' ) ) {
									die( \__( 'The WooCommerce Export Add-On Pro is required to run this export. If you already own it, you can download the add-on here: <a href="https://www.wpallimport.com/portal/downloads" target="_blank">https://www.wpallimport.com/portal/downloads</a>', 'wp_all_export_plugin' ) );
								} else if ( ! $addons->isWooCommerceAddonActive() && ! $addons->isWooCommerceOrderAddonActive() && strpos( $export->options['wp_query'], 'shop_order' ) !== false ) {
									die( \__( 'The WooCommerce Export Add-On Pro is required to run this export. If you already own it, you can download the add-on here: <a href="https://www.wpallimport.com/portal/downloads" target="_blank">https://www.wpallimport.com/portal/downloads</a>', 'wp_all_export_plugin' ) );
								} else if ( ! $addons->isWooCommerceAddonActive() && strpos( $export->options['wp_query'], 'shop_coupon' ) !== false ) {
									die( \__( 'The WooCommerce Export Add-On Pro is required to run this export. If you already own it, you can download the add-on here: <a href="https://www.wpallimport.com/portal/downloads" target="_blank">https://www.wpallimport.com/portal/downloads</a>', 'wp_all_export_plugin' ) );
								}
							}

							// Block Google Merchant Exports if the supporting add-on isn't active.
							if ( isset( $export->options['xml_template_type'] ) && $export->options['xml_template_type'] == \XmlExportEngine::EXPORT_TYPE_GOOLE_MERCHANTS && ! $addons->isWooCommerceAddonActive() ) {

								die( \__( 'The WooCommerce Export Add-On Pro is required to run this export. If you already own it, you can download the add-on here: <a href="https://www.wpallimport.com/portal/downloads" target="_blank">https://www.wpallimport.com/portal/downloads</a>', 'wp_all_export_plugin' ) );

							}

							if ( ( ( in_array( 'users', $cpt ) || in_array( 'shop_customer', $cpt ) ) && ! $addons->isUserAddonActive() ) || ( $export->options['export_type'] == 'advanced' && $export->options['wp_query_selector'] == 'wp_user_query' && ! $addons->isUserAddonActive() ) ) {
								die( \__( 'The User Export Add-On Pro is required to run this export. If you already own it, you can download the add-on here: <a href="https://www.wpallimport.com/portal/downloads" target="_blank">https://www.wpallimport.com/portal/downloads</a>', 'wp_all_export_plugin' ) );
							}

							if ( ( $export->options['export_type'] == 'advanced' && $export->options['wp_query_selector'] != 'wp_user_query' && is_object( $export->options['exportquery'] ) && property_exists( $export->options['exportquery'], 'query' ) && isset( $export->options['exportquery']->query['post_type'] ) && ( ( $export->options['exportquery']->query['post_type'] == array(
												'product',
												'product_variation',
											) && \class_exists( 'WooCommerce' ) && ! $addons->isWooCommerceAddonActive() && ! $addons->isWooCommerceProductAddonActive() ) || ( $export->options['exportquery']->query['post_type'] == 'shop_order' && ! $addons->isWooCommerceAddonActive() && ! $addons->isWooCommerceOrderAddonActive() ) || ( $export->options['exportquery']->query['post_type'] == 'shop_coupon' && ! $addons->isWooCommerceAddonActive() ) ) ) || ( in_array( 'shop_order', $cpt ) && ! $addons->isWooCommerceOrderAddonActive() && ! $addons->isWooCommerceAddonActive() ) || ( ( in_array( 'shop_review', $cpt ) || in_array( 'shop_coupon', $cpt ) ) && ! $addons->isWooCommerceAddonActive() ) ) {
								die( \__( 'The WooCommerce Export Add-On Pro is required to run this export. If you already own it, you can download the add-on here: <a href="https://www.wpallimport.com/portal/downloads" target="_blank">https://www.wpallimport.com/portal/downloads</a>', 'wp_all_export_plugin' ) );
							}

							if ( ( in_array( 'product', $cpt ) && \class_exists( 'WooCommerce' ) ) && ! $addons->isWooCommerceAddonActive() && ! $addons->isWooCommerceProductAddonActive() ) {
								die( \__( 'The WooCommerce Export Add-On Pro is required to run this export. If you already own it, you can download the add-on here: <a href="https://www.wpallimport.com/portal/downloads" target="_blank">https://www.wpallimport.com/portal/downloads</a>', 'wp_all_export_plugin' ) );
							}

							if ( ( in_array( 'acf', $export->options['cc_type'] ) || $export->options['xml_template_type'] == 'custom' && in_array( 'acf', $export->options['custom_xml_template_options']['cc_type'] ) ) && ! $addons->isAcfAddonActive() ) {
								die( \__( 'The ACF Export Add-On Pro is required to run this export. If you already own it, you can download the add-on here: <a href="https://www.wpallimport.com/portal/downloads" target="_blank">https://www.wpallimport.com/portal/downloads</a>', 'wp_all_export_plugin' ) );
							}
						}

						if ( $controller instanceof PMXE_Admin_Manage && ( $action == 'update_action' || $action == 'template_action' || $action == 'options_action' ) && isset( $_GET['id'] ) ) {

							$exportId = intval( $_GET['id'] );

							$export = new \PMXE_Export_Record();
							$export->getById( $exportId );

							$cpt = $export->options['cpt'];

							if ( is_array( $cpt ) ) {
								$cpt = reset( $cpt );
							}

							if ( strpos( $cpt, 'custom_' ) === 0 && ! class_exists( 'GF_Export_Add_On' ) ) {

								die( 'The Gravity Forms Add-On Pro is required for this export. If you already own it, you can download the add-on here: <a href="https://www.wpallimport.com/portal/downloads" target="_blank">http://www.wpallimport.com/portal/</a>' );
							}
						}

						if ( $this->_admin_current_screen->is_ajax ) { // ajax request
							try {
								$controller->$action();
							} catch ( \Wpae\App\Service\Addons\AddonNotFoundException $e ) {
								die( $e->getMessage() );
							}
							do_action( 'wpallexport_action_after' );
							die(); // stop processing since we want to output only what controller has rendered, nothing in addition
						} elseif ( ! $controller->isInline ) {
							@ob_start();
							$controller->$action();
							self::$buffer = @ob_get_clean();
						} else {
							self::$buffer_callback = array( $controller, $action );
						}


					} else { // redirect to dashboard if requested page and/or action don't exist
						wp_redirect( admin_url() );
						die();
					}
				}
			}
		}

		/**
		 * Dispatch shorttag: create corresponding controller instance and call its index method
		 *
		 * @param array $args Shortcode tag attributes
		 * @param string $content Shortcode tag content
		 * @param string $tag shortcode tag name which is being dispatched
		 *
		 * @return string
		 * @throws Exception
		 */
		public function shortcodeDispatcher( $args, $content, $tag ) {

			$controllerName = self::PREFIX . preg_replace_callback( '%(^|_).%', array(
					$this,
					"replace_callback",
				), $tag );// capitalize first letters of class name parts and add prefix
			$controller     = new $controllerName();
			if ( ! $controller instanceof PMXE_Controller ) {
				throw new Exception( "Shortcode `$tag` matches to a wrong controller type." );
			}
			ob_start();
			$controller->index( $args, $content );

			return ob_get_clean();
		}

		static $buffer = null;
		static $buffer_callback = null;

		/**
		 * Dispatch admin page: call corresponding controller based on get parameter `page`
		 * The method is called twice: 1st time as handler `parse_header` action and then as admin menu item handler
		 *
		 * @param string $page
		 * @param string $action
		 *
		 * @throws Exception
		 * @internal param $string [optional] $page When $page set to empty string ealier buffered content is outputted, otherwise controller is called based on $page value
		 */
		public function adminDispatcher( $page = '', $action = 'index' ) {
			if ( '' === $page ) {
				if ( ! is_null( self::$buffer ) ) {
					echo '<div class="wrap">';
					echo self::$buffer;
					do_action( 'wpallexport_action_after' );
					echo '</div>';
				} elseif ( ! is_null( self::$buffer_callback ) ) {
					echo '<div class="wrap">';
					call_user_func( self::$buffer_callback );
					do_action( 'wpallexport_action_after' );
					echo '</div>';
				} else {
					throw new Exception( 'There is no previously buffered content to display.' );
				}
			}
		}

		public function replace_callback( $matches ) {
			return strtoupper( $matches[0] );
		}

		protected $_admin_current_screen = null;

		public function getAdminCurrentScreen() {
			return $this->_admin_current_screen;
		}

		/**
		 * Autoloader
		 * It's assumed class name consists of prefix folloed by its name which in turn corresponds to location of source file
		 * if `_` symbols replaced by directory path separator. File name consists of prefix folloed by last part in class name (i.e.
		 * symbols after last `_` in class name)
		 * When class has prefix it's source is looked in `models`, `controllers`, `shortcodes` folders, otherwise it looked in `core` or `library` folder
		 *
		 * @param string $className
		 *
		 * @return bool
		 */
		public function autoload( $className ) {
			if ( ! preg_match( '/Wpae|Xml|VariableProductTitle|PMXE|CdataStrategy/m', $className ) ) {
				return false;
			}

			$is_prefix = false;
			$filePath  = str_replace( '_', '/', preg_replace( '%^' . preg_quote( self::PREFIX, '%' ) . '%', '', strtolower( $className ), 1, $is_prefix ) ) . '.php';
			if ( ! $is_prefix ) { // also check file with original letter case
				$filePathAlt = $className . '.php';
			}
			foreach (
				$is_prefix ? array(
					'models',
					'controllers',
					'shortcodes',
					'classes',
				) : array( 'libraries' ) as $subdir
			) {
				$path = self::ROOT_DIR . '/' . $subdir . '/' . $filePath;
				if ( is_file( $path ) ) {
					require_once $path;

					return true;
				}
				if ( ! $is_prefix ) {
					if ( strpos( $className, '_' ) !== false ) {
						$filePathAlt = $this->lreplace( '_', DIRECTORY_SEPARATOR, $filePathAlt );
					}

					$pathAlt = self::ROOT_DIR . DIRECTORY_SEPARATOR . $subdir . DIRECTORY_SEPARATOR . $filePathAlt;

					if ( is_file( $pathAlt ) ) {
						require_once $pathAlt;

						return true;
					}
				}
			}
			if ( $className === 'CdataStrategyFactory' ) {
				//TODO: Move this to a namespace
				require_once( self::ROOT_DIR . '/classes/CdataStrategyFactory.php' );
			}


			if ( strpos( $className, '\\' ) !== false ) {

				// project-specific namespace prefix
				$prefix = 'Wpae\\';

				// base directory for the namespace prefix
				$base_dir = self::ROOT_DIR . '/src/';

				// does the class use the namespace prefix?
				$len = strlen( $prefix );
				if ( strncmp( $prefix, $className, $len ) !== 0 ) {
					// no, move to the next registered autoloader
					return false;
				}

				// get the relative class name
				$relative_class = substr( $className, $len );

				// replace the namespace prefix with the base directory, replace namespace
				// separators with directory separators in the relative class name, append
				// with .php
				$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

				// if the file exists, require it
				if ( file_exists( $file ) ) {
					require_once $file;
				}
			}

			return false;
		}

		/**
		 * Get plugin option
		 *
		 * @param string [optional] $option Parameter to return, all array of options is returned if not set
		 *
		 * @return mixed
		 * @throws Exception
		 */
		public function getOption( $option = null ) {
			$options = apply_filters( 'wp_all_export_config_options', $this->options );
			if ( is_null( $option ) ) {
				return $options;
			} else if ( isset( $options[ $option ] ) ) {
				return $options[ $option ];
			} else {
				throw new Exception( "Specified option is not defined for the plugin" );
			}
		}

		/**
		 * Update plugin option value
		 *
		 * @param string|array $option Parameter name or array of name => value pairs
		 * @param null $value
		 *
		 * @return array
		 * @throws Exception
		 * @internal param $mixed [optional] $value New value for the option, if not set than 1st parameter is supposed to be array of name => value pairs
		 */
		public function updateOption( $option, $value = null ) {
			is_null( $value ) or $option = array( $option => $value );
			if ( array_diff_key( $option, $this->options ) ) {
				throw new Exception( "Specified option is not defined for the plugin" );
			}

			if ( ! empty( $option['license'] ) ) {
				$option['license'] = self::encode( self::decode( $option['license'] ) );
			}

			if ( ! empty( $option['scheduling_license'] ) ) {
				$option['scheduling_license'] = self::encode( self::decode( $option['scheduling_license'] ) );
			}

			$this->options = $option + $this->options;
			update_option( get_class( $this ) . '_Options', $this->options );

			return $this->options;
		}

		/**
		 * @param $value
		 *
		 * @return string
		 */
		public static function encode( $value ) {
			$salt = md5( defined( 'AUTH_SALT' ) ? AUTH_SALT : wp_salt() );
			// This new salt storage is to avoid the problems encountered
			// when the AUTH_SALT value changes on a site.
			$new_salt = get_option( 'wpae_config_version', false );
			if ( $new_salt ) {
				$salt = $new_salt;
			} else {
				// Save the salt for later use.
				update_option( 'wpae_config_version', $salt, false );
			}

			return base64_encode( $salt . $value . md5( $salt ) );
		}

		/**
		 * @param $encoded
		 *
		 * @return mixed
		 */
		public static function decode( $encoded ) {
			$salt     = md5( defined( 'AUTH_SALT' ) ? AUTH_SALT : wp_salt() );
			$new_salt = get_option( 'wpae_config_version', false );
			if ( $new_salt ) {
				$salt = $new_salt;
			}

			return preg_match( '/^[a-f0-9]{32}$/', $encoded ) ? $encoded : str_replace( array(
				$salt,
				md5( $salt ),
			), '', base64_decode( $encoded ) );
		}

		/**
		 * Plugin activation logic
		 */
		public function activation() {

			$installer = new PMXE_Installer();
			$installer->checkActivationConditions();

			if ( class_exists( 'PMXI_Plugin' ) ) {
				if ( method_exists( 'PMXI_Plugin', 'getSchedulingName' ) ) {
					$schedulingLicenseData                              = array();
					$schedulingLicenseData['scheduling_license']        = PMXI_Plugin::getInstance()->getOption( 'scheduling_license' );
					$schedulingLicenseData['scheduling_license_status'] = PMXI_Plugin::getInstance()->getOption( 'scheduling_license_status' );

					PMXE_Plugin::getInstance()->updateOption( $schedulingLicenseData );
				}
			}

			// uncaught exception doesn't prevent plugin from being activated, therefore replace it with fatal error so it does
			set_exception_handler( function ( $e ) {
				trigger_error( $e->getMessage(), E_USER_ERROR );
			} );

			// create plugin options
			$option_name     = get_class( $this ) . '_Options';
			$options_default = PMXE_Config::createFromFile( self::ROOT_DIR . '/config/options.php' )->toArray();
			$wpai_options    = get_option( $option_name, false );
			if ( ! $wpai_options ) {
				update_option( $option_name, $options_default );
			}

			// create/update required database tables
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			require self::ROOT_DIR . '/schema.php';
			global $wpdb;

			delete_transient( PMXE_Plugin::$cache_key );

			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name = %s", 'wp-all-export-pro_' . PMXE_Plugin::$cache_key ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name = %s", 'wp-all-export-pro_timeout_' . PMXE_Plugin::$cache_key ) );

			delete_site_transient( 'update_plugins' );

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				// check if it is a network activation - if so, run the activation function for each blog id
				if ( isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1 ) ) {
					$old_blog = $wpdb->blogid;
					// Get all blog ids
					$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
					foreach ( $blogids as $blog_id ) {
						switch_to_blog( $blog_id );
						require self::ROOT_DIR . '/schema.php';
						dbDelta( $plugin_queries );
					}
					switch_to_blog( $old_blog );

					return;
				}
			}

			dbDelta( $plugin_queries );

		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present
		 *
		 * @access public
		 * @return void
		 */
		public function load_plugin_textdomain() {

			$locale = apply_filters( 'plugin_locale', get_locale(), 'wp_all_export_plugin' );

			load_plugin_textdomain( 'wp_all_export_plugin', false, dirname( plugin_basename( __FILE__ ) ) . "/i18n/languages" );
		}

		public function fix_db_schema() {

			global $wpdb;

			$db_version_old = get_option( 'wp_all_export_db_version' );
			$installed_ver  = get_option( 'wp_all_export_pro_db_version' );

			// We leave the old option so if it doesn't exist then this was installed after the export addons release.
			// If it does exist we make sure it's not a free version since such an old Pro version would no longer work.
			if ( ! $db_version_old || version_compare( $db_version_old, '1.3.0' ) == - 1 ) {
				update_option( "wp_all_export_pro_addons_not_included", true );
			}

			if ( $installed_ver == PMXE_VERSION ) {
				return true;
			}

			// Declare variable to avoid nuisance notices when charset and collate aren't set.
			$charset_collate = '';

			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE $wpdb->collate";
			}

			$table_prefix = $this->getTablePrefix();

			$wpdb->query( "CREATE TABLE IF NOT EXISTS {$table_prefix}templates (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				name VARCHAR(200) NOT NULL DEFAULT '',
				options LONGTEXT,
				PRIMARY KEY  (id)
			) $charset_collate;" );

			$wpdb->query( "CREATE TABLE IF NOT EXISTS {$table_prefix}posts (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				post_id BIGINT(20) UNSIGNED NOT NULL,
				export_id BIGINT(20) UNSIGNED NOT NULL,	
				iteration BIGINT(20) NOT NULL DEFAULT 0,
				PRIMARY KEY  (id)	
			) $charset_collate;" );

			$googleCatsTableExists = $wpdb->query( "SHOW TABLES LIKE '{$table_prefix}google_cats'" );
			if ( ! $googleCatsTableExists ) {
				require_once self::ROOT_DIR . '/schema.php';
				$wpdb->query( $googleCatsQueryCreate );
				$wpdb->query( $googleCatsQueryData );
			}
			$table               = $this->getTablePrefix() . 'exports';
			$tablefields         = $wpdb->get_results( "DESCRIBE {$table};" );
			$iteration           = false;
			$parent_id           = false;
			$export_post_type    = false;
			$client_mode_enabled = false;
			$created_at          = false;
			$created_at_gmt      = false;
			$rte_last_row        = false;

			// Check if field exists
			foreach ( $tablefields as $tablefield ) {
				if ( 'iteration' == $tablefield->Field ) {
					$iteration = true;
				}
				if ( 'parent_id' == $tablefield->Field ) {
					$parent_id = true;
				}
				if ( 'export_post_type' == $tablefield->Field ) {
					$export_post_type = true;
				}
				if ( 'client_mode_enabled' == $tablefield->Field ) {
					$client_mode_enabled = true;
				}
				if ( 'created_at' == $tablefield->Field ) {
					$created_at = true;
				}
				if ( 'created_at_gmt' == $tablefield->Field ) {
					$created_at_gmt = true;
				}
				if ( 'rte_last_row' == $tablefield->Field ) {
					$rte_last_row = true;
				}

			}

			if ( ! $iteration ) {
				$wpdb->query( "ALTER TABLE {$table} ADD `iteration` BIGINT(20) NOT NULL DEFAULT 0;" );
			}
			if ( ! $parent_id ) {
				$wpdb->query( "ALTER TABLE {$table} ADD `parent_id` BIGINT(20) NOT NULL DEFAULT 0;" );
			}
			if ( ! $export_post_type ) {
				$wpdb->query( "ALTER TABLE {$table} ADD `export_post_type` TEXT NOT NULL DEFAULT '';" );
			}

			if ( ! $client_mode_enabled ) {
				$wpdb->query( "ALTER TABLE {$table} ADD `client_mode_enabled` TINYINT NOT NULL DEFAULT '0';" );
			}

			if ( ! $created_at ) {
				$wpdb->query( "ALTER TABLE {$table} ADD `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;" );
				$wpdb->query( "UPDATE {$table} SET `created_at` = `registered_on` WHERE 1" );
			}

			if ( ! $created_at_gmt ) {
				$wpdb->query( "ALTER TABLE {$table} ADD `created_at_gmt` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';" );
			}

			if ( ! $rte_last_row ) {
				$wpdb->query( "ALTER TABLE {$table} ADD `rte_last_row` MEDIUMTEXT NOT NULL DEFAULT '';" );
			}

			update_option( "wp_all_export_pro_db_version", PMXE_VERSION );
		}

		/**
		 * Determine is current export was created before current version
		 */
		public static function isExistingExport( $checkVersion = false ) {

			$input     = new PMXE_Input();
			$export_id = $input->get( 'id', 0 );

			if ( empty( $export_id ) ) {
				$export_id = $input->get( 'export_id', 0 );
			}

			// ID not found means this is new export
			if ( empty( $export_id ) ) {
				return false;
			}

			if ( ! $checkVersion ) {
				$checkVersion = PMXE_VERSION;
			}

			$export = new PMXE_Export_Record();
			$export->getById( $export_id );
			if ( ! $export->isEmpty() && ( empty( $export->options['created_at_version'] ) || version_compare( $export->options['created_at_version'], $checkVersion ) < 0 ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Determine is current export is first time running
		 */
		public static function isNewExport() {

			$input     = new PMXE_Input();
			$export_id = $input->get( 'id', 0 );

			if ( empty( $export_id ) ) {
				$export_id = $input->get( 'export_id', 0 );
			}

			if ( empty( $export_id ) ) {
				$export_id = XmlExportEngine::$exportID;
			}

			// ID not found means this is new export
			if ( empty( $export_id ) ) {
				return true;
			}

			$export = new PMXE_Export_Record();
			$export->getById( $export_id );
			if ( ! $export->isEmpty() && ! $export->iteration ) {
				return true;
			}

			return false;
		}

		/**
		 * Method returns default import options, main utility of the method is to avoid warnings when new
		 * option is introduced but already registered imports don't have it
		 */
		public static function get_default_import_options() {
			return array(
				'cpt'                                       => array(),
				'whereclause'                               => '',
				'joinclause'                                => '',
				'filter_rules_hierarhy'                     => '',
				'product_matching_mode'                     => 'parent',
				'order_item_per_row'                        => 1,
				'order_item_fill_empty_columns'             => 1,
				'csv_omit_empty_columns'                    => 0,
				'filepath'                                  => '',
				'current_filepath'                          => '',
				'bundlepath'                                => '',
				'export_type'                               => 'specific',
				'wp_query'                                  => '',
				'wp_query_selector'                         => 'wp_query',
				'is_user_export'                            => false,
				'is_comment_export'                         => false,
				'export_to'                                 => 'csv',
				'export_to_sheet'                           => 'csv',
				'delimiter'                                 => ',',
				'encoding'                                  => 'UTF-8',
				'is_generate_templates'                     => 1,
				'is_generate_import'                        => 1,
				'import_id'                                 => 0,
				'template_name'                             => '',
				'is_scheduled'                              => 0,
				'scheduled_period'                          => '',
				'scheduled_email'                           => '',
				'cc_label'                                  => array(),
				'cc_type'                                   => array(),
				'cc_value'                                  => array(),
				'cc_name'                                   => array(),
				'cc_php'                                    => array(),
				'cc_code'                                   => array(),
				'cc_sql'                                    => array(),
				'cc_options'                                => array(),
				'cc_settings'                               => array(),
				'cc_combine_multiple_fields'                => array(),
				'cc_combine_multiple_fields_value'          => array(),
				'friendly_name'                             => '',
				'fields'                                    => array( 'default', 'other', 'cf', 'cats' ),
				'ids'                                       => array(),
				'rules'                                     => array(),
				'records_per_iteration'                     => 50,
				'include_bom'                               => 1,
				'include_functions'                         => 1,
				'split_large_exports'                       => 0,
				'split_large_exports_count'                 => 10000,
				'split_files_list'                          => array(),
				'main_xml_tag'                              => 'data',
				'record_xml_tag'                            => 'post',
				'save_template_as'                          => 0,
				'name'                                      => '',
				'export_only_new_stuff'                     => 0,
				'export_only_modified_stuff'                => 0,
				'creata_a_new_export_file'                  => 0,
				'attachment_list'                           => array(),
				'order_include_poducts'                     => 0,
				'order_include_all_poducts'                 => 0,
				'order_include_coupons'                     => 0,
				'order_include_all_coupons'                 => 0,
				'order_include_customers'                   => 0,
				'order_include_all_customers'               => 0,
				'migration'                                 => '',
				'xml_template_type'                         => 'simple',
				'custom_xml_template'                       => '',
				'custom_xml_template_header'                => '',
				'custom_xml_template_loop'                  => '',
				'custom_xml_template_footer'                => '',
				'custom_xml_template_options'               => array(),
				'custom_xml_cdata_logic'                    => 'auto',
				'show_cdata_in_preview'                     => 0,
				'taxonomy_to_export'                        => '',
				'sub_post_type_to_export'                   => '',
				'created_at_version'                        => '',
				'export_variations'                         => XmlExportEngine::VARIABLE_PRODUCTS_EXPORT_PARENT_AND_VARIATION,
				'export_variations_title'                   => XmlExportEngine::VARIATION_USE_PARENT_TITLE,
				'export_only_customers_that_made_purchases' => ( PMXE_Plugin::isExistingExport() ) ? 1 : 0,
				'include_header_row'                        => 1,
				'wpml_lang'                                 => 'all',
				'enable_export_scheduling'                  => 'false',

				'scheduling_enable'      => false,
				'scheduling_weekly_days' => '',
				'scheduling_run_on'      => 'weekly',
				'scheduling_monthly_day' => '',
				'scheduling_times'       => array(),
				'scheduling_timezone'    => 'UTC',

				'allow_client_mode'                   => 0,
				'enable_real_time_exports'            => 0,
				'enable_real_time_exports_running'    => 0,
				'do_not_generate_file_on_new_records' => 0,

				'security_token' => '',
			);
		}

		public static function is_ajax() {
			return ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) ? true : false;
		}

		/**
		 * Replace last occurence of string
		 * Used in autoloader, that's not muved in string class
		 *
		 * @param $search
		 * @param $replace
		 * @param $subject
		 *
		 * @return mixed
		 */
		private function lreplace( $search, $replace, $subject ) {
			$pos = strrpos( $subject, $search );
			if ( $pos !== false ) {
				$subject = substr_replace( $subject, $replace, $pos, strlen( $search ) );
			}

			return $subject;
		}

		public static function hposEnabled() {
			return class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
		}

	}

	PMXE_Plugin::getInstance();

	function wp_all_export_pro_updater() {
		// retrieve our license key from the DB
		$wp_all_export_options = get_option( 'PMXE_Plugin_Options' );

		// setup the updater
		$updater = new PMXE_Updater( $wp_all_export_options['info_api_url_new'], __FILE__, array(
				'version'   => PMXE_VERSION,
				// current version number
				'license'   => ( ! empty( $wp_all_export_options['license'] ) ) ? PMXE_Plugin::decode( $wp_all_export_options['license'] ) : false,
				// license key (used get_option above to retrieve from DB)
				'item_name' => PMXE_Plugin::getEddName(),
				// name of this plugin
				'author'    => 'Soflyy',
				// author of this plugin
			) );

		// Provide updater for version 1.0.0 of the Gravity Forms Export Add-On.
		if ( class_exists( 'GF_Export_Add_On' ) && in_array( GF_Export_Add_On::VERSION, [ '1.0.0', '1.0.1' ] ) ) {
			// Plugin path.
			$wpae_gf_path = plugin_dir_path( __DIR__ ) . 'wpae-gravity-forms-export-addon/wpae-gf-addon.php';

			// Make sure the plugin file actually exists.
			if ( file_exists( $wpae_gf_path ) ) {

				// Provide supplemental updater for early GF Export Add-On version.
				$wpae_gf_updater = new PMXE_Updater( $wp_all_export_options['info_api_url'], $wpae_gf_path, array(
					'version'   => GF_Export_Add_On::VERSION,        // current version number
					'license'   => false,
					'item_name' => 'Gravity Forms Export Add-On Pro',    // name of this plugin
					'author'    => 'Soflyy',  // author of this plugin
				) );
			}

		}
	}

	add_action( 'plugins_loaded', 'wp_all_export_pro_updater', 0 );

	// Include the api front controller
	include_once( 'wpae_api.php' );

}
