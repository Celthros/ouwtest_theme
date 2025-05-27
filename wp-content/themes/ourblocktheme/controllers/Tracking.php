<?php

namespace Ourblocktheme\controllers;

class Tracking {

	public function __construct() {
		add_action( 'wp_head', [ self::class, 'insert_gtm_into_header' ] );
		add_action( 'wp_body_open', [ self::class, 'insert_gtm_into_body' ] );
	}

	/*
	 *  Google Tag Manager that goes in the <head> of your theme
	 *
	 */
	public static function insert_gtm_into_header(): void {
		?>
        <!-- Google Tag Manager -->
        <script>( function ( w , d , s , l , i ) {
				w[ l ] = w[ l ] || [];
				w[ l ].push ( {
					'gtm.start' :
						new Date ().getTime () , event : 'gtm.js'
				} );
				var f = d.getElementsByTagName ( s )[ 0 ] ,
					j = d.createElement ( s ) , dl = l != 'dataLayer' ? '&l=' + l : '';
				j.async = true;
				j.src =
					'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
				f.parentNode.insertBefore ( j , f );
			} ) ( window , document , 'script' , 'dataLayer' , 'GTM-WM5GFH7D' );</script>
        <!-- End Google Tag Manager -->
		<?php
	}


	/*
	 *  Google Tag Manager that goes in the <body> of your theme
	 *
	 */
	public static function insert_gtm_into_body(): void {
		?>
        <!-- Google Tag Manager (noscript) -->
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WM5GFH7D"
                    height="0" width="0" style="display:none;visibility:hidden"></iframe>
        </noscript>
        <!-- End Google Tag Manager (noscript) -->
		<?php
	}

}
