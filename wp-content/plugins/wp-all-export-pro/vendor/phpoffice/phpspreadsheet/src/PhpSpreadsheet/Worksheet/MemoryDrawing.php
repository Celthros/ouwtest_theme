<?php

namespace PhpOffice\PhpSpreadsheet\Worksheet;

use GdImage;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Shared\File;

class MemoryDrawing extends BaseDrawing {
	// Rendering functions
	const RENDERING_DEFAULT = 'imagepng';
	const RENDERING_PNG = 'imagepng';
	const RENDERING_GIF = 'imagegif';
	const RENDERING_JPEG = 'imagejpeg';

	// MIME types
	const MIMETYPE_DEFAULT = 'image/png';
	const MIMETYPE_PNG = 'image/png';
	const MIMETYPE_GIF = 'image/gif';
	const MIMETYPE_JPEG = 'image/jpeg';

	const SUPPORTED_MIME_TYPES = [
		self::MIMETYPE_GIF,
		self::MIMETYPE_JPEG,
		self::MIMETYPE_PNG,
	];

	/**
	 * Image resource.
	 *
	 * @var null|GdImage|resource
	 */
	private $imageResource;

	/**
	 * Rendering function.
	 *
	 * @var string
	 */
	private $renderingFunction;

	/**
	 * Mime type.
	 *
	 * @var string
	 */
	private $mimeType;

	/**
	 * Unique name.
	 *
	 * @var string
	 */
	private $uniqueName;

	/** @var null|resource */
	private $alwaysNull;

	/**
	 * Create a new MemoryDrawing.
	 */
	public function __construct() {
		// Initialise values
		$this->renderingFunction = self::RENDERING_DEFAULT;
		$this->mimeType          = self::MIMETYPE_DEFAULT;
		$this->uniqueName        = md5( mt_rand( 0, 9999 ) . time() . mt_rand( 0, 9999 ) );
		$this->alwaysNull        = null;

		// Initialize parent
		parent::__construct();
	}

	public function __destruct() {
		if ( $this->imageResource ) {
			$rslt = @imagedestroy( $this->imageResource );
			// "Fix" for Scrutinizer
			$this->imageResource = $rslt ? null : $this->alwaysNull;
		}
	}

	public function __clone() {
		parent::__clone();
		$this->cloneResource();
	}

	private function cloneResource(): void {
		if ( ! $this->imageResource ) {
			return;
		}

		$width  = (int) imagesx( $this->imageResource );
		$height = (int) imagesy( $this->imageResource );

		if ( imageistruecolor( $this->imageResource ) ) {
			$clone = imagecreatetruecolor( $width, $height );
			if ( ! $clone ) {
				throw new Exception( 'Could not clone image resource' );
			}

			imagealphablending( $clone, false );
			imagesavealpha( $clone, true );
		} else {
			$clone = imagecreate( $width, $height );
			if ( ! $clone ) {
				throw new Exception( 'Could not clone image resource' );
			}

			// If the image has transparency...
			$transparent = imagecolortransparent( $this->imageResource );
			if ( $transparent >= 0 ) {
				$rgb = imagecolorsforindex( $this->imageResource, $transparent );
				if ( empty( $rgb ) ) {
					throw new Exception( 'Could not get image colors' );
				}

				imagesavealpha( $clone, true );
				$color = imagecolorallocatealpha( $clone, $rgb['red'], $rgb['green'], $rgb['blue'], $rgb['alpha'] );
				if ( $color === false ) {
					throw new Exception( 'Could not get image alpha color' );
				}

				imagefill( $clone, 0, 0, $color );
			}
		}

		//Create the Clone!!
		imagecopy( $clone, $this->imageResource, 0, 0, 0, 0, $width, $height );

		$this->imageResource = $clone;
	}

	/**
	 * @param resource $imageStream Stream data to be converted to a Memory Drawing
	 *
	 * @throws Exception
	 */
	public static function fromStream( $imageStream ): self {
		$streamValue = stream_get_contents( $imageStream );
		if ( $streamValue === false ) {
			throw new Exception( 'Unable to read data from stream' );
		}

		return self::fromString( $streamValue );
	}

	/**
	 * @param string $imageString String data to be converted to a Memory Drawing
	 *
	 * @throws Exception
	 */
	public static function fromString( string $imageString ): self {
		$gdImage = @imagecreatefromstring( $imageString );
		if ( $gdImage === false ) {
			throw new Exception( 'Value cannot be converted to an image' );
		}

		$mimeType          = self::identifyMimeType( $imageString );
		$renderingFunction = self::identifyRenderingFunction( $mimeType );

		$drawing = new self();
		$drawing->setImageResource( $gdImage );
		$drawing->setRenderingFunction( $renderingFunction );
		$drawing->setMimeType( $mimeType );

		return $drawing;
	}

	private static function identifyRenderingFunction( string $mimeType ): string {
		switch ( $mimeType ) {
			case self::MIMETYPE_PNG:
				return self::RENDERING_PNG;
			case self::MIMETYPE_JPEG:
				return self::RENDERING_JPEG;
			case self::MIMETYPE_GIF:
				return self::RENDERING_GIF;
		}

		return self::RENDERING_DEFAULT;
	}

	/**
	 * @throws Exception
	 */
	private static function identifyMimeType( string $imageString ): string {
		$temporaryFileName = File::temporaryFilename();
		file_put_contents( $temporaryFileName, $imageString );

		$mimeType = self::identifyMimeTypeUsingExif( $temporaryFileName );
		if ( $mimeType !== null ) {
			unlink( $temporaryFileName );

			return $mimeType;
		}

		$mimeType = self::identifyMimeTypeUsingGd( $temporaryFileName );
		if ( $mimeType !== null ) {
			unlink( $temporaryFileName );

			return $mimeType;
		}

		unlink( $temporaryFileName );

		return self::MIMETYPE_DEFAULT;
	}

	private static function identifyMimeTypeUsingExif( string $temporaryFileName ): ?string {
		if ( function_exists( 'exif_imagetype' ) ) {
			$imageType = @exif_imagetype( $temporaryFileName );
			$mimeType  = ( $imageType ) ? image_type_to_mime_type( $imageType ) : null;

			return self::supportedMimeTypes( $mimeType );
		}

		return null;
	}

	private static function identifyMimeTypeUsingGd( string $temporaryFileName ): ?string {
		if ( function_exists( 'getimagesize' ) ) {
			$imageSize = @getimagesize( $temporaryFileName );
			if ( is_array( $imageSize ) ) {
				$mimeType = $imageSize['mime'] ?? null;

				return self::supportedMimeTypes( $mimeType );
			}
		}

		return null;
	}

	private static function supportedMimeTypes( ?string $mimeType = null ): ?string {
		if ( in_array( $mimeType, self::SUPPORTED_MIME_TYPES, true ) ) {
			return $mimeType;
		}

		return null;
	}

	/**
	 * Get image resource.
	 *
	 * @return null|GdImage|resource
	 */
	public function getImageResource() {
		return $this->imageResource;
	}

	/**
	 * Set image resource.
	 *
	 * @param GdImage|resource $value
	 *
	 * @return $this
	 */
	public function setImageResource( $value ) {
		$this->imageResource = $value;

		if ( $this->imageResource !== null ) {
			// Get width/height
			$this->width  = (int) imagesx( $this->imageResource );
			$this->height = (int) imagesy( $this->imageResource );
		}

		return $this;
	}

	/**
	 * Get rendering function.
	 *
	 * @return string
	 */
	public function getRenderingFunction() {
		return $this->renderingFunction;
	}

	/**
	 * Set rendering function.
	 *
	 * @param string $value see self::RENDERING_*
	 *
	 * @return $this
	 */
	public function setRenderingFunction( $value ) {
		$this->renderingFunction = $value;

		return $this;
	}

	/**
	 * Get mime type.
	 *
	 * @return string
	 */
	public function getMimeType() {
		return $this->mimeType;
	}

	/**
	 * Set mime type.
	 *
	 * @param string $value see self::MIMETYPE_*
	 *
	 * @return $this
	 */
	public function setMimeType( $value ) {
		$this->mimeType = $value;

		return $this;
	}

	/**
	 * Get indexed filename (using image index).
	 */
	public function getIndexedFilename(): string {
		$extension = strtolower( $this->getMimeType() );
		$extension = explode( '/', $extension );
		$extension = $extension[1];

		return $this->uniqueName . $this->getImageIndex() . '.' . $extension;
	}

	/**
	 * Get hash code.
	 *
	 * @return string Hash code
	 */
	public function getHashCode() {
		return md5( $this->renderingFunction . $this->mimeType . $this->uniqueName . parent::getHashCode() . __CLASS__ );
	}
}
