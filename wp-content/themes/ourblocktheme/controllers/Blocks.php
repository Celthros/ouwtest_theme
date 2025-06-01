<?php

namespace Ourblocktheme\controllers;

class Blocks {

	public function __construct() {
		add_action( 'init', [ self::class, 'enqueueBlocks' ] );
		//add_action( 'init', [ self::class, 'autoLoadBlocks' ] );
	}

	public static function autoLoadBlocks(): void {
		$blockPath         = get_template_directory() . "/build/blocks/";
		$directoryIterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $blockPath ) );

		wp_localize_script( 'wp-editor', 'ourThemeData', [ 'themePath' => get_stylesheet_directory_uri() ] );

		foreach ( $directoryIterator as $file ) {
			if ( $file->isFile() && $file->getFilename() === 'block.json' ) {
				$blockDir = $file->getPath();
				var_dump( $blockDir );
				register_block_type_from_metadata( $blockDir );
			}
		}
	}

	public static function enqueueBlocks(): void {
		wp_localize_script( 'wp-editor', 'ourThemeData', [ 'themePath' => get_stylesheet_directory_uri() ] );

		$ourBlocks = [
			'pagebanner',
			"genericheading",
			"genericbutton",
			"slide",
			"slideshow",
			"page",
			"blogindex",
			"programarchive",
			"archivecampus",
			"archive-event",
			"search",
			"searchresults",
			"singlecampus",
			"singleevent",
			"singleprofessor",
			"singleprogram",
			"pastevents",
			"mynotes",
			"singlepost",
			"archive",
			"banner",
			"footer",
			"header",
			"eventsandblogs"
		];

		foreach ( $ourBlocks as $ourBlock ) {
			$blockPath = get_template_directory() . "/build/" . $ourBlock;
			register_block_type_from_metadata( $blockPath );
		}
	}
}
