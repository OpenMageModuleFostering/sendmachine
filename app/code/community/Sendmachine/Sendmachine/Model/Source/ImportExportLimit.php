<?php

class Sendmachine_Sendmachine_Model_Source_ImportExportLimit {

	public function toOptionArray() {

		$values = array(100, 200, 500, 1000, 5000, 10000);

		$ret = array();
		
		foreach ($values as $val) {
			array_push($ret, array('value' => $val, 'label' => $val));
		}

		return $ret;
	}

}
