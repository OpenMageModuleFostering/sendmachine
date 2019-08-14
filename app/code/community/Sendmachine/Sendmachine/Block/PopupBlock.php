<?php

Class Sendmachine_Sendmachine_Block_PopupBlock extends Mage_Core_Block_Template {

	private $sm = NULL;

	public function displayPopup() {

		$smInstance = Mage::registry('sm_model');
		$this->sm = $smInstance ? $smInstance : Mage::getModel('sendmachine/sendmachine');

		if ($this->sm->apiConnected() && $this->sm->get('plugin_enabled') && $this->sm->get('enable_subscribe_popup')) {
			
			$cookie = Mage::getSingleton('core/cookie');
			$cookie_key = 'sendmachine_popup_count_'.Mage::app()->getStore()->getStoreId();
			if (!$count = $cookie->get($cookie_key)) {
				$cookie->set($cookie_key, 1, 3600 * 24 * 365, '/');
				$count = 0;
			}

			$configCount = $this->sm->get('popup_show_after_page');

			if ($configCount > $count) {
				$count++;
				$cookie->set($cookie_key, $count, 3600 * 24 * 365, '/');

				if ($configCount == $count) {
					return true;
				}
			}

			return false;
		}

		return false;
	}

	public function formData() {

		$fields = $this->sm->get('list_custom_fields');
		return Mage::helper('sendmachine')->customfields2form($fields);
	}

	public function popupDescription() {

		return $this->sm->get('popup_text_header');
	}

	public function getPopupDelay() {

		return (int) $this->sm->get('popup_delay');
	}

	public function getPostUrl() {

		return Mage::getUrl('sendmachine/subscribe/');
	}

	public function getSubscribePopupDelay() {

		return $this->sm->get('hide_after_subscribe');
	}

}
