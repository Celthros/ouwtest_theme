<?php

namespace PhpOffice\PhpSpreadsheet\Calculation;

/**
 * @deprecated 1.18.0
 */
class MathTrig {
	/**
	 * ARABIC.
	 *
	 * Converts a Roman numeral to an Arabic numeral.
	 *
	 * Excel Function:
	 *        ARABIC(text)
	 *
	 * @param array|string $roman
	 *
	 * @return array|int|string the arabic numberal contrived from the roman numeral
	 * @deprecated 1.18.0
	 *      Use the evaluate method in the MathTrig\Arabic class instead
	 * @see MathTrig\Arabic::evaluate()
	 *
	 */
	public static function ARABIC( $roman ) {
		return MathTrig\Arabic::evaluate( $roman );
	}

	/**
	 * ATAN2.
	 *
	 * This function calculates the arc tangent of the two variables x and y. It is similar to
	 *        calculating the arc tangent of y ÷ x, except that the signs of both arguments are used
	 *        to determine the quadrant of the result.
	 * The arctangent is the angle from the x-axis to a line containing the origin (0, 0) and a
	 *        point with coordinates (xCoordinate, yCoordinate). The angle is given in radians between
	 *        -pi and pi, excluding -pi.
	 *
	 * Note that the Excel ATAN2() function accepts its arguments in the reverse order to the standard
	 *        PHP atan2() function, so we need to reverse them here before calling the PHP atan() function.
	 *
	 * Excel Function:
	 *        ATAN2(xCoordinate,yCoordinate)
	 *
	 * @param array|float $xCoordinate the x-coordinate of the point
	 * @param array|float $yCoordinate the y-coordinate of the point
	 *
	 * @return array|float|string the inverse tangent of the specified x- and y-coordinates, or a string containing an error
	 * @see MathTrig\Trig\Tangent::atan2()
	 *
	 * @deprecated 1.18.0
	 *      Use the atan2 method in the MathTrig\Trig\Tangent class instead
	 */
	public static function ATAN2( $xCoordinate = null, $yCoordinate = null ) {
		return MathTrig\Trig\Tangent::atan2( $xCoordinate, $yCoordinate );
	}

	/**
	 * BASE.
	 *
	 * Converts a number into a text representation with the given radix (base).
	 *
	 * Excel Function:
	 *        BASE(Number, Radix [Min_length])
	 *
	 * @param float $number
	 * @param float $radix
	 * @param int $minLength
	 *
	 * @return array|string the text representation with the given radix (base)
	 * @deprecated 1.18.0
	 *      Use the evaluate method in the MathTrig\Base class instead
	 * @see MathTrig\Base::evaluate()
	 *
	 */
	public static function BASE( $number, $radix, $minLength = null ) {
		return MathTrig\Base::evaluate( $number, $radix, $minLength );
	}

	/**
	 * CEILING.
	 *
	 * Returns number rounded up, away from zero, to the nearest multiple of significance.
	 *        For example, if you want to avoid using pennies in your prices and your product is
	 *        priced at $4.42, use the formula =CEILING(4.42,0.05) to round prices up to the
	 *        nearest nickel.
	 *
	 * Excel Function:
	 *        CEILING(number[,significance])
	 *
	 * @param float $number the number you want to round
	 * @param float $significance the multiple to which you want to round
	 *
	 * @return array|float|string Rounded Number, or a string containing an error
	 * @see MathTrig\Ceiling::ceiling()
	 *
	 * @deprecated 1.17.0
	 *      Use the ceiling() method in the MathTrig\Ceiling class instead
	 */
	public static function CEILING( $number, $significance = null ) {
		return MathTrig\Ceiling::ceiling( $number, $significance );
	}

	/**
	 * COMBIN.
	 *
	 * Returns the number of combinations for a given number of items. Use COMBIN to
	 *        determine the total possible number of groups for a given number of items.
	 *
	 * Excel Function:
	 *        COMBIN(numObjs,numInSet)
	 *
	 * @param array|int $numObjs Number of different objects
	 * @param array|int $numInSet Number of objects in each combination
	 *
	 * @return array|float|int|string Number of combinations, or a string containing an error
	 * @see MathTrig\Combinations::withoutRepetition()
	 *
	 * @deprecated 1.18.0
	 *      Use the withoutRepetition() method in the MathTrig\Combinations class instead
	 */
	public static function COMBIN( $numObjs, $numInSet ) {
		return MathTrig\Combinations::withoutRepetition( $numObjs, $numInSet );
	}

	/**
	 * EVEN.
	 *
	 * Returns number rounded up to the nearest even integer.
	 * You can use this function for processing items that come in twos. For example,
	 *        a packing crate accepts rows of one or two items. The crate is full when
	 *        the number of items, rounded up to the nearest two, matches the crate's
	 *        capacity.
	 *
	 * Excel Function:
	 *        EVEN(number)
	 *
	 * @param array|float $number Number to round
	 *
	 * @return array|float|int|string Rounded Number, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the even() method in the MathTrig\Round class instead
	 * @see MathTrig\Round::even()
	 *
	 */
	public static function EVEN( $number ) {
		return MathTrig\Round::even( $number );
	}

	/**
	 * Helper function for Even.
	 *
	 * @deprecated 1.18.0
	 *      Use the evaluate() method in the MathTrig\Helpers class instead
	 * @see MathTrig\Helpers::getEven()
	 */
	public static function getEven( float $number ): int {
		return (int) MathTrig\Helpers::getEven( $number );
	}

	/**
	 * FACT.
	 *
	 * Returns the factorial of a number.
	 * The factorial of a number is equal to 1*2*3*...* number.
	 *
	 * Excel Function:
	 *        FACT(factVal)
	 *
	 * @param array|float $factVal Factorial Value
	 *
	 * @return array|float|int|string Factorial, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the fact() method in the MathTrig\Factorial class instead
	 * @see MathTrig\Factorial::fact()
	 *
	 */
	public static function FACT( $factVal ) {
		return MathTrig\Factorial::fact( $factVal );
	}

	/**
	 * FACTDOUBLE.
	 *
	 * Returns the double factorial of a number.
	 *
	 * Excel Function:
	 *        FACTDOUBLE(factVal)
	 *
	 * @param array|float $factVal Factorial Value
	 *
	 * @return array|float|int|string Double Factorial, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the factDouble() method in the MathTrig\Factorial class instead
	 * @see MathTrig\Factorial::factDouble()
	 *
	 */
	public static function FACTDOUBLE( $factVal ) {
		return MathTrig\Factorial::factDouble( $factVal );
	}

	/**
	 * FLOOR.
	 *
	 * Rounds number down, toward zero, to the nearest multiple of significance.
	 *
	 * Excel Function:
	 *        FLOOR(number[,significance])
	 *
	 * @param float $number Number to round
	 * @param float $significance Significance
	 *
	 * @return array|float|string Rounded Number, or a string containing an error
	 * @see MathTrig\Floor::floor()
	 *
	 * @deprecated 1.17.0
	 *      Use the floor() method in the MathTrig\Floor class instead
	 */
	public static function FLOOR( $number, $significance = null ) {
		return MathTrig\Floor::floor( $number, $significance );
	}

	/**
	 * FLOOR.MATH.
	 *
	 * Round a number down to the nearest integer or to the nearest multiple of significance.
	 *
	 * Excel Function:
	 *        FLOOR.MATH(number[,significance[,mode]])
	 *
	 * @param float $number Number to round
	 * @param float $significance Significance
	 * @param int $mode direction to round negative numbers
	 *
	 * @return array|float|string Rounded Number, or a string containing an error
	 * @deprecated 1.17.0
	 *      Use the math() method in the MathTrig\Floor class instead
	 * @see MathTrig\Floor::math()
	 *
	 */
	public static function FLOORMATH( $number, $significance = null, $mode = 0 ) {
		return MathTrig\Floor::math( $number, $significance, $mode );
	}

	/**
	 * FLOOR.PRECISE.
	 *
	 * Rounds number down, toward zero, to the nearest multiple of significance.
	 *
	 * Excel Function:
	 *        FLOOR.PRECISE(number[,significance])
	 *
	 * @param float $number Number to round
	 * @param float $significance Significance
	 *
	 * @return array|float|string Rounded Number, or a string containing an error
	 * @see MathTrig\Floor::precise()
	 *
	 * @deprecated 1.17.0
	 *      Use the precise() method in the MathTrig\Floor class instead
	 */
	public static function FLOORPRECISE( $number, $significance = 1 ) {
		return MathTrig\Floor::precise( $number, $significance );
	}

	/**
	 * INT.
	 *
	 * Casts a floating point value to an integer
	 *
	 * Excel Function:
	 *        INT(number)
	 *
	 * @param array|float $number Number to cast to an integer
	 *
	 * @return array|int|string Integer value, or a string containing an error
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the MathTrig\IntClass class instead
	 * @see MathTrig\IntClass::evaluate()
	 *
	 */
	public static function INT( $number ) {
		return MathTrig\IntClass::evaluate( $number );
	}

	/**
	 * GCD.
	 *
	 * Returns the greatest common divisor of a series of numbers.
	 * The greatest common divisor is the largest integer that divides both
	 *        number1 and number2 without a remainder.
	 *
	 * Excel Function:
	 *        GCD(number1[,number2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return int|mixed|string Greatest Common Divisor, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the evaluate() method in the MathTrig\Gcd class instead
	 * @see MathTrig\Gcd::evaluate()
	 *
	 */
	public static function GCD( ...$args ) {
		return MathTrig\Gcd::evaluate( ...$args );
	}

	/**
	 * LCM.
	 *
	 * Returns the lowest common multiplier of a series of numbers
	 * The least common multiple is the smallest positive integer that is a multiple
	 * of all integer arguments number1, number2, and so on. Use LCM to add fractions
	 * with different denominators.
	 *
	 * Excel Function:
	 *        LCM(number1[,number2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return int|string Lowest Common Multiplier, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the evaluate() method in the MathTrig\Lcm class instead
	 * @see MathTrig\Lcm::evaluate()
	 *
	 */
	public static function LCM( ...$args ) {
		return MathTrig\Lcm::evaluate( ...$args );
	}

	/**
	 * LOG_BASE.
	 *
	 * Returns the logarithm of a number to a specified base. The default base is 10.
	 *
	 * Excel Function:
	 *        LOG(number[,base])
	 *
	 * @param float $number The positive real number for which you want the logarithm
	 * @param float $base The base of the logarithm. If base is omitted, it is assumed to be 10.
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @see MathTrig\Logarithms::withBase()
	 *
	 * @deprecated 1.18.0
	 *      Use the withBase() method in the MathTrig\Logarithms class instead
	 */
	public static function logBase( $number, $base = 10 ) {
		return MathTrig\Logarithms::withBase( $number, $base );
	}

	/**
	 * MDETERM.
	 *
	 * Returns the matrix determinant of an array.
	 *
	 * Excel Function:
	 *        MDETERM(array)
	 *
	 * @param array $matrixValues A matrix of values
	 *
	 * @return float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the determinant() method in the MathTrig\MatrixFunctions class instead
	 * @see MathTrig\MatrixFunctions::determinant()
	 *
	 */
	public static function MDETERM( $matrixValues ) {
		return MathTrig\MatrixFunctions::determinant( $matrixValues );
	}

	/**
	 * MINVERSE.
	 *
	 * Returns the inverse matrix for the matrix stored in an array.
	 *
	 * Excel Function:
	 *        MINVERSE(array)
	 *
	 * @param array $matrixValues A matrix of values
	 *
	 * @return array|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the inverse() method in the MathTrig\MatrixFunctions class instead
	 * @see MathTrig\MatrixFunctions::inverse()
	 *
	 */
	public static function MINVERSE( $matrixValues ) {
		return MathTrig\MatrixFunctions::inverse( $matrixValues );
	}

	/**
	 * MMULT.
	 *
	 * @param array $matrixData1 A matrix of values
	 * @param array $matrixData2 A matrix of values
	 *
	 * @return array|string The result, or a string containing an error
	 * @see MathTrig\MatrixFunctions::multiply()
	 *
	 * @deprecated 1.18.0
	 *      Use the multiply() method in the MathTrig\MatrixFunctions class instead
	 */
	public static function MMULT( $matrixData1, $matrixData2 ) {
		return MathTrig\MatrixFunctions::multiply( $matrixData1, $matrixData2 );
	}

	/**
	 * MOD.
	 *
	 * @param int $a Dividend
	 * @param int $b Divisor
	 *
	 * @return array|float|int|string Remainder, or a string containing an error
	 * @see MathTrig\Operations::mod()
	 *
	 * @deprecated 1.18.0
	 *      Use the mod() method in the MathTrig\Operations class instead
	 */
	public static function MOD( $a = 1, $b = 1 ) {
		return MathTrig\Operations::mod( $a, $b );
	}

	/**
	 * MROUND.
	 *
	 * Rounds a number to the nearest multiple of a specified value
	 *
	 * @param float $number Number to round
	 * @param array|int $multiple Multiple to which you want to round $number
	 *
	 * @return array|float|string Rounded Number, or a string containing an error
	 * @see MathTrig\Round::multiple()
	 *
	 * @deprecated 1.17.0
	 *      Use the multiple() method in the MathTrig\Mround class instead
	 */
	public static function MROUND( $number, $multiple ) {
		return MathTrig\Round::multiple( $number, $multiple );
	}

	/**
	 * MULTINOMIAL.
	 *
	 * Returns the ratio of the factorial of a sum of values to the product of factorials.
	 *
	 * @param mixed[] $args An array of mixed values for the Data Series
	 *
	 * @return float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the multinomial method in the MathTrig\Factorial class instead
	 * @see MathTrig\Factorial::multinomial()
	 *
	 */
	public static function MULTINOMIAL( ...$args ) {
		return MathTrig\Factorial::multinomial( ...$args );
	}

	/**
	 * ODD.
	 *
	 * Returns number rounded up to the nearest odd integer.
	 *
	 * @param array|float $number Number to round
	 *
	 * @return array|float|int|string Rounded Number, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the odd method in the MathTrig\Round class instead
	 * @see MathTrig\Round::odd()
	 *
	 */
	public static function ODD( $number ) {
		return MathTrig\Round::odd( $number );
	}

	/**
	 * POWER.
	 *
	 * Computes x raised to the power y.
	 *
	 * @param float $x
	 * @param float $y
	 *
	 * @return array|float|int|string The result, or a string containing an error
	 * @see MathTrig\Operations::power()
	 *
	 * @deprecated 1.18.0
	 *      Use the evaluate method in the MathTrig\Power class instead
	 */
	public static function POWER( $x = 0, $y = 2 ) {
		return MathTrig\Operations::power( $x, $y );
	}

	/**
	 * PRODUCT.
	 *
	 * PRODUCT returns the product of all the values and cells referenced in the argument list.
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the product method in the MathTrig\Operations class instead
	 * @see MathTrig\Operations::product()
	 *
	 * Excel Function:
	 *        PRODUCT(value1[,value2[, ...]])
	 *
	 */
	public static function PRODUCT( ...$args ) {
		return MathTrig\Operations::product( ...$args );
	}

	/**
	 * QUOTIENT.
	 *
	 * QUOTIENT function returns the integer portion of a division. Numerator is the divided number
	 *        and denominator is the divisor.
	 *
	 * @param mixed $numerator
	 * @param mixed $denominator
	 *
	 * @return array|int|string
	 * @see MathTrig\Operations::quotient()
	 *
	 * Excel Function:
	 *        QUOTIENT(value1[,value2[, ...]])
	 *
	 * @deprecated 1.18.0
	 *      Use the quotient method in the MathTrig\Operations class instead
	 */
	public static function QUOTIENT( $numerator, $denominator ) {
		return MathTrig\Operations::quotient( $numerator, $denominator );
	}

	/**
	 * RAND/RANDBETWEEN.
	 *
	 * @param int $min Minimal value
	 * @param int $max Maximal value
	 *
	 * @return array|float|int|string Random number
	 * @see MathTrig\Random::randBetween()
	 *
	 * @deprecated 1.18.0
	 *      Use the randBetween or randBetween method in the MathTrig\Random class instead
	 */
	public static function RAND( $min = 0, $max = 0 ) {
		return MathTrig\Random::randBetween( $min, $max );
	}

	/**
	 * ROMAN.
	 *
	 * Converts a number to Roman numeral
	 *
	 * @param mixed $aValue Number to convert
	 * @param mixed $style Number indicating one of five possible forms
	 *
	 * @return array|string Roman numeral, or a string containing an error
	 * @see MathTrig\Roman::evaluate()
	 *
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the MathTrig\Roman class instead
	 */
	public static function ROMAN( $aValue, $style = 0 ) {
		return MathTrig\Roman::evaluate( $aValue, $style );
	}

	/**
	 * ROUNDUP.
	 *
	 * Rounds a number up to a specified number of decimal places
	 *
	 * @param array|float $number Number to round
	 * @param array|int $digits Number of digits to which you want to round $number
	 *
	 * @return array|float|string Rounded Number, or a string containing an error
	 * @see MathTrig\Round::up()
	 *
	 * @deprecated 1.17.0
	 *      Use the up() method in the MathTrig\Round class instead
	 */
	public static function ROUNDUP( $number, $digits ) {
		return MathTrig\Round::up( $number, $digits );
	}

	/**
	 * ROUNDDOWN.
	 *
	 * Rounds a number down to a specified number of decimal places
	 *
	 * @param array|float $number Number to round
	 * @param array|int $digits Number of digits to which you want to round $number
	 *
	 * @return array|float|string Rounded Number, or a string containing an error
	 * @see MathTrig\Round::down()
	 *
	 * @deprecated 1.17.0
	 *      Use the down() method in the MathTrig\Round class instead
	 */
	public static function ROUNDDOWN( $number, $digits ) {
		return MathTrig\Round::down( $number, $digits );
	}

	/**
	 * SERIESSUM.
	 *
	 * Returns the sum of a power series
	 *
	 * @param mixed $x Input value
	 * @param mixed $n Initial power
	 * @param mixed $m Step
	 * @param mixed[] $args An array of coefficients for the Data Series
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @see MathTrig\SeriesSum::evaluate()
	 *
	 * @deprecated 1.18.0
	 *      Use the evaluate method in the MathTrig\SeriesSum class instead
	 */
	public static function SERIESSUM( $x, $n, $m, ...$args ) {
		return MathTrig\SeriesSum::evaluate( $x, $n, $m, ...$args );
	}

	/**
	 * SIGN.
	 *
	 * Determines the sign of a number. Returns 1 if the number is positive, zero (0)
	 *        if the number is 0, and -1 if the number is negative.
	 *
	 * @param array|float $number Number to round
	 *
	 * @return array|int|string sign value, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the evaluate method in the MathTrig\Sign class instead
	 * @see MathTrig\Sign::evaluate()
	 *
	 */
	public static function SIGN( $number ) {
		return MathTrig\Sign::evaluate( $number );
	}

	/**
	 * returnSign = returns 0/-1/+1.
	 *
	 * @deprecated 1.18.0
	 *      Use the returnSign method in the MathTrig\Helpers class instead
	 * @see MathTrig\Helpers::returnSign()
	 */
	public static function returnSign( float $number ): int {
		return MathTrig\Helpers::returnSign( $number );
	}

	/**
	 * SQRTPI.
	 *
	 * Returns the square root of (number * pi).
	 *
	 * @param array|float $number Number
	 *
	 * @return array|float|string Square Root of Number * Pi, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the pi method in the MathTrig\Sqrt class instead
	 * @see MathTrig\Sqrt::sqrt()
	 *
	 */
	public static function SQRTPI( $number ) {
		return MathTrig\Sqrt::pi( $number );
	}

	/**
	 * SUBTOTAL.
	 *
	 * Returns a subtotal in a list or database.
	 *
	 * @param int $functionType
	 *            A number 1 to 11 that specifies which function to
	 *                    use in calculating subtotals within a range
	 *                    list
	 *            Numbers 101 to 111 shadow the functions of 1 to 11
	 *                    but ignore any values in the range that are
	 *                    in hidden rows or columns
	 * @param mixed[] $args A mixed data series of values
	 *
	 * @return float|string
	 * @see MathTrig\Subtotal::evaluate()
	 *
	 * @deprecated 1.18.0
	 *      Use the evaluate method in the MathTrig\Subtotal class instead
	 */
	public static function SUBTOTAL( $functionType, ...$args ) {
		return MathTrig\Subtotal::evaluate( $functionType, ...$args );
	}

	/**
	 * SUM.
	 *
	 * SUM computes the sum of all the values and cells referenced in the argument list.
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the sumErroringStrings method in the MathTrig\Sum class instead
	 * @see MathTrig\Sum::sumErroringStrings()
	 *
	 * Excel Function:
	 *        SUM(value1[,value2[, ...]])
	 *
	 */
	public static function SUM( ...$args ) {
		return MathTrig\Sum::sumIgnoringStrings( ...$args );
	}

	/**
	 * SUMIF.
	 *
	 * Totals the values of cells that contain numbers within the list of arguments
	 *
	 * Excel Function:
	 *        SUMIF(range, criteria, [sum_range])
	 *
	 * @param mixed $range Data values
	 * @param string $criteria the criteria that defines which cells will be summed
	 * @param mixed $sumRange
	 *
	 * @return null|float|string
	 * @deprecated 1.17.0
	 *      Use the SUMIF() method in the Statistical\Conditional class instead
	 * @see Statistical\Conditional::SUMIF()
	 *
	 */
	public static function SUMIF( $range, $criteria, $sumRange = [] ) {
		return Statistical\Conditional::SUMIF( $range, $criteria, $sumRange );
	}

	/**
	 * SUMIFS.
	 *
	 *    Totals the values of cells that contain numbers within the list of arguments
	 *
	 *    Excel Function:
	 *        SUMIFS(sum_range, criteria_range1, criteria1, [criteria_range2, criteria2], ...)
	 *
	 * @param mixed $args Data values
	 *
	 * @return null|float|string
	 * @deprecated 1.17.0
	 *      Use the SUMIFS() method in the Statistical\Conditional class instead
	 * @see Statistical\Conditional::SUMIFS()
	 *
	 */
	public static function SUMIFS( ...$args ) {
		return Statistical\Conditional::SUMIFS( ...$args );
	}

	/**
	 * SUMPRODUCT.
	 *
	 * Excel Function:
	 *        SUMPRODUCT(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the product method in the MathTrig\Sum class instead
	 * @see MathTrig\Sum::product()
	 *
	 */
	public static function SUMPRODUCT( ...$args ) {
		return MathTrig\Sum::product( ...$args );
	}

	/**
	 * SUMSQ.
	 *
	 * SUMSQ returns the sum of the squares of the arguments
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the sumSquare method in the MathTrig\SumSquares class instead
	 * @see MathTrig\SumSquares::sumSquare()
	 *
	 * Excel Function:
	 *        SUMSQ(value1[,value2[, ...]])
	 *
	 */
	public static function SUMSQ( ...$args ) {
		return MathTrig\SumSquares::sumSquare( ...$args );
	}

	/**
	 * SUMX2MY2.
	 *
	 * @param mixed[] $matrixData1 Matrix #1
	 * @param mixed[] $matrixData2 Matrix #2
	 *
	 * @return float|string
	 * @see MathTrig\SumSquares::sumXSquaredMinusYSquared()
	 *
	 * @deprecated 1.18.0
	 *     Use the sumXSquaredMinusYSquared method in the MathTrig\SumSquares class instead
	 */
	public static function SUMX2MY2( $matrixData1, $matrixData2 ) {
		return MathTrig\SumSquares::sumXSquaredMinusYSquared( $matrixData1, $matrixData2 );
	}

	/**
	 * SUMX2PY2.
	 *
	 * @param mixed[] $matrixData1 Matrix #1
	 * @param mixed[] $matrixData2 Matrix #2
	 *
	 * @return float|string
	 * @see MathTrig\SumSquares::sumXSquaredPlusYSquared()
	 *
	 * @deprecated 1.18.0
	 *     Use the sumXSquaredPlusYSquared method in the MathTrig\SumSquares class instead
	 */
	public static function SUMX2PY2( $matrixData1, $matrixData2 ) {
		return MathTrig\SumSquares::sumXSquaredPlusYSquared( $matrixData1, $matrixData2 );
	}

	/**
	 * SUMXMY2.
	 *
	 * @param mixed[] $matrixData1 Matrix #1
	 * @param mixed[] $matrixData2 Matrix #2
	 *
	 * @return float|string
	 * @see MathTrig\SumSquares::sumXMinusYSquared()
	 *
	 * @deprecated 1.18.0
	 *      Use the sumXMinusYSquared method in the MathTrig\SumSquares class instead
	 */
	public static function SUMXMY2( $matrixData1, $matrixData2 ) {
		return MathTrig\SumSquares::sumXMinusYSquared( $matrixData1, $matrixData2 );
	}

	/**
	 * TRUNC.
	 *
	 * Truncates value to the number of fractional digits by number_digits.
	 *
	 * @param float $value
	 * @param int $digits
	 *
	 * @return array|float|string Truncated value, or a string containing an error
	 * @see MathTrig\Trunc::evaluate()
	 *
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the MathTrig\Trunc class instead
	 */
	public static function TRUNC( $value = 0, $digits = 0 ) {
		return MathTrig\Trunc::evaluate( $value, $digits );
	}

	/**
	 * SEC.
	 *
	 * Returns the secant of an angle.
	 *
	 * @param array|float $angle Number
	 *
	 * @return array|float|string The secant of the angle
	 * @deprecated 1.18.0
	 *      Use the sec method in the MathTrig\Trig\Secant class instead
	 * @see MathTrig\Trig\Secant::sec()
	 *
	 */
	public static function SEC( $angle ) {
		return MathTrig\Trig\Secant::sec( $angle );
	}

	/**
	 * SECH.
	 *
	 * Returns the hyperbolic secant of an angle.
	 *
	 * @param array|float $angle Number
	 *
	 * @return array|float|string The hyperbolic secant of the angle
	 * @deprecated 1.18.0
	 *      Use the sech method in the MathTrig\Trig\Secant class instead
	 * @see MathTrig\Trig\Secant::sech()
	 *
	 */
	public static function SECH( $angle ) {
		return MathTrig\Trig\Secant::sech( $angle );
	}

	/**
	 * CSC.
	 *
	 * Returns the cosecant of an angle.
	 *
	 * @param array|float $angle Number
	 *
	 * @return array|float|string The cosecant of the angle
	 * @deprecated 1.18.0
	 *      Use the csc method in the MathTrig\Trig\Cosecant class instead
	 * @see MathTrig\Trig\Cosecant::csc()
	 *
	 */
	public static function CSC( $angle ) {
		return MathTrig\Trig\Cosecant::csc( $angle );
	}

	/**
	 * CSCH.
	 *
	 * Returns the hyperbolic cosecant of an angle.
	 *
	 * @param array|float $angle Number
	 *
	 * @return array|float|string The hyperbolic cosecant of the angle
	 * @deprecated 1.18.0
	 *      Use the csch method in the MathTrig\Trig\Cosecant class instead
	 * @see MathTrig\Trig\Cosecant::csch()
	 *
	 */
	public static function CSCH( $angle ) {
		return MathTrig\Trig\Cosecant::csch( $angle );
	}

	/**
	 * COT.
	 *
	 * Returns the cotangent of an angle.
	 *
	 * @param array|float $angle Number
	 *
	 * @return array|float|string The cotangent of the angle
	 * @deprecated 1.18.0
	 *      Use the cot method in the MathTrig\Trig\Cotangent class instead
	 * @see MathTrig\Trig\Cotangent::cot()
	 *
	 */
	public static function COT( $angle ) {
		return MathTrig\Trig\Cotangent::cot( $angle );
	}

	/**
	 * COTH.
	 *
	 * Returns the hyperbolic cotangent of an angle.
	 *
	 * @param array|float $angle Number
	 *
	 * @return array|float|string The hyperbolic cotangent of the angle
	 * @deprecated 1.18.0
	 *      Use the coth method in the MathTrig\Trig\Cotangent class instead
	 * @see MathTrig\Trig\Cotangent::coth()
	 *
	 */
	public static function COTH( $angle ) {
		return MathTrig\Trig\Cotangent::coth( $angle );
	}

	/**
	 * ACOT.
	 *
	 * Returns the arccotangent of a number.
	 *
	 * @param array|float $number Number
	 *
	 * @return array|float|string The arccotangent of the number
	 * @deprecated 1.18.0
	 *      Use the acot method in the MathTrig\Trig\Cotangent class instead
	 * @see MathTrig\Trig\Cotangent::acot()
	 *
	 */
	public static function ACOT( $number ) {
		return MathTrig\Trig\Cotangent::acot( $number );
	}

	/**
	 * Return NAN or value depending on argument.
	 *
	 * @param float $result Number
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the numberOrNan method in the MathTrig\Helpers class instead
	 * @see MathTrig\Helpers::numberOrNan()
	 *
	 */
	public static function numberOrNan( $result ) {
		return MathTrig\Helpers::numberOrNan( $result );
	}

	/**
	 * ACOTH.
	 *
	 * Returns the hyperbolic arccotangent of a number.
	 *
	 * @param array|float $number Number
	 *
	 * @return array|float|string The hyperbolic arccotangent of the number
	 * @deprecated 1.18.0
	 *      Use the acoth method in the MathTrig\Trig\Cotangent class instead
	 * @see MathTrig\Trig\Cotangent::acoth()
	 *
	 */
	public static function ACOTH( $number ) {
		return MathTrig\Trig\Cotangent::acoth( $number );
	}

	/**
	 * ROUND.
	 *
	 * Returns the result of builtin function round after validating args.
	 *
	 * @param array|mixed $number Should be numeric
	 * @param array|mixed $precision Should be int
	 *
	 * @return array|float|string Rounded number
	 * @see MathTrig\Round::round()
	 *
	 * @deprecated 1.17.0
	 *      Use the round() method in the MathTrig\Round class instead
	 */
	public static function builtinROUND( $number, $precision ) {
		return MathTrig\Round::round( $number, $precision );
	}

	/**
	 * ABS.
	 *
	 * Returns the result of builtin function abs after validating args.
	 *
	 * @param array|mixed $number Should be numeric
	 *
	 * @return array|float|int|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the evaluate method in the MathTrig\Absolute class instead
	 * @see MathTrig\Absolute::evaluate()
	 *
	 */
	public static function builtinABS( $number ) {
		return MathTrig\Absolute::evaluate( $number );
	}

	/**
	 * ACOS.
	 *
	 * @param array|float $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the acos method in the MathTrig\Trig\Cosine class instead
	 * @see MathTrig\Trig\Cosine::acos()
	 *
	 * Returns the result of builtin function acos after validating args.
	 *
	 */
	public static function builtinACOS( $number ) {
		return MathTrig\Trig\Cosine::acos( $number );
	}

	/**
	 * ACOSH.
	 *
	 * Returns the result of builtin function acosh after validating args.
	 *
	 * @param array|float $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the acosh method in the MathTrig\Trig\Cosine class instead
	 * @see MathTrig\Trig\Cosine::acosh()
	 *
	 */
	public static function builtinACOSH( $number ) {
		return MathTrig\Trig\Cosine::acosh( $number );
	}

	/**
	 * ASIN.
	 *
	 * Returns the result of builtin function asin after validating args.
	 *
	 * @param array|float $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the asin method in the MathTrig\Trig\Sine class instead
	 * @see MathTrig\Trig\Sine::asin()
	 *
	 */
	public static function builtinASIN( $number ) {
		return MathTrig\Trig\Sine::asin( $number );
	}

	/**
	 * ASINH.
	 *
	 * Returns the result of builtin function asinh after validating args.
	 *
	 * @param array|float $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the asinh method in the MathTrig\Trig\Sine class instead
	 * @see MathTrig\Trig\Sine::asinh()
	 *
	 */
	public static function builtinASINH( $number ) {
		return MathTrig\Trig\Sine::asinh( $number );
	}

	/**
	 * ATAN.
	 *
	 * Returns the result of builtin function atan after validating args.
	 *
	 * @param array|float $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the atan method in the MathTrig\Trig\Tangent class instead
	 * @see MathTrig\Trig\Tangent::atan()
	 *
	 */
	public static function builtinATAN( $number ) {
		return MathTrig\Trig\Tangent::atan( $number );
	}

	/**
	 * ATANH.
	 *
	 * Returns the result of builtin function atanh after validating args.
	 *
	 * @param array|float $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the atanh method in the MathTrig\Trig\Tangent class instead
	 * @see MathTrig\Trig\Tangent::atanh()
	 *
	 */
	public static function builtinATANH( $number ) {
		return MathTrig\Trig\Tangent::atanh( $number );
	}

	/**
	 * COS.
	 *
	 * Returns the result of builtin function cos after validating args.
	 *
	 * @param array|mixed $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the cos method in the MathTrig\Trig\Cosine class instead
	 * @see MathTrig\Trig\Cosine::cos()
	 *
	 */
	public static function builtinCOS( $number ) {
		return MathTrig\Trig\Cosine::cos( $number );
	}

	/**
	 * COSH.
	 *
	 * Returns the result of builtin function cos after validating args.
	 *
	 * @param array|mixed $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the cosh method in the MathTrig\Trig\Cosine class instead
	 * @see MathTrig\Trig\Cosine::cosh()
	 *
	 */
	public static function builtinCOSH( $number ) {
		return MathTrig\Trig\Cosine::cosh( $number );
	}

	/**
	 * DEGREES.
	 *
	 * Returns the result of builtin function rad2deg after validating args.
	 *
	 * @param array|mixed $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the toDegrees method in the MathTrig\Angle class instead
	 * @see MathTrig\Angle::toDegrees()
	 *
	 */
	public static function builtinDEGREES( $number ) {
		return MathTrig\Angle::toDegrees( $number );
	}

	/**
	 * EXP.
	 *
	 * Returns the result of builtin function exp after validating args.
	 *
	 * @param array|mixed $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the evaluate method in the MathTrig\Exp class instead
	 * @see MathTrig\Exp::evaluate()
	 *
	 */
	public static function builtinEXP( $number ) {
		return MathTrig\Exp::evaluate( $number );
	}

	/**
	 * LN.
	 *
	 * Returns the result of builtin function log after validating args.
	 *
	 * @param mixed $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the natural method in the MathTrig\Logarithms class instead
	 * @see MathTrig\Logarithms::natural()
	 *
	 */
	public static function builtinLN( $number ) {
		return MathTrig\Logarithms::natural( $number );
	}

	/**
	 * LOG10.
	 *
	 * Returns the result of builtin function log after validating args.
	 *
	 * @param mixed $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the natural method in the MathTrig\Logarithms class instead
	 * @see MathTrig\Logarithms::base10()
	 *
	 */
	public static function builtinLOG10( $number ) {
		return MathTrig\Logarithms::base10( $number );
	}

	/**
	 * RADIANS.
	 *
	 * Returns the result of builtin function deg2rad after validating args.
	 *
	 * @param array|mixed $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the toRadians method in the MathTrig\Angle class instead
	 * @see MathTrig\Angle::toRadians()
	 *
	 */
	public static function builtinRADIANS( $number ) {
		return MathTrig\Angle::toRadians( $number );
	}

	/**
	 * SIN.
	 *
	 * Returns the result of builtin function sin after validating args.
	 *
	 * @param array|mixed $number Should be numeric
	 *
	 * @return array|float|string sine
	 * @deprecated 1.18.0
	 *      Use the sin method in the MathTrig\Trig\Sine class instead
	 * @see MathTrig\Trig\Sine::evaluate()
	 *
	 */
	public static function builtinSIN( $number ) {
		return MathTrig\Trig\Sine::sin( $number );
	}

	/**
	 * SINH.
	 *
	 * Returns the result of builtin function sinh after validating args.
	 *
	 * @param array|mixed $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the sinh method in the MathTrig\Trig\Sine class instead
	 * @see MathTrig\Trig\Sine::sinh()
	 *
	 */
	public static function builtinSINH( $number ) {
		return MathTrig\Trig\Sine::sinh( $number );
	}

	/**
	 * SQRT.
	 *
	 * Returns the result of builtin function sqrt after validating args.
	 *
	 * @param array|mixed $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the sqrt method in the MathTrig\Sqrt class instead
	 * @see MathTrig\Sqrt::sqrt()
	 *
	 */
	public static function builtinSQRT( $number ) {
		return MathTrig\Sqrt::sqrt( $number );
	}

	/**
	 * TAN.
	 *
	 * Returns the result of builtin function tan after validating args.
	 *
	 * @param array|mixed $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the tan method in the MathTrig\Trig\Tangent class instead
	 * @see MathTrig\Trig\Tangent::tan()
	 *
	 */
	public static function builtinTAN( $number ) {
		return MathTrig\Trig\Tangent::tan( $number );
	}

	/**
	 * TANH.
	 *
	 * Returns the result of builtin function sinh after validating args.
	 *
	 * @param array|mixed $number Should be numeric
	 *
	 * @return array|float|string Rounded number
	 * @deprecated 1.18.0
	 *      Use the tanh method in the MathTrig\Trig\Tangent class instead
	 * @see MathTrig\Trig\Tangent::tanh()
	 *
	 */
	public static function builtinTANH( $number ) {
		return MathTrig\Trig\Tangent::tanh( $number );
	}

	/**
	 * Many functions accept null/false/true argument treated as 0/0/1.
	 *
	 * @param mixed $number
	 *
	 * @see MathTrig\Helpers::validateNumericNullBool()
	 *
	 * @deprecated 1.18.0
	 *      Use the validateNumericNullBool method in the MathTrig\Helpers class instead
	 */
	public static function nullFalseTrueToNumber( &$number ): void {
		$number = Functions::flattenSingleValue( $number );
		if ( $number === null ) {
			$number = 0;
		} elseif ( is_bool( $number ) ) {
			$number = (int) $number;
		}
	}
}
