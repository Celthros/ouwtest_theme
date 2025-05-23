<?php

namespace Ourblocktheme\controllers;

class User {

	public function __construct() {
		add_action( 'admin_init', [ self::class, 'redirectSubsToFrontend' ] );
		add_action( 'wp_loaded', [ self::class, 'noSubsAdminBar' ] );
	}

	/*
	 * Redirect subscriber accounts out of admin and onto homepage.
	 *
	 * @return void
	 */
	public static function redirectSubsToFrontend(): void {
		$ourCurrentUser = wp_get_current_user();

		if ( count( $ourCurrentUser->roles ) == 1 && $ourCurrentUser->roles[0] == 'subscriber' ) {
			wp_redirect( site_url( '/' ) );
			exit;
		}
	}


	/*
	 * Hide the admin bar for subscriber accounts.
	 *
	 * @return void
	 */
	public static function noSubsAdminBar(): void {
		$ourCurrentUser = wp_get_current_user();

		if ( count( $ourCurrentUser->roles ) == 1 && $ourCurrentUser->roles[0] == 'subscriber' ) {
			show_admin_bar( false );
		}
	}

}