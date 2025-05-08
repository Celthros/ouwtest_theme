<?php

namespace Ourblocktheme;

use function file_exists;
use function str_replace;

class Autoload {

	protected static string $root_namespace = 'Ourblocktheme';
	protected static array $directories = array( "controllers", "blocks" );

	public static function init(): void {
		spl_autoload_register( [ __CLASS__, 'autoload' ] );
	}

	public static function autoload( $class ): void {
		$prefix = self::$root_namespace . '\\';
		$len    = strlen( $prefix );

		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );

		echo "Class: $class\n";
		foreach ( self::$directories as $directory ) {
			$base_dir = __DIR__ . '/' . $directory . '/';
			$file     = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

			// Debugging statements
			var_dump( "Checking directory: $directory" );
			error_log( "Looking for file: $file" );

			if ( file_exists( $file ) ) {
				require $file;
				error_log( "Loaded class: $class from file: $file" );

				return;
			}
		}

		error_log( "Class $class not found in any directory" );
	}
}
