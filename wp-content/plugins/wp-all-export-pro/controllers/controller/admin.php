<?php

/**
 * Introduce special type for controllers which render pages inside admin area
 *
 * @author Pavel Kulbakin <p.kulbakin@gmail.com>
 */
abstract class PMXE_Controller_Admin extends PMXE_Controller {
	/**
	 * Admin page base url (request url without all get parameters but `page`)
	 * @var string
	 */
	public $baseUrl;
	/**
	 * Parameters which is left when baseUrl is detected
	 * @var array
	 */
	public $baseUrlParamNames = array( 'page', 'pagenum', 'order', 'order_by', 'type', 's', 'f' );
	/**
	 * Whether controller is rendered inside wordpress page
	 * @var bool
	 */
	public $isInline = false;

	/**
	 * Constructor
	 */
	public function __construct() {

		$remove = array_diff( array_keys( $_GET ), $this->baseUrlParamNames );
		if ( $remove ) {
			$this->baseUrl = remove_query_arg( $remove );
		} else {
			$this->baseUrl = $_SERVER['REQUEST_URI'];
		}
		parent::__construct();

		// add special filter for url fields
		$this->input->addFilter( 'pmxe_url_filter' );

		// enqueue required sripts and styles
		global $wp_styles;
		if ( ! is_a( $wp_styles, 'WP_Styles' ) ) {
			$wp_styles = new WP_Styles();
		}

		wp_enqueue_style( 'jquery-ui', PMXE_ROOT_URL . '/static/js/jquery/css/redmond/jquery-ui.css', array( 'media-views' ) );
		wp_enqueue_style( 'jquery-tipsy', PMXE_ROOT_URL . '/static/js/jquery/css/smoothness/jquery.tipsy.css', array( 'media-views' ) );
		wp_enqueue_style( 'pmxe-admin-style', PMXE_ROOT_URL . '/static/css/admin.css', array( 'media-views' ), PMXE_VERSION );
		wp_enqueue_style( 'pmxe-scheduling-style', PMXE_ROOT_URL . '/static/css/scheduling.css', array(), PMXE_VERSION );
		wp_enqueue_style( 'pmxe-admin-style-ie', PMXE_ROOT_URL . '/static/css/admin-ie.css', array( 'media-views' ) );
		wp_enqueue_style( 'jquery-select2', PMXE_ROOT_URL . '/static/js/jquery/css/select2/select2.css', array( 'media-views' ) );
		wp_enqueue_style( 'jquery-select2', PMXE_ROOT_URL . '/static/js/jquery/css/select2/select2-bootstrap.css', array( 'media-views' ) );
		wp_enqueue_style( 'jquery-chosen', PMXE_ROOT_URL . '/static/js/jquery/css/chosen/chosen.css', array( 'media-views' ) );
		wp_enqueue_style( 'jquery-codemirror', PMXE_ROOT_URL . '/static/codemirror/codemirror.css', array( 'media-views' ), PMXE_VERSION );
		wp_enqueue_style( 'jquery-timepicker', PMXE_ROOT_URL . '/static/js/jquery/css/timepicker/jquery.timepicker.css', array( 'media-views' ), PMXE_VERSION );
		wp_enqueue_style( 'pmxe-angular-scss', PMXE_ROOT_URL . '/dist/styles.css', array( 'media-views' ), PMXE_VERSION );
		wp_enqueue_style( 'jquery-codemirror', PMXE_ROOT_URL . '/static/css/codemirror.css', array(), PMXE_VERSION );

		$wp_styles->add_data( 'pmxe-admin-style-ie', 'conditional', 'lte IE 7' );
		wp_enqueue_style( 'wp-pointer' );

		if ( version_compare( get_bloginfo( 'version' ), '3.8-RC1' ) >= 0 ) {
			wp_enqueue_style( 'pmxe-admin-style-wp-3.8', PMXE_ROOT_URL . '/static/css/admin-wp-3.8.css', array( 'media-views' ) );
		}

		if ( version_compare( get_bloginfo( 'version' ), '4.4' ) >= 0 ) {
			wp_enqueue_style( 'pmxe-admin-style-wp-4.4', PMXE_ROOT_URL . '/static/css/admin-wp-4.4.css', array( 'media-views' ) );
		}

		$scheme_color = get_user_option( 'admin_color' ) and is_file( PMXE_Plugin::ROOT_DIR . '/static/css/admin-colors-' . $scheme_color . '.css' ) or $scheme_color = 'fresh';
		if ( is_file( PMXE_Plugin::ROOT_DIR . '/static/css/admin-colors-' . $scheme_color . '.css' ) ) {
			wp_enqueue_style( 'pmxe-admin-style-color', PMXE_ROOT_URL . '/static/css/admin-colors-' . $scheme_color . '.css', array( 'media-views' ) );
		}

		add_action( "admin_enqueue_scripts", [ $this, 'add_admin_scripts' ] );

		wp_enqueue_script( 'jquery-ui-datepicker', PMXE_ROOT_URL . '/static/js/jquery/ui.datepicker.js', 'jquery-ui-core' );
		wp_enqueue_script( 'tipsy', PMXE_ROOT_URL . '/static/js/jquery/jquery.tipsy.js', 'jquery', PMXE_VERSION );
		wp_enqueue_script( 'jquery-pmxe-nestable', PMXE_ROOT_URL . '/static/js/jquery/jquery.mjs.pmxe_nestedSortable.js', array(
			'jquery',
			'jquery-ui-dialog',
			'jquery-ui-sortable',
			'jquery-ui-draggable',
			'jquery-ui-droppable',
			'jquery-ui-tabs',
			'jquery-ui-progressbar',
		) );
		wp_enqueue_script( 'jquery-select2', PMXE_ROOT_URL . '/static/js/jquery/select2.min.js', 'jquery' );
		wp_enqueue_script( 'jquery-ddslick', PMXE_ROOT_URL . '/static/js/jquery/jquery.ddslick.min.js', 'jquery' );
		wp_enqueue_script( 'jquery-chosen', PMXE_ROOT_URL . '/static/js/jquery/chosen.jquery.js', 'jquery' );
		wp_enqueue_script( 'jquery-timepicker', PMXE_ROOT_URL . '/static/js/jquery/jquery.timepicker.js', array( 'jquery' ), PMXE_VERSION );

		wp_enqueue_script( 'wp-pointer' );

		/* load plupload scripts */
		wp_enqueue_script( 'pmxe-admin-script', PMXE_ROOT_URL . '/static/js/admin.js', array(
			'jquery',
			'jquery-ui-dialog',
			'jquery-ui-datepicker',
			'jquery-ui-draggable',
			'jquery-ui-droppable',
			'jquery-ui-core',
			'jquery-ui-widget',
			'jquery-ui-position',
			'jquery-ui-autocomplete',
		), PMXE_VERSION );
		wp_enqueue_script( 'pmxe-scheduling-script', PMXE_ROOT_URL . '/static/js/scheduling.js', array( 'pmxe-admin-script' ), PMXE_VERSION );
		wp_enqueue_script( 'pmxe-admin-validate-braces', PMXE_ROOT_URL . '/static/js/validate-braces.js', array( 'pmxe-admin-script' ), PMXE_VERSION );

		if ( getenv( 'WPAE_DEV' ) ) {
			wp_enqueue_script( 'pmxe-angular-app', PMXE_ROOT_URL . '/dist/app.js', array( 'jquery' ), PMXE_VERSION );
		} else {
			wp_enqueue_script( 'pmxe-angular-app', PMXE_ROOT_URL . '/dist/app.min.js', array( 'jquery' ), PMXE_VERSION );
		}
	}

	public function add_admin_scripts() {
		$cm_settings['codeEditor'] = wp_enqueue_code_editor( [ 'type' => 'php' ] );

		// Use our modified function if user has disabled the syntax editor.
		if ( false === $cm_settings['codeEditor'] ) {
			$cm_settings['codeEditor'] = wpae_wp_enqueue_code_editor( [ 'type' => 'php' ] );
		}

		wp_localize_script( 'jquery', 'wpae_cm_settings', $cm_settings );

		// Addons
		wp_localize_script( 'jquery', 'wpae_addons', \XmlExportEngine::get_addons() );
	}

	/**
	 * @see Controller::render()
	 */
	protected function render( $viewPath = null ) {
		// assume template file name depending on calling function
		if ( is_null( $viewPath ) ) {
			$trace = debug_backtrace();
			$dispatchedFunction = str_replace( '_action', '', $trace[1]['function'] );
			$viewPath = str_replace( '_', '/', preg_replace( '%^' . preg_quote( PMXE_Plugin::PREFIX, '%' ) . '%', '', strtolower( $trace[1]['class'] ) ) ) . '/' . $dispatchedFunction;
		}
		parent::render( $viewPath );
	}

	protected function onlyAllowAdmin() {
		if ( ! current_user_can( PMXE_Plugin::$capabilities ) ) {
			die( 'Security check' );
		}
	}

	/**
	 * @param $item
	 */
	protected function userHasAccessToItem( $item ) {
		if ( ! current_user_can( PMXE_Plugin::$capabilities ) && ! ( current_user_can( PMXE_Plugin::CLIENT_MODE_CAP ) && $item['client_mode_enabled'] ) ) {
			die( 'Security check' );
		}
	}

}