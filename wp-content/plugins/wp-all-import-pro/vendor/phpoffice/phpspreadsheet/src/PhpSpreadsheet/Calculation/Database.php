<?php

namespace PhpOffice\PhpSpreadsheet\Calculation;

/**
 * @deprecated 1.17.0
 *
 * @codeCoverageIgnore
 */
class Database {
	/**
	 * DAVERAGE.
	 *
	 * Averages the values in a column of a list or database that match conditions you specify.
	 *
	 * Excel Function:
	 *        DAVERAGE(database,field,criteria)
	 *
	 * @param mixed[] $database The range of cells that makes up the list or database.
	 *                                        A database is a list of related data in which rows of related
	 *                                        information are records, and columns of data are fields. The
	 *                                        first row of the list contains labels for each column.
	 * @param int|string $field Indicates which column is used in the function. Enter the
	 *                                        column label enclosed between double quotation marks, such as
	 *                                        "Age" or "Yield," or a number (without quotation marks) that
	 *                                        represents the position of the column within the list: 1 for
	 *                                        the first column, 2 for the second column, and so on.
	 * @param mixed[] $criteria The range of cells that contains the conditions you specify.
	 *                                        You can use any range for the criteria argument, as long as it
	 *                                        includes at least one column label and at least one cell below
	 *                                        the column label in which you specify a condition for the
	 *                                        column.
	 *
	 * @return float|string
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the Database\DAverage class instead
	 * @see Database\DAverage::evaluate()
	 *
	 */
	public static function DAVERAGE( $database, $field, $criteria ) {
		return Database\DAverage::evaluate( $database, $field, $criteria );
	}

	/**
	 * DCOUNT.
	 *
	 * Counts the cells that contain numbers in a column of a list or database that match conditions
	 * that you specify.
	 *
	 * Excel Function:
	 *        DCOUNT(database,[field],criteria)
	 *
	 * @param mixed[] $database The range of cells that makes up the list or database.
	 *                                        A database is a list of related data in which rows of related
	 *                                        information are records, and columns of data are fields. The
	 *                                        first row of the list contains labels for each column.
	 * @param null|int|string $field Indicates which column is used in the function. Enter the
	 *                                        column label enclosed between double quotation marks, such as
	 *                                        "Age" or "Yield," or a number (without quotation marks) that
	 *                                        represents the position of the column within the list: 1 for
	 *                                        the first column, 2 for the second column, and so on.
	 * @param mixed[] $criteria The range of cells that contains the conditions you specify.
	 *                                        You can use any range for the criteria argument, as long as it
	 *                                        includes at least one column label and at least one cell below
	 *                                        the column label in which you specify a condition for the
	 *                                        column.
	 *
	 * @return int|string
	 *
	 * @TODO    The field argument is optional. If field is omitted, DCOUNT counts all records in the
	 *            database that match the criteria.
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the Database\DCount class instead
	 * @see Database\DCount::evaluate()
	 *
	 */
	public static function DCOUNT( $database, $field, $criteria ) {
		return Database\DCount::evaluate( $database, $field, $criteria );
	}

	/**
	 * DCOUNTA.
	 *
	 * Counts the nonblank cells in a column of a list or database that match conditions that you specify.
	 *
	 * Excel Function:
	 *        DCOUNTA(database,[field],criteria)
	 *
	 * @param mixed[] $database The range of cells that makes up the list or database.
	 *                                        A database is a list of related data in which rows of related
	 *                                        information are records, and columns of data are fields. The
	 *                                        first row of the list contains labels for each column.
	 * @param int|string $field Indicates which column is used in the function. Enter the
	 *                                        column label enclosed between double quotation marks, such as
	 *                                        "Age" or "Yield," or a number (without quotation marks) that
	 *                                        represents the position of the column within the list: 1 for
	 *                                        the first column, 2 for the second column, and so on.
	 * @param mixed[] $criteria The range of cells that contains the conditions you specify.
	 *                                        You can use any range for the criteria argument, as long as it
	 *                                        includes at least one column label and at least one cell below
	 *                                        the column label in which you specify a condition for the
	 *                                        column.
	 *
	 * @return int|string
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the Database\DCountA class instead
	 * @see Database\DCountA::evaluate()
	 *
	 */
	public static function DCOUNTA( $database, $field, $criteria ) {
		return Database\DCountA::evaluate( $database, $field, $criteria );
	}

	/**
	 * DGET.
	 *
	 * Extracts a single value from a column of a list or database that matches conditions that you
	 * specify.
	 *
	 * Excel Function:
	 *        DGET(database,field,criteria)
	 *
	 * @param mixed[] $database The range of cells that makes up the list or database.
	 *                                        A database is a list of related data in which rows of related
	 *                                        information are records, and columns of data are fields. The
	 *                                        first row of the list contains labels for each column.
	 * @param int|string $field Indicates which column is used in the function. Enter the
	 *                                        column label enclosed between double quotation marks, such as
	 *                                        "Age" or "Yield," or a number (without quotation marks) that
	 *                                        represents the position of the column within the list: 1 for
	 *                                        the first column, 2 for the second column, and so on.
	 * @param mixed[] $criteria The range of cells that contains the conditions you specify.
	 *                                        You can use any range for the criteria argument, as long as it
	 *                                        includes at least one column label and at least one cell below
	 *                                        the column label in which you specify a condition for the
	 *                                        column.
	 *
	 * @return mixed
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the Database\DGet class instead
	 * @see Database\DGet::evaluate()
	 *
	 */
	public static function DGET( $database, $field, $criteria ) {
		return Database\DGet::evaluate( $database, $field, $criteria );
	}

	/**
	 * DMAX.
	 *
	 * Returns the largest number in a column of a list or database that matches conditions you that
	 * specify.
	 *
	 * Excel Function:
	 *        DMAX(database,field,criteria)
	 *
	 * @param mixed[] $database The range of cells that makes up the list or database.
	 *                                        A database is a list of related data in which rows of related
	 *                                        information are records, and columns of data are fields. The
	 *                                        first row of the list contains labels for each column.
	 * @param int|string $field Indicates which column is used in the function. Enter the
	 *                                        column label enclosed between double quotation marks, such as
	 *                                        "Age" or "Yield," or a number (without quotation marks) that
	 *                                        represents the position of the column within the list: 1 for
	 *                                        the first column, 2 for the second column, and so on.
	 * @param mixed[] $criteria The range of cells that contains the conditions you specify.
	 *                                        You can use any range for the criteria argument, as long as it
	 *                                        includes at least one column label and at least one cell below
	 *                                        the column label in which you specify a condition for the
	 *                                        column.
	 *
	 * @return null|float|string
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the Database\DMax class instead
	 * @see Database\DMax::evaluate()
	 *
	 */
	public static function DMAX( $database, $field, $criteria ) {
		return Database\DMax::evaluate( $database, $field, $criteria );
	}

	/**
	 * DMIN.
	 *
	 * Returns the smallest number in a column of a list or database that matches conditions you that
	 * specify.
	 *
	 * Excel Function:
	 *        DMIN(database,field,criteria)
	 *
	 * @param mixed[] $database The range of cells that makes up the list or database.
	 *                                        A database is a list of related data in which rows of related
	 *                                        information are records, and columns of data are fields. The
	 *                                        first row of the list contains labels for each column.
	 * @param int|string $field Indicates which column is used in the function. Enter the
	 *                                        column label enclosed between double quotation marks, such as
	 *                                        "Age" or "Yield," or a number (without quotation marks) that
	 *                                        represents the position of the column within the list: 1 for
	 *                                        the first column, 2 for the second column, and so on.
	 * @param mixed[] $criteria The range of cells that contains the conditions you specify.
	 *                                        You can use any range for the criteria argument, as long as it
	 *                                        includes at least one column label and at least one cell below
	 *                                        the column label in which you specify a condition for the
	 *                                        column.
	 *
	 * @return null|float|string
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the Database\DMin class instead
	 * @see Database\DMin::evaluate()
	 *
	 */
	public static function DMIN( $database, $field, $criteria ) {
		return Database\DMin::evaluate( $database, $field, $criteria );
	}

	/**
	 * DPRODUCT.
	 *
	 * Multiplies the values in a column of a list or database that match conditions that you specify.
	 *
	 * Excel Function:
	 *        DPRODUCT(database,field,criteria)
	 *
	 * @param mixed[] $database The range of cells that makes up the list or database.
	 *                                        A database is a list of related data in which rows of related
	 *                                        information are records, and columns of data are fields. The
	 *                                        first row of the list contains labels for each column.
	 * @param int|string $field Indicates which column is used in the function. Enter the
	 *                                        column label enclosed between double quotation marks, such as
	 *                                        "Age" or "Yield," or a number (without quotation marks) that
	 *                                        represents the position of the column within the list: 1 for
	 *                                        the first column, 2 for the second column, and so on.
	 * @param mixed[] $criteria The range of cells that contains the conditions you specify.
	 *                                        You can use any range for the criteria argument, as long as it
	 *                                        includes at least one column label and at least one cell below
	 *                                        the column label in which you specify a condition for the
	 *                                        column.
	 *
	 * @return float|string
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the Database\DProduct class instead
	 * @see Database\DProduct::evaluate()
	 *
	 */
	public static function DPRODUCT( $database, $field, $criteria ) {
		return Database\DProduct::evaluate( $database, $field, $criteria );
	}

	/**
	 * DSTDEV.
	 *
	 * Estimates the standard deviation of a population based on a sample by using the numbers in a
	 * column of a list or database that match conditions that you specify.
	 *
	 * Excel Function:
	 *        DSTDEV(database,field,criteria)
	 *
	 * @param mixed[] $database The range of cells that makes up the list or database.
	 *                                        A database is a list of related data in which rows of related
	 *                                        information are records, and columns of data are fields. The
	 *                                        first row of the list contains labels for each column.
	 * @param int|string $field Indicates which column is used in the function. Enter the
	 *                                        column label enclosed between double quotation marks, such as
	 *                                        "Age" or "Yield," or a number (without quotation marks) that
	 *                                        represents the position of the column within the list: 1 for
	 *                                        the first column, 2 for the second column, and so on.
	 * @param mixed[] $criteria The range of cells that contains the conditions you specify.
	 *                                        You can use any range for the criteria argument, as long as it
	 *                                        includes at least one column label and at least one cell below
	 *                                        the column label in which you specify a condition for the
	 *                                        column.
	 *
	 * @return float|string
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the Database\DStDev class instead
	 * @see Database\DStDev::evaluate()
	 *
	 */
	public static function DSTDEV( $database, $field, $criteria ) {
		return Database\DStDev::evaluate( $database, $field, $criteria );
	}

	/**
	 * DSTDEVP.
	 *
	 * Calculates the standard deviation of a population based on the entire population by using the
	 * numbers in a column of a list or database that match conditions that you specify.
	 *
	 * Excel Function:
	 *        DSTDEVP(database,field,criteria)
	 *
	 * @param mixed[] $database The range of cells that makes up the list or database.
	 *                                        A database is a list of related data in which rows of related
	 *                                        information are records, and columns of data are fields. The
	 *                                        first row of the list contains labels for each column.
	 * @param int|string $field Indicates which column is used in the function. Enter the
	 *                                        column label enclosed between double quotation marks, such as
	 *                                        "Age" or "Yield," or a number (without quotation marks) that
	 *                                        represents the position of the column within the list: 1 for
	 *                                        the first column, 2 for the second column, and so on.
	 * @param mixed[] $criteria The range of cells that contains the conditions you specify.
	 *                                        You can use any range for the criteria argument, as long as it
	 *                                        includes at least one column label and at least one cell below
	 *                                        the column label in which you specify a condition for the
	 *                                        column.
	 *
	 * @return float|string
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the Database\DStDevP class instead
	 * @see Database\DStDevP::evaluate()
	 *
	 */
	public static function DSTDEVP( $database, $field, $criteria ) {
		return Database\DStDevP::evaluate( $database, $field, $criteria );
	}

	/**
	 * DSUM.
	 *
	 * Adds the numbers in a column of a list or database that match conditions that you specify.
	 *
	 * Excel Function:
	 *        DSUM(database,field,criteria)
	 *
	 * @param mixed[] $database The range of cells that makes up the list or database.
	 *                                        A database is a list of related data in which rows of related
	 *                                        information are records, and columns of data are fields. The
	 *                                        first row of the list contains labels for each column.
	 * @param int|string $field Indicates which column is used in the function. Enter the
	 *                                        column label enclosed between double quotation marks, such as
	 *                                        "Age" or "Yield," or a number (without quotation marks) that
	 *                                        represents the position of the column within the list: 1 for
	 *                                        the first column, 2 for the second column, and so on.
	 * @param mixed[] $criteria The range of cells that contains the conditions you specify.
	 *                                        You can use any range for the criteria argument, as long as it
	 *                                        includes at least one column label and at least one cell below
	 *                                        the column label in which you specify a condition for the
	 *                                        column.
	 *
	 * @return null|float|string
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the Database\DSum class instead
	 * @see Database\DSum::evaluate()
	 *
	 */
	public static function DSUM( $database, $field, $criteria ) {
		return Database\DSum::evaluate( $database, $field, $criteria );
	}

	/**
	 * DVAR.
	 *
	 * Estimates the variance of a population based on a sample by using the numbers in a column
	 * of a list or database that match conditions that you specify.
	 *
	 * Excel Function:
	 *        DVAR(database,field,criteria)
	 *
	 * @param mixed[] $database The range of cells that makes up the list or database.
	 *                                        A database is a list of related data in which rows of related
	 *                                        information are records, and columns of data are fields. The
	 *                                        first row of the list contains labels for each column.
	 * @param int|string $field Indicates which column is used in the function. Enter the
	 *                                        column label enclosed between double quotation marks, such as
	 *                                        "Age" or "Yield," or a number (without quotation marks) that
	 *                                        represents the position of the column within the list: 1 for
	 *                                        the first column, 2 for the second column, and so on.
	 * @param mixed[] $criteria The range of cells that contains the conditions you specify.
	 *                                        You can use any range for the criteria argument, as long as it
	 *                                        includes at least one column label and at least one cell below
	 *                                        the column label in which you specify a condition for the
	 *                                        column.
	 *
	 * @return float|string (string if result is an error)
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the Database\DVar class instead
	 * @see Database\DVar::evaluate()
	 *
	 */
	public static function DVAR( $database, $field, $criteria ) {
		return Database\DVar::evaluate( $database, $field, $criteria );
	}

	/**
	 * DVARP.
	 *
	 * Calculates the variance of a population based on the entire population by using the numbers
	 * in a column of a list or database that match conditions that you specify.
	 *
	 * Excel Function:
	 *        DVARP(database,field,criteria)
	 *
	 * @param mixed[] $database The range of cells that makes up the list or database.
	 *                                        A database is a list of related data in which rows of related
	 *                                        information are records, and columns of data are fields. The
	 *                                        first row of the list contains labels for each column.
	 * @param int|string $field Indicates which column is used in the function. Enter the
	 *                                        column label enclosed between double quotation marks, such as
	 *                                        "Age" or "Yield," or a number (without quotation marks) that
	 *                                        represents the position of the column within the list: 1 for
	 *                                        the first column, 2 for the second column, and so on.
	 * @param mixed[] $criteria The range of cells that contains the conditions you specify.
	 *                                        You can use any range for the criteria argument, as long as it
	 *                                        includes at least one column label and at least one cell below
	 *                                        the column label in which you specify a condition for the
	 *                                        column.
	 *
	 * @return float|string (string if result is an error)
	 * @deprecated 1.17.0
	 *      Use the evaluate() method in the Database\DVarP class instead
	 * @see Database\DVarP::evaluate()
	 *
	 */
	public static function DVARP( $database, $field, $criteria ) {
		return Database\DVarP::evaluate( $database, $field, $criteria );
	}
}
