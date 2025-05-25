<?php

namespace Ourblocktheme\controllers;

class Notes {

	public function __construct() {
		add_filter( 'wp_insert_post_data', [ self::class, 'makeNotePrivate' ], 10, 2 );
	}

	public static function registerPostType(): void{}

	/*
	 * Make note private if user has reached their note limit.
	 * Force note posts to be private
	 *
	 * @param array $data
	 * @param array $postarr
	 * @return array
	 */
	public static function makeNotePrivate( $data, $postarr ): array {
		if ( $data['post_type'] === 'note' ) {
			if ( count_user_posts( get_current_user_id(), 'note' ) > 4 && ! $postarr['ID'] ) {
				die( "You have reached your note limit." );
			}

			$data['post_content'] = sanitize_textarea_field( $data['post_content'] );
			$data['post_title']   = sanitize_text_field( $data['post_title'] );
		}

		if ( $data['post_type'] == 'note' && $data['post_status'] != 'trash' ) {
			$data['post_status'] = "private";
		}

		return $data;
	}
}
