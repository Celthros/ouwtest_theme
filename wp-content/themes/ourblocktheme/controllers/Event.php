<?php

namespace Ourblocktheme\controllers;

class Event {

	public function __construct() {
		add_action( 'init', [ self::class, 'registerPostType' ] );
	}

	public static function registerPostType(): void {
		// Event(s) Post Type
		register_post_type( 'event', array(
			'labels'        => array(
				'name'          => __( 'Events', 'ourblocktheme' ),
				'singular_name' => __( 'Event', 'ourblocktheme' ),
				'add_new_item'  => __( 'Add New Event', 'ourblocktheme' ),
				'edit_item'     => __( 'Edit Event', 'ourblocktheme' ),
				'view_item'     => __( 'View Event', 'ourblocktheme' ),
				'view_items'    => __( 'View Events', 'ourblocktheme' ),
				"all_items"     => __( 'All Events', 'ourblocktheme' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-calendar',
			'menu_position' => 8,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'rewrite'       => array( 'slug' => 'events' ),
			'show_in_rest'  => true,

		) );
	}
}
