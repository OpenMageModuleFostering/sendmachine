<?php

class Sendmachine_Sendmachine_Model_Source_Smtp {

	public function toOptionArray() {
		
		return [
			[
				'value' => 'OPEN',
				'label' => 'No Encryption',
			],
			[
				'value' => 'SSL',
				'label' => 'SSL',
			],
			[
				'value' => 'TLS',
				'label' => 'TLS'
			]
		];
	}

}
