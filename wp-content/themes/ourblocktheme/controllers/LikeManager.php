<?php

namespace Ourblocktheme\controllers;

use WP_Query;
use WP_Error;

class LikeManager {

	function __construct() {
		add_action( 'rest_api_init', [ self::class, 'universityLikeRoutes' ] );;
	}

	public static function universityLikeRoutes(): void {
		register_rest_route( 'university/v1', 'manageLike', array(
			'methods'  => 'POST',
			'callback' => [ self::class, 'createLike' ]
		) );

		register_rest_route( 'university/v1', 'manageLike', array(
			'methods'  => 'DELETE',
			'callback' => [ self::class, 'deleteLike' ]
		) );
	}

	public static function createLike( $data ): int|null|WP_Error {
		if ( is_user_logged_in() ) {
			$professor = sanitize_text_field( $data['professorId'] );

			$existQuery = new WP_Query( array(
				'author'     => get_current_user_id(),
				'post_type'  => 'like',
				'meta_query' => array(
					array(
						'key'     => 'liked_professor_id',
						'compare' => '=',
						'value'   => $professor
					)
				)
			) );

			if ( $existQuery->found_posts == 0 and get_post_type( $professor ) == 'professor' ) {
				return wp_insert_post( array(
					'post_type'   => 'like',
					'post_status' => 'publish',
					'post_title'  => '2nd PHP Test',
					'meta_input'  => array(
						'liked_professor_id' => $professor
					)
				) );
			} else {
				die( "Invalid professor id" );
			}


		} else {
			die( "Only logged in users can create a like." );
		}


	}

	public static function deleteLike( $data ): string|null|WP_Error {
		$likeId = sanitize_text_field( $data['like'] );
		if ( get_current_user_id() == get_post_field( 'post_author', $likeId ) and get_post_type( $likeId ) == 'like' ) {
			wp_delete_post( $likeId, true );

			return 'Congrats, like deleted.';
		} else {
			die( "You do not have permission to delete that." );
		}
	}

}