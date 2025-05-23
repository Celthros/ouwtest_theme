<?php

namespace Ourblocktheme;

use function file_exists;
use function is_dir;
use function scandir;
use function str_replace;

class autoload {

	protected static string $root_namespace = 'Ourblocktheme';
	protected static string $base_directory = __DIR__;

	public static function init(): void {
		spl_autoload_register( [ __CLASS__, 'autoload' ] );
	}

	public static function autoload( $class ): void {
		$prefix = self::$root_namespace . '\\';
		$len    = strlen( $prefix );

		// Ensure the class belongs to the root namespace
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$file           = self::find_file( $relative_class );

		if ( $file && file_exists( $file ) ) {
			require $file;
		} else {
			error_log( "Autoload error: Class $class not found." );
		}
	}

	private static function find_file( string $relative_class ): ?string {
		$directories = self::get_all_directories( self::$base_directory );

		foreach ( $directories as $directory ) {
			$file = $directory . '/' . str_replace( '\\', '/', $relative_class ) . '.php';
			if ( file_exists( $file ) ) {
				return $file;
			}
		}

		return null;
	}

	private static function get_all_directories( string $base_dir ): array {
		$directories = [ $base_dir ];
		$items       = scandir( $base_dir );

		foreach ( $items as $item ) {
			if ( $item === '.' || $item === '..' ) {
				continue;
			}

			$path = $base_dir . '/' . $item;
			if ( is_dir( $path ) ) {
				$directories = array_merge( $directories, self::get_all_directories( $path ) );
			}
		}

		return $directories;
	}
}