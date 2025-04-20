<?php

function university_post_types(): void {

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
			'menu_position' => 7,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'rewrite'       => array( 'slug' => 'events' ),
			'show_in_rest'  => true,

		) );


	// Program(s) Post Type
	register_post_type( 'program', array(
			'labels'        => array(
				'name'          => __( 'Programs', 'ourblocktheme' ),
				'singular_name' => __( 'Program', 'ourblocktheme' ),
				'add_new_item'  => __( 'Add New Program', 'ourblocktheme' ),
				'edit_item'     => __( 'Edit Program', 'ourblocktheme' ),
				'view_item'     => __( 'View Program', 'ourblocktheme' ),
				'view_items'    => __( 'View Programs', 'ourblocktheme' ),
				"all_items"     => __( 'All Programs', 'ourblocktheme' ),


			),
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-awards',
			'menu_position' => 8,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'rewrite'       => array( 'slug' => 'programs' ),
			'show_in_rest'  => true,

		) );

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
			'menu_position' => 9,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'show_in_rest'  => true,

		) );

}

add_action( 'init', 'university_post_types' );
