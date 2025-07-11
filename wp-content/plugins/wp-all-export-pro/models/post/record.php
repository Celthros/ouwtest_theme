<?php

class PMXE_Post_Record extends PMXE_Model_Record {
	protected $primary = array( 'post_id' );

	/**
	 * Initialize model instance
	 *
	 * @param array[optional] $data Array of record data to initialize object with
	 */
	public function __construct( $data = array() ) {
		parent::__construct( $data );
		$this->setTable( PMXE_Plugin::getInstance()->getTablePrefix() . 'posts' );
	}

}