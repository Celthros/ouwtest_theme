<?php

namespace PhpOffice\PhpSpreadsheet\Worksheet;

/**
 * <code>
 * Header/Footer Formatting Syntax taken from Office Open XML Part 4 - Markup Language Reference, page 1970:.
 *
 * There are a number of formatting codes that can be written inline with the actual header / footer text, which
 * affect the formatting in the header or footer.
 *
 * Example: This example shows the text "Center Bold Header" on the first line (center section), and the date on
 * the second line (center section).
 *         &CCenter &"-,Bold"Bold&"-,Regular"Header_x000A_&D
 *
 * General Rules:
 * There is no required order in which these codes must appear.
 *
 * The first occurrence of the following codes turns the formatting ON, the second occurrence turns it OFF again:
 * - strikethrough
 * - superscript
 * - subscript
 * Superscript and subscript cannot both be ON at same time. Whichever comes first wins and the other is ignored,
 * while the first is ON.
 * &L - code for "left section" (there are three header / footer locations, "left", "center", and "right"). When
 * two or more occurrences of this section marker exist, the contents from all markers are concatenated, in the
 * order of appearance, and placed into the left section.
 * &P - code for "current page #"
 * &N - code for "total pages"
 * &font size - code for "text font size", where font size is a font size in points.
 * &K - code for "text font color"
 * RGB Color is specified as RRGGBB
 * Theme Color is specifed as TTSNN where TT is the theme color Id, S is either "+" or "-" of the tint/shade
 * value, NN is the tint/shade value.
 * &S - code for "text strikethrough" on / off
 * &X - code for "text super script" on / off
 * &Y - code for "text subscript" on / off
 * &C - code for "center section". When two or more occurrences of this section marker exist, the contents
 * from all markers are concatenated, in the order of appearance, and placed into the center section.
 *
 * &D - code for "date"
 * &T - code for "time"
 * &G - code for "picture as background"
 * &U - code for "text single underline"
 * &E - code for "double underline"
 * &R - code for "right section". When two or more occurrences of this section marker exist, the contents
 * from all markers are concatenated, in the order of appearance, and placed into the right section.
 * &Z - code for "this workbook's file path"
 * &F - code for "this workbook's file name"
 * &A - code for "sheet tab name"
 * &+ - code for add to page #.
 * &- - code for subtract from page #.
 * &"font name,font type" - code for "text font name" and "text font type", where font name and font type
 * are strings specifying the name and type of the font, separated by a comma. When a hyphen appears in font
 * name, it means "none specified". Both of font name and font type can be localized values.
 * &"-,Bold" - code for "bold font style"
 * &B - also means "bold font style".
 * &"-,Regular" - code for "regular font style"
 * &"-,Italic" - code for "italic font style"
 * &I - also means "italic font style"
 * &"-,Bold Italic" code for "bold italic font style"
 * &O - code for "outline style"
 * &H - code for "shadow style"
 * </code>
 */
class HeaderFooter {
	// Header/footer image location
	const IMAGE_HEADER_LEFT = 'LH';
	const IMAGE_HEADER_CENTER = 'CH';
	const IMAGE_HEADER_RIGHT = 'RH';
	const IMAGE_FOOTER_LEFT = 'LF';
	const IMAGE_FOOTER_CENTER = 'CF';
	const IMAGE_FOOTER_RIGHT = 'RF';

	/**
	 * OddHeader.
	 *
	 * @var string
	 */
	private $oddHeader = '';

	/**
	 * OddFooter.
	 *
	 * @var string
	 */
	private $oddFooter = '';

	/**
	 * EvenHeader.
	 *
	 * @var string
	 */
	private $evenHeader = '';

	/**
	 * EvenFooter.
	 *
	 * @var string
	 */
	private $evenFooter = '';

	/**
	 * FirstHeader.
	 *
	 * @var string
	 */
	private $firstHeader = '';

	/**
	 * FirstFooter.
	 *
	 * @var string
	 */
	private $firstFooter = '';

	/**
	 * Different header for Odd/Even, defaults to false.
	 *
	 * @var bool
	 */
	private $differentOddEven = false;

	/**
	 * Different header for first page, defaults to false.
	 *
	 * @var bool
	 */
	private $differentFirst = false;

	/**
	 * Scale with document, defaults to true.
	 *
	 * @var bool
	 */
	private $scaleWithDocument = true;

	/**
	 * Align with margins, defaults to true.
	 *
	 * @var bool
	 */
	private $alignWithMargins = true;

	/**
	 * Header/footer images.
	 *
	 * @var HeaderFooterDrawing[]
	 */
	private $headerFooterImages = [];

	/**
	 * Create a new HeaderFooter.
	 */
	public function __construct() {
	}

	/**
	 * Get OddHeader.
	 *
	 * @return string
	 */
	public function getOddHeader() {
		return $this->oddHeader;
	}

	/**
	 * Set OddHeader.
	 *
	 * @param string $oddHeader
	 *
	 * @return $this
	 */
	public function setOddHeader( $oddHeader ) {
		$this->oddHeader = $oddHeader;

		return $this;
	}

	/**
	 * Get OddFooter.
	 *
	 * @return string
	 */
	public function getOddFooter() {
		return $this->oddFooter;
	}

	/**
	 * Set OddFooter.
	 *
	 * @param string $oddFooter
	 *
	 * @return $this
	 */
	public function setOddFooter( $oddFooter ) {
		$this->oddFooter = $oddFooter;

		return $this;
	}

	/**
	 * Get EvenHeader.
	 *
	 * @return string
	 */
	public function getEvenHeader() {
		return $this->evenHeader;
	}

	/**
	 * Set EvenHeader.
	 *
	 * @param string $eventHeader
	 *
	 * @return $this
	 */
	public function setEvenHeader( $eventHeader ) {
		$this->evenHeader = $eventHeader;

		return $this;
	}

	/**
	 * Get EvenFooter.
	 *
	 * @return string
	 */
	public function getEvenFooter() {
		return $this->evenFooter;
	}

	/**
	 * Set EvenFooter.
	 *
	 * @param string $evenFooter
	 *
	 * @return $this
	 */
	public function setEvenFooter( $evenFooter ) {
		$this->evenFooter = $evenFooter;

		return $this;
	}

	/**
	 * Get FirstHeader.
	 *
	 * @return string
	 */
	public function getFirstHeader() {
		return $this->firstHeader;
	}

	/**
	 * Set FirstHeader.
	 *
	 * @param string $firstHeader
	 *
	 * @return $this
	 */
	public function setFirstHeader( $firstHeader ) {
		$this->firstHeader = $firstHeader;

		return $this;
	}

	/**
	 * Get FirstFooter.
	 *
	 * @return string
	 */
	public function getFirstFooter() {
		return $this->firstFooter;
	}

	/**
	 * Set FirstFooter.
	 *
	 * @param string $firstFooter
	 *
	 * @return $this
	 */
	public function setFirstFooter( $firstFooter ) {
		$this->firstFooter = $firstFooter;

		return $this;
	}

	/**
	 * Get DifferentOddEven.
	 *
	 * @return bool
	 */
	public function getDifferentOddEven() {
		return $this->differentOddEven;
	}

	/**
	 * Set DifferentOddEven.
	 *
	 * @param bool $differentOddEvent
	 *
	 * @return $this
	 */
	public function setDifferentOddEven( $differentOddEvent ) {
		$this->differentOddEven = $differentOddEvent;

		return $this;
	}

	/**
	 * Get DifferentFirst.
	 *
	 * @return bool
	 */
	public function getDifferentFirst() {
		return $this->differentFirst;
	}

	/**
	 * Set DifferentFirst.
	 *
	 * @param bool $differentFirst
	 *
	 * @return $this
	 */
	public function setDifferentFirst( $differentFirst ) {
		$this->differentFirst = $differentFirst;

		return $this;
	}

	/**
	 * Get ScaleWithDocument.
	 *
	 * @return bool
	 */
	public function getScaleWithDocument() {
		return $this->scaleWithDocument;
	}

	/**
	 * Set ScaleWithDocument.
	 *
	 * @param bool $scaleWithDocument
	 *
	 * @return $this
	 */
	public function setScaleWithDocument( $scaleWithDocument ) {
		$this->scaleWithDocument = $scaleWithDocument;

		return $this;
	}

	/**
	 * Get AlignWithMargins.
	 *
	 * @return bool
	 */
	public function getAlignWithMargins() {
		return $this->alignWithMargins;
	}

	/**
	 * Set AlignWithMargins.
	 *
	 * @param bool $alignWithMargins
	 *
	 * @return $this
	 */
	public function setAlignWithMargins( $alignWithMargins ) {
		$this->alignWithMargins = $alignWithMargins;

		return $this;
	}

	/**
	 * Add header/footer image.
	 *
	 * @param string $location
	 *
	 * @return $this
	 */
	public function addImage( HeaderFooterDrawing $image, $location = self::IMAGE_HEADER_LEFT ) {
		$this->headerFooterImages[ $location ] = $image;

		return $this;
	}

	/**
	 * Remove header/footer image.
	 *
	 * @param string $location
	 *
	 * @return $this
	 */
	public function removeImage( $location = self::IMAGE_HEADER_LEFT ) {
		if ( isset( $this->headerFooterImages[ $location ] ) ) {
			unset( $this->headerFooterImages[ $location ] );
		}

		return $this;
	}

	/**
	 * Set header/footer images.
	 *
	 * @param HeaderFooterDrawing[] $images
	 *
	 * @return $this
	 */
	public function setImages( array $images ) {
		$this->headerFooterImages = $images;

		return $this;
	}

	/**
	 * Get header/footer images.
	 *
	 * @return HeaderFooterDrawing[]
	 */
	public function getImages() {
		// Sort array
		$images = [];
		if ( isset( $this->headerFooterImages[ self::IMAGE_HEADER_LEFT ] ) ) {
			$images[ self::IMAGE_HEADER_LEFT ] = $this->headerFooterImages[ self::IMAGE_HEADER_LEFT ];
		}
		if ( isset( $this->headerFooterImages[ self::IMAGE_HEADER_CENTER ] ) ) {
			$images[ self::IMAGE_HEADER_CENTER ] = $this->headerFooterImages[ self::IMAGE_HEADER_CENTER ];
		}
		if ( isset( $this->headerFooterImages[ self::IMAGE_HEADER_RIGHT ] ) ) {
			$images[ self::IMAGE_HEADER_RIGHT ] = $this->headerFooterImages[ self::IMAGE_HEADER_RIGHT ];
		}
		if ( isset( $this->headerFooterImages[ self::IMAGE_FOOTER_LEFT ] ) ) {
			$images[ self::IMAGE_FOOTER_LEFT ] = $this->headerFooterImages[ self::IMAGE_FOOTER_LEFT ];
		}
		if ( isset( $this->headerFooterImages[ self::IMAGE_FOOTER_CENTER ] ) ) {
			$images[ self::IMAGE_FOOTER_CENTER ] = $this->headerFooterImages[ self::IMAGE_FOOTER_CENTER ];
		}
		if ( isset( $this->headerFooterImages[ self::IMAGE_FOOTER_RIGHT ] ) ) {
			$images[ self::IMAGE_FOOTER_RIGHT ] = $this->headerFooterImages[ self::IMAGE_FOOTER_RIGHT ];
		}
		$this->headerFooterImages = $images;

		return $this->headerFooterImages;
	}

	/**
	 * Implement PHP __clone to create a deep clone, not just a shallow copy.
	 */
	public function __clone() {
		$vars = get_object_vars( $this );
		foreach ( $vars as $key => $value ) {
			if ( is_object( $value ) ) {
				$this->$key = clone $value;
			} else {
				$this->$key = $value;
			}
		}
	}
}
