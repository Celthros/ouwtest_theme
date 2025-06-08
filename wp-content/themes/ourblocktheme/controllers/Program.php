<?php

namespace Ourblocktheme\controllers;

class Program {

	public function __construct() {
		add_action( 'init', [ self::class, 'registerPostType' ] );
	}

	public static function registerPostType(): void {
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
			'menu_position' => 9,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'rewrite'       => array( 'slug' => 'programs' ),
			'show_in_rest'  => true,

		) );
	}

	public static function is_Related_txt(): string {
		$relatedPrograms = get_field( 'related_programs' );
		$programText     = 'Program';

		if ( $relatedPrograms ) {
			$count = count( $relatedPrograms );

			if ( $count > 1 ) {
				$programText = $programText . 's';
			}
		}

		return $programText;
	}

}
