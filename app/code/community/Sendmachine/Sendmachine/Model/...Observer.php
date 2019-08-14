<?php

class Septi_Sendmachine_Model_Observer {

	public function __construct() {

		$this->sm = Mage::getModel('sendmachine/sm4magento');
	}

	public function sendmachineObserver() {

		$credentials = $this->sm->getCredentials();
		$this->sm->connectApi($credentials);
		
		if (Mage::getStoreConfigFlag('system/smtp/disable')) {
			$this->sm->setData('system/smtp/disable', 0);
		}
	}

}
