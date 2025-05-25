<?php

namespace Ourblocktheme\controllers;

class Professor {

	public function __construct() {
		add_action( 'init', [ self::class, 'registerPostType' ] );
	}

	public static function registerPostType(): void {
		// Professor Post Type
		register_post_type( 'professor', array(
			'labels'        => array(
				'name'          => __( 'Professors', 'ourblocktheme' ),
				'singular_name' => __( 'Professor', 'ourblocktheme' ),
				'add_new_item'  => __( 'Add New Professor', 'ourblocktheme' ),
				'edit_item'     => __( 'Edit Professor', 'ourblocktheme' ),
				'view_item'     => __( 'View Professor', 'ourblocktheme' ),
				'view_items'    => __( 'View Professors', 'ourblocktheme' ),
				"all_items"     => __( 'All Professors', 'ourblocktheme' ),


			),
			'public'        => true,
			'has_archive'   => false,
			'menu_icon'     => 'dashicons-welcome-learn-more',
			'menu_position' => 10,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'show_in_rest'  => true,

		) );
	}
}
