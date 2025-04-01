<?php

function university_post_types(): void {
	register_post_type( 'event',
		array(
			'labels'        => array(
				'name'          => __( 'Events', 'ourblocktheme' ),
				'singular_name' => __( 'Event', 'ourblocktheme' ),
				'add_new_item'  => __( 'Add New Event', 'ourblocktheme' ),
				'edit_item'     => __( 'Edit Event', 'ourblocktheme' ),
				"all_items"     => __( 'All Events', 'ourblocktheme' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-calendar',
			'menu_position' => 7,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'rewrite'       => array( 'slug' => 'events' ),
			'show_in_rest'  => true,

		)
	);
}

add_action( 'init', 'university_post_types' );
