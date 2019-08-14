<?php

class Sendmachine_Sendmachine_Model_Source_TransactionalCampAreas {

	public function __construct() {
		
		$this->sm = Mage::registry('sm_model');
	}

	public function toOptionArray() {

		$cl = [];
		if ($this->sm->get('api_connected')) {

			$cl = Mage::getModel('core/email_template')->getDefaultTemplatesAsOptionsArray();
		}
		return $cl;
	}

}
