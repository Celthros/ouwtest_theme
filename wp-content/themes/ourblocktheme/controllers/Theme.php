<?php

namespace Ourblocktheme\controllers;

class Theme {

	public function __construct() {
		add_action( 'after_setup_theme', [ self::class, 'add_Theme_Support' ] );
		add_filter( 'login_headerurl', [ self::class, 'ourHeaderUrl' ] );
		add_filter( 'login_headertitle', [ self::class, 'ourLoginTitle' ] );
		add_action( 'rest_api_init', [ self::class, 'university_custom_rest' ] );
		add_action( 'pre_get_posts', [ self::class, 'university_adjust_queries' ] );
	}

	public static function add_Theme_Support(): void {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_image_size( 'professorLandscape', 400, 260, true );
		add_image_size( 'professorPortrait', 480, 650, true );
		add_image_size( 'pageBanner', 1500, 350, true );
		add_theme_support( 'editor-styles' );
		add_editor_style( array(
			'https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i',
			'build/style-index.css',
			'build/index.css',
		) );
	}

	// Customize Login Screen
	public static function ourHeaderUrl(): string {
		return esc_url( site_url( '/' ) );
	}

	public static function ourLoginTitle(): null|string {
		return get_bloginfo( 'name' );
	}

	public static function university_custom_rest(): void {
		register_rest_field( 'post', 'authorName', array(
			'get_callback' => function () {
				return get_the_author();
			},
		) );

		register_rest_field( 'note', 'userNoteCount', array(
			'get_callback' => function () {
				return count_user_posts( get_current_user_id(), 'note' );
			},
		) );
	}

	public static function pageBanner( $args = null ): void {

		if ( ! isset( $args['title'] ) ) {
			$args['title'] = get_the_title() ?? get_bloginfo( 'name' );
		}

		if ( ! isset( $args['subtitle'] ) ) {
			$args['subtitle'] = get_field( 'page_banner_subtitle' ) ?? get_bloginfo( 'description' ) ?? 'Welcome to our university';
		}

		if ( ! isset( $args['photo'] ) ) {
			$args['photo'] = get_theme_file_uri( '/images/ocean.jpg' ) ?? '';
			if ( get_field( 'page_banner_background_image' ) && ! is_archive() && ! is_home() ) {
				$args['photo'] = get_field( 'page_banner_background_image' )['sizes']['pageBanner'] ?? '';
			}
		}

		?>
        <div class="page-banner">
            <div class="page-banner__bg-image" style="background-image: url(<?php echo $args['photo']; ?>);"></div>
            <div class="page-banner__content container container--narrow">
                <h1 class="page-banner__title"><?php echo $args['title'] ?></h1>
                <div class="page-banner__intro">
                    <p><?php echo $args['subtitle']; ?></p>
                </div>
            </div>
        </div>
	<?php }

	public static function university_adjust_queries( $query ): void {

		if ( ! is_admin() && is_post_type_archive( 'campus' ) && $query->is_main_query() ) {
			$query->set( 'posts_per_page', - 1 );
		}

		if ( ! is_admin() && is_post_type_archive( 'program' ) && $query->is_main_query() ) {
			$query->set( 'orderby', 'title' );
			$query->set( 'order', 'ASC' );
			$query->set( 'posts_per_page', - 1 );
		}

		if ( ! is_admin() && is_post_type_archive( 'event' ) && $query->is_main_query() ) {
			$today = date( 'Ymd' );
			$query->set( 'meta_key', 'event_date' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'ASC' );
			$query->set( 'meta_query', array(
				array(
					'key'     => 'event_date',
					'compare' => '>=',
					'value'   => $today,
					'type'    => 'numeric',
				),
			) );
		}
	}



}
