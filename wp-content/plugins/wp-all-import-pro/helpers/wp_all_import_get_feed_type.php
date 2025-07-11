<?php

if ( ! function_exists( 'wp_all_import_get_feed_type' ) ) {
	function wp_all_import_get_feed_type( $url ) {

		$type = wp_all_import_get_remote_file_name( $url );

		if ( $type !== false ) {

			return array(
				'Content-Type'     => $type,
				'Content-Encoding' => false,
			);

		}

		$header_context = stream_context_create( [
			'http' => [
				'timeout' => 10,
			],
		] );

		$headers = @get_headers( $url, 1, $header_context );

		if ( empty( $headers ) ) {
			$response = wp_remote_get( $url );
			$headers  = wp_remote_retrieve_headers( $response );
		}

		$extensions = array( 'gzip', 'gz', 'xml', 'csv', 'json', 'sql' );
		$type       = false;

		$contentType = ( ! empty( $headers['Content-Type'] ) ) ? $headers['Content-Type'] : false;
		if ( $contentType === false ) {
			$contentType = ( ! empty( $headers['content-type'] ) ) ? $headers['content-type'] : false;
		}

		if ( ! empty( $contentType ) ) {
			if ( is_array( $contentType ) ) {
				foreach ( $contentType as $key => $ct ) {
					foreach ( $extensions as $ext ) {
						if ( strpos( $ct, $ext ) !== false ) {
							$type = $ext;
							break( 2 );
						}
					}
				}
			} else {
				foreach ( $extensions as $ext ) {
					if ( strpos( $contentType, $ext ) !== false ) {
						$type = $ext;
						break;
					}
				}
			}
			if ( ! empty( $headers['Content-Disposition'] ) ) {
				foreach ( $extensions as $ext ) {
					if ( is_array( $headers['Content-Disposition'] ) ) {
						$headers['Content-Disposition'] = array_pop( $headers['Content-Disposition'] );
					}
					if ( strpos( $headers['Content-Disposition'], $ext ) !== false ) {
						$type = $ext;
						break;
					}
				}
			}
		}

		return array(
			'Content-Type'        => $type,
			'Content-Encoding'    => ( ! empty( $headers['Content-Encoding'] ) ) ? $headers['Content-Encoding'] : false,
			'Content-Disposition' => ( ! empty( $headers['Content-Disposition'] ) ) ? $headers['Content-Disposition'] : false,
		);
	}
}