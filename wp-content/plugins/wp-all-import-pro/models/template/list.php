<?php

class PMXI_Template_List extends PMXI_Model_List {
	public function __construct() {
		parent::__construct();
		$this->setTable( PMXI_Plugin::getInstance()->getTablePrefix() . 'templates' );
	}
}