<?php

namespace PhpOffice\PhpSpreadsheet\Calculation;

use PhpOffice\PhpSpreadsheet\Calculation\Financial\Amortization;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\Coupons;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\Depreciation;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\Dollar;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\InterestRate;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\Securities;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\TreasuryBill;

/**
 * @deprecated 1.18.0
 */
class Financial {
	const FINANCIAL_MAX_ITERATIONS = 128;

	const FINANCIAL_PRECISION = 1.0e-08;

	/**
	 * ACCRINT.
	 *
	 * Returns the accrued interest for a security that pays periodic interest.
	 *
	 * Excel Function:
	 *        ACCRINT(issue,firstinterest,settlement,rate,par,frequency[,basis][,calc_method])
	 *
	 * @param mixed $issue the security's issue date
	 * @param mixed $firstInterest the security's first interest date
	 * @param mixed $settlement The security's settlement date.
	 *                              The security settlement date is the date after the issue date
	 *                                  when the security is traded to the buyer.
	 * @param mixed $rate the security's annual coupon rate
	 * @param mixed $parValue The security's par value.
	 *                            If you omit par, ACCRINT uses $1,000.
	 * @param mixed $frequency The number of coupon payments per year.
	 *                             Valid frequency values are:
	 *                               1    Annual
	 *                               2    Semi-Annual
	 *                               4    Quarterly
	 * @param mixed $basis The type of day count to use.
	 *                         0 or omitted    US (NASD) 30/360
	 *                         1               Actual/actual
	 *                         2               Actual/360
	 *                         3               Actual/365
	 *                         4               European 30/360
	 * @param mixed $calcMethod
	 *                          If true, use Issue to Settlement
	 *                          If false, use FirstInterest to Settlement
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the periodic() method in the Financial\Securities\AccruedInterest class instead
	 * @see Securities\AccruedInterest::periodic()
	 *
	 */
	public static function ACCRINT(
		$issue, $firstInterest, $settlement, $rate, $parValue = 1000, $frequency = 1, $basis = 0, $calcMethod = true
	) {
		return Securities\AccruedInterest::periodic( $issue, $firstInterest, $settlement, $rate, $parValue, $frequency, $basis, $calcMethod );
	}

	/**
	 * ACCRINTM.
	 *
	 * Returns the accrued interest for a security that pays interest at maturity.
	 *
	 * Excel Function:
	 *        ACCRINTM(issue,settlement,rate[,par[,basis]])
	 *
	 * @param mixed $issue The security's issue date
	 * @param mixed $settlement The security's settlement (or maturity) date
	 * @param mixed $rate The security's annual coupon rate
	 * @param mixed $parValue The security's par value.
	 *                            If you omit par, ACCRINT uses $1,000.
	 * @param mixed $basis The type of day count to use.
	 *                            0 or omitted    US (NASD) 30/360
	 *                            1               Actual/actual
	 *                            2               Actual/360
	 *                            3               Actual/365
	 *                            4               European 30/360
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the atMaturity() method in the Financial\Securities\AccruedInterest class instead
	 * @see Financial\Securities\AccruedInterest::atMaturity()
	 *
	 */
	public static function ACCRINTM( $issue, $settlement, $rate, $parValue = 1000, $basis = 0 ) {
		return Securities\AccruedInterest::atMaturity( $issue, $settlement, $rate, $parValue, $basis );
	}

	/**
	 * AMORDEGRC.
	 *
	 * Returns the depreciation for each accounting period.
	 * This function is provided for the French accounting system. If an asset is purchased in
	 * the middle of the accounting period, the prorated depreciation is taken into account.
	 * The function is similar to AMORLINC, except that a depreciation coefficient is applied in
	 * the calculation depending on the life of the assets.
	 * This function will return the depreciation until the last period of the life of the assets
	 * or until the cumulated value of depreciation is greater than the cost of the assets minus
	 * the salvage value.
	 *
	 * Excel Function:
	 *        AMORDEGRC(cost,purchased,firstPeriod,salvage,period,rate[,basis])
	 *
	 * @param float $cost The cost of the asset
	 * @param mixed $purchased Date of the purchase of the asset
	 * @param mixed $firstPeriod Date of the end of the first period
	 * @param mixed $salvage The salvage value at the end of the life of the asset
	 * @param float $period The period
	 * @param float $rate Rate of depreciation
	 * @param int $basis The type of day count to use.
	 *                       0 or omitted    US (NASD) 30/360
	 *                       1                Actual/actual
	 *                       2                Actual/360
	 *                       3                Actual/365
	 *                       4                European 30/360
	 *
	 * @return float|string (string containing the error type if there is an error)
	 * @deprecated 1.18.0
	 *      Use the AMORDEGRC() method in the Financial\Amortization class instead
	 * @see Financial\Amortization::AMORDEGRC()
	 *
	 */
	public static function AMORDEGRC( $cost, $purchased, $firstPeriod, $salvage, $period, $rate, $basis = 0 ) {
		return Amortization::AMORDEGRC( $cost, $purchased, $firstPeriod, $salvage, $period, $rate, $basis );
	}

	/**
	 * AMORLINC.
	 *
	 * Returns the depreciation for each accounting period.
	 * This function is provided for the French accounting system. If an asset is purchased in
	 * the middle of the accounting period, the prorated depreciation is taken into account.
	 *
	 * Excel Function:
	 *        AMORLINC(cost,purchased,firstPeriod,salvage,period,rate[,basis])
	 *
	 * @param float $cost The cost of the asset
	 * @param mixed $purchased Date of the purchase of the asset
	 * @param mixed $firstPeriod Date of the end of the first period
	 * @param mixed $salvage The salvage value at the end of the life of the asset
	 * @param float $period The period
	 * @param float $rate Rate of depreciation
	 * @param int $basis The type of day count to use.
	 *                       0 or omitted    US (NASD) 30/360
	 *                       1               Actual/actual
	 *                       2               Actual/360
	 *                       3               Actual/365
	 *                       4               European 30/360
	 *
	 * @return float|string (string containing the error type if there is an error)
	 * @deprecated 1.18.0
	 *      Use the AMORLINC() method in the Financial\Amortization class instead
	 * @see Financial\Amortization::AMORLINC()
	 *
	 */
	public static function AMORLINC( $cost, $purchased, $firstPeriod, $salvage, $period, $rate, $basis = 0 ) {
		return Amortization::AMORLINC( $cost, $purchased, $firstPeriod, $salvage, $period, $rate, $basis );
	}

	/**
	 * COUPDAYBS.
	 *
	 * Returns the number of days from the beginning of the coupon period to the settlement date.
	 *
	 * Excel Function:
	 *        COUPDAYBS(settlement,maturity,frequency[,basis])
	 *
	 * @param mixed $settlement The security's settlement date.
	 *                                The security settlement date is the date after the issue
	 *                                date when the security is traded to the buyer.
	 * @param mixed $maturity The security's maturity date.
	 *                                The maturity date is the date when the security expires.
	 * @param int $frequency the number of coupon payments per year.
	 *                                    Valid frequency values are:
	 *                                        1    Annual
	 *                                        2    Semi-Annual
	 *                                        4    Quarterly
	 * @param int $basis The type of day count to use.
	 *                                        0 or omitted    US (NASD) 30/360
	 *                                        1                Actual/actual
	 *                                        2                Actual/360
	 *                                        3                Actual/365
	 *                                        4                European 30/360
	 *
	 * @return float|string
	 * @see Financial\Coupons::COUPDAYBS()
	 *
	 * @deprecated 1.18.0
	 *      Use the COUPDAYBS() method in the Financial\Coupons class instead
	 */
	public static function COUPDAYBS( $settlement, $maturity, $frequency, $basis = 0 ) {
		return Coupons::COUPDAYBS( $settlement, $maturity, $frequency, $basis );
	}

	/**
	 * COUPDAYS.
	 *
	 * Returns the number of days in the coupon period that contains the settlement date.
	 *
	 * Excel Function:
	 *        COUPDAYS(settlement,maturity,frequency[,basis])
	 *
	 * @param mixed $settlement The security's settlement date.
	 *                                The security settlement date is the date after the issue
	 *                                date when the security is traded to the buyer.
	 * @param mixed $maturity The security's maturity date.
	 *                                The maturity date is the date when the security expires.
	 * @param mixed $frequency the number of coupon payments per year.
	 *                                    Valid frequency values are:
	 *                                        1    Annual
	 *                                        2    Semi-Annual
	 *                                        4    Quarterly
	 * @param int $basis The type of day count to use.
	 *                                        0 or omitted    US (NASD) 30/360
	 *                                        1                Actual/actual
	 *                                        2                Actual/360
	 *                                        3                Actual/365
	 *                                        4                European 30/360
	 *
	 * @return float|string
	 * @see Financial\Coupons::COUPDAYS()
	 *
	 * @deprecated 1.18.0
	 *      Use the COUPDAYS() method in the Financial\Coupons class instead
	 */
	public static function COUPDAYS( $settlement, $maturity, $frequency, $basis = 0 ) {
		return Coupons::COUPDAYS( $settlement, $maturity, $frequency, $basis );
	}

	/**
	 * COUPDAYSNC.
	 *
	 * Returns the number of days from the settlement date to the next coupon date.
	 *
	 * Excel Function:
	 *        COUPDAYSNC(settlement,maturity,frequency[,basis])
	 *
	 * @param mixed $settlement The security's settlement date.
	 *                                The security settlement date is the date after the issue
	 *                                date when the security is traded to the buyer.
	 * @param mixed $maturity The security's maturity date.
	 *                                The maturity date is the date when the security expires.
	 * @param mixed $frequency the number of coupon payments per year.
	 *                                    Valid frequency values are:
	 *                                        1    Annual
	 *                                        2    Semi-Annual
	 *                                        4    Quarterly
	 * @param int $basis The type of day count to use.
	 *                                        0 or omitted    US (NASD) 30/360
	 *                                        1                Actual/actual
	 *                                        2                Actual/360
	 *                                        3                Actual/365
	 *                                        4                European 30/360
	 *
	 * @return float|string
	 * @see Financial\Coupons::COUPDAYSNC()
	 *
	 * @deprecated 1.18.0
	 *      Use the COUPDAYSNC() method in the Financial\Coupons class instead
	 */
	public static function COUPDAYSNC( $settlement, $maturity, $frequency, $basis = 0 ) {
		return Coupons::COUPDAYSNC( $settlement, $maturity, $frequency, $basis );
	}

	/**
	 * COUPNCD.
	 *
	 * Returns the next coupon date after the settlement date.
	 *
	 * Excel Function:
	 *        COUPNCD(settlement,maturity,frequency[,basis])
	 *
	 * @param mixed $settlement The security's settlement date.
	 *                                The security settlement date is the date after the issue
	 *                                date when the security is traded to the buyer.
	 * @param mixed $maturity The security's maturity date.
	 *                                The maturity date is the date when the security expires.
	 * @param mixed $frequency the number of coupon payments per year.
	 *                                    Valid frequency values are:
	 *                                        1    Annual
	 *                                        2    Semi-Annual
	 *                                        4    Quarterly
	 * @param int $basis The type of day count to use.
	 *                                        0 or omitted    US (NASD) 30/360
	 *                                        1                Actual/actual
	 *                                        2                Actual/360
	 *                                        3                Actual/365
	 *                                        4                European 30/360
	 *
	 * @return mixed Excel date/time serial value, PHP date/time serial value or PHP date/time object,
	 *                        depending on the value of the ReturnDateType flag
	 * @see Financial\Coupons::COUPNCD()
	 *
	 * @deprecated 1.18.0
	 *      Use the COUPNCD() method in the Financial\Coupons class instead
	 */
	public static function COUPNCD( $settlement, $maturity, $frequency, $basis = 0 ) {
		return Coupons::COUPNCD( $settlement, $maturity, $frequency, $basis );
	}

	/**
	 * COUPNUM.
	 *
	 * Returns the number of coupons payable between the settlement date and maturity date,
	 * rounded up to the nearest whole coupon.
	 *
	 * Excel Function:
	 *        COUPNUM(settlement,maturity,frequency[,basis])
	 *
	 * @param mixed $settlement The security's settlement date.
	 *                                The security settlement date is the date after the issue
	 *                                date when the security is traded to the buyer.
	 * @param mixed $maturity The security's maturity date.
	 *                                The maturity date is the date when the security expires.
	 * @param mixed $frequency the number of coupon payments per year.
	 *                                    Valid frequency values are:
	 *                                        1    Annual
	 *                                        2    Semi-Annual
	 *                                        4    Quarterly
	 * @param int $basis The type of day count to use.
	 *                                        0 or omitted    US (NASD) 30/360
	 *                                        1                Actual/actual
	 *                                        2                Actual/360
	 *                                        3                Actual/365
	 *                                        4                European 30/360
	 *
	 * @return int|string
	 * @see Financial\Coupons::COUPNUM()
	 *
	 * @deprecated 1.18.0
	 *      Use the COUPNUM() method in the Financial\Coupons class instead
	 */
	public static function COUPNUM( $settlement, $maturity, $frequency, $basis = 0 ) {
		return Coupons::COUPNUM( $settlement, $maturity, $frequency, $basis );
	}

	/**
	 * COUPPCD.
	 *
	 * Returns the previous coupon date before the settlement date.
	 *
	 * Excel Function:
	 *        COUPPCD(settlement,maturity,frequency[,basis])
	 *
	 * @param mixed $settlement The security's settlement date.
	 *                                The security settlement date is the date after the issue
	 *                                date when the security is traded to the buyer.
	 * @param mixed $maturity The security's maturity date.
	 *                                The maturity date is the date when the security expires.
	 * @param mixed $frequency the number of coupon payments per year.
	 *                                    Valid frequency values are:
	 *                                        1    Annual
	 *                                        2    Semi-Annual
	 *                                        4    Quarterly
	 * @param int $basis The type of day count to use.
	 *                                        0 or omitted    US (NASD) 30/360
	 *                                        1                Actual/actual
	 *                                        2                Actual/360
	 *                                        3                Actual/365
	 *                                        4                European 30/360
	 *
	 * @return mixed Excel date/time serial value, PHP date/time serial value or PHP date/time object,
	 *                        depending on the value of the ReturnDateType flag
	 * @see Financial\Coupons::COUPPCD()
	 *
	 * @deprecated 1.18.0
	 *      Use the COUPPCD() method in the Financial\Coupons class instead
	 */
	public static function COUPPCD( $settlement, $maturity, $frequency, $basis = 0 ) {
		return Coupons::COUPPCD( $settlement, $maturity, $frequency, $basis );
	}

	/**
	 * CUMIPMT.
	 *
	 * Returns the cumulative interest paid on a loan between the start and end periods.
	 *
	 * Excel Function:
	 *        CUMIPMT(rate,nper,pv,start,end[,type])
	 *
	 * @param float $rate The Interest rate
	 * @param int $nper The total number of payment periods
	 * @param float $pv Present Value
	 * @param int $start The first period in the calculation.
	 *                       Payment periods are numbered beginning with 1.
	 * @param int $end the last period in the calculation
	 * @param int $type A number 0 or 1 and indicates when payments are due:
	 *                    0 or omitted    At the end of the period.
	 *                    1               At the beginning of the period.
	 *
	 * @return float|string
	 * @see Financial\CashFlow\Constant\Periodic\Cumulative::interest()
	 *
	 * @deprecated 1.18.0
	 *      Use the interest() method in the Financial\CashFlow\Constant\Periodic\Cumulative class instead
	 */
	public static function CUMIPMT( $rate, $nper, $pv, $start, $end, $type = 0 ) {
		return Financial\CashFlow\Constant\Periodic\Cumulative::interest( $rate, $nper, $pv, $start, $end, $type );
	}

	/**
	 * CUMPRINC.
	 *
	 * Returns the cumulative principal paid on a loan between the start and end periods.
	 *
	 * Excel Function:
	 *        CUMPRINC(rate,nper,pv,start,end[,type])
	 *
	 * @param float $rate The Interest rate
	 * @param int $nper The total number of payment periods
	 * @param float $pv Present Value
	 * @param int $start The first period in the calculation.
	 *                       Payment periods are numbered beginning with 1.
	 * @param int $end the last period in the calculation
	 * @param int $type A number 0 or 1 and indicates when payments are due:
	 *                    0 or omitted    At the end of the period.
	 *                    1               At the beginning of the period.
	 *
	 * @return float|string
	 * @see Financial\CashFlow\Constant\Periodic\Cumulative::principal()
	 *
	 * @deprecated 1.18.0
	 *      Use the principal() method in the Financial\CashFlow\Constant\Periodic\Cumulative class instead
	 */
	public static function CUMPRINC( $rate, $nper, $pv, $start, $end, $type = 0 ) {
		return Financial\CashFlow\Constant\Periodic\Cumulative::principal( $rate, $nper, $pv, $start, $end, $type );
	}

	/**
	 * DB.
	 *
	 * Returns the depreciation of an asset for a specified period using the
	 * fixed-declining balance method.
	 * This form of depreciation is used if you want to get a higher depreciation value
	 * at the beginning of the depreciation (as opposed to linear depreciation). The
	 * depreciation value is reduced with every depreciation period by the depreciation
	 * already deducted from the initial cost.
	 *
	 * Excel Function:
	 *        DB(cost,salvage,life,period[,month])
	 *
	 * @param float $cost Initial cost of the asset
	 * @param float $salvage Value at the end of the depreciation.
	 *                                (Sometimes called the salvage value of the asset)
	 * @param int $life Number of periods over which the asset is depreciated.
	 *                                (Sometimes called the useful life of the asset)
	 * @param int $period The period for which you want to calculate the
	 *                                depreciation. Period must use the same units as life.
	 * @param int $month Number of months in the first year. If month is omitted,
	 *                                it defaults to 12.
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the DB() method in the Financial\Depreciation class instead
	 * @see Financial\Depreciation::DB()
	 *
	 */
	public static function DB( $cost, $salvage, $life, $period, $month = 12 ) {
		return Depreciation::DB( $cost, $salvage, $life, $period, $month );
	}

	/**
	 * DDB.
	 *
	 * Returns the depreciation of an asset for a specified period using the
	 * double-declining balance method or some other method you specify.
	 *
	 * Excel Function:
	 *        DDB(cost,salvage,life,period[,factor])
	 *
	 * @param float $cost Initial cost of the asset
	 * @param float $salvage Value at the end of the depreciation.
	 *                                (Sometimes called the salvage value of the asset)
	 * @param int $life Number of periods over which the asset is depreciated.
	 *                                (Sometimes called the useful life of the asset)
	 * @param int $period The period for which you want to calculate the
	 *                                depreciation. Period must use the same units as life.
	 * @param float $factor The rate at which the balance declines.
	 *                                If factor is omitted, it is assumed to be 2 (the
	 *                                double-declining balance method).
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the DDB() method in the Financial\Depreciation class instead
	 * @see Financial\Depreciation::DDB()
	 *
	 */
	public static function DDB( $cost, $salvage, $life, $period, $factor = 2.0 ) {
		return Depreciation::DDB( $cost, $salvage, $life, $period, $factor );
	}

	/**
	 * DISC.
	 *
	 * Returns the discount rate for a security.
	 *
	 * Excel Function:
	 *        DISC(settlement,maturity,price,redemption[,basis])
	 *
	 * @param mixed $settlement The security's settlement date.
	 *                                The security settlement date is the date after the issue
	 *                                date when the security is traded to the buyer.
	 * @param mixed $maturity The security's maturity date.
	 *                                The maturity date is the date when the security expires.
	 * @param mixed $price The security's price per $100 face value
	 * @param int $redemption The security's redemption value per $100 face value
	 * @param int $basis The type of day count to use.
	 *                                        0 or omitted    US (NASD) 30/360
	 *                                        1                Actual/actual
	 *                                        2                Actual/360
	 *                                        3                Actual/365
	 *                                        4                European 30/360
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the discount() method in the Financial\Securities\Rates class instead
	 * @see Financial\Securities\Rates::discount()
	 *
	 */
	public static function DISC( $settlement, $maturity, $price, $redemption, $basis = 0 ) {
		return Financial\Securities\Rates::discount( $settlement, $maturity, $price, $redemption, $basis );
	}

	/**
	 * DOLLARDE.
	 *
	 * Converts a dollar price expressed as an integer part and a fraction
	 *        part into a dollar price expressed as a decimal number.
	 * Fractional dollar numbers are sometimes used for security prices.
	 *
	 * Excel Function:
	 *        DOLLARDE(fractional_dollar,fraction)
	 *
	 * @param array|float $fractional_dollar Fractional Dollar
	 * @param array|int $fraction Fraction
	 *
	 * @return array|float|string
	 * @see Financial\Dollar::decimal()
	 *
	 * @deprecated 1.18.0
	 *      Use the decimal() method in the Financial\Dollar class instead
	 */
	public static function DOLLARDE( $fractional_dollar = null, $fraction = 0 ) {
		return Dollar::decimal( $fractional_dollar, $fraction );
	}

	/**
	 * DOLLARFR.
	 *
	 * Converts a dollar price expressed as a decimal number into a dollar price
	 *        expressed as a fraction.
	 * Fractional dollar numbers are sometimes used for security prices.
	 *
	 * Excel Function:
	 *        DOLLARFR(decimal_dollar,fraction)
	 *
	 * @param array|float $decimal_dollar Decimal Dollar
	 * @param array|int $fraction Fraction
	 *
	 * @return array|float|string
	 * @see Financial\Dollar::fractional()
	 *
	 * @deprecated 1.18.0
	 *      Use the fractional() method in the Financial\Dollar class instead
	 */
	public static function DOLLARFR( $decimal_dollar = null, $fraction = 0 ) {
		return Dollar::fractional( $decimal_dollar, $fraction );
	}

	/**
	 * EFFECT.
	 *
	 * Returns the effective interest rate given the nominal rate and the number of
	 *        compounding payments per year.
	 *
	 * Excel Function:
	 *        EFFECT(nominal_rate,npery)
	 *
	 * @param float $nominalRate Nominal interest rate
	 * @param int $periodsPerYear Number of compounding payments per year
	 *
	 * @return float|string
	 * @see Financial\InterestRate::effective()
	 *
	 * @deprecated 1.18.0
	 *      Use the effective() method in the Financial\InterestRate class instead
	 */
	public static function EFFECT( $nominalRate = 0, $periodsPerYear = 0 ) {
		return Financial\InterestRate::effective( $nominalRate, $periodsPerYear );
	}

	/**
	 * FV.
	 *
	 * Returns the Future Value of a cash flow with constant payments and interest rate (annuities).
	 *
	 * Excel Function:
	 *        FV(rate,nper,pmt[,pv[,type]])
	 *
	 * @param float $rate The interest rate per period
	 * @param int $nper Total number of payment periods in an annuity
	 * @param float $pmt The payment made each period: it cannot change over the
	 *                            life of the annuity. Typically, pmt contains principal
	 *                            and interest but no other fees or taxes.
	 * @param float $pv present Value, or the lump-sum amount that a series of
	 *                            future payments is worth right now
	 * @param int $type A number 0 or 1 and indicates when payments are due:
	 *                                0 or omitted    At the end of the period.
	 *                                1                At the beginning of the period.
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the futureValue() method in the Financial\CashFlow\Constant\Periodic class instead
	 * @see Financial\CashFlow\Constant\Periodic::futureValue()
	 *
	 */
	public static function FV( $rate = 0, $nper = 0, $pmt = 0, $pv = 0, $type = 0 ) {
		return Financial\CashFlow\Constant\Periodic::futureValue( $rate, $nper, $pmt, $pv, $type );
	}

	/**
	 * FVSCHEDULE.
	 *
	 * Returns the future value of an initial principal after applying a series of compound interest rates.
	 * Use FVSCHEDULE to calculate the future value of an investment with a variable or adjustable rate.
	 *
	 * Excel Function:
	 *        FVSCHEDULE(principal,schedule)
	 *
	 * @param float $principal the present value
	 * @param float[] $schedule an array of interest rates to apply
	 *
	 * @return float|string
	 * @see Financial\CashFlow\Single::futureValue()
	 *
	 * @deprecated 1.18.0
	 *      Use the futureValue() method in the Financial\CashFlow\Single class instead
	 */
	public static function FVSCHEDULE( $principal, $schedule ) {
		return Financial\CashFlow\Single::futureValue( $principal, $schedule );
	}

	/**
	 * INTRATE.
	 *
	 * Returns the interest rate for a fully invested security.
	 *
	 * Excel Function:
	 *        INTRATE(settlement,maturity,investment,redemption[,basis])
	 *
	 * @param mixed $settlement The security's settlement date.
	 *                              The security settlement date is the date after the issue date when the security
	 *                                  is traded to the buyer.
	 * @param mixed $maturity The security's maturity date.
	 *                            The maturity date is the date when the security expires.
	 * @param int $investment the amount invested in the security
	 * @param int $redemption the amount to be received at maturity
	 * @param int $basis The type of day count to use.
	 *                       0 or omitted    US (NASD) 30/360
	 *                       1               Actual/actual
	 *                       2               Actual/360
	 *                       3               Actual/365
	 *                       4               European 30/360
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the interest() method in the Financial\Securities\Rates class instead
	 * @see Financial\Securities\Rates::interest()
	 *
	 */
	public static function INTRATE( $settlement, $maturity, $investment, $redemption, $basis = 0 ) {
		return Financial\Securities\Rates::interest( $settlement, $maturity, $investment, $redemption, $basis );
	}

	/**
	 * IPMT.
	 *
	 * Returns the interest payment for a given period for an investment based on periodic, constant payments
	 *         and a constant interest rate.
	 *
	 * Excel Function:
	 *        IPMT(rate,per,nper,pv[,fv][,type])
	 *
	 * @param float $rate Interest rate per period
	 * @param int $per Period for which we want to find the interest
	 * @param int $nper Number of periods
	 * @param float $pv Present Value
	 * @param float $fv Future Value
	 * @param int $type Payment type: 0 = at the end of each period, 1 = at the beginning of each period
	 *
	 * @return float|string
	 * @see Financial\CashFlow\Constant\Periodic\Interest::payment()
	 *
	 * @deprecated 1.18.0
	 *      Use the payment() method in the Financial\CashFlow\Constant\Periodic\Interest class instead
	 */
	public static function IPMT( $rate, $per, $nper, $pv, $fv = 0, $type = 0 ) {
		return Financial\CashFlow\Constant\Periodic\Interest::payment( $rate, $per, $nper, $pv, $fv, $type );
	}

	/**
	 * IRR.
	 *
	 * Returns the internal rate of return for a series of cash flows represented by the numbers in values.
	 * These cash flows do not have to be even, as they would be for an annuity. However, the cash flows must occur
	 * at regular intervals, such as monthly or annually. The internal rate of return is the interest rate received
	 * for an investment consisting of payments (negative values) and income (positive values) that occur at regular
	 * periods.
	 *
	 * Excel Function:
	 *        IRR(values[,guess])
	 *
	 * @param mixed $values An array or a reference to cells that contain numbers for which you want
	 *                                    to calculate the internal rate of return.
	 *                                Values must contain at least one positive value and one negative value to
	 *                                    calculate the internal rate of return.
	 * @param mixed $guess A number that you guess is close to the result of IRR
	 *
	 * @return float|string
	 * @see Financial\CashFlow\Variable\Periodic::rate()
	 *
	 * @deprecated 1.18.0
	 *      Use the rate() method in the Financial\CashFlow\Variable\Periodic class instead
	 */
	public static function IRR( $values, $guess = 0.1 ) {
		return Financial\CashFlow\Variable\Periodic::rate( $values, $guess );
	}

	/**
	 * ISPMT.
	 *
	 * Returns the interest payment for an investment based on an interest rate and a constant payment schedule.
	 *
	 * Excel Function:
	 *     =ISPMT(interest_rate, period, number_payments, pv)
	 *
	 * @param array $args
	 *
	 * @return float|string
	 * @deprecated 1.18.0
	 *      Use the schedulePayment() method in the Financial\CashFlow\Constant\Periodic\Interest class instead
	 * @see Financial\CashFlow\Constant\Periodic\Interest::schedulePayment()
	 *
	 * interest_rate is the interest rate for the investment
	 *
	 * period is the period to calculate the interest rate.  It must be betweeen 1 and number_payments.
	 *
	 * number_payments is the number of payments for the annuity
	 *
	 * pv is the loan amount or present value of the payments
	 *
	 */
	public static function ISPMT( ...$args ) {
		return Financial\CashFlow\Constant\Periodic\Interest::schedulePayment( ...$args );
	}

	/**
	 * MIRR.
	 *
	 * Returns the modified internal rate of return for a series of periodic cash flows. MIRR considers both
	 *        the cost of the investment and the interest received on reinvestment of cash.
	 *
	 * Excel Function:
	 *        MIRR(values,finance_rate, reinvestment_rate)
	 *
	 * @param mixed $values An array or a reference to cells that contain a series of payments and
	 *                         income occurring at regular intervals.
	 *                      Payments are negative value, income is positive values.
	 * @param mixed $finance_rate The interest rate you pay on the money used in the cash flows
	 * @param mixed $reinvestment_rate The interest rate you receive on the cash flows as you reinvest them
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the modifiedRate() method in the Financial\CashFlow\Variable\Periodic class instead
	 * @see Financial\CashFlow\Variable\Periodic::modifiedRate()
	 *
	 */
	public static function MIRR( $values, $finance_rate, $reinvestment_rate ) {
		return Financial\CashFlow\Variable\Periodic::modifiedRate( $values, $finance_rate, $reinvestment_rate );
	}

	/**
	 * NOMINAL.
	 *
	 * Returns the nominal interest rate given the effective rate and the number of compounding payments per year.
	 *
	 * Excel Function:
	 *        NOMINAL(effect_rate, npery)
	 *
	 * @param float $effectiveRate Effective interest rate
	 * @param int $periodsPerYear Number of compounding payments per year
	 *
	 * @return float|string Result, or a string containing an error
	 * @see Financial\InterestRate::nominal()
	 *
	 * @deprecated 1.18.0
	 *      Use the nominal() method in the Financial\InterestRate class instead
	 */
	public static function NOMINAL( $effectiveRate = 0, $periodsPerYear = 0 ) {
		return InterestRate::nominal( $effectiveRate, $periodsPerYear );
	}

	/**
	 * NPER.
	 *
	 * Returns the number of periods for a cash flow with constant periodic payments (annuities), and interest rate.
	 *
	 * @param float $rate Interest rate per period
	 * @param int $pmt Periodic payment (annuity)
	 * @param float $pv Present Value
	 * @param float $fv Future Value
	 * @param int $type Payment type: 0 = at the end of each period, 1 = at the beginning of each period
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the periods() method in the Financial\CashFlow\Constant\Periodic class instead
	 * @see Financial\CashFlow\Constant\Periodic::periods()
	 *
	 */
	public static function NPER( $rate = 0, $pmt = 0, $pv = 0, $fv = 0, $type = 0 ) {
		return Financial\CashFlow\Constant\Periodic::periods( $rate, $pmt, $pv, $fv, $type );
	}

	/**
	 * NPV.
	 *
	 * Returns the Net Present Value of a cash flow series given a discount rate.
	 *
	 * @param array $args
	 *
	 * @return float
	 * @deprecated 1.18.0
	 *      Use the presentValue() method in the Financial\CashFlow\Variable\Periodic class instead
	 * @see Financial\CashFlow\Variable\Periodic::presentValue()
	 *
	 */
	public static function NPV( ...$args ) {
		return Financial\CashFlow\Variable\Periodic::presentValue( ...$args );
	}

	/**
	 * PDURATION.
	 *
	 * Calculates the number of periods required for an investment to reach a specified value.
	 *
	 * @param float $rate Interest rate per period
	 * @param float $pv Present Value
	 * @param float $fv Future Value
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the periods() method in the Financial\CashFlow\Single class instead
	 * @see Financial\CashFlow\Single::periods()
	 *
	 */
	public static function PDURATION( $rate = 0, $pv = 0, $fv = 0 ) {
		return Financial\CashFlow\Single::periods( $rate, $pv, $fv );
	}

	/**
	 * PMT.
	 *
	 * Returns the constant payment (annuity) for a cash flow with a constant interest rate.
	 *
	 * @param float $rate Interest rate per period
	 * @param int $nper Number of periods
	 * @param float $pv Present Value
	 * @param float $fv Future Value
	 * @param int $type Payment type: 0 = at the end of each period, 1 = at the beginning of each period
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the annuity() method in the Financial\CashFlow\Constant\Periodic\Payments class instead
	 * @see Financial\CashFlow\Constant\Periodic\Payments::annuity()
	 *
	 */
	public static function PMT( $rate = 0, $nper = 0, $pv = 0, $fv = 0, $type = 0 ) {
		return Financial\CashFlow\Constant\Periodic\Payments::annuity( $rate, $nper, $pv, $fv, $type );
	}

	/**
	 * PPMT.
	 *
	 * Returns the interest payment for a given period for an investment based on periodic, constant payments
	 *         and a constant interest rate.
	 *
	 * @param float $rate Interest rate per period
	 * @param int $per Period for which we want to find the interest
	 * @param int $nper Number of periods
	 * @param float $pv Present Value
	 * @param float $fv Future Value
	 * @param int $type Payment type: 0 = at the end of each period, 1 = at the beginning of each period
	 *
	 * @return float|string Result, or a string containing an error
	 * @see Financial\CashFlow\Constant\Periodic\Payments::interestPayment()
	 *
	 * @deprecated 1.18.0
	 *      Use the interestPayment() method in the Financial\CashFlow\Constant\Periodic\Payments class instead
	 */
	public static function PPMT( $rate, $per, $nper, $pv, $fv = 0, $type = 0 ) {
		return Financial\CashFlow\Constant\Periodic\Payments::interestPayment( $rate, $per, $nper, $pv, $fv, $type );
	}

	/**
	 * PRICE.
	 *
	 * Returns the price per $100 face value of a security that pays periodic interest.
	 *
	 * @param mixed $settlement The security's settlement date.
	 *                              The security settlement date is the date after the issue date when the security
	 *                              is traded to the buyer.
	 * @param mixed $maturity The security's maturity date.
	 *                                The maturity date is the date when the security expires.
	 * @param float $rate the security's annual coupon rate
	 * @param float $yield the security's annual yield
	 * @param float $redemption The number of coupon payments per year.
	 *                              For annual payments, frequency = 1;
	 *                              for semiannual, frequency = 2;
	 *                              for quarterly, frequency = 4.
	 * @param int $frequency
	 * @param int $basis The type of day count to use.
	 *                       0 or omitted    US (NASD) 30/360
	 *                       1                Actual/actual
	 *                       2                Actual/360
	 *                       3                Actual/365
	 *                       4                European 30/360
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the price() method in the Financial\Securities\Price class instead
	 * @see Financial\Securities\Price::price()
	 *
	 */
	public static function PRICE( $settlement, $maturity, $rate, $yield, $redemption, $frequency, $basis = 0 ) {
		return Securities\Price::price( $settlement, $maturity, $rate, $yield, $redemption, $frequency, $basis );
	}

	/**
	 * PRICEDISC.
	 *
	 * Returns the price per $100 face value of a discounted security.
	 *
	 * @param mixed $settlement The security's settlement date.
	 *                              The security settlement date is the date after the issue date when the security
	 *                              is traded to the buyer.
	 * @param mixed $maturity The security's maturity date.
	 *                            The maturity date is the date when the security expires.
	 * @param mixed $discount The security's discount rate
	 * @param int $redemption The security's redemption value per $100 face value
	 * @param int $basis The type of day count to use.
	 *                                        0 or omitted    US (NASD) 30/360
	 *                                        1                Actual/actual
	 *                                        2                Actual/360
	 *                                        3                Actual/365
	 *                                        4                European 30/360
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the priceDiscounted() method in the Financial\Securities\Price class instead
	 * @see Financial\Securities\Price::priceDiscounted()
	 *
	 */
	public static function PRICEDISC( $settlement, $maturity, $discount, $redemption, $basis = 0 ) {
		return Securities\Price::priceDiscounted( $settlement, $maturity, $discount, $redemption, $basis );
	}

	/**
	 * PRICEMAT.
	 *
	 * Returns the price per $100 face value of a security that pays interest at maturity.
	 *
	 * @param mixed $settlement The security's settlement date.
	 *                              The security's settlement date is the date after the issue date when the security
	 *                              is traded to the buyer.
	 * @param mixed $maturity The security's maturity date.
	 *                            The maturity date is the date when the security expires.
	 * @param mixed $issue The security's issue date
	 * @param mixed $rate The security's interest rate at date of issue
	 * @param mixed $yield The security's annual yield
	 * @param int $basis The type of day count to use.
	 *                                        0 or omitted    US (NASD) 30/360
	 *                                        1                Actual/actual
	 *                                        2                Actual/360
	 *                                        3                Actual/365
	 *                                        4                European 30/360
	 *
	 * @return float|string Result, or a string containing an error
	 * @see Financial\Securities\Price::priceAtMaturity()
	 *
	 * @deprecated 1.18.0
	 *      Use the priceAtMaturity() method in the Financial\Securities\Price class instead
	 */
	public static function PRICEMAT( $settlement, $maturity, $issue, $rate, $yield, $basis = 0 ) {
		return Securities\Price::priceAtMaturity( $settlement, $maturity, $issue, $rate, $yield, $basis );
	}

	/**
	 * PV.
	 *
	 * Returns the Present Value of a cash flow with constant payments and interest rate (annuities).
	 *
	 * @param float $rate Interest rate per period
	 * @param int $nper Number of periods
	 * @param float $pmt Periodic payment (annuity)
	 * @param float $fv Future Value
	 * @param int $type Payment type: 0 = at the end of each period, 1 = at the beginning of each period
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the presentValue() method in the Financial\CashFlow\Constant\Periodic class instead
	 * @see Financial\CashFlow\Constant\Periodic::presentValue()
	 *
	 */
	public static function PV( $rate = 0, $nper = 0, $pmt = 0, $fv = 0, $type = 0 ) {
		return Financial\CashFlow\Constant\Periodic::presentValue( $rate, $nper, $pmt, $fv, $type );
	}

	/**
	 * RATE.
	 *
	 * Returns the interest rate per period of an annuity.
	 * RATE is calculated by iteration and can have zero or more solutions.
	 * If the successive results of RATE do not converge to within 0.0000001 after 20 iterations,
	 * RATE returns the #NUM! error value.
	 *
	 * Excel Function:
	 *        RATE(nper,pmt,pv[,fv[,type[,guess]]])
	 *
	 * @param mixed $nper The total number of payment periods in an annuity
	 * @param mixed $pmt The payment made each period and cannot change over the life
	 *                                    of the annuity.
	 *                                Typically, pmt includes principal and interest but no other
	 *                                    fees or taxes.
	 * @param mixed $pv The present value - the total amount that a series of future
	 *                                    payments is worth now
	 * @param mixed $fv The future value, or a cash balance you want to attain after
	 *                                    the last payment is made. If fv is omitted, it is assumed
	 *                                    to be 0 (the future value of a loan, for example, is 0).
	 * @param mixed $type A number 0 or 1 and indicates when payments are due:
	 *                                        0 or omitted    At the end of the period.
	 *                                        1                At the beginning of the period.
	 * @param mixed $guess Your guess for what the rate will be.
	 *                                    If you omit guess, it is assumed to be 10 percent.
	 *
	 * @return float|string
	 * @see Financial\CashFlow\Constant\Periodic\Interest::rate()
	 *
	 * @deprecated 1.18.0
	 *      Use the rate() method in the Financial\CashFlow\Constant\Periodic\Interest class instead
	 */
	public static function RATE( $nper, $pmt, $pv, $fv = 0.0, $type = 0, $guess = 0.1 ) {
		return Financial\CashFlow\Constant\Periodic\Interest::rate( $nper, $pmt, $pv, $fv, $type, $guess );
	}

	/**
	 * RECEIVED.
	 *
	 * Returns the amount received at maturity for a fully invested Security.
	 *
	 * @param mixed $settlement The security's settlement date.
	 *                              The security settlement date is the date after the issue date when the security
	 *                                  is traded to the buyer.
	 * @param mixed $maturity The security's maturity date.
	 *                            The maturity date is the date when the security expires.
	 * @param mixed $investment The amount invested in the security
	 * @param mixed $discount The security's discount rate
	 * @param mixed $basis The type of day count to use.
	 *                         0 or omitted    US (NASD) 30/360
	 *                         1               Actual/actual
	 *                         2               Actual/360
	 *                         3               Actual/365
	 *                         4               European 30/360
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the received() method in the Financial\Securities\Price class instead
	 * @see Financial\Securities\Price::received()
	 *
	 */
	public static function RECEIVED( $settlement, $maturity, $investment, $discount, $basis = 0 ) {
		return Financial\Securities\Price::received( $settlement, $maturity, $investment, $discount, $basis );
	}

	/**
	 * RRI.
	 *
	 * Calculates the interest rate required for an investment to grow to a specified future value .
	 *
	 * @param float $nper The number of periods over which the investment is made
	 * @param float $pv Present Value
	 * @param float $fv Future Value
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the interestRate() method in the Financial\CashFlow\Single class instead
	 * @see Financial\CashFlow\Single::interestRate()
	 *
	 */
	public static function RRI( $nper = 0, $pv = 0, $fv = 0 ) {
		return Financial\CashFlow\Single::interestRate( $nper, $pv, $fv );
	}

	/**
	 * SLN.
	 *
	 * Returns the straight-line depreciation of an asset for one period
	 *
	 * @param mixed $cost Initial cost of the asset
	 * @param mixed $salvage Value at the end of the depreciation
	 * @param mixed $life Number of periods over which the asset is depreciated
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the SLN() method in the Financial\Depreciation class instead
	 * @see Financial\Depreciation::SLN()
	 *
	 */
	public static function SLN( $cost, $salvage, $life ) {
		return Depreciation::SLN( $cost, $salvage, $life );
	}

	/**
	 * SYD.
	 *
	 * Returns the sum-of-years' digits depreciation of an asset for a specified period.
	 *
	 * @param mixed $cost Initial cost of the asset
	 * @param mixed $salvage Value at the end of the depreciation
	 * @param mixed $life Number of periods over which the asset is depreciated
	 * @param mixed $period Period
	 *
	 * @return float|string Result, or a string containing an error
	 * @see Financial\Depreciation::SYD()
	 *
	 * @deprecated 1.18.0
	 *      Use the SYD() method in the Financial\Depreciation class instead
	 */
	public static function SYD( $cost, $salvage, $life, $period ) {
		return Depreciation::SYD( $cost, $salvage, $life, $period );
	}

	/**
	 * TBILLEQ.
	 *
	 * Returns the bond-equivalent yield for a Treasury bill.
	 *
	 * @param mixed $settlement The Treasury bill's settlement date.
	 *                          The Treasury bill's settlement date is the date after the issue date when the
	 *                              Treasury bill is traded to the buyer.
	 * @param mixed $maturity The Treasury bill's maturity date.
	 *                                The maturity date is the date when the Treasury bill expires.
	 * @param mixed $discount The Treasury bill's discount rate
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the bondEquivalentYield() method in the Financial\TreasuryBill class instead
	 * @see Financial\TreasuryBill::bondEquivalentYield()
	 *
	 */
	public static function TBILLEQ( $settlement, $maturity, $discount ) {
		return TreasuryBill::bondEquivalentYield( $settlement, $maturity, $discount );
	}

	/**
	 * TBILLPRICE.
	 *
	 * Returns the price per $100 face value for a Treasury bill.
	 *
	 * @param mixed $settlement The Treasury bill's settlement date.
	 *                                The Treasury bill's settlement date is the date after the issue date
	 *                                    when the Treasury bill is traded to the buyer.
	 * @param mixed $maturity The Treasury bill's maturity date.
	 *                                The maturity date is the date when the Treasury bill expires.
	 * @param mixed $discount The Treasury bill's discount rate
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the price() method in the Financial\TreasuryBill class instead
	 * @see Financial\TreasuryBill::price()
	 *
	 */
	public static function TBILLPRICE( $settlement, $maturity, $discount ) {
		return TreasuryBill::price( $settlement, $maturity, $discount );
	}

	/**
	 * TBILLYIELD.
	 *
	 * Returns the yield for a Treasury bill.
	 *
	 * @param mixed $settlement The Treasury bill's settlement date.
	 *                                The Treasury bill's settlement date is the date after the issue date
	 *                                    when the Treasury bill is traded to the buyer.
	 * @param mixed $maturity The Treasury bill's maturity date.
	 *                                The maturity date is the date when the Treasury bill expires.
	 * @param mixed $price The Treasury bill's price per $100 face value
	 *
	 * @return float|mixed|string
	 * @deprecated 1.18.0
	 *      Use the yield() method in the Financial\TreasuryBill class instead
	 * @see Financial\TreasuryBill::yield()
	 *
	 */
	public static function TBILLYIELD( $settlement, $maturity, $price ) {
		return TreasuryBill::yield( $settlement, $maturity, $price );
	}

	/**
	 * XIRR.
	 *
	 * Returns the internal rate of return for a schedule of cash flows that is not necessarily periodic.
	 *
	 * Excel Function:
	 *        =XIRR(values,dates,guess)
	 *
	 * @param float[] $values A series of cash flow payments
	 *                                The series of values must contain at least one positive value & one negative value
	 * @param mixed[] $dates A series of payment dates
	 *                                The first payment date indicates the beginning of the schedule of payments
	 *                                All other dates must be later than this date, but they may occur in any order
	 * @param float $guess An optional guess at the expected answer
	 *
	 * @return float|mixed|string
	 * @deprecated 1.18.0
	 *      Use the rate() method in the Financial\CashFlow\Variable\NonPeriodic class instead
	 * @see Financial\CashFlow\Variable\NonPeriodic::rate()
	 *
	 */
	public static function XIRR( $values, $dates, $guess = 0.1 ) {
		return Financial\CashFlow\Variable\NonPeriodic::rate( $values, $dates, $guess );
	}

	/**
	 * XNPV.
	 *
	 * Returns the net present value for a schedule of cash flows that is not necessarily periodic.
	 * To calculate the net present value for a series of cash flows that is periodic, use the NPV function.
	 *
	 * Excel Function:
	 *        =XNPV(rate,values,dates)
	 *
	 * @param float $rate the discount rate to apply to the cash flows
	 * @param float[] $values A series of cash flows that corresponds to a schedule of payments in dates.
	 *                          The first payment is optional and corresponds to a cost or payment that occurs
	 *                              at the beginning of the investment.
	 *                          If the first value is a cost or payment, it must be a negative value.
	 *                             All succeeding payments are discounted based on a 365-day year.
	 *                          The series of values must contain at least one positive value and one negative value.
	 * @param mixed[] $dates A schedule of payment dates that corresponds to the cash flow payments.
	 *                         The first payment date indicates the beginning of the schedule of payments.
	 *                         All other dates must be later than this date, but they may occur in any order.
	 *
	 * @return float|mixed|string
	 * @deprecated 1.18.0
	 *      Use the presentValue() method in the Financial\CashFlow\Variable\NonPeriodic class instead
	 * @see Financial\CashFlow\Variable\NonPeriodic::presentValue()
	 *
	 */
	public static function XNPV( $rate, $values, $dates ) {
		return Financial\CashFlow\Variable\NonPeriodic::presentValue( $rate, $values, $dates );
	}

	/**
	 * YIELDDISC.
	 *
	 * Returns the annual yield of a security that pays interest at maturity.
	 *
	 * @param mixed $settlement The security's settlement date.
	 *                              The security's settlement date is the date after the issue date when the security
	 *                              is traded to the buyer.
	 * @param mixed $maturity The security's maturity date.
	 *                            The maturity date is the date when the security expires.
	 * @param mixed $price The security's price per $100 face value
	 * @param int $redemption The security's redemption value per $100 face value
	 * @param int $basis The type of day count to use.
	 *                                        0 or omitted    US (NASD) 30/360
	 *                                        1                Actual/actual
	 *                                        2                Actual/360
	 *                                        3                Actual/365
	 *                                        4                European 30/360
	 *
	 * @return float|string Result, or a string containing an error
	 * @deprecated 1.18.0
	 *      Use the yieldDiscounted() method in the Financial\Securities\Yields class instead
	 * @see Financial\Securities\Yields::yieldDiscounted()
	 *
	 */
	public static function YIELDDISC( $settlement, $maturity, $price, $redemption, $basis = 0 ) {
		return Securities\Yields::yieldDiscounted( $settlement, $maturity, $price, $redemption, $basis );
	}

	/**
	 * YIELDMAT.
	 *
	 * Returns the annual yield of a security that pays interest at maturity.
	 *
	 * @param mixed $settlement The security's settlement date.
	 *                              The security's settlement date is the date after the issue date when the security
	 *                              is traded to the buyer.
	 * @param mixed $maturity The security's maturity date.
	 *                            The maturity date is the date when the security expires.
	 * @param mixed $issue The security's issue date
	 * @param mixed $rate The security's interest rate at date of issue
	 * @param mixed $price The security's price per $100 face value
	 * @param int $basis The type of day count to use.
	 *                       0 or omitted    US (NASD) 30/360
	 *                       1               Actual/actual
	 *                       2               Actual/360
	 *                       3               Actual/365
	 *                       4               European 30/360
	 *
	 * @return float|string Result, or a string containing an error
	 * @see Financial\Securities\Yields::yieldAtMaturity()
	 *
	 * @deprecated 1.18.0
	 *      Use the yieldAtMaturity() method in the Financial\Securities\Yields class instead
	 */
	public static function YIELDMAT( $settlement, $maturity, $issue, $rate, $price, $basis = 0 ) {
		return Securities\Yields::yieldAtMaturity( $settlement, $maturity, $issue, $rate, $price, $basis );
	}
}
