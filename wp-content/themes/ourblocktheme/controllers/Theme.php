<?php

namespace Ourblocktheme\controllers;

class Theme {

	public function __construct() {
		echo 'Theme instantiated!';
		add_action( 'after_setup_theme', [ self::class, 'add_Theme_Support' ] );
		add_filter( 'login_headerurl', [ self::class, 'ourHeaderUrl' ] );
		add_filter( 'login_headertitle', [ self::class, 'ourLoginTitle' ] );
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

}
