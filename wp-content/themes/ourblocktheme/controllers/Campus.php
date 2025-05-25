<?php

namespace Ourblocktheme\controllers;

class Campus {

	public function __construct() {
		add_action( 'init', [ self::class, 'registerPostType' ] );
	}

	public static function registerPostType(): void {
		// Campus(s) Post Type
		register_post_type( 'campus', array(
			'labels'        => array(
				'name'          => __( 'Campuses', 'ourblocktheme' ),
				'singular_name' => __( 'Campus', 'ourblocktheme' ),
				'add_new_item'  => __( 'Add New Campus', 'ourblocktheme' ),
				'edit_item'     => __( 'Edit Campus', 'ourblocktheme' ),
				'view_item'     => __( 'View Campus', 'ourblocktheme' ),
				'view_items'    => __( 'View Campuses', 'ourblocktheme' ),
				"all_items"     => __( 'All Campuses', 'ourblocktheme' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-location-alt',
			'menu_position' => 7,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'rewrite'       => array( 'slug' => 'campuses' ),
			'show_in_rest'  => true,

		) );
	}
}
