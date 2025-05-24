<?php

namespace Ourblocktheme\controllers;

class Event {

	/**
	 * @var Event
	 */
	private static ?Event $instance = null;

	public function __construct() {
		// Constructor logic here
		//echo 'Events instantiated!';
	}

	public static function getPastEvents() {
		$today      = date( 'Ymd' );
		$pastEvents = new WP_Query( array(
			'paged'      => get_query_var( 'paged', 1 ),
			'post_type'  => 'event',
			'meta_key'   => 'event_date',
			'orderby'    => 'meta_value_num',
			'order'      => 'ASC',
			'meta_query' => array(
				array(
					'key'     => 'event_date',
					'compare' => '<',
					'value'   => $today,
					'type'    => 'numeric'
				)
			)
		) );

		return $pastEvents;
	}
}
