<?php

/**
 * Pure-PHP implementation of RC2.
 *
 * Uses mcrypt, if available, and an internal implementation, otherwise.
 *
 * PHP version 5
 *
 * Useful resources are as follows:
 *
 *  - {@link http://tools.ietf.org/html/rfc2268}
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'vendor/autoload.php';
 *
 *    $rc2 = new \phpseclib3\Crypt\RC2('ctr');
 *
 *    $rc2->setKey('abcdefgh');
 *
 *    $plaintext = str_repeat('a', 1024);
 *
 *    echo $rc2->decrypt($rc2->encrypt($plaintext));
 * ?>
 * </code>
 *
 * @author   Patrick Monnerat <pm@datasphere.ch>
 * @license  http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link     http://phpseclib.sourceforge.net
 */

namespace phpseclib3\Crypt;

use phpseclib3\Crypt\Common\BlockCipher;
use phpseclib3\Exception\BadModeException;

/**
 * Pure-PHP implementation of RC2.
 *
 */
class RC2 extends BlockCipher {
	/**
	 * Block Length of the cipher
	 *
	 * @see \phpseclib3\Crypt\Common\SymmetricKey::block_size
	 * @var int
	 */
	protected $block_size = 8;

	/**
	 * The Key
	 *
	 * @see \phpseclib3\Crypt\Common\SymmetricKey::key
	 * @see self::setKey()
	 * @var string
	 */
	protected $key;

	/**
	 * The Original (unpadded) Key
	 *
	 * @see \phpseclib3\Crypt\Common\SymmetricKey::key
	 * @see self::setKey()
	 * @see self::encrypt()
	 * @see self::decrypt()
	 * @var string
	 */
	private $orig_key;

	/**
	 * Key Length (in bytes)
	 *
	 * @see \phpseclib3\Crypt\RC2::setKeyLength()
	 * @var int
	 */
	protected $key_length = 16; // = 128 bits

	/**
	 * The mcrypt specific name of the cipher
	 *
	 * @see \phpseclib3\Crypt\Common\SymmetricKey::cipher_name_mcrypt
	 * @var string
	 */
	protected $cipher_name_mcrypt = 'rc2';

	/**
	 * Optimizing value while CFB-encrypting
	 *
	 * @see \phpseclib3\Crypt\Common\SymmetricKey::cfb_init_len
	 * @var int
	 */
	protected $cfb_init_len = 500;

	/**
	 * The key length in bits.
	 *
	 * {@internal Should be in range [1..1024].}
	 *
	 * {@internal Changing this value after setting the key has no effect.}
	 *
	 * @see self::setKeyLength()
	 * @see self::setKey()
	 * @var int
	 */
	private $default_key_length = 1024;

	/**
	 * The key length in bits.
	 *
	 * {@internal Should be in range [1..1024].}
	 *
	 * @see self::isValidEnine()
	 * @see self::setKey()
	 * @var int
	 */
	private $current_key_length;

	/**
	 * The Key Schedule
	 *
	 * @see self::setupKey()
	 * @var array
	 */
	private $keys;

	/**
	 * Key expansion randomization table.
	 * Twice the same 256-value sequence to save a modulus in key expansion.
	 *
	 * @see self::setKey()
	 * @var array
	 */
	private static $pitable = [
		0xD9,
		0x78,
		0xF9,
		0xC4,
		0x19,
		0xDD,
		0xB5,
		0xED,
		0x28,
		0xE9,
		0xFD,
		0x79,
		0x4A,
		0xA0,
		0xD8,
		0x9D,
		0xC6,
		0x7E,
		0x37,
		0x83,
		0x2B,
		0x76,
		0x53,
		0x8E,
		0x62,
		0x4C,
		0x64,
		0x88,
		0x44,
		0x8B,
		0xFB,
		0xA2,
		0x17,
		0x9A,
		0x59,
		0xF5,
		0x87,
		0xB3,
		0x4F,
		0x13,
		0x61,
		0x45,
		0x6D,
		0x8D,
		0x09,
		0x81,
		0x7D,
		0x32,
		0xBD,
		0x8F,
		0x40,
		0xEB,
		0x86,
		0xB7,
		0x7B,
		0x0B,
		0xF0,
		0x95,
		0x21,
		0x22,
		0x5C,
		0x6B,
		0x4E,
		0x82,
		0x54,
		0xD6,
		0x65,
		0x93,
		0xCE,
		0x60,
		0xB2,
		0x1C,
		0x73,
		0x56,
		0xC0,
		0x14,
		0xA7,
		0x8C,
		0xF1,
		0xDC,
		0x12,
		0x75,
		0xCA,
		0x1F,
		0x3B,
		0xBE,
		0xE4,
		0xD1,
		0x42,
		0x3D,
		0xD4,
		0x30,
		0xA3,
		0x3C,
		0xB6,
		0x26,
		0x6F,
		0xBF,
		0x0E,
		0xDA,
		0x46,
		0x69,
		0x07,
		0x57,
		0x27,
		0xF2,
		0x1D,
		0x9B,
		0xBC,
		0x94,
		0x43,
		0x03,
		0xF8,
		0x11,
		0xC7,
		0xF6,
		0x90,
		0xEF,
		0x3E,
		0xE7,
		0x06,
		0xC3,
		0xD5,
		0x2F,
		0xC8,
		0x66,
		0x1E,
		0xD7,
		0x08,
		0xE8,
		0xEA,
		0xDE,
		0x80,
		0x52,
		0xEE,
		0xF7,
		0x84,
		0xAA,
		0x72,
		0xAC,
		0x35,
		0x4D,
		0x6A,
		0x2A,
		0x96,
		0x1A,
		0xD2,
		0x71,
		0x5A,
		0x15,
		0x49,
		0x74,
		0x4B,
		0x9F,
		0xD0,
		0x5E,
		0x04,
		0x18,
		0xA4,
		0xEC,
		0xC2,
		0xE0,
		0x41,
		0x6E,
		0x0F,
		0x51,
		0xCB,
		0xCC,
		0x24,
		0x91,
		0xAF,
		0x50,
		0xA1,
		0xF4,
		0x70,
		0x39,
		0x99,
		0x7C,
		0x3A,
		0x85,
		0x23,
		0xB8,
		0xB4,
		0x7A,
		0xFC,
		0x02,
		0x36,
		0x5B,
		0x25,
		0x55,
		0x97,
		0x31,
		0x2D,
		0x5D,
		0xFA,
		0x98,
		0xE3,
		0x8A,
		0x92,
		0xAE,
		0x05,
		0xDF,
		0x29,
		0x10,
		0x67,
		0x6C,
		0xBA,
		0xC9,
		0xD3,
		0x00,
		0xE6,
		0xCF,
		0xE1,
		0x9E,
		0xA8,
		0x2C,
		0x63,
		0x16,
		0x01,
		0x3F,
		0x58,
		0xE2,
		0x89,
		0xA9,
		0x0D,
		0x38,
		0x34,
		0x1B,
		0xAB,
		0x33,
		0xFF,
		0xB0,
		0xBB,
		0x48,
		0x0C,
		0x5F,
		0xB9,
		0xB1,
		0xCD,
		0x2E,
		0xC5,
		0xF3,
		0xDB,
		0x47,
		0xE5,
		0xA5,
		0x9C,
		0x77,
		0x0A,
		0xA6,
		0x20,
		0x68,
		0xFE,
		0x7F,
		0xC1,
		0xAD,
		0xD9,
		0x78,
		0xF9,
		0xC4,
		0x19,
		0xDD,
		0xB5,
		0xED,
		0x28,
		0xE9,
		0xFD,
		0x79,
		0x4A,
		0xA0,
		0xD8,
		0x9D,
		0xC6,
		0x7E,
		0x37,
		0x83,
		0x2B,
		0x76,
		0x53,
		0x8E,
		0x62,
		0x4C,
		0x64,
		0x88,
		0x44,
		0x8B,
		0xFB,
		0xA2,
		0x17,
		0x9A,
		0x59,
		0xF5,
		0x87,
		0xB3,
		0x4F,
		0x13,
		0x61,
		0x45,
		0x6D,
		0x8D,
		0x09,
		0x81,
		0x7D,
		0x32,
		0xBD,
		0x8F,
		0x40,
		0xEB,
		0x86,
		0xB7,
		0x7B,
		0x0B,
		0xF0,
		0x95,
		0x21,
		0x22,
		0x5C,
		0x6B,
		0x4E,
		0x82,
		0x54,
		0xD6,
		0x65,
		0x93,
		0xCE,
		0x60,
		0xB2,
		0x1C,
		0x73,
		0x56,
		0xC0,
		0x14,
		0xA7,
		0x8C,
		0xF1,
		0xDC,
		0x12,
		0x75,
		0xCA,
		0x1F,
		0x3B,
		0xBE,
		0xE4,
		0xD1,
		0x42,
		0x3D,
		0xD4,
		0x30,
		0xA3,
		0x3C,
		0xB6,
		0x26,
		0x6F,
		0xBF,
		0x0E,
		0xDA,
		0x46,
		0x69,
		0x07,
		0x57,
		0x27,
		0xF2,
		0x1D,
		0x9B,
		0xBC,
		0x94,
		0x43,
		0x03,
		0xF8,
		0x11,
		0xC7,
		0xF6,
		0x90,
		0xEF,
		0x3E,
		0xE7,
		0x06,
		0xC3,
		0xD5,
		0x2F,
		0xC8,
		0x66,
		0x1E,
		0xD7,
		0x08,
		0xE8,
		0xEA,
		0xDE,
		0x80,
		0x52,
		0xEE,
		0xF7,
		0x84,
		0xAA,
		0x72,
		0xAC,
		0x35,
		0x4D,
		0x6A,
		0x2A,
		0x96,
		0x1A,
		0xD2,
		0x71,
		0x5A,
		0x15,
		0x49,
		0x74,
		0x4B,
		0x9F,
		0xD0,
		0x5E,
		0x04,
		0x18,
		0xA4,
		0xEC,
		0xC2,
		0xE0,
		0x41,
		0x6E,
		0x0F,
		0x51,
		0xCB,
		0xCC,
		0x24,
		0x91,
		0xAF,
		0x50,
		0xA1,
		0xF4,
		0x70,
		0x39,
		0x99,
		0x7C,
		0x3A,
		0x85,
		0x23,
		0xB8,
		0xB4,
		0x7A,
		0xFC,
		0x02,
		0x36,
		0x5B,
		0x25,
		0x55,
		0x97,
		0x31,
		0x2D,
		0x5D,
		0xFA,
		0x98,
		0xE3,
		0x8A,
		0x92,
		0xAE,
		0x05,
		0xDF,
		0x29,
		0x10,
		0x67,
		0x6C,
		0xBA,
		0xC9,
		0xD3,
		0x00,
		0xE6,
		0xCF,
		0xE1,
		0x9E,
		0xA8,
		0x2C,
		0x63,
		0x16,
		0x01,
		0x3F,
		0x58,
		0xE2,
		0x89,
		0xA9,
		0x0D,
		0x38,
		0x34,
		0x1B,
		0xAB,
		0x33,
		0xFF,
		0xB0,
		0xBB,
		0x48,
		0x0C,
		0x5F,
		0xB9,
		0xB1,
		0xCD,
		0x2E,
		0xC5,
		0xF3,
		0xDB,
		0x47,
		0xE5,
		0xA5,
		0x9C,
		0x77,
		0x0A,
		0xA6,
		0x20,
		0x68,
		0xFE,
		0x7F,
		0xC1,
		0xAD,
	];

	/**
	 * Inverse key expansion randomization table.
	 *
	 * @see self::setKey()
	 * @var array
	 */
	private static $invpitable = [
		0xD1,
		0xDA,
		0xB9,
		0x6F,
		0x9C,
		0xC8,
		0x78,
		0x66,
		0x80,
		0x2C,
		0xF8,
		0x37,
		0xEA,
		0xE0,
		0x62,
		0xA4,
		0xCB,
		0x71,
		0x50,
		0x27,
		0x4B,
		0x95,
		0xD9,
		0x20,
		0x9D,
		0x04,
		0x91,
		0xE3,
		0x47,
		0x6A,
		0x7E,
		0x53,
		0xFA,
		0x3A,
		0x3B,
		0xB4,
		0xA8,
		0xBC,
		0x5F,
		0x68,
		0x08,
		0xCA,
		0x8F,
		0x14,
		0xD7,
		0xC0,
		0xEF,
		0x7B,
		0x5B,
		0xBF,
		0x2F,
		0xE5,
		0xE2,
		0x8C,
		0xBA,
		0x12,
		0xE1,
		0xAF,
		0xB2,
		0x54,
		0x5D,
		0x59,
		0x76,
		0xDB,
		0x32,
		0xA2,
		0x58,
		0x6E,
		0x1C,
		0x29,
		0x64,
		0xF3,
		0xE9,
		0x96,
		0x0C,
		0x98,
		0x19,
		0x8D,
		0x3E,
		0x26,
		0xAB,
		0xA5,
		0x85,
		0x16,
		0x40,
		0xBD,
		0x49,
		0x67,
		0xDC,
		0x22,
		0x94,
		0xBB,
		0x3C,
		0xC1,
		0x9B,
		0xEB,
		0x45,
		0x28,
		0x18,
		0xD8,
		0x1A,
		0x42,
		0x7D,
		0xCC,
		0xFB,
		0x65,
		0x8E,
		0x3D,
		0xCD,
		0x2A,
		0xA3,
		0x60,
		0xAE,
		0x93,
		0x8A,
		0x48,
		0x97,
		0x51,
		0x15,
		0xF7,
		0x01,
		0x0B,
		0xB7,
		0x36,
		0xB1,
		0x2E,
		0x11,
		0xFD,
		0x84,
		0x2D,
		0x3F,
		0x13,
		0x88,
		0xB3,
		0x34,
		0x24,
		0x1B,
		0xDE,
		0xC5,
		0x1D,
		0x4D,
		0x2B,
		0x17,
		0x31,
		0x74,
		0xA9,
		0xC6,
		0x43,
		0x6D,
		0x39,
		0x90,
		0xBE,
		0xC3,
		0xB0,
		0x21,
		0x6B,
		0xF6,
		0x0F,
		0xD5,
		0x99,
		0x0D,
		0xAC,
		0x1F,
		0x5C,
		0x9E,
		0xF5,
		0xF9,
		0x4C,
		0xD6,
		0xDF,
		0x89,
		0xE4,
		0x8B,
		0xFF,
		0xC7,
		0xAA,
		0xE7,
		0xED,
		0x46,
		0x25,
		0xB6,
		0x06,
		0x5E,
		0x35,
		0xB5,
		0xEC,
		0xCE,
		0xE8,
		0x6C,
		0x30,
		0x55,
		0x61,
		0x4A,
		0xFE,
		0xA0,
		0x79,
		0x03,
		0xF0,
		0x10,
		0x72,
		0x7C,
		0xCF,
		0x52,
		0xA6,
		0xA7,
		0xEE,
		0x44,
		0xD3,
		0x9A,
		0x57,
		0x92,
		0xD0,
		0x5A,
		0x7A,
		0x41,
		0x7F,
		0x0E,
		0x00,
		0x63,
		0xF2,
		0x4F,
		0x05,
		0x83,
		0xC9,
		0xA1,
		0xD4,
		0xDD,
		0xC4,
		0x56,
		0xF4,
		0xD2,
		0x77,
		0x81,
		0x09,
		0x82,
		0x33,
		0x9F,
		0x07,
		0x86,
		0x75,
		0x38,
		0x4E,
		0x69,
		0xF1,
		0xAD,
		0x23,
		0x73,
		0x87,
		0x70,
		0x02,
		0xC2,
		0x1E,
		0xB8,
		0x0A,
		0xFC,
		0xE6,
	];

	/**
	 * Default Constructor.
	 *
	 * @param string $mode
	 *
	 * @throws \InvalidArgumentException if an invalid / unsupported mode is provided
	 */
	public function __construct( $mode ) {
		parent::__construct( $mode );

		if ( $this->mode == self::MODE_STREAM ) {
			throw new BadModeException( 'Block ciphers cannot be ran in stream mode' );
		}
	}

	/**
	 * Test for engine validity
	 *
	 * This is mainly just a wrapper to set things up for \phpseclib3\Crypt\Common\SymmetricKey::isValidEngine()
	 *
	 * @param int $engine
	 *
	 * @return bool
	 * @see \phpseclib3\Crypt\Common\SymmetricKey::__construct()
	 */
	protected function isValidEngineHelper( $engine ) {
		switch ( $engine ) {
			case self::ENGINE_OPENSSL:
				if ( $this->current_key_length != 128 || strlen( $this->orig_key ) < 16 ) {
					return false;
				}
				// quoting https://www.openssl.org/news/openssl-3.0-notes.html, OpenSSL 3.0.1
				// "Moved all variations of the EVP ciphers CAST5, BF, IDEA, SEED, RC2, RC4, RC5, and DES to the legacy provider"
				// in theory openssl_get_cipher_methods() should catch this but, on GitHub Actions, at least, it does not
				if ( defined( 'OPENSSL_VERSION_TEXT' ) && version_compare( preg_replace( '#OpenSSL (\d+\.\d+\.\d+) .*#', '$1', OPENSSL_VERSION_TEXT ), '3.0.1', '>=' ) ) {
					return false;
				}
				$this->cipher_name_openssl_ecb = 'rc2-ecb';
				$this->cipher_name_openssl     = 'rc2-' . $this->openssl_translate_mode();
		}

		return parent::isValidEngineHelper( $engine );
	}

	/**
	 * Sets the key length.
	 *
	 * Valid key lengths are 8 to 1024.
	 * Calling this function after setting the key has no effect until the next
	 *  \phpseclib3\Crypt\RC2::setKey() call.
	 *
	 * @param int $length in bits
	 *
	 * @throws \LengthException if the key length isn't supported
	 */
	public function setKeyLength( $length ) {
		if ( $length < 8 || $length > 1024 ) {
			throw new \LengthException( 'Key size of ' . $length . ' bits is not supported by this algorithm. Only keys between 1 and 1024 bits, inclusive, are supported' );
		}

		$this->default_key_length  = $this->current_key_length = $length;
		$this->explicit_key_length = $length >> 3;
	}

	/**
	 * Returns the current key length
	 *
	 * @return int
	 */
	public function getKeyLength() {
		return $this->current_key_length;
	}

	/**
	 * Sets the key.
	 *
	 * Keys can be of any length. RC2, itself, uses 8 to 1024 bit keys (eg.
	 * strlen($key) <= 128), however, we only use the first 128 bytes if $key
	 * has more then 128 bytes in it, and set $key to a single null byte if
	 * it is empty.
	 *
	 * @param string $key
	 * @param int|boolean $t1 optional Effective key length in bits.
	 *
	 * @throws \LengthException if the key length isn't supported
	 * @see \phpseclib3\Crypt\Common\SymmetricKey::setKey()
	 */
	public function setKey( $key, $t1 = false ) {
		$this->orig_key = $key;

		if ( $t1 === false ) {
			$t1 = $this->default_key_length;
		}

		if ( $t1 < 1 || $t1 > 1024 ) {
			throw new \LengthException( 'Key size of ' . $length . ' bits is not supported by this algorithm. Only keys between 1 and 1024 bits, inclusive, are supported' );
		}

		$this->current_key_length = $t1;
		if ( strlen( $key ) < 1 || strlen( $key ) > 128 ) {
			throw new \LengthException( 'Key of size ' . strlen( $key ) . ' not supported by this algorithm. Only keys of sizes between 8 and 1024 bits, inclusive, are supported' );
		}

		$t = strlen( $key );

		// The mcrypt RC2 implementation only supports effective key length
		// of 1024 bits. It is however possible to handle effective key
		// lengths in range 1..1024 by expanding the key and applying
		// inverse pitable mapping to the first byte before submitting it
		// to mcrypt.

		// Key expansion.
		$l  = array_values( unpack( 'C*', $key ) );
		$t8 = ( $t1 + 7 ) >> 3;
		$tm = 0xFF >> ( 8 * $t8 - $t1 );

		// Expand key.
		$pitable = self::$pitable;
		for ( $i = $t; $i < 128; $i ++ ) {
			$l[ $i ] = $pitable[ $l[ $i - 1 ] + $l[ $i - $t ] ];
		}
		$i       = 128 - $t8;
		$l[ $i ] = $pitable[ $l[ $i ] & $tm ];
		while ( $i -- ) {
			$l[ $i ] = $pitable[ $l[ $i + 1 ] ^ $l[ $i + $t8 ] ];
		}

		// Prepare the key for mcrypt.
		$l[0] = self::$invpitable[ $l[0] ];
		array_unshift( $l, 'C*' );

		$this->key        = pack( ...$l );
		$this->key_length = strlen( $this->key );
		$this->changed    = $this->nonIVChanged = true;
		$this->setEngine();
	}

	/**
	 * Encrypts a message.
	 *
	 * Mostly a wrapper for \phpseclib3\Crypt\Common\SymmetricKey::encrypt, with some additional OpenSSL handling code
	 *
	 * @param string $plaintext
	 *
	 * @return string $ciphertext
	 * @see self::decrypt()
	 */
	public function encrypt( $plaintext ) {
		if ( $this->engine == self::ENGINE_OPENSSL ) {
			$temp      = $this->key;
			$this->key = $this->orig_key;
			$result    = parent::encrypt( $plaintext );
			$this->key = $temp;

			return $result;
		}

		return parent::encrypt( $plaintext );
	}

	/**
	 * Decrypts a message.
	 *
	 * Mostly a wrapper for \phpseclib3\Crypt\Common\SymmetricKey::decrypt, with some additional OpenSSL handling code
	 *
	 * @param string $ciphertext
	 *
	 * @return string $plaintext
	 * @see self::encrypt()
	 */
	public function decrypt( $ciphertext ) {
		if ( $this->engine == self::ENGINE_OPENSSL ) {
			$temp      = $this->key;
			$this->key = $this->orig_key;
			$result    = parent::decrypt( $ciphertext );
			$this->key = $temp;

			return $result;
		}

		return parent::decrypt( $ciphertext );
	}

	/**
	 * Encrypts a block
	 *
	 * @param string $in
	 *
	 * @return string
	 * @see \phpseclib3\Crypt\Common\SymmetricKey::encryptBlock()
	 * @see \phpseclib3\Crypt\Common\SymmetricKey::encrypt()
	 */
	protected function encryptBlock( $in ) {
		list( $r0, $r1, $r2, $r3 ) = array_values( unpack( 'v*', $in ) );
		$keys    = $this->keys;
		$limit   = 20;
		$actions = [ $limit => 44, 44 => 64 ];
		$j       = 0;

		for ( ; ; ) {
			// Mixing round.
			$r0 = ( ( $r0 + $keys[ $j ++ ] + ( ( ( $r1 ^ $r2 ) & $r3 ) ^ $r1 ) ) & 0xFFFF ) << 1;
			$r0 |= $r0 >> 16;
			$r1 = ( ( $r1 + $keys[ $j ++ ] + ( ( ( $r2 ^ $r3 ) & $r0 ) ^ $r2 ) ) & 0xFFFF ) << 2;
			$r1 |= $r1 >> 16;
			$r2 = ( ( $r2 + $keys[ $j ++ ] + ( ( ( $r3 ^ $r0 ) & $r1 ) ^ $r3 ) ) & 0xFFFF ) << 3;
			$r2 |= $r2 >> 16;
			$r3 = ( ( $r3 + $keys[ $j ++ ] + ( ( ( $r0 ^ $r1 ) & $r2 ) ^ $r0 ) ) & 0xFFFF ) << 5;
			$r3 |= $r3 >> 16;

			if ( $j === $limit ) {
				if ( $limit === 64 ) {
					break;
				}

				// Mashing round.
				$r0    += $keys[ $r3 & 0x3F ];
				$r1    += $keys[ $r0 & 0x3F ];
				$r2    += $keys[ $r1 & 0x3F ];
				$r3    += $keys[ $r2 & 0x3F ];
				$limit = $actions[ $limit ];
			}
		}

		return pack( 'vvvv', $r0, $r1, $r2, $r3 );
	}

	/**
	 * Decrypts a block
	 *
	 * @param string $in
	 *
	 * @return string
	 * @see \phpseclib3\Crypt\Common\SymmetricKey::decryptBlock()
	 * @see \phpseclib3\Crypt\Common\SymmetricKey::decrypt()
	 */
	protected function decryptBlock( $in ) {
		list( $r0, $r1, $r2, $r3 ) = array_values( unpack( 'v*', $in ) );
		$keys    = $this->keys;
		$limit   = 44;
		$actions = [ $limit => 20, 20 => 0 ];
		$j       = 64;

		for ( ; ; ) {
			// R-mixing round.
			$r3 = ( $r3 | ( $r3 << 16 ) ) >> 5;
			$r3 = ( $r3 - $keys[ -- $j ] - ( ( ( $r0 ^ $r1 ) & $r2 ) ^ $r0 ) ) & 0xFFFF;
			$r2 = ( $r2 | ( $r2 << 16 ) ) >> 3;
			$r2 = ( $r2 - $keys[ -- $j ] - ( ( ( $r3 ^ $r0 ) & $r1 ) ^ $r3 ) ) & 0xFFFF;
			$r1 = ( $r1 | ( $r1 << 16 ) ) >> 2;
			$r1 = ( $r1 - $keys[ -- $j ] - ( ( ( $r2 ^ $r3 ) & $r0 ) ^ $r2 ) ) & 0xFFFF;
			$r0 = ( $r0 | ( $r0 << 16 ) ) >> 1;
			$r0 = ( $r0 - $keys[ -- $j ] - ( ( ( $r1 ^ $r2 ) & $r3 ) ^ $r1 ) ) & 0xFFFF;

			if ( $j === $limit ) {
				if ( $limit === 0 ) {
					break;
				}

				// R-mashing round.
				$r3    = ( $r3 - $keys[ $r2 & 0x3F ] ) & 0xFFFF;
				$r2    = ( $r2 - $keys[ $r1 & 0x3F ] ) & 0xFFFF;
				$r1    = ( $r1 - $keys[ $r0 & 0x3F ] ) & 0xFFFF;
				$r0    = ( $r0 - $keys[ $r3 & 0x3F ] ) & 0xFFFF;
				$limit = $actions[ $limit ];
			}
		}

		return pack( 'vvvv', $r0, $r1, $r2, $r3 );
	}

	/**
	 * Creates the key schedule
	 *
	 * @see \phpseclib3\Crypt\Common\SymmetricKey::setupKey()
	 */
	protected function setupKey() {
		if ( ! isset( $this->key ) ) {
			$this->setKey( '' );
		}

		// Key has already been expanded in \phpseclib3\Crypt\RC2::setKey():
		// Only the first value must be altered.
		$l = unpack( 'Ca/Cb/v*', $this->key );
		array_unshift( $l, self::$pitable[ $l['a'] ] | ( $l['b'] << 8 ) );
		unset( $l['a'] );
		unset( $l['b'] );
		$this->keys = $l;
	}

	/**
	 * Setup the performance-optimized function for de/encrypt()
	 *
	 * @see \phpseclib3\Crypt\Common\SymmetricKey::setupInlineCrypt()
	 */
	protected function setupInlineCrypt() {
		// Init code for both, encrypt and decrypt.
		$init_crypt = '$keys = $this->keys;';

		$keys = $this->keys;

		// $in is the current 8 bytes block which has to be en/decrypt
		$encrypt_block = $decrypt_block = '
            $in = unpack("v4", $in);
            $r0 = $in[1];
            $r1 = $in[2];
            $r2 = $in[3];
            $r3 = $in[4];
        ';

		// Create code for encryption.
		$limit   = 20;
		$actions = [ $limit => 44, 44 => 64 ];
		$j       = 0;

		for ( ; ; ) {
			// Mixing round.
			$encrypt_block .= '
                $r0 = (($r0 + ' . $keys[ $j ++ ] . ' +
                       ((($r1 ^ $r2) & $r3) ^ $r1)) & 0xFFFF) << 1;
                $r0 |= $r0 >> 16;
                $r1 = (($r1 + ' . $keys[ $j ++ ] . ' +
                       ((($r2 ^ $r3) & $r0) ^ $r2)) & 0xFFFF) << 2;
                $r1 |= $r1 >> 16;
                $r2 = (($r2 + ' . $keys[ $j ++ ] . ' +
                       ((($r3 ^ $r0) & $r1) ^ $r3)) & 0xFFFF) << 3;
                $r2 |= $r2 >> 16;
                $r3 = (($r3 + ' . $keys[ $j ++ ] . ' +
                       ((($r0 ^ $r1) & $r2) ^ $r0)) & 0xFFFF) << 5;
                $r3 |= $r3 >> 16;';

			if ( $j === $limit ) {
				if ( $limit === 64 ) {
					break;
				}

				// Mashing round.
				$encrypt_block .= '
                    $r0 += $keys[$r3 & 0x3F];
                    $r1 += $keys[$r0 & 0x3F];
                    $r2 += $keys[$r1 & 0x3F];
                    $r3 += $keys[$r2 & 0x3F];';
				$limit         = $actions[ $limit ];
			}
		}

		$encrypt_block .= '$in = pack("v4", $r0, $r1, $r2, $r3);';

		// Create code for decryption.
		$limit   = 44;
		$actions = [ $limit => 20, 20 => 0 ];
		$j       = 64;

		for ( ; ; ) {
			// R-mixing round.
			$decrypt_block .= '
                $r3 = ($r3 | ($r3 << 16)) >> 5;
                $r3 = ($r3 - ' . $keys[ -- $j ] . ' -
                       ((($r0 ^ $r1) & $r2) ^ $r0)) & 0xFFFF;
                $r2 = ($r2 | ($r2 << 16)) >> 3;
                $r2 = ($r2 - ' . $keys[ -- $j ] . ' -
                       ((($r3 ^ $r0) & $r1) ^ $r3)) & 0xFFFF;
                $r1 = ($r1 | ($r1 << 16)) >> 2;
                $r1 = ($r1 - ' . $keys[ -- $j ] . ' -
                       ((($r2 ^ $r3) & $r0) ^ $r2)) & 0xFFFF;
                $r0 = ($r0 | ($r0 << 16)) >> 1;
                $r0 = ($r0 - ' . $keys[ -- $j ] . ' -
                       ((($r1 ^ $r2) & $r3) ^ $r1)) & 0xFFFF;';

			if ( $j === $limit ) {
				if ( $limit === 0 ) {
					break;
				}

				// R-mashing round.
				$decrypt_block .= '
                    $r3 = ($r3 - $keys[$r2 & 0x3F]) & 0xFFFF;
                    $r2 = ($r2 - $keys[$r1 & 0x3F]) & 0xFFFF;
                    $r1 = ($r1 - $keys[$r0 & 0x3F]) & 0xFFFF;
                    $r0 = ($r0 - $keys[$r3 & 0x3F]) & 0xFFFF;';
				$limit         = $actions[ $limit ];
			}
		}

		$decrypt_block .= '$in = pack("v4", $r0, $r1, $r2, $r3);';

		// Creates the inline-crypt function
		$this->inline_crypt = $this->createInlineCryptFunction( [
				'init_crypt'    => $init_crypt,
				'encrypt_block' => $encrypt_block,
				'decrypt_block' => $decrypt_block,
			] );
	}
}
