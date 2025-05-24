<?php

namespace Ourblocktheme;

use function file_exists;
use function str_replace;
use DirectoryIterator;

function autoload( string $class ): void {
	$root_namespace = 'Ourblocktheme\\controllers';
	$base_directory = __DIR__ . '\controllers';

	$prefix = $root_namespace . '\\';
	$len    = strlen( $prefix );

	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file           = $base_directory . '\\' . str_replace( '\\', '\\', $relative_class ) . '.php';

	error_log( "Attempting to load class: $class" );
	error_log( "Expected file path: $file" );

	if ( file_exists( $file ) ) {
		require $file;
		error_log( "Class $class successfully loaded." );
	} else {
		error_log( "Class $class not found. File $file does not exist." );
	}
}

function auto_instantiate_controllers(): void {
	$base_directory = __DIR__ . '/controllers';
	$namespace      = 'Ourblocktheme\\controllers';

	foreach ( new DirectoryIterator( $base_directory ) as $file ) {
		if ( $file->isFile() && $file->getExtension() === 'php' ) {
			$class_name = $namespace . '\\' . $file->getBasename( '.php' );

			if ( class_exists( $class_name ) ) {
				new $class_name();
			} else {
				error_log( "Class $class_name not found." );
			}
		}
	}
}

// Register the autoloader
spl_autoload_register( __NAMESPACE__ . '\\autoload' );

// Run the auto-instantiation after autoload is registered
add_action( 'after_setup_theme', __NAMESPACE__ . '\\auto_instantiate_controllers' );