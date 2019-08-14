<?php

class Sendmachine_Sendmachine_Model_Mysql4_Sendmachine extends Mage_Core_Model_Mysql4_Abstract {

	public function _construct() {
		
		$this->_init('sendmachine/sendmachine', 'id');
	}

}
