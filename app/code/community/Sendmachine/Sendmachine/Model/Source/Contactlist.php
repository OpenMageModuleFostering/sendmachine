<?php

class Sendmachine_Sendmachine_Model_Source_Contactlist {

	public function __construct() {
		
		$this->sm = Mage::registry('sm_model');
	}

	public function toOptionArray() {

		$cl = array(array(
				'value' => '0',
				'label' => ''
		));
		if ($this->sm->apiConnected()) {

			$data = $this->sm->get('contact_lists');
			if (count($data)) {
				foreach ($data as $value) {
					array_push($cl, array(
						'value' => $value['list_id'],
						'label' => $value['name']
					));
				}
			}
		}
		return $cl;
	}

}
