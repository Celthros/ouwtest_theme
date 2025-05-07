<?php

namespace PhpOffice\PhpSpreadsheet\Calculation;

use DateTimeInterface;

/**
 * @deprecated 1.18.0
 */
class TextData {
	/**
	 * CHARACTER.
	 *
	 * @param string $character Value
	 *
	 * @return array|string
	 * @deprecated 1.18.0
	 *      Use the character() method in the TextData\CharacterConvert class instead
	 * @see TextData\CharacterConvert::character()
	 *
	 */
	public static function CHARACTER( $character ) {
		return TextData\CharacterConvert::character( $character );
	}

	/**
	 * TRIMNONPRINTABLE.
	 *
	 * @param mixed $stringValue Value to check
	 *
	 * @return null|array|string
	 * @deprecated 1.18.0
	 *      Use the nonPrintable() method in the TextData\Trim class instead
	 * @see TextData\Trim::nonPrintable()
	 *
	 */
	public static function TRIMNONPRINTABLE( $stringValue = '' ) {
		return TextData\Trim::nonPrintable( $stringValue );
	}

	/**
	 * TRIMSPACES.
	 *
	 * @param mixed $stringValue Value to check
	 *
	 * @return array|string
	 * @deprecated 1.18.0
	 *      Use the spaces() method in the TextData\Trim class instead
	 * @see TextData\Trim::spaces()
	 *
	 */
	public static function TRIMSPACES( $stringValue = '' ) {
		return TextData\Trim::spaces( $stringValue );
	}

	/**
	 * ASCIICODE.
	 *
	 * @param array|string $characters Value
	 *
	 * @return array|int|string A string if arguments are invalid
	 * @deprecated 1.18.0
	 *      Use the code() method in the TextData\CharacterConvert class instead
	 * @see TextData\CharacterConvert::code()
	 *
	 */
	public static function ASCIICODE( $characters ) {
		return TextData\CharacterConvert::code( $characters );
	}

	/**
	 * CONCATENATE.
	 *
	 * @param array $args
	 *
	 * @return string
	 * @deprecated 1.18.0
	 *      Use the CONCATENATE() method in the TextData\Concatenate class instead
	 * @see TextData\Concatenate::CONCATENATE()
	 *
	 */
	public static function CONCATENATE( ...$args ) {
		return TextData\Concatenate::CONCATENATE( ...$args );
	}

	/**
	 * DOLLAR.
	 *
	 * This function converts a number to text using currency format, with the decimals rounded to the specified place.
	 * The format used is $#,##0.00_);($#,##0.00)..
	 *
	 * @param float $value The value to format
	 * @param int $decimals The number of digits to display to the right of the decimal point.
	 *                                    If decimals is negative, number is rounded to the left of the decimal point.
	 *                                    If you omit decimals, it is assumed to be 2
	 *
	 * @return array|string
	 * @see TextData\Format::DOLLAR()
	 *
	 * @deprecated 1.18.0
	 *      Use the DOLLAR() method in the TextData\Format class instead
	 */
	public static function DOLLAR( $value = 0, $decimals = 2 ) {
		return TextData\Format::DOLLAR( $value, $decimals );
	}

	/**
	 * FIND.
	 *
	 * @param array|string $needle The string to look for
	 * @param array|string $haystack The string in which to look
	 * @param array|int $offset Offset within $haystack
	 *
	 * @return array|int|string
	 * @deprecated 1.18.0
	 *      Use the sensitive() method in the TextData\Search class instead
	 * @see TextData\Search::sensitive()
	 *
	 */
	public static function SEARCHSENSITIVE( $needle, $haystack, $offset = 1 ) {
		return TextData\Search::sensitive( $needle, $haystack, $offset );
	}

	/**
	 * SEARCH.
	 *
	 * @param array|string $needle The string to look for
	 * @param array|string $haystack The string in which to look
	 * @param array|int $offset Offset within $haystack
	 *
	 * @return array|int|string
	 * @deprecated 1.18.0
	 *      Use the insensitive() method in the TextData\Search class instead
	 * @see TextData\Search::insensitive()
	 *
	 */
	public static function SEARCHINSENSITIVE( $needle, $haystack, $offset = 1 ) {
		return TextData\Search::insensitive( $needle, $haystack, $offset );
	}

	/**
	 * FIXEDFORMAT.
	 *
	 * @param mixed $value Value to check
	 * @param int $decimals
	 * @param bool $no_commas
	 *
	 * @return array|string
	 * @deprecated 1.18.0
	 *      Use the FIXEDFORMAT() method in the TextData\Format class instead
	 * @see TextData\Format::FIXEDFORMAT()
	 *
	 */
	public static function FIXEDFORMAT( $value, $decimals = 2, $no_commas = false ) {
		return TextData\Format::FIXEDFORMAT( $value, $decimals, $no_commas );
	}

	/**
	 * LEFT.
	 *
	 * @param array|string $value Value
	 * @param array|int $chars Number of characters
	 *
	 * @return array|string
	 * @see TextData\Extract::left()
	 *
	 * @deprecated 1.18.0
	 *      Use the left() method in the TextData\Extract class instead
	 */
	public static function LEFT( $value = '', $chars = 1 ) {
		return TextData\Extract::left( $value, $chars );
	}

	/**
	 * MID.
	 *
	 * @param array|string $value Value
	 * @param array|int $start Start character
	 * @param array|int $chars Number of characters
	 *
	 * @return array|string
	 * @deprecated 1.18.0
	 *      Use the mid() method in the TextData\Extract class instead
	 * @see TextData\Extract::mid()
	 *
	 */
	public static function MID( $value = '', $start = 1, $chars = null ) {
		return TextData\Extract::mid( $value, $start, $chars );
	}

	/**
	 * RIGHT.
	 *
	 * @param array|string $value Value
	 * @param array|int $chars Number of characters
	 *
	 * @return array|string
	 * @see TextData\Extract::right()
	 *
	 * @deprecated 1.18.0
	 *      Use the right() method in the TextData\Extract class instead
	 */
	public static function RIGHT( $value = '', $chars = 1 ) {
		return TextData\Extract::right( $value, $chars );
	}

	/**
	 * STRINGLENGTH.
	 *
	 * @param string $value Value
	 *
	 * @return array|int
	 * @deprecated 1.18.0
	 *      Use the length() method in the TextData\Text class instead
	 * @see TextData\Text::length()
	 *
	 */
	public static function STRINGLENGTH( $value = '' ) {
		return TextData\Text::length( $value );
	}

	/**
	 * LOWERCASE.
	 *
	 * Converts a string value to lower case.
	 *
	 * @param array|string $mixedCaseString
	 *
	 * @return array|string
	 * @deprecated 1.18.0
	 *      Use the lower() method in the TextData\CaseConvert class instead
	 * @see TextData\CaseConvert::lower()
	 *
	 */
	public static function LOWERCASE( $mixedCaseString ) {
		return TextData\CaseConvert::lower( $mixedCaseString );
	}

	/**
	 * UPPERCASE.
	 *
	 * Converts a string value to upper case.
	 *
	 * @param string $mixedCaseString
	 *
	 * @return array|string
	 * @deprecated 1.18.0
	 *      Use the upper() method in the TextData\CaseConvert class instead
	 * @see TextData\CaseConvert::upper()
	 *
	 */
	public static function UPPERCASE( $mixedCaseString ) {
		return TextData\CaseConvert::upper( $mixedCaseString );
	}

	/**
	 * PROPERCASE.
	 *
	 * Converts a string value to proper/title case.
	 *
	 * @param array|string $mixedCaseString
	 *
	 * @return array|string
	 * @deprecated 1.18.0
	 *      Use the proper() method in the TextData\CaseConvert class instead
	 * @see TextData\CaseConvert::proper()
	 *
	 */
	public static function PROPERCASE( $mixedCaseString ) {
		return TextData\CaseConvert::proper( $mixedCaseString );
	}

	/**
	 * REPLACE.
	 *
	 * @param string $oldText String to modify
	 * @param int $start Start character
	 * @param int $chars Number of characters
	 * @param string $newText String to replace in defined position
	 *
	 * @return array|string
	 * @see TextData\Replace::replace()
	 *
	 * @deprecated 1.18.0
	 *      Use the replace() method in the TextData\Replace class instead
	 */
	public static function REPLACE( $oldText, $start, $chars, $newText ) {
		return TextData\Replace::replace( $oldText, $start, $chars, $newText );
	}

	/**
	 * SUBSTITUTE.
	 *
	 * @param string $text Value
	 * @param string $fromText From Value
	 * @param string $toText To Value
	 * @param int $instance Instance Number
	 *
	 * @return array|string
	 * @see TextData\Replace::substitute()
	 *
	 * @deprecated 1.18.0
	 *      Use the substitute() method in the TextData\Replace class instead
	 */
	public static function SUBSTITUTE( $text = '', $fromText = '', $toText = '', $instance = 0 ) {
		return TextData\Replace::substitute( $text, $fromText, $toText, $instance );
	}

	/**
	 * RETURNSTRING.
	 *
	 * @param mixed $testValue Value to check
	 *
	 * @return null|array|string
	 * @deprecated 1.18.0
	 *      Use the test() method in the TextData\Text class instead
	 * @see TextData\Text::test()
	 *
	 */
	public static function RETURNSTRING( $testValue = '' ) {
		return TextData\Text::test( $testValue );
	}

	/**
	 * TEXTFORMAT.
	 *
	 * @param mixed $value Value to check
	 * @param string $format Format mask to use
	 *
	 * @return array|string
	 * @see TextData\Format::TEXTFORMAT()
	 *
	 * @deprecated 1.18.0
	 *      Use the TEXTFORMAT() method in the TextData\Format class instead
	 */
	public static function TEXTFORMAT( $value, $format ) {
		return TextData\Format::TEXTFORMAT( $value, $format );
	}

	/**
	 * VALUE.
	 *
	 * @param mixed $value Value to check
	 *
	 * @return array|DateTimeInterface|float|int|string A string if arguments are invalid
	 * @deprecated 1.18.0
	 *      Use the VALUE() method in the TextData\Format class instead
	 * @see TextData\Format::VALUE()
	 *
	 */
	public static function VALUE( $value = '' ) {
		return TextData\Format::VALUE( $value );
	}

	/**
	 * NUMBERVALUE.
	 *
	 * @param mixed $value Value to check
	 * @param string $decimalSeparator decimal separator, defaults to locale defined value
	 * @param string $groupSeparator group/thosands separator, defaults to locale defined value
	 *
	 * @return array|float|string
	 * @deprecated 1.18.0
	 *      Use the NUMBERVALUE() method in the TextData\Format class instead
	 * @see TextData\Format::NUMBERVALUE()
	 *
	 */
	public static function NUMBERVALUE( $value = '', $decimalSeparator = null, $groupSeparator = null ) {
		return TextData\Format::NUMBERVALUE( $value, $decimalSeparator, $groupSeparator );
	}

	/**
	 * Compares two text strings and returns TRUE if they are exactly the same, FALSE otherwise.
	 * EXACT is case-sensitive but ignores formatting differences.
	 * Use EXACT to test text being entered into a document.
	 *
	 * @param mixed $value1
	 * @param mixed $value2
	 *
	 * @return array|bool
	 * @see TextData\Text::exact()
	 *
	 * @deprecated 1.18.0
	 *      Use the exact() method in the TextData\Text class instead
	 */
	public static function EXACT( $value1, $value2 ) {
		return TextData\Text::exact( $value1, $value2 );
	}

	/**
	 * TEXTJOIN.
	 *
	 * @param mixed $delimiter
	 * @param mixed $ignoreEmpty
	 * @param mixed $args
	 *
	 * @return array|string
	 * @deprecated 1.18.0
	 *      Use the TEXTJOIN() method in the TextData\Concatenate class instead
	 * @see TextData\Concatenate::TEXTJOIN()
	 *
	 */
	public static function TEXTJOIN( $delimiter, $ignoreEmpty, ...$args ) {
		return TextData\Concatenate::TEXTJOIN( $delimiter, $ignoreEmpty, ...$args );
	}

	/**
	 * REPT.
	 *
	 * Returns the result of builtin function repeat after validating args.
	 *
	 * @param array|string $str Should be numeric
	 * @param mixed $number Should be int
	 *
	 * @return array|string
	 * @see TextData\Concatenate::builtinREPT()
	 *
	 * @deprecated 1.18.0
	 *      Use the builtinREPT() method in the TextData\Concatenate class instead
	 */
	public static function builtinREPT( $str, $number ) {
		return TextData\Concatenate::builtinREPT( $str, $number );
	}
}
