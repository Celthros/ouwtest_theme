<?php

declare( strict_types=1 );

namespace Ourblocktheme\controllers;

class Theme {

	private const DEFAULT_BANNER_IMAGE = '/images/ocean.jpg';

	public function __construct() {
		add_filter( 'login_headerurl', [ self::class, 'ourHeaderUrl' ] );
		add_filter( 'login_title', [ self::class, 'ourLoginHeaderTitle' ] );
		add_filter( 'login_headertext', [ self::class, 'ourLoginTitle' ] );
		add_action( 'rest_api_init', [ self::class, 'university_custom_rest' ] );
		add_action( 'pre_get_posts', [ self::class, 'adjusted_queries' ] );
		add_action( 'init', [ self::class, 'addThemeSupport' ] );
	}

	/*
	 * Add Theme Support
	 *
	 * @return void
	 *
	 * Description:
	 *  - Title Tag
	 *  - Post Thumbnails
	 *  - Page Banner Image Size
	 *  - Editor Styles
	 *  - Editor Style
	 *
	 * can't use after_setup_theme because it's too early
	 * https://developer.wordpress.org/reference/hooks/after_setup_theme/
	 * will try to use init instead, for now
	 *
	 *  To do
	 *  - Add support for custom logo
	 *  - Add support for custom header
	 *  - Add support for custom background
	 *  - Add support for custom menu
	 *  Maybe return after_setup_theme support
	 *  - Add support for custom editor styles
	 *
	 */
	public static function addThemeSupport(): void {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_image_size( 'professorLandscape', 400, 260, true );
		add_image_size( 'professorPortrait', 480, 650, true );
		add_image_size( 'pageBanner', 1500, 350, true );
		add_theme_support( 'editor-styles' );
		add_editor_style( [
			'https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i',
			get_theme_file_uri( '/build/style-index.css' ),
			get_theme_file_uri( '/build/index.css' ),
		] );
	}

	public static function removeThemeSupport(): void {
	}

	/*
	 * Redirect subscriber accounts out of admin and onto homepage
	 */
	public static function ourHeaderUrl(): string {
		return esc_url( site_url( '/' ) );
	}

	/*
	 * Customize Login Header Title
	 */
	public static function ourLoginHeaderTitle(): ?string {
		$action = $_GET['action'] ?? '';
		$title  = match ( $action ) {
			'register' => 'Register for ' . get_bloginfo( 'name' ),
			'lostpassword' => 'Reset Password for ' . get_bloginfo( 'name' ),
			default => 'Login to ' . get_bloginfo( 'name' ),
		};

		return esc_html( $title );
	}

	/*
	 * Customize Login Screen aka covered by Logo
	 */
	public static function ourLoginTitle(): ?string {
		return esc_html( get_bloginfo( 'name' ) );
	}


	/*
	 * Force note posts to be private
	 */

	public static function university_custom_rest(): void {
		register_rest_field( 'post', 'authorName', [
			'get_callback' => fn() => esc_html( get_the_author() ),
		] );

		register_rest_field( 'note', 'userNoteCount', [
			'get_callback' => fn() => count_user_posts( get_current_user_id(), 'note' ),
		] );
	}

	/*
	 * Adjust queries for post types
	 *
	 * @param WP_Query $query
	 * @return void
	 *
	 * Description:
	 *  - Campus: All posts
	 *  - Program: All posts
	 *  - Event: All posts with event_date >= today
	 *
	 */
	public static function adjusted_queries( $query ): void {
		if ( ! is_admin() && $query->is_main_query() ) {
			if ( is_post_type_archive( 'campus' ) ) {
				$query->set( 'posts_per_page', - 1 );
			}

			if ( is_post_type_archive( 'program' ) ) {
				$query->set( 'orderby', 'title' );
				$query->set( 'order', 'ASC' );
				$query->set( 'posts_per_page', - 1 );
			}

			if ( is_post_type_archive( 'event' ) ) {
				$today = date( 'Ymd' );
				$query->set( 'meta_key', 'event_date' );
				$query->set( 'orderby', 'meta_value_num' );
				$query->set( 'order', 'ASC' );
				$query->set( 'meta_query', [
					[
						'key'     => 'event_date',
						'compare' => '>=',
						'value'   => $today,
						'type'    => 'numeric',
					],
				] );
			}
		}
	}

	/*
	 * Add query vars
	 *
	 * @param array $vars
	 * @return array
	 *
	 * Description: used to test if query vars are working. not used for now but wanted to keep function
	 *  - grassColor
	 *  - skyColor
	 *
	 */
	public static function add_query_vars( $vars ): array {
		$vars[] = 'skyColor';
		$vars[] = 'grassColor';

		return $vars;
	}
}