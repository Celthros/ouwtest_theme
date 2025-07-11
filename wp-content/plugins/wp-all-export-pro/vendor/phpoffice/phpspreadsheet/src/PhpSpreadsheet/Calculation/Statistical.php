<?php

namespace PhpOffice\PhpSpreadsheet\Calculation;

use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Averages;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Conditional;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Confidence;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Counts;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Maximum;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Minimum;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Permutations;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\StandardDeviations;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Trends;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Variances;

/**
 * @deprecated 1.18.0
 */
class Statistical {
	const LOG_GAMMA_X_MAX_VALUE = 2.55e305;
	const EPS = 2.22e-16;
	const MAX_VALUE = 1.2e308;
	const SQRT2PI = 2.5066282746310005024157652848110452530069867406099;

	/**
	 * AVEDEV.
	 *
	 * Returns the average of the absolute deviations of data points from their mean.
	 * AVEDEV is a measure of the variability in a data set.
	 *
	 * Excel Function:
	 *        AVEDEV(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string
	 * @deprecated 1.17.0
	 *      Use the averageDeviations() method in the Statistical\Averages class instead
	 * @see Statistical\Averages::averageDeviations()
	 *
	 */
	public static function AVEDEV( ...$args ) {
		return Averages::averageDeviations( ...$args );
	}

	/**
	 * AVERAGE.
	 *
	 * Returns the average (arithmetic mean) of the arguments
	 *
	 * Excel Function:
	 *        AVERAGE(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string
	 * @deprecated 1.17.0
	 *      Use the average() method in the Statistical\Averages class instead
	 * @see Statistical\Averages::average()
	 *
	 */
	public static function AVERAGE( ...$args ) {
		return Averages::average( ...$args );
	}

	/**
	 * AVERAGEA.
	 *
	 * Returns the average of its arguments, including numbers, text, and logical values
	 *
	 * Excel Function:
	 *        AVERAGEA(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string
	 * @deprecated 1.17.0
	 *      Use the averageA() method in the Statistical\Averages class instead
	 * @see Statistical\Averages::averageA()
	 *
	 */
	public static function AVERAGEA( ...$args ) {
		return Averages::averageA( ...$args );
	}

	/**
	 * AVERAGEIF.
	 *
	 * Returns the average value from a range of cells that contain numbers within the list of arguments
	 *
	 * Excel Function:
	 *        AVERAGEIF(value1[,value2[, ...]],condition)
	 *
	 * @param mixed $range Data values
	 * @param string $condition the criteria that defines which cells will be checked
	 * @param mixed[] $averageRange Data values
	 *
	 * @return null|float|string
	 * @deprecated 1.17.0
	 *      Use the AVERAGEIF() method in the Statistical\Conditional class instead
	 * @see Statistical\Conditional::AVERAGEIF()
	 *
	 */
	public static function AVERAGEIF( $range, $condition, $averageRange = [] ) {
		return Conditional::AVERAGEIF( $range, $condition, $averageRange );
	}

	/**
	 * BETADIST.
	 *
	 * Returns the beta distribution.
	 *
	 * @param float $value Value at which you want to evaluate the distribution
	 * @param float $alpha Parameter to the distribution
	 * @param float $beta Parameter to the distribution
	 * @param mixed $rMin
	 * @param mixed $rMax
	 *
	 * @return array|float|string
	 * @deprecated 1.18.0
	 *      Use the distribution() method in the Statistical\Distributions\Beta class instead
	 * @see Statistical\Distributions\Beta::distribution()
	 *
	 */
	public static function BETADIST( $value, $alpha, $beta, $rMin = 0, $rMax = 1 ) {
		return Statistical\Distributions\Beta::distribution( $value, $alpha, $beta, $rMin, $rMax );
	}

	/**
	 * BETAINV.
	 *
	 * Returns the inverse of the Beta distribution.
	 *
	 * @param float $probability Probability at which you want to evaluate the distribution
	 * @param float $alpha Parameter to the distribution
	 * @param float $beta Parameter to the distribution
	 * @param float $rMin Minimum value
	 * @param float $rMax Maximum value
	 *
	 * @return array|float|string
	 * @deprecated 1.18.0
	 *      Use the inverse() method in the Statistical\Distributions\Beta class instead
	 * @see Statistical\Distributions\Beta::inverse()
	 *
	 */
	public static function BETAINV( $probability, $alpha, $beta, $rMin = 0, $rMax = 1 ) {
		return Statistical\Distributions\Beta::inverse( $probability, $alpha, $beta, $rMin, $rMax );
	}

	/**
	 * BINOMDIST.
	 *
	 * Returns the individual term binomial distribution probability. Use BINOMDIST in problems with
	 *        a fixed number of tests or trials, when the outcomes of any trial are only success or failure,
	 *        when trials are independent, and when the probability of success is constant throughout the
	 *        experiment. For example, BINOMDIST can calculate the probability that two of the next three
	 *        babies born are male.
	 *
	 * @param mixed $value Number of successes in trials
	 * @param mixed $trials Number of trials
	 * @param mixed $probability Probability of success on each trial
	 * @param mixed $cumulative
	 *
	 * @return array|float|string
	 * @see Statistical\Distributions\Binomial::distribution()
	 *
	 * @deprecated 1.18.0
	 *      Use the distribution() method in the Statistical\Distributions\Binomial class instead
	 */
	public static function BINOMDIST( $value, $trials, $probability, $cumulative ) {
		return Statistical\Distributions\Binomial::distribution( $value, $trials, $probability, $cumulative );
	}

	/**
	 * CHIDIST.
	 *
	 * Returns the one-tailed probability of the chi-squared distribution.
	 *
	 * @param float $value Value for the function
	 * @param float $degrees degrees of freedom
	 *
	 * @return array|float|string
	 * @see Statistical\Distributions\ChiSquared::distributionRightTail()
	 *
	 * @deprecated 1.18.0
	 *      Use the distributionRightTail() method in the Statistical\Distributions\ChiSquared class instead
	 */
	public static function CHIDIST( $value, $degrees ) {
		return Statistical\Distributions\ChiSquared::distributionRightTail( $value, $degrees );
	}

	/**
	 * CHIINV.
	 *
	 * Returns the one-tailed probability of the chi-squared distribution.
	 *
	 * @param float $probability Probability for the function
	 * @param float $degrees degrees of freedom
	 *
	 * @return array|float|string
	 * @see Statistical\Distributions\ChiSquared::inverseRightTail()
	 *
	 * @deprecated 1.18.0
	 *      Use the inverseRightTail() method in the Statistical\Distributions\ChiSquared class instead
	 */
	public static function CHIINV( $probability, $degrees ) {
		return Statistical\Distributions\ChiSquared::inverseRightTail( $probability, $degrees );
	}

	/**
	 * CONFIDENCE.
	 *
	 * Returns the confidence interval for a population mean
	 *
	 * @param float $alpha
	 * @param float $stdDev Standard Deviation
	 * @param float $size
	 *
	 * @return array|float|string
	 * @deprecated 1.18.0
	 *      Use the CONFIDENCE() method in the Statistical\Confidence class instead
	 * @see Statistical\Confidence::CONFIDENCE()
	 *
	 */
	public static function CONFIDENCE( $alpha, $stdDev, $size ) {
		return Confidence::CONFIDENCE( $alpha, $stdDev, $size );
	}

	/**
	 * CORREL.
	 *
	 * Returns covariance, the average of the products of deviations for each data point pair.
	 *
	 * @param mixed $yValues array of mixed Data Series Y
	 * @param null|mixed $xValues array of mixed Data Series X
	 *
	 * @return float|string
	 * @see Statistical\Trends::CORREL()
	 *
	 * @deprecated 1.18.0
	 *      Use the CORREL() method in the Statistical\Trends class instead
	 */
	public static function CORREL( $yValues, $xValues = null ) {
		return Trends::CORREL( $xValues, $yValues );
	}

	/**
	 * COUNT.
	 *
	 * Counts the number of cells that contain numbers within the list of arguments
	 *
	 * Excel Function:
	 *        COUNT(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return int
	 * @deprecated 1.17.0
	 *      Use the COUNT() method in the Statistical\Counts class instead
	 * @see Statistical\Counts::COUNT()
	 *
	 */
	public static function COUNT( ...$args ) {
		return Counts::COUNT( ...$args );
	}

	/**
	 * COUNTA.
	 *
	 * Counts the number of cells that are not empty within the list of arguments
	 *
	 * Excel Function:
	 *        COUNTA(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return int
	 * @deprecated 1.17.0
	 *      Use the COUNTA() method in the Statistical\Counts class instead
	 * @see Statistical\Counts::COUNTA()
	 *
	 */
	public static function COUNTA( ...$args ) {
		return Counts::COUNTA( ...$args );
	}

	/**
	 * COUNTBLANK.
	 *
	 * Counts the number of empty cells within the list of arguments
	 *
	 * Excel Function:
	 *        COUNTBLANK(value1[,value2[, ...]])
	 *
	 * @param mixed $range Data values
	 *
	 * @return int
	 * @deprecated 1.17.0
	 *      Use the COUNTBLANK() method in the Statistical\Counts class instead
	 * @see Statistical\Counts::COUNTBLANK()
	 *
	 */
	public static function COUNTBLANK( $range ) {
		return Counts::COUNTBLANK( $range );
	}

	/**
	 * COUNTIF.
	 *
	 * Counts the number of cells that contain numbers within the list of arguments
	 *
	 * Excel Function:
	 *        COUNTIF(range,condition)
	 *
	 * @param mixed $range Data values
	 * @param string $condition the criteria that defines which cells will be counted
	 *
	 * @return int|string
	 * @see Statistical\Conditional::COUNTIF()
	 *
	 * @deprecated 1.17.0
	 *      Use the COUNTIF() method in the Statistical\Conditional class instead
	 */
	public static function COUNTIF( $range, $condition ) {
		return Conditional::COUNTIF( $range, $condition );
	}

	/**
	 * COUNTIFS.
	 *
	 * Counts the number of cells that contain numbers within the list of arguments
	 *
	 * Excel Function:
	 *        COUNTIFS(criteria_range1, criteria1, [criteria_range2, criteria2]…)
	 *
	 * @param mixed $args Pairs of Ranges and Criteria
	 *
	 * @return int|string
	 * @deprecated 1.17.0
	 *      Use the COUNTIFS() method in the Statistical\Conditional class instead
	 * @see Statistical\Conditional::COUNTIFS()
	 *
	 */
	public static function COUNTIFS( ...$args ) {
		return Conditional::COUNTIFS( ...$args );
	}

	/**
	 * COVAR.
	 *
	 * Returns covariance, the average of the products of deviations for each data point pair.
	 *
	 * @param mixed $yValues array of mixed Data Series Y
	 * @param mixed $xValues array of mixed Data Series X
	 *
	 * @return float|string
	 * @see Statistical\Trends::COVAR()
	 *
	 * @deprecated 1.18.0
	 *      Use the COVAR() method in the Statistical\Trends class instead
	 */
	public static function COVAR( $yValues, $xValues ) {
		return Trends::COVAR( $yValues, $xValues );
	}

	/**
	 * CRITBINOM.
	 *
	 * Returns the smallest value for which the cumulative binomial distribution is greater
	 *        than or equal to a criterion value
	 *
	 * See https://support.microsoft.com/en-us/help/828117/ for details of the algorithm used
	 *
	 * @param float $trials number of Bernoulli trials
	 * @param float $probability probability of a success on each trial
	 * @param float $alpha criterion value
	 *
	 * @return array|int|string
	 * @deprecated 1.18.0
	 *      Use the inverse() method in the Statistical\Distributions\Binomial class instead
	 * @see Statistical\Distributions\Binomial::inverse()
	 *
	 */
	public static function CRITBINOM( $trials, $probability, $alpha ) {
		return Statistical\Distributions\Binomial::inverse( $trials, $probability, $alpha );
	}

	/**
	 * DEVSQ.
	 *
	 * Returns the sum of squares of deviations of data points from their sample mean.
	 *
	 * Excel Function:
	 *        DEVSQ(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the sumSquares() method in the Statistical\Deviations class instead
	 * @see Statistical\Deviations::sumSquares()
	 *
	 */
	public static function DEVSQ( ...$args ) {
		return Statistical\Deviations::sumSquares( ...$args );
	}

	/**
	 * EXPONDIST.
	 *
	 *    Returns the exponential distribution. Use EXPONDIST to model the time between events,
	 *        such as how long an automated bank teller takes to deliver cash. For example, you can
	 *        use EXPONDIST to determine the probability that the process takes at most 1 minute.
	 *
	 * @param float $value Value of the function
	 * @param float $lambda The parameter value
	 * @param bool $cumulative
	 *
	 * @return array|float|string
	 * @deprecated 1.18.0
	 *      Use the distribution() method in the Statistical\Distributions\Exponential class instead
	 * @see Statistical\Distributions\Exponential::distribution()
	 *
	 */
	public static function EXPONDIST( $value, $lambda, $cumulative ) {
		return Statistical\Distributions\Exponential::distribution( $value, $lambda, $cumulative );
	}

	/**
	 * F.DIST.
	 *
	 *    Returns the F probability distribution.
	 *    You can use this function to determine whether two data sets have different degrees of diversity.
	 *    For example, you can examine the test scores of men and women entering high school, and determine
	 *        if the variability in the females is different from that found in the males.
	 *
	 * @param float $value Value of the function
	 * @param int $u The numerator degrees of freedom
	 * @param int $v The denominator degrees of freedom
	 * @param bool $cumulative If cumulative is TRUE, F.DIST returns the cumulative distribution function;
	 *                         if FALSE, it returns the probability density function.
	 *
	 * @return array|float|string
	 * @see Statistical\Distributions\F::distribution()
	 *
	 * @deprecated 1.18.0
	 *      Use the distribution() method in the Statistical\Distributions\F class instead
	 */
	public static function FDIST2( $value, $u, $v, $cumulative ) {
		return Statistical\Distributions\F::distribution( $value, $u, $v, $cumulative );
	}

	/**
	 * FISHER.
	 *
	 * Returns the Fisher transformation at x. This transformation produces a function that
	 *        is normally distributed rather than skewed. Use this function to perform hypothesis
	 *        testing on the correlation coefficient.
	 *
	 * @param float $value
	 *
	 * @return array|float|string
	 * @deprecated 1.18.0
	 *      Use the distribution() method in the Statistical\Distributions\Fisher class instead
	 * @see Statistical\Distributions\Fisher::distribution()
	 *
	 */
	public static function FISHER( $value ) {
		return Statistical\Distributions\Fisher::distribution( $value );
	}

	/**
	 * FISHERINV.
	 *
	 * Returns the inverse of the Fisher transformation. Use this transformation when
	 *        analyzing correlations between ranges or arrays of data. If y = FISHER(x), then
	 *        FISHERINV(y) = x.
	 *
	 * @param float $value
	 *
	 * @return array|float|string
	 * @deprecated 1.18.0
	 *      Use the inverse() method in the Statistical\Distributions\Fisher class instead
	 * @see Statistical\Distributions\Fisher::inverse()
	 *
	 */
	public static function FISHERINV( $value ) {
		return Statistical\Distributions\Fisher::inverse( $value );
	}

	/**
	 * FORECAST.
	 *
	 * Calculates, or predicts, a future value by using existing values. The predicted value is a y-value for a given x-value.
	 *
	 * @param float $xValue Value of X for which we want to find Y
	 * @param mixed $yValues array of mixed Data Series Y
	 * @param mixed $xValues of mixed Data Series X
	 *
	 * @return array|bool|float|string
	 * @deprecated 1.18.0
	 *      Use the FORECAST() method in the Statistical\Trends class instead
	 * @see Statistical\Trends::FORECAST()
	 *
	 */
	public static function FORECAST( $xValue, $yValues, $xValues ) {
		return Trends::FORECAST( $xValue, $yValues, $xValues );
	}

	/**
	 * GAMMA.
	 *
	 * Returns the gamma function value.
	 *
	 * @param float $value
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the gamma() method in the Statistical\Distributions\Gamma class instead
	 * @see Statistical\Distributions\Gamma::gamma()
	 *
	 */
	public static function GAMMAFunction( $value ) {
		return Statistical\Distributions\Gamma::gamma( $value );
	}

	/**
	 * GAMMADIST.
	 *
	 * Returns the gamma distribution.
	 *
	 * @param float $value Value at which you want to evaluate the distribution
	 * @param float $a Parameter to the distribution
	 * @param float $b Parameter to the distribution
	 * @param bool $cumulative
	 *
	 * @return array|float|string
	 * @see Statistical\Distributions\Gamma::distribution()
	 *
	 * @deprecated 1.18.0
	 *      Use the distribution() method in the Statistical\Distributions\Gamma class instead
	 */
	public static function GAMMADIST( $value, $a, $b, $cumulative ) {
		return Statistical\Distributions\Gamma::distribution( $value, $a, $b, $cumulative );
	}

	/**
	 * GAMMAINV.
	 *
	 * Returns the inverse of the Gamma distribution.
	 *
	 * @param float $probability Probability at which you want to evaluate the distribution
	 * @param float $alpha Parameter to the distribution
	 * @param float $beta Parameter to the distribution
	 *
	 * @return array|float|string
	 * @deprecated 1.18.0
	 *      Use the inverse() method in the Statistical\Distributions\Gamma class instead
	 * @see Statistical\Distributions\Gamma::inverse()
	 *
	 */
	public static function GAMMAINV( $probability, $alpha, $beta ) {
		return Statistical\Distributions\Gamma::inverse( $probability, $alpha, $beta );
	}

	/**
	 * GAMMALN.
	 *
	 * Returns the natural logarithm of the gamma function.
	 *
	 * @param float $value
	 *
	 * @return array|float|string
	 * @deprecated 1.18.0
	 *      Use the ln() method in the Statistical\Distributions\Gamma class instead
	 * @see Statistical\Distributions\Gamma::ln()
	 *
	 */
	public static function GAMMALN( $value ) {
		return Statistical\Distributions\Gamma::ln( $value );
	}

	/**
	 * GAUSS.
	 *
	 * Calculates the probability that a member of a standard normal population will fall between
	 *     the mean and z standard deviations from the mean.
	 *
	 * @param float $value
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the gauss() method in the Statistical\Distributions\StandardNormal class instead
	 * @see Statistical\Distributions\StandardNormal::gauss()
	 *
	 */
	public static function GAUSS( $value ) {
		return Statistical\Distributions\StandardNormal::gauss( $value );
	}

	/**
	 * GEOMEAN.
	 *
	 * Returns the geometric mean of an array or range of positive data. For example, you
	 *        can use GEOMEAN to calculate average growth rate given compound interest with
	 *        variable rates.
	 *
	 * Excel Function:
	 *        GEOMEAN(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the geometric() method in the Statistical\Averages\Mean class instead
	 * @see Statistical\Averages\Mean::geometric()
	 *
	 */
	public static function GEOMEAN( ...$args ) {
		return Statistical\Averages\Mean::geometric( ...$args );
	}

	/**
	 * GROWTH.
	 *
	 * Returns values along a predicted exponential Trend
	 *
	 * @param mixed[] $yValues Data Series Y
	 * @param mixed[] $xValues Data Series X
	 * @param mixed[] $newValues Values of X for which we want to find Y
	 * @param bool $const a logical value specifying whether to force the intersect to equal 0
	 *
	 * @return float[]
	 * @see Statistical\Trends::GROWTH()
	 *
	 * @deprecated 1.18.0
	 *      Use the GROWTH() method in the Statistical\Trends class instead
	 */
	public static function GROWTH( $yValues, $xValues = [], $newValues = [], $const = true ) {
		return Trends::GROWTH( $yValues, $xValues, $newValues, $const );
	}

	/**
	 * HARMEAN.
	 *
	 * Returns the harmonic mean of a data set. The harmonic mean is the reciprocal of the
	 *        arithmetic mean of reciprocals.
	 *
	 * Excel Function:
	 *        HARMEAN(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the harmonic() method in the Statistical\Averages\Mean class instead
	 * @see Statistical\Averages\Mean::harmonic()
	 *
	 */
	public static function HARMEAN( ...$args ) {
		return Statistical\Averages\Mean::harmonic( ...$args );
	}

	/**
	 * HYPGEOMDIST.
	 *
	 * Returns the hypergeometric distribution. HYPGEOMDIST returns the probability of a given number of
	 * sample successes, given the sample size, population successes, and population size.
	 *
	 * @param mixed $sampleSuccesses Number of successes in the sample
	 * @param mixed $sampleNumber Size of the sample
	 * @param mixed $populationSuccesses Number of successes in the population
	 * @param mixed $populationNumber Population size
	 *
	 * @return array|float|string
	 * @see Statistical\Distributions\HyperGeometric::distribution()
	 *
	 * @deprecated 1.18.0
	 *      Use the distribution() method in the Statistical\Distributions\HyperGeometric class instead
	 */
	public static function HYPGEOMDIST( $sampleSuccesses, $sampleNumber, $populationSuccesses, $populationNumber ) {
		return Statistical\Distributions\HyperGeometric::distribution( $sampleSuccesses, $sampleNumber, $populationSuccesses, $populationNumber );
	}

	/**
	 * INTERCEPT.
	 *
	 * Calculates the point at which a line will intersect the y-axis by using existing x-values and y-values.
	 *
	 * @param mixed[] $yValues Data Series Y
	 * @param mixed[] $xValues Data Series X
	 *
	 * @return float|string
	 * @see Statistical\Trends::INTERCEPT()
	 *
	 * @deprecated 1.18.0
	 *      Use the INTERCEPT() method in the Statistical\Trends class instead
	 */
	public static function INTERCEPT( $yValues, $xValues ) {
		return Trends::INTERCEPT( $yValues, $xValues );
	}

	/**
	 * KURT.
	 *
	 * Returns the kurtosis of a data set. Kurtosis characterizes the relative peakedness
	 * or flatness of a distribution compared with the normal distribution. Positive
	 * kurtosis indicates a relatively peaked distribution. Negative kurtosis indicates a
	 * relatively flat distribution.
	 *
	 * @param array ...$args Data Series
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the kurtosis() method in the Statistical\Deviations class instead
	 * @see Statistical\Deviations::kurtosis()
	 *
	 */
	public static function KURT( ...$args ) {
		return Statistical\Deviations::kurtosis( ...$args );
	}

	/**
	 * LARGE.
	 *
	 * Returns the nth largest value in a data set. You can use this function to
	 *        select a value based on its relative standing.
	 *
	 * Excel Function:
	 *        LARGE(value1[,value2[, ...]],entry)
	 *
	 * @param mixed $args Data values
	 *
	 * @return float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the large() method in the Statistical\Size class instead
	 * @see Statistical\Size::large()
	 *
	 */
	public static function LARGE( ...$args ) {
		return Statistical\Size::large( ...$args );
	}

	/**
	 * LINEST.
	 *
	 * Calculates the statistics for a line by using the "least squares" method to calculate a straight line that best fits your data,
	 *        and then returns an array that describes the line.
	 *
	 * @param mixed[] $yValues Data Series Y
	 * @param null|mixed[] $xValues Data Series X
	 * @param bool $const a logical value specifying whether to force the intersect to equal 0
	 * @param bool $stats a logical value specifying whether to return additional regression statistics
	 *
	 * @return array|int|string The result, or a string containing an error
	 * @see Statistical\Trends::LINEST()
	 *
	 * @deprecated 1.18.0
	 *      Use the LINEST() method in the Statistical\Trends class instead
	 */
	public static function LINEST( $yValues, $xValues = null, $const = true, $stats = false ) {
		return Trends::LINEST( $yValues, $xValues, $const, $stats );
	}

	/**
	 * LOGEST.
	 *
	 * Calculates an exponential curve that best fits the X and Y data series,
	 *        and then returns an array that describes the line.
	 *
	 * @param mixed[] $yValues Data Series Y
	 * @param null|mixed[] $xValues Data Series X
	 * @param bool $const a logical value specifying whether to force the intersect to equal 0
	 * @param bool $stats a logical value specifying whether to return additional regression statistics
	 *
	 * @return array|int|string The result, or a string containing an error
	 * @see Statistical\Trends::LOGEST()
	 *
	 * @deprecated 1.18.0
	 *      Use the LOGEST() method in the Statistical\Trends class instead
	 */
	public static function LOGEST( $yValues, $xValues = null, $const = true, $stats = false ) {
		return Trends::LOGEST( $yValues, $xValues, $const, $stats );
	}

	/**
	 * LOGINV.
	 *
	 * Returns the inverse of the normal cumulative distribution
	 *
	 * @param float $probability
	 * @param float $mean
	 * @param float $stdDev
	 *
	 * @return array|float|string The result, or a string containing an error
	 *
	 * @TODO    Try implementing P J Acklam's refinement algorithm for greater
	 *            accuracy if I can get my head round the mathematics
	 *            (as described at) http://home.online.no/~pjacklam/notes/invnorm/
	 * @deprecated 1.18.0
	 *      Use the inverse() method in the Statistical\Distributions\LogNormal class instead
	 * @see Statistical\Distributions\LogNormal::inverse()
	 *
	 */
	public static function LOGINV( $probability, $mean, $stdDev ) {
		return Statistical\Distributions\LogNormal::inverse( $probability, $mean, $stdDev );
	}

	/**
	 * LOGNORMDIST.
	 *
	 * Returns the cumulative lognormal distribution of x, where ln(x) is normally distributed
	 * with parameters mean and standard_dev.
	 *
	 * @param float $value
	 * @param float $mean
	 * @param float $stdDev
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the cumulative() method in the Statistical\Distributions\LogNormal class instead
	 * @see Statistical\Distributions\LogNormal::cumulative()
	 *
	 */
	public static function LOGNORMDIST( $value, $mean, $stdDev ) {
		return Statistical\Distributions\LogNormal::cumulative( $value, $mean, $stdDev );
	}

	/**
	 * LOGNORM.DIST.
	 *
	 * Returns the lognormal distribution of x, where ln(x) is normally distributed
	 * with parameters mean and standard_dev.
	 *
	 * @param float $value
	 * @param float $mean
	 * @param float $stdDev
	 * @param bool $cumulative
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @see Statistical\Distributions\LogNormal::distribution()
	 *
	 * @deprecated 1.18.0
	 *      Use the distribution() method in the Statistical\Distributions\LogNormal class instead
	 */
	public static function LOGNORMDIST2( $value, $mean, $stdDev, $cumulative = false ) {
		return Statistical\Distributions\LogNormal::distribution( $value, $mean, $stdDev, $cumulative );
	}

	/**
	 * MAX.
	 *
	 * MAX returns the value of the element of the values passed that has the highest value,
	 *        with negative numbers considered smaller than positive numbers.
	 *
	 * Excel Function:
	 *        max(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float
	 * @deprecated 1.17.0
	 *      Use the MAX() method in the Statistical\Maximum class instead
	 * @see Statistical\Maximum::max()
	 *
	 */
	public static function MAX( ...$args ) {
		return Maximum::max( ...$args );
	}

	/**
	 * MAXA.
	 *
	 * Returns the greatest value in a list of arguments, including numbers, text, and logical values
	 *
	 * Excel Function:
	 *        maxA(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float
	 * @deprecated 1.17.0
	 *      Use the MAXA() method in the Statistical\Maximum class instead
	 * @see Statistical\Maximum::maxA()
	 *
	 */
	public static function MAXA( ...$args ) {
		return Maximum::maxA( ...$args );
	}

	/**
	 * MAXIFS.
	 *
	 * Counts the maximum value within a range of cells that contain numbers within the list of arguments
	 *
	 * Excel Function:
	 *        MAXIFS(max_range, criteria_range1, criteria1, [criteria_range2, criteria2], ...)
	 *
	 * @param mixed $args Data range and criterias
	 *
	 * @return null|float|string
	 * @deprecated 1.17.0
	 *      Use the MAXIFS() method in the Statistical\Conditional class instead
	 * @see Statistical\Conditional::MAXIFS()
	 *
	 */
	public static function MAXIFS( ...$args ) {
		return Conditional::MAXIFS( ...$args );
	}

	/**
	 * MEDIAN.
	 *
	 * Returns the median of the given numbers. The median is the number in the middle of a set of numbers.
	 *
	 * Excel Function:
	 *        MEDIAN(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the median() method in the Statistical\Averages class instead
	 * @see Statistical\Averages::median()
	 *
	 */
	public static function MEDIAN( ...$args ) {
		return Statistical\Averages::median( ...$args );
	}

	/**
	 * MIN.
	 *
	 * MIN returns the value of the element of the values passed that has the smallest value,
	 *        with negative numbers considered smaller than positive numbers.
	 *
	 * Excel Function:
	 *        MIN(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float
	 * @deprecated 1.17.0
	 *      Use the min() method in the Statistical\Minimum class instead
	 * @see Statistical\Minimum::min()
	 *
	 */
	public static function MIN( ...$args ) {
		return Minimum::min( ...$args );
	}

	/**
	 * MINA.
	 *
	 * Returns the smallest value in a list of arguments, including numbers, text, and logical values
	 *
	 * Excel Function:
	 *        MINA(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float
	 * @deprecated 1.17.0
	 *      Use the minA() method in the Statistical\Minimum class instead
	 * @see Statistical\Minimum::minA()
	 *
	 */
	public static function MINA( ...$args ) {
		return Minimum::minA( ...$args );
	}

	/**
	 * MINIFS.
	 *
	 * Returns the minimum value within a range of cells that contain numbers within the list of arguments
	 *
	 * Excel Function:
	 *        MINIFS(min_range, criteria_range1, criteria1, [criteria_range2, criteria2], ...)
	 *
	 * @param mixed $args Data range and criterias
	 *
	 * @return null|float|string
	 * @deprecated 1.17.0
	 *      Use the MINIFS() method in the Statistical\Conditional class instead
	 * @see Statistical\Conditional::MINIFS()
	 *
	 */
	public static function MINIFS( ...$args ) {
		return Conditional::MINIFS( ...$args );
	}

	/**
	 * MODE.
	 *
	 * Returns the most frequently occurring, or repetitive, value in an array or range of data
	 *
	 * Excel Function:
	 *        MODE(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the mode() method in the Statistical\Averages class instead
	 * @see Statistical\Averages::mode()
	 *
	 */
	public static function MODE( ...$args ) {
		return Statistical\Averages::mode( ...$args );
	}

	/**
	 * NEGBINOMDIST.
	 *
	 * Returns the negative binomial distribution. NEGBINOMDIST returns the probability that
	 *        there will be number_f failures before the number_s-th success, when the constant
	 *        probability of a success is probability_s. This function is similar to the binomial
	 *        distribution, except that the number of successes is fixed, and the number of trials is
	 *        variable. Like the binomial, trials are assumed to be independent.
	 *
	 * @param mixed $failures Number of Failures
	 * @param mixed $successes Threshold number of Successes
	 * @param mixed $probability Probability of success on each trial
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the negative() method in the Statistical\Distributions\Binomial class instead
	 * @see Statistical\Distributions\Binomial::negative()
	 *
	 */
	public static function NEGBINOMDIST( $failures, $successes, $probability ) {
		return Statistical\Distributions\Binomial::negative( $failures, $successes, $probability );
	}

	/**
	 * NORMDIST.
	 *
	 * Returns the normal distribution for the specified mean and standard deviation. This
	 * function has a very wide range of applications in statistics, including hypothesis
	 * testing.
	 *
	 * @param mixed $value
	 * @param mixed $mean Mean Value
	 * @param mixed $stdDev Standard Deviation
	 * @param mixed $cumulative
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @see Statistical\Distributions\Normal::distribution()
	 *
	 * @deprecated 1.18.0
	 *      Use the distribution() method in the Statistical\Distributions\Normal class instead
	 */
	public static function NORMDIST( $value, $mean, $stdDev, $cumulative ) {
		return Statistical\Distributions\Normal::distribution( $value, $mean, $stdDev, $cumulative );
	}

	/**
	 * NORMINV.
	 *
	 * Returns the inverse of the normal cumulative distribution for the specified mean and standard deviation.
	 *
	 * @param mixed $probability
	 * @param mixed $mean Mean Value
	 * @param mixed $stdDev Standard Deviation
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the inverse() method in the Statistical\Distributions\Normal class instead
	 * @see Statistical\Distributions\Normal::inverse()
	 *
	 */
	public static function NORMINV( $probability, $mean, $stdDev ) {
		return Statistical\Distributions\Normal::inverse( $probability, $mean, $stdDev );
	}

	/**
	 * NORMSDIST.
	 *
	 * Returns the standard normal cumulative distribution function. The distribution has
	 * a mean of 0 (zero) and a standard deviation of one. Use this function in place of a
	 * table of standard normal curve areas.
	 *
	 * @param mixed $value
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the cumulative() method in the Statistical\Distributions\StandardNormal class instead
	 * @see Statistical\Distributions\StandardNormal::cumulative()
	 *
	 */
	public static function NORMSDIST( $value ) {
		return Statistical\Distributions\StandardNormal::cumulative( $value );
	}

	/**
	 * NORM.S.DIST.
	 *
	 * Returns the standard normal cumulative distribution function. The distribution has
	 * a mean of 0 (zero) and a standard deviation of one. Use this function in place of a
	 * table of standard normal curve areas.
	 *
	 * @param mixed $value
	 * @param mixed $cumulative
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @see Statistical\Distributions\StandardNormal::distribution()
	 *
	 * @deprecated 1.18.0
	 *      Use the distribution() method in the Statistical\Distributions\StandardNormal class instead
	 */
	public static function NORMSDIST2( $value, $cumulative ) {
		return Statistical\Distributions\StandardNormal::distribution( $value, $cumulative );
	}

	/**
	 * NORMSINV.
	 *
	 * Returns the inverse of the standard normal cumulative distribution
	 *
	 * @param mixed $value
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the inverse() method in the Statistical\Distributions\StandardNormal class instead
	 * @see Statistical\Distributions\StandardNormal::inverse()
	 *
	 */
	public static function NORMSINV( $value ) {
		return Statistical\Distributions\StandardNormal::inverse( $value );
	}

	/**
	 * PERCENTILE.
	 *
	 * Returns the nth percentile of values in a range..
	 *
	 * Excel Function:
	 *        PERCENTILE(value1[,value2[, ...]],entry)
	 *
	 * @param mixed $args Data values
	 *
	 * @return float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 * Use the PERCENTILE() method in the Statistical\Percentiles class instead
	 * @see Statistical\Percentiles::PERCENTILE()
	 *
	 */
	public static function PERCENTILE( ...$args ) {
		return Statistical\Percentiles::PERCENTILE( ...$args );
	}

	/**
	 * PERCENTRANK.
	 *
	 * Returns the rank of a value in a data set as a percentage of the data set.
	 * Note that the returned rank is simply rounded to the appropriate significant digits,
	 *      rather than floored (as MS Excel), so value 3 for a value set of  1, 2, 3, 4 will return
	 *      0.667 rather than 0.666
	 *
	 * @param mixed $valueSet An array of, or a reference to, a list of numbers
	 * @param mixed $value the number whose rank you want to find
	 * @param mixed $significance the number of significant digits for the returned percentage value
	 *
	 * @return float|string (string if result is an error)
	 * @deprecated 1.18.0
	 * Use the PERCENTRANK() method in the Statistical\Percentiles class instead
	 * @see Statistical\Percentiles::PERCENTRANK()
	 *
	 */
	public static function PERCENTRANK( $valueSet, $value, $significance = 3 ) {
		return Statistical\Percentiles::PERCENTRANK( $valueSet, $value, $significance );
	}

	/**
	 * PERMUT.
	 *
	 * Returns the number of permutations for a given number of objects that can be
	 *        selected from number objects. A permutation is any set or subset of objects or
	 *        events where internal order is significant. Permutations are different from
	 *        combinations, for which the internal order is not significant. Use this function
	 *        for lottery-style probability calculations.
	 *
	 * @param int $numObjs Number of different objects
	 * @param int $numInSet Number of objects in each permutation
	 *
	 * @return array|float|int|string Number of permutations, or a string containing an error
	 * @see Statistical\Permutations::PERMUT()
	 *
	 * @deprecated 1.17.0
	 * Use the PERMUT() method in the Statistical\Permutations class instead
	 */
	public static function PERMUT( $numObjs, $numInSet ) {
		return Permutations::PERMUT( $numObjs, $numInSet );
	}

	/**
	 * POISSON.
	 *
	 * Returns the Poisson distribution. A common application of the Poisson distribution
	 * is predicting the number of events over a specific time, such as the number of
	 * cars arriving at a toll plaza in 1 minute.
	 *
	 * @param mixed $value
	 * @param mixed $mean Mean Value
	 * @param mixed $cumulative
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 * Use the distribution() method in the Statistical\Distributions\Poisson class instead
	 * @see Statistical\Distributions\Poisson::distribution()
	 *
	 */
	public static function POISSON( $value, $mean, $cumulative ) {
		return Statistical\Distributions\Poisson::distribution( $value, $mean, $cumulative );
	}

	/**
	 * QUARTILE.
	 *
	 * Returns the quartile of a data set.
	 *
	 * Excel Function:
	 *        QUARTILE(value1[,value2[, ...]],entry)
	 *
	 * @param mixed $args Data values
	 *
	 * @return float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 * Use the QUARTILE() method in the Statistical\Percentiles class instead
	 * @see Statistical\Percentiles::QUARTILE()
	 *
	 */
	public static function QUARTILE( ...$args ) {
		return Statistical\Percentiles::QUARTILE( ...$args );
	}

	/**
	 * RANK.
	 *
	 * Returns the rank of a number in a list of numbers.
	 *
	 * @param mixed $value the number whose rank you want to find
	 * @param mixed $valueSet An array of, or a reference to, a list of numbers
	 * @param mixed $order Order to sort the values in the value set
	 *
	 * @return float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 * Use the RANK() method in the Statistical\Percentiles class instead
	 * @see Statistical\Percentiles::RANK()
	 *
	 */
	public static function RANK( $value, $valueSet, $order = 0 ) {
		return Statistical\Percentiles::RANK( $value, $valueSet, $order );
	}

	/**
	 * RSQ.
	 *
	 * Returns the square of the Pearson product moment correlation coefficient through data points in known_y's and known_x's.
	 *
	 * @param mixed[] $yValues Data Series Y
	 * @param mixed[] $xValues Data Series X
	 *
	 * @return float|string The result, or a string containing an error
	 * @see Statistical\Trends::RSQ()
	 *
	 * @deprecated 1.18.0
	 *      Use the RSQ() method in the Statistical\Trends class instead
	 */
	public static function RSQ( $yValues, $xValues ) {
		return Trends::RSQ( $yValues, $xValues );
	}

	/**
	 * SKEW.
	 *
	 * Returns the skewness of a distribution. Skewness characterizes the degree of asymmetry
	 * of a distribution around its mean. Positive skewness indicates a distribution with an
	 * asymmetric tail extending toward more positive values. Negative skewness indicates a
	 * distribution with an asymmetric tail extending toward more negative values.
	 *
	 * @param array ...$args Data Series
	 *
	 * @return float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the skew() method in the Statistical\Deviations class instead
	 * @see Statistical\Deviations::skew()
	 *
	 */
	public static function SKEW( ...$args ) {
		return Statistical\Deviations::skew( ...$args );
	}

	/**
	 * SLOPE.
	 *
	 * Returns the slope of the linear regression line through data points in known_y's and known_x's.
	 *
	 * @param mixed[] $yValues Data Series Y
	 * @param mixed[] $xValues Data Series X
	 *
	 * @return float|string The result, or a string containing an error
	 * @see Statistical\Trends::SLOPE()
	 *
	 * @deprecated 1.18.0
	 *      Use the SLOPE() method in the Statistical\Trends class instead
	 */
	public static function SLOPE( $yValues, $xValues ) {
		return Trends::SLOPE( $yValues, $xValues );
	}

	/**
	 * SMALL.
	 *
	 * Returns the nth smallest value in a data set. You can use this function to
	 *        select a value based on its relative standing.
	 *
	 * Excel Function:
	 *        SMALL(value1[,value2[, ...]],entry)
	 *
	 * @param mixed $args Data values
	 *
	 * @return float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the small() method in the Statistical\Size class instead
	 * @see Statistical\Size::small()
	 *
	 */
	public static function SMALL( ...$args ) {
		return Statistical\Size::small( ...$args );
	}

	/**
	 * STANDARDIZE.
	 *
	 * Returns a normalized value from a distribution characterized by mean and standard_dev.
	 *
	 * @param float $value Value to normalize
	 * @param float $mean Mean Value
	 * @param float $stdDev Standard Deviation
	 *
	 * @return array|float|string Standardized value, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the execute() method in the Statistical\Standardize class instead
	 * @see Statistical\Standardize::execute()
	 *
	 */
	public static function STANDARDIZE( $value, $mean, $stdDev ) {
		return Statistical\Standardize::execute( $value, $mean, $stdDev );
	}

	/**
	 * STDEV.
	 *
	 * Estimates standard deviation based on a sample. The standard deviation is a measure of how
	 *        widely values are dispersed from the average value (the mean).
	 *
	 * Excel Function:
	 *        STDEV(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string The result, or a string containing an error
	 * @deprecated 1.17.0
	 *      Use the STDEV() method in the Statistical\StandardDeviations class instead
	 * @see Statistical\StandardDeviations::STDEV()
	 *
	 */
	public static function STDEV( ...$args ) {
		return StandardDeviations::STDEV( ...$args );
	}

	/**
	 * STDEVA.
	 *
	 * Estimates standard deviation based on a sample, including numbers, text, and logical values
	 *
	 * Excel Function:
	 *        STDEVA(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string
	 * @deprecated 1.17.0
	 *      Use the STDEVA() method in the Statistical\StandardDeviations class instead
	 * @see Statistical\StandardDeviations::STDEVA()
	 *
	 */
	public static function STDEVA( ...$args ) {
		return StandardDeviations::STDEVA( ...$args );
	}

	/**
	 * STDEVP.
	 *
	 * Calculates standard deviation based on the entire population
	 *
	 * Excel Function:
	 *        STDEVP(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string
	 * @deprecated 1.17.0
	 *      Use the STDEVP() method in the Statistical\StandardDeviations class instead
	 * @see Statistical\StandardDeviations::STDEVP()
	 *
	 */
	public static function STDEVP( ...$args ) {
		return StandardDeviations::STDEVP( ...$args );
	}

	/**
	 * STDEVPA.
	 *
	 * Calculates standard deviation based on the entire population, including numbers, text, and logical values
	 *
	 * Excel Function:
	 *        STDEVPA(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string
	 * @deprecated 1.17.0
	 *      Use the STDEVPA() method in the Statistical\StandardDeviations class instead
	 * @see Statistical\StandardDeviations::STDEVPA()
	 *
	 */
	public static function STDEVPA( ...$args ) {
		return StandardDeviations::STDEVPA( ...$args );
	}

	/**
	 * STEYX.
	 *
	 * @param mixed[] $yValues Data Series Y
	 * @param mixed[] $xValues Data Series X
	 *
	 * @return float|string
	 * @see Statistical\Trends::STEYX()
	 *
	 * Returns the standard error of the predicted y-value for each x in the regression.
	 *
	 * @deprecated 1.18.0
	 *      Use the STEYX() method in the Statistical\Trends class instead
	 */
	public static function STEYX( $yValues, $xValues ) {
		return Trends::STEYX( $yValues, $xValues );
	}

	/**
	 * TDIST.
	 *
	 * Returns the probability of Student's T distribution.
	 *
	 * @param float $value Value for the function
	 * @param float $degrees degrees of freedom
	 * @param float $tails number of tails (1 or 2)
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the distribution() method in the Statistical\Distributions\StudentT class instead
	 * @see Statistical\Distributions\StudentT::distribution()
	 *
	 */
	public static function TDIST( $value, $degrees, $tails ) {
		return Statistical\Distributions\StudentT::distribution( $value, $degrees, $tails );
	}

	/**
	 * TINV.
	 *
	 * Returns the one-tailed probability of the Student-T distribution.
	 *
	 * @param float $probability Probability for the function
	 * @param float $degrees degrees of freedom
	 *
	 * @return array|float|string The result, or a string containing an error
	 * @see Statistical\Distributions\StudentT::inverse()
	 *
	 * @deprecated 1.18.0
	 *      Use the inverse() method in the Statistical\Distributions\StudentT class instead
	 */
	public static function TINV( $probability, $degrees ) {
		return Statistical\Distributions\StudentT::inverse( $probability, $degrees );
	}

	/**
	 * TREND.
	 *
	 * Returns values along a linear Trend
	 *
	 * @param mixed[] $yValues Data Series Y
	 * @param mixed[] $xValues Data Series X
	 * @param mixed[] $newValues Values of X for which we want to find Y
	 * @param bool $const a logical value specifying whether to force the intersect to equal 0
	 *
	 * @return float[]
	 * @see Statistical\Trends::TREND()
	 *
	 * @deprecated 1.18.0
	 *      Use the TREND() method in the Statistical\Trends class instead
	 */
	public static function TREND( $yValues, $xValues = [], $newValues = [], $const = true ) {
		return Trends::TREND( $yValues, $xValues, $newValues, $const );
	}

	/**
	 * TRIMMEAN.
	 *
	 * Returns the mean of the interior of a data set. TRIMMEAN calculates the mean
	 *        taken by excluding a percentage of data points from the top and bottom tails
	 *        of a data set.
	 *
	 * Excel Function:
	 *        TRIMEAN(value1[,value2[, ...]], $discard)
	 *
	 * @param mixed $args Data values
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the trim() method in the Statistical\Averages\Mean class instead
	 * @see Statistical\Averages\Mean::trim()
	 *
	 */
	public static function TRIMMEAN( ...$args ) {
		return Statistical\Averages\Mean::trim( ...$args );
	}

	/**
	 * VARFunc.
	 *
	 * Estimates variance based on a sample.
	 *
	 * Excel Function:
	 *        VAR(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string (string if result is an error)
	 * @deprecated 1.17.0
	 *      Use the VAR() method in the Statistical\Variances class instead
	 * @see Statistical\Variances::VAR()
	 *
	 */
	public static function VARFunc( ...$args ) {
		return Variances::VAR( ...$args );
	}

	/**
	 * VARA.
	 *
	 * Estimates variance based on a sample, including numbers, text, and logical values
	 *
	 * Excel Function:
	 *        VARA(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string (string if result is an error)
	 * @deprecated 1.17.0
	 *      Use the VARA() method in the Statistical\Variances class instead
	 * @see Statistical\Variances::VARA()
	 *
	 */
	public static function VARA( ...$args ) {
		return Variances::VARA( ...$args );
	}

	/**
	 * VARP.
	 *
	 * Calculates variance based on the entire population
	 *
	 * Excel Function:
	 *        VARP(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string (string if result is an error)
	 * @deprecated 1.17.0
	 *      Use the VARP() method in the Statistical\Variances class instead
	 * @see Statistical\Variances::VARP()
	 *
	 */
	public static function VARP( ...$args ) {
		return Variances::VARP( ...$args );
	}

	/**
	 * VARPA.
	 *
	 * Calculates variance based on the entire population, including numbers, text, and logical values
	 *
	 * Excel Function:
	 *        VARPA(value1[,value2[, ...]])
	 *
	 * @param mixed ...$args Data values
	 *
	 * @return float|string (string if result is an error)
	 * @deprecated 1.17.0
	 *      Use the VARPA() method in the Statistical\Variances class instead
	 * @see Statistical\Variances::VARPA()
	 *
	 */
	public static function VARPA( ...$args ) {
		return Variances::VARPA( ...$args );
	}

	/**
	 * WEIBULL.
	 *
	 * Returns the Weibull distribution. Use this distribution in reliability
	 * analysis, such as calculating a device's mean time to failure.
	 *
	 * @param float $value
	 * @param float $alpha Alpha Parameter
	 * @param float $beta Beta Parameter
	 * @param bool $cumulative
	 *
	 * @return array|float|string (string if result is an error)
	 * @see Statistical\Distributions\Weibull::distribution()
	 *
	 * @deprecated 1.18.0
	 *      Use the distribution() method in the Statistical\Distributions\Weibull class instead
	 */
	public static function WEIBULL( $value, $alpha, $beta, $cumulative ) {
		return Statistical\Distributions\Weibull::distribution( $value, $alpha, $beta, $cumulative );
	}

	/**
	 * ZTEST.
	 *
	 * Returns the one-tailed P-value of a z-test.
	 *
	 * For a given hypothesized population mean, x, Z.TEST returns the probability that the sample mean would be
	 *     greater than the average of observations in the data set (array) — that is, the observed sample mean.
	 *
	 * @param mixed $dataSet
	 * @param float $m0 Alpha Parameter
	 * @param float $sigma Beta Parameter
	 *
	 * @return array|float|string (string if result is an error)
	 * @deprecated 1.18.0
	 *      Use the zTest() method in the Statistical\Distributions\StandardNormal class instead
	 * @see Statistical\Distributions\StandardNormal::zTest()
	 *
	 */
	public static function ZTEST( $dataSet, $m0, $sigma = null ) {
		return Statistical\Distributions\StandardNormal::zTest( $dataSet, $m0, $sigma );
	}
}
