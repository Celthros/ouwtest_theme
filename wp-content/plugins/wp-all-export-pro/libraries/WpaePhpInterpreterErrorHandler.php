<?php

class WpaePhpInterpreterErrorHandler {
	public function handle() {

		$error = $this->getLastError();

		if ( isset( $error['file'] ) ) {
			if ( $error && strpos( $error['file'], 'uploads/wpallexport/functions.php' ) !== false ) {
				$wp_uploads = $this->getUploadsDir();
				$functions = $wp_uploads['basedir'] . DIRECTORY_SEPARATOR . WP_ALL_EXPORT_UPLOADS_BASE_DIRECTORY . DIRECTORY_SEPARATOR . 'functions.php';
				$functions = apply_filters( 'wp_all_export_functions_file_path', $functions );
				$functions = 'in ' . $functions . ':' . $error['line'];
				$error['message'] = str_replace( $functions, '', $error['message'] );
				$error['message'] = str_replace( "\\n", '', $error['message'] );
				$errorParts = explode( 'Stack trace', $error['message'] );
				$error['message'] = $errorParts[0];
				$error['message'] .= ' on line ' . $error['line'];
				$error['message'] = str_replace( "\n", '', $error['message'] );
				$error['message'] = str_replace( "Uncaught Error:", '', $error['message'] );
				$error['message'] = 'PHP Error: ' . $error['message'];
				$error['message'] = str_replace( '  ', ' ', $error['message'] );
				echo "[[ERROR]]";
				if ( $error['message'] == '' ) {
					$error['message'] = __( 'An unknown error occured', 'wp_all_import_plugin' );
				}
				$this->terminate( json_encode( array(
					'error' => '<span class="error">' . $error['message'] . ' of the Functions Editor' . '</span>',
					'line'  => $error['line'],
					'title' => __( 'PHP Error', 'wp_all_import_plugin' ),
				) ) );
			} else if ( $error && strpos( $error['file'], 'XMLWriter.php' ) !== false ) {
				if ( strpos( $error['message'], 'syntax error, unexpected' ) !== false ) {
					echo "[[ERROR]]";
					$this->terminate( json_encode( array(
						'error' => __( 'You probably forgot to close a quote', 'wp_all_import_plugin' ),
						'title' => __( 'PHP Error', 'wp_all_import_plugin' ),
					) ) );
				}
			}
		}
	}

	/**
	 * @return array
	 */
	protected function getLastError() {
		return error_get_last();
	}

	/**
	 * @return mixed
	 */
	protected function getUploadsDir() {
		return wp_upload_dir();
	}

	/**
	 * Hack to be able to test the class in isolation
	 *
	 * @param $message
	 */
	protected function terminate( $message ) {
		exit( $message );
	}
}