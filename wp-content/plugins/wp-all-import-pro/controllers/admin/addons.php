<?php

/**
 * Admin Add-ons page
 *
 * @author Maksym Tsypliakov <maksym.tsypliakov@gmail.com>
 */
class PMXI_Admin_Addons extends PMXI_Controller_Admin {

	public static $addons = array( 'PMWI_Plugin'     => 0,
	                               'PMAI_Plugin'     => 0,
	                               'PMWITabs_Plugin' => 0,
	                               'PMLI_Plugin'     => 0,
	                               'PMLCA_Plugin'    => 0,
	                               'PMUI_Plugin'     => 0,
	                               'PMTI_Plugin'     => 0,
	                               'PMGI_Plugin'     => 0,
	                               'PMMI_Plugin'     => 0,
	); // inactive by default

	public static $premium = array();

	public static $free = array();

	public function __construct() {

		parent::__construct();

		// Woocommerce add-on
		self::$premium['PMWI_Plugin'] = array(
			'title'            => __( "WooCommerce Addon", 'wp-all-import-pro' ),
			'description'      => __( "Import Products from any XML or CSV to WooCommerce", 'wp-all-import-pro' ),
			'thumbnail'        => 'http://placehold.it/220x220',
			'active'           => ( class_exists( 'PMWI_Plugin' ) and defined( 'PMWI_EDITION' ) and PMWI_EDITION == 'paid' ),
			'free_installed'   => ( class_exists( 'PMWI_Plugin' ) and defined( 'PMWI_EDITION' ) and PMWI_EDITION == 'free' ),
			'required_plugins' => false,
			'url'              => 'https://www.wpallimport.com/woocommerce-product-import/',
		);

		// ACF add-on
		self::$premium['PMAI_Plugin'] = array(
			'title'            => __( "ACF Addon", 'wp-all-import-pro' ),
			'description'      => __( "Import to advanced custom fields", 'wp-all-import-pro' ),
			'thumbnail'        => 'http://placehold.it/220x220',
			'active'           => ( class_exists( 'PMAI_Plugin' ) and defined( 'PMAI_EDITION' ) and PMAI_EDITION == 'paid' ),
			'free_installed'   => ( class_exists( 'PMAI_Plugin' ) and defined( 'PMAI_EDITION' ) and PMAI_EDITION == 'free' ),
			'required_plugins' => array( 'Advanced Custom Fields' => class_exists( 'acf' ) ),
			'url'              => 'https://www.wpallimport.com/advanced-custom-fields/',
		);

		// Toolset Types add-on
		self::$premium['PMTI_Plugin'] = array(
			'title'            => __( "Toolset Types Addon", 'wp-all-import-pro' ),
			'description'      => __( "Import to toolset types", 'wp-all-import-pro' ),
			'thumbnail'        => 'http://placehold.it/220x220',
			'active'           => ( class_exists( 'PMTI_Plugin' ) and defined( 'PMTI_EDITION' ) and PMTI_EDITION == 'paid' ),
			'free_installed'   => ( class_exists( 'PMTI_Plugin' ) and defined( 'PMTI_EDITION' ) and PMTI_EDITION == 'free' ),
			'required_plugins' => array( 'Toolset Types' => defined( TYPES_VERSION ) ),
			'url'              => 'https://www.wpallimport.com/advanced-custom-fields/',
		);

		// WPML add-on
		self::$premium['PMLI_Plugin'] = array(
			'title'            => __( "WPML Addon", 'wp-all-import-pro' ),
			'description'      => __( "Import to WPML", 'wp-all-import-pro' ),
			'thumbnail'        => 'http://placehold.it/220x220',
			'active'           => ( class_exists( 'PMLI_Plugin' ) and defined( 'PMLI_EDITION' ) and PMLI_EDITION == 'paid' ),
			'free_installed'   => ( class_exists( 'PMLI_Plugin' ) and defined( 'PMLI_EDITION' ) and PMLI_EDITION == 'free' ),
			'required_plugins' => array( 'WPML' => class_exists( 'SitePress' ) ),
			'url'              => 'https://www.wpallimport.com/add-ons/wpml/',
		);

		// User add-on
		self::$premium['PMUI_Plugin'] = array(
			'title'            => __( "User Addon", 'wp-all-import-pro' ),
			'description'      => __( "Import Users", 'wp-all-import-pro' ),
			'thumbnail'        => 'http://placehold.it/220x220',
			'active'           => ( class_exists( 'PMUI_Plugin' ) and defined( 'PMUI_EDITION' ) and PMUI_EDITION == 'paid' ),
			'free_installed'   => ( class_exists( 'PMUI_Plugin' ) and defined( 'PMUI_EDITION' ) and PMUI_EDITION == 'free' ),
			'required_plugins' => false,
			'url'              => 'https://www.wpallimport.com/import-wordpress-users/',
		);

		// Gravity Forms add-on
		self::$premium['PMGI_Plugin'] = array(
			'title'            => __( "Gravity Forms Addon", 'wp-all-import-pro' ),
			'description'      => __( "Import Gravity Forms Entries", 'wp-all-import-pro' ),
			'thumbnail'        => 'http://placehold.it/220x220',
			'active'           => ( class_exists( 'PMGI_Plugin' ) and defined( 'PMGI_EDITION' ) and PMGI_EDITION == 'paid' ),
			'free_installed'   => ( class_exists( 'PMGI_Plugin' ) and defined( 'PMGI_EDITION' ) and PMGI_EDITION == 'free' ),
			'required_plugins' => array( 'Gravity Forms' => class_exists( 'GFForms' ) ),
			'url'              => 'https://www.wpallimport.com/import-gravity-forms-entries-from-xml-csv/',
		);

		// Meta Box add-on
		self::$premium['PMMI_Plugin'] = array(
			'title'            => __( "Meta Box Addon", 'wp-all-import-pro' ),
			'description'      => __( "Import Meta Box Custom Fields", 'wp-all-import-pro' ),
			'thumbnail'        => 'http://placehold.it/220x220',
			'active'           => ( class_exists( 'PMMI_Plugin' ) and defined( 'PMMI_EDITION' ) and PMMI_EDITION == 'paid' ),
			'free_installed'   => ( class_exists( 'PMMI_Plugin' ) and defined( 'PMMI_EDITION' ) and PMMI_EDITION == 'free' ),
			'required_plugins' => array( 'Meta Box' => class_exists( 'RWMB_Loader' ) ),
			'url'              => '',
		);

		// Affiliate link cloaking add-on
		self::$premium['PMLCA_Plugin'] = array(
			'title'            => __( "Link cloaking Addon", 'wp-all-import-pro' ),
			'description'      => __( "Affiliate link cloaking", 'wp-all-import-pro' ),
			'thumbnail'        => 'http://placehold.it/220x220',
			'active'           => ( class_exists( 'PMLCA_Plugin' ) and defined( 'PMLCA_EDITION' ) and PMLCA_EDITION == 'paid' ),
			'free_installed'   => ( class_exists( 'PMLCA_Plugin' ) and defined( 'PMLCA_EDITION' ) and PMLCA_EDITION == 'free' ),
			'required_plugins' => false,
			'url'              => '',
		);

		self::$free['PMWI_Plugin']     = array(
			'title'            => __( "WooCommerce Addon - free edition", 'wp-all-import-pro' ),
			'description'      => __( "Import Products from any XML or CSV to WooCommerce", 'wp-all-import-pro' ),
			'thumbnail'        => 'http://placehold.it/220x220',
			'active'           => ( class_exists( 'PMWI_Plugin' ) and defined( 'PMWI_EDITION' ) and PMWI_EDITION == 'free' ),
			'paid_installed'   => ( class_exists( 'PMWI_Plugin' ) and defined( 'PMWI_EDITION' ) and PMWI_EDITION == 'paid' ),
			'required_plugins' => false,
			'url'              => 'https://wordpress.org/plugins/woocommerce-xml-csv-product-import',
		);
		self::$free['PMWITabs_Plugin'] = array(
			'title'            => __( "WooCommerce Tabs Addon", 'wp-all-import-pro' ),
			'description'      => __( "Import data to WooCommerce tabs", 'wp-all-import-pro' ),
			'thumbnail'        => 'http://placehold.it/220x220',
			'active'           => class_exists( 'PMWITabs_Plugin' ),
			'paid_installed'   => false,
			'required_plugins' => array( 'WooCommerce Addon' => class_exists( 'PMWI_Plugin' ) ),
			'url'              => 'https://www.wpallimport.com',
		);


	}

	public function index() {

		$this->data['premium'] = self::$premium;

		$this->data['free'] = self::$free;

		$this->render();
	}

	public function get_premium_addons() {
		return self::$premium;
	}

	public function get_free_addons() {
		return self::$free;
	}

	protected static function set_addons_status() {
		foreach ( self::$addons as $class => $active ) {
			self::$addons[ $class ] = class_exists( $class );
		}

		self::$addons = apply_filters( 'pmxi_addons', self::$addons );

	}

	public static function get_all_addons() {

		self::set_addons_status();

		return self::$addons;
	}

	public static function get_addon( $addon = false ) {

		self::set_addons_status();

		return ( $addon ) ? self::$addons[ $addon ] : false;
	}

	public static function get_active_addons() {

		self::set_addons_status();
		$active_addons = array();
		foreach ( self::$addons as $class => $active ) {
			if ( $active ) {
				$active_addons[] = $class;
			}
		}

		return $active_addons;
	}

}
