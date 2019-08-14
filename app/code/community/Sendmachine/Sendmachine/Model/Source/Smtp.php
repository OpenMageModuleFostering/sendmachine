<?php

class Sendmachine_Sendmachine_Model_Source_Smtp {

	public function toOptionArray() {
		
		return array(array(
				'value' => 'OPEN',
				'label' => 'No Encryption',
			), array(
				'value' => 'SSL',
				'label' => 'SSL',
			), array(
				'value' => 'TLS',
				'label' => 'TLS'
		));
	}

}
