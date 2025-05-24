<?php

namespace Ourblocktheme\controllers;

class ThemeEnqueue {

	private static string $googleMapApiKey = "AIzaSyBRRnSwouBfAyhQ47rfDX0NMPcqiQ1Qm4s"; // Replace with a secure source

	public function __construct() {
		self::load_dependencies();
		self::register_hooks();
	}

	private function register_hooks(): void {
		add_action( 'wp_enqueue_scripts', [ self::class, 'themeEnqueues' ] );
		add_filter( 'login_enqueue_scripts', [ self::class, 'ourLoginCSS' ] );
		add_filter( 'acf/fields/google_map/api', [ self::class, 'campusMapKey' ] );
		add_action( 'init', [ self::class, 'our_new_blocks' ] );
	}

	public static function themeEnqueues(): void {
		wp_enqueue_script( 'googleMap', '//maps.googleapis.com/maps/api/js?key=' . self::$googleMapApiKey . '&v=weekly&libraries=marker', null, '1.0', true );
		wp_enqueue_script( 'main-university-js', get_theme_file_uri( '/build/index.js' ), [ 'jquery' ], '1.0', true );
		wp_enqueue_style( 'custom-google-fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i' );
		wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
		wp_enqueue_style( 'university_main_styles', get_theme_file_uri( '/build/style-index.css' ) );
		wp_enqueue_style( 'university_extra_styles', get_theme_file_uri( '/build/index.css' ) );

		wp_localize_script( 'main-university-js', 'universityData', [
			'root_url' => get_site_url(),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
		] );
	}

	public static function ourLoginCSS(): void {
		// Reuse styles from themeEnqueues to avoid duplication
		self::themeEnqueues();
	}

	private static function load_dependencies(): void {
		require get_theme_file_path( '/inc/like-route.php' );
		require get_theme_file_path( '/inc/search-route.php' );
		require get_theme_file_path( '/inc/gtm.php' );
	}

	public static function campusMapKey( $api ): array {
		$api['key'] = self::$googleMapApiKey;

		return $api;
	}

	public static function our_new_blocks(): void {

		wp_localize_script( 'wp-editor', 'ourThemeData', [ 'themePath' => get_stylesheet_directory_uri() ] );

		$ourBlocks = [
			'pagebanner',
			"genericheading",
			"genericbutton",
			"slide",
			"slideshow",
			"page",
			"blogindex",
			"programarchive",
			"archivecampus",
			"archive-event",
			"search",
			"searchresults",
			"singlecampus",
			"singleevent",
			"singleprofessor",
			"singleprogram",
			"pastevents",
			"mynotes",
			"singlepost",
			"archive",
			"banner",
			"footer",
			"header",
			"eventsandblogs"
		];

		foreach ( $ourBlocks as $ourBlock ) {
			$blockPath = get_template_directory() . "/build/" . $ourBlock;
			register_block_type_from_metadata( $blockPath );
		}
	}
}