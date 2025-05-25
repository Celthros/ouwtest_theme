<?php

add_action( 'rest_api_init', 'universityRegisterSearch' );

function universityRegisterSearch(): void {
	register_rest_route( 'university/v1', 'search', array(
		'methods'  => WP_REST_SERVER::READABLE,
		'callback' => 'universitySearchResults'
	) );
}

function universitySearchResults( $data ): WP_Error|array {
	$term = sanitize_text_field( $data['term'] );
	if ( empty( $term ) ) {
		return new WP_Error( 'no_term', 'Search term is required', array( 'status' => 400 ) );
	}

	$mainQuery = new WP_Query( array(
		'post_type' => array( 'post', 'page', 'professor', 'program', 'campus', 'event' ),
		's'         => $term
	) );

	$results = array(
		'generalInfo' => array(),
		'professors'  => array(),
		'programs'    => array(),
		'events'      => array(),
		'campuses'    => array()
	);

	while ( $mainQuery->have_posts() ) {
		$mainQuery->the_post();
		$postType = get_post_type();

		switch ( $postType ) {
			case 'post':
			case 'page':
				$results['generalInfo'][] = array(
					'title'      => get_the_title(),
					'permalink'  => get_the_permalink(),
					'postType'   => $postType,
					'authorName' => get_the_author()
				);
				break;

			case 'professor':
				$results['professors'][] = array(
					'title'     => get_the_title(),
					'permalink' => get_the_permalink(),
					'image'     => get_the_post_thumbnail_url( 0, 'professorLandscape' )
				);
				break;

			case 'program':
				$relatedCampuses = get_field( 'related_campus' );
				if ( $relatedCampuses ) {
					foreach ( $relatedCampuses as $campus ) {
						$results['campuses'][] = array(
							'title'     => get_the_title( $campus ),
							'permalink' => get_the_permalink( $campus )
						);
					}
				}
				$results['programs'][] = array(
					'title'     => get_the_title(),
					'permalink' => get_the_permalink(),
					'id'        => get_the_id()
				);
				break;

			case 'campus':
				$results['campuses'][] = array(
					'title'     => get_the_title(),
					'permalink' => get_the_permalink()
				);
				break;

			case 'event':
				$results['events'][] = formatEventResult();
				break;
		}
	}

	if ( $results['programs'] ) {
		$results = enrichResultsWithProgramRelationships( $results );
	}

	wp_reset_postdata();
	return $results;
}

function formatEventResult(): array {
	$eventDate   = new DateTime( get_field( 'event_date' ) );
	$description = has_excerpt() ? get_the_excerpt() : wp_trim_words( get_the_content(), 18 );

	return array(
		'title'       => get_the_title(),
		'permalink'   => get_the_permalink(),
		'month'       => $eventDate->format( 'M' ),
		'day'         => $eventDate->format( 'd' ),
		'description' => $description
	);
}

function enrichResultsWithProgramRelationships( $results ): array {
	$programsMetaQuery = array( 'relation' => 'OR' );

	foreach ( $results['programs'] as $item ) {
		$programsMetaQuery[] = array(
			'key'     => 'related_programs',
			'compare' => 'LIKE',
			'value'   => '"' . $item['id'] . '"'
		);
	}

	$programRelationshipQuery = new WP_Query( array(
		'post_type'  => array( 'professor', 'event' ),
		'meta_query' => $programsMetaQuery
	) );

	while ( $programRelationshipQuery->have_posts() ) {
		$programRelationshipQuery->the_post();
		$postType = get_post_type();

		if ( $postType == 'event' ) {
			$results['events'][] = formatEventResult();
		}

		if ( $postType == 'professor' ) {
			$results['professors'][] = array(
				'title'     => get_the_title(),
				'permalink' => get_the_permalink(),
				'image'     => get_the_post_thumbnail_url( 0, 'professorLandscape' )
			);
		}
	}

	wp_reset_postdata();

	$results['professors'] = array_values( array_unique( $results['professors'], SORT_REGULAR ) );
	$results['events']     = array_values( array_unique( $results['events'], SORT_REGULAR ) );

	return $results;
}