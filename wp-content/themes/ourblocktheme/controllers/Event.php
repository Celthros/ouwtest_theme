<?php

namespace Ourblocktheme\controllers;

class Event {

	/**
	 * @var Event
	 */
	private static ?Event $instance = null;

	public function __construct() {
		// Constructor logic here
		echo 'Events instantiated!';
	}



	/**
	 * @return Event
	 */
	public static function reference(): Event {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
