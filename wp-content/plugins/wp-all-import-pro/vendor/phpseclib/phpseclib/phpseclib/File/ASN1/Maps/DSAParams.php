<?php

/**
 * DSAParams
 *
 * PHP version 5
 *
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2016 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

namespace phpseclib3\File\ASN1\Maps;

use phpseclib3\File\ASN1;

/**
 * DSAParams
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 */
abstract class DSAParams {
	const MAP = [
		'type'     => ASN1::TYPE_SEQUENCE,
		'children' => [
			'p' => [ 'type' => ASN1::TYPE_INTEGER ],
			'q' => [ 'type' => ASN1::TYPE_INTEGER ],
			'g' => [ 'type' => ASN1::TYPE_INTEGER ],
		],
	];
}
