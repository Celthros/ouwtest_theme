<?php
/**
 * Get correct import URL for Dropbox and Google share URLs
 *
 *
 * @param $link
 *   The share URL for your Dropbox or Google Drive/Spreadsheets file.
 * @param $format
 *     The type of file, if it's a Spreadsheet file. Acceptable examples: 'csv', 'xls'. Defaults to 'csv'.
 *
 * @return string
 *     The direct download URL.
 */
if ( ! function_exists( 'wp_all_import_sanitize_url' ) ) {

	function wp_all_import_sanitize_url( $link, $format = 'csv' ) {
		$link  = str_replace( " ", "%20", $link );
		$parse = parse_url( $link );
		preg_match( '/(?<=.com\/).*?(?=\/d)/', $link, $match );
		// Check for 'spreadsheets' or 'file' from Google URL.
		if ( ! empty( $match[0] ) ) {
			// The type is either 'file' or 'spreadsheets' typically.
			$type = $match[0];
		}
		$domain = isset( $parse['host'] ) ? $parse['host'] : '';
		if ( preg_match( '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $match ) ) {
			// Set the domain - i.e. google.com
			$domain = $match['domain'];
		}
		if ( ! empty( $domain ) ) {
			switch ( $domain ) {
				case 'dropbox.com':
					if ( substr( $link, - 4 ) == 'dl=0' ) {
						return str_replace( 'dl=0', 'dl=1', $link );
					}
					if ( strpos( $link, '?' ) === false ) {
						return $link . '?dl=1';
					}
					if ( strpos( $link, 'dl=1' ) === false ) {
						return $link . '&dl=1';
					}
					break;
				case 'google.com':
					if ( ! empty( $type ) ) {
						switch ( $type ) {
							case 'file':
								$pattern = '/(?<=\/file\/d\/).*?(?=\/edit)/';
								preg_match( $pattern, $link, $match );
								if ( ! empty( $match[0] ) ) {
									$file_id = $match[0];

									return 'https://drive.google.com/uc?export=download&id=' . $file_id;
								}
								break;
							case 'spreadsheets':
								$pattern = '/(?<=\/spreadsheets\/d\/).*?(?=\/edit)/';
								preg_match( $pattern, $link, $match );
								if ( ! empty( $match[0] ) ) {
									$file_id = $match[0];

									return 'https://docs.google.com/spreadsheets/d/' . $file_id . '/export?format=' . $format;
								}
								break;
							default:
								return $link;
								break;
						}
					}
				default:
					return $link;
					break;
			}
		}

		return $link;
	}
}