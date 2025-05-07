<?php

namespace PhpOffice\PhpSpreadsheet\Calculation;

/**
 * @deprecated 1.18.0
 *
 * @codeCoverageIgnore
 */
class Web {
	/**
	 * WEBSERVICE.
	 *
	 * Returns data from a web service on the Internet or Intranet.
	 *
	 * Excel Function:
	 *        Webservice(url)
	 *
	 * @return string the output resulting from a call to the webservice
	 * @see Web\Service::webService()
	 *
	 * @deprecated 1.18.0
	 *      Use the webService() method in the Web\Service class instead
	 */
	public static function WEBSERVICE( string $url ) {
		return Web\Service::webService( $url );
	}
}
