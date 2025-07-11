<?php

/**
 * Base class for models
 *
 * @author Pavel Kulbakin <p.kulbakin@gmail.com>
 */
class PMXI_Model_Record extends PMXI_Model {
	/**
	 * Initialize model
	 *
	 * @param array[optional] $data Array of record data to initialize object with
	 */
	public function __construct( $data = array() ) {
		parent::__construct();
		if ( ! is_array( $data ) ) {
			throw new Exception( "Array expected as paramenter for " . get_class( $this ) . "::" . __METHOD__ );
		}
		$data and $this->set( $data );
	}

	/**
	 * @return PMXI_Model_Record
	 * @see PMXI_Model::getBy()
	 */
	public function getBy( $field = null, $value = null ) {
		if ( is_null( $field ) ) {
			throw new Exception( "Field parameter is expected at " . get_class( $this ) . "::" . __METHOD__ );
		}
		$sql    = "SELECT * FROM $this->table WHERE " . $this->buildWhere( $field, $value );
		$result = $this->wpdb->get_row( $sql, ARRAY_A );
		if ( is_array( $result ) ) {
			foreach ( $result as $k => $v ) {
				if ( is_serialized( $v ) ) {
					$result[ $k ] = \pmxi_maybe_unserialize( $v );
				}
			}
			$this->exchangeArray( $result );
		} else {
			$this->clear();
		}

		return $this;
	}

	/**
	 * Ger records related to current one
	 *
	 * @param string $model Class name of model of related records
	 * @param array[optoinal] $keyAssoc
	 *
	 * @return PMXI_Model_List
	 */
	public function getRelated( $model, $keyAssoc = null ) {
		$related = new $model();
		if ( ! empty( $this->id ) ) {
			if ( is_null( $keyAssoc ) ) {
				$defaultPrefix = strtolower( preg_replace( '%^' . strtoupper( PMXI_Plugin::PREFIX ) . '|_Record$%', '', get_class( $this ) ) );
				$keyAssoc      = array();
				foreach ( $this->primary as $key ) {
					$keyAssoc = array( $defaultPrefix . '_' . $key => $key );
				}
			}
			foreach ( $keyAssoc as $foreign => $local ) {
				$keyAssoc[ $foreign ] = $this->$local;
			}
			$related->getBy( $keyAssoc );
		}

		return $related instanceof PMXI_Model_List ? $related->convertRecords() : $related;
	}

	/**
	 * Saves currently set object data as database record
	 * @return PMXI_Model_Record
	 */
	public function insert() {
		if ( $this->wpdb->insert( $this->table, $this->toArray( true ) ) ) {
			if ( isset( $this->auto_increment ) ) {
				$this[ $this->primary[0] ] = $this->wpdb->insert_id;
			}

			return $this;
		} else {
			throw new Exception( $this->wpdb->last_error );
		}
	}

	/**
	 * Update record in database
	 * @return PMXI_Model_Record
	 */
	public function update() {
		$record = $this->toArray( true );
		$this->wpdb->update( $this->table, $record, array_intersect_key( $record, array_flip( $this->primary ) ) );
		if ( $this->wpdb->last_error ) {
			throw new Exception( $this->wpdb->last_error );
		}

		return $this;
	}

	/**
	 * Delete record form database
	 * @return PMXI_Model_Record
	 */
	public function delete() {
		if ( $this->wpdb->query( "DELETE FROM $this->table WHERE " . $this->buildWhere( array_intersect_key( $this->toArray( true ), array_flip( $this->primary ) ) ) ) ) {
			return $this;
		} elseif ( $this->wpdb->last_error ) {
			throw new Exception( $this->wpdb->last_error );
		}
	}

	/**
	 * Insert or Update the record
	 * WARNING: function doesn't check actual record presents in database, it simply tries to insert if no primary key specified and update otherwise
	 * @return PMXI_Model_Record
	 */
	public function save() {
		if ( array_intersect_key( $this->toArray( true ), array_flip( $this->primary ) ) ) {
			$this->update();
		} else {
			$this->insert();
		}

		return $this;
	}

	/**
	 * Set record data
	 * When 1st parameter is an array, it expected to be an associative array of field => value pairs
	 * If 2 parameters are set, first one is expected to be a field name and second - it's value
	 *
	 * @param string|array $field
	 * @param mixed[optional] $value
	 *
	 * @return PMXI_Model_Record
	 */
	public function set( $field, $value = null ) {
		if ( is_array( $field ) and ( ! is_null( $value ) or 0 == count( $field ) ) ) {
			throw new Exception( __CLASS__ . "::set method expects either not empty associative array as the only paramter or field name and it's value as two seperate parameters." );
		}
		if ( is_array( $field ) ) {
			$this->exchangeArray( array_merge( $this->toArray(), $field ) );
		} else {
			$this[ $field ] = $value;
		}

		return $this;
	}

	/**
	 * Magic method to resolved object-like request to record values in format $obj->%FIELD_NAME%
	 *
	 * @param string $field
	 *
	 * @return mixed
	 */
	public function __get( $field ) {
		if ( ! $this->offsetExists( $field ) ) {
			// Retrieve calling function details.
			$called_by = debug_backtrace( ! DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 2 )[1];

			throw new Exception( "Undefined field '$field' requested by: [ " . $called_by['function'] . ' in ' . $called_by['file'] . ' line ' . $called_by['line'] . ' ]' );
		}

		return $this[ $field ];
	}

	/**
	 * Magic method to assign values to record fields in format $obj->%FIELD_NAME = value
	 *
	 * @param string $field
	 * @param mixed $value
	 */
	public function __set( $field, $value ) {
		$this[ $field ] = $value;
	}

	/**
	 * Magic method to check wether some record fields are set
	 *
	 * @param string $field
	 *
	 * @return bool
	 */
	public function __isset( $field ) {
		return $this->offsetExists( $field );
	}

	/**
	 * Magic method to unset record fields
	 *
	 * @param string $field
	 */
	public function __unset( $field ) {
		$this->offsetUnset( $field );
	}

}