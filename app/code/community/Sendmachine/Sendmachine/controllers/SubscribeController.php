<?php

class Sendmachine_Sendmachine_SubscribeController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {

		$smInstance = Mage::registry('sm_model');
		$sm = $smInstance ? $smInstance : Mage::getModel('sendmachine/sendmachine');
		
		$params = $this->getRequest()->getParams();

		$fields = $sm->get('list_custom_fields');
		$response = NULL;
		if (!$fields || !count($fields)) {
			$response = ['code' => 0, 'message' => $this->__('Something went wrong.You were not subscribed')];
		} else {

			$arr = [];
			foreach ($fields as $k => $v) {

				if ($v['required'] && empty($params[$k])) {
					$response = ['code' => 0, 'message' => $this->__('Required data was not filled')];
					break;
				}

				if (!empty($params[$k])) {
					$arr[strtolower($k)] = $params[$k];
				}
			}
			if (!$response) {

				$listId = $sm->get('selected_contact_list');
				if ($sm->subscribeToList([$arr], $listId))
					$response = ['code' => 1, 'message' => $this->__('You have been successfully subscribed')];
				else
					$response = ['code' => 0, 'message' => $this->__('Something went wrong.You were not subscribed')];
			}
		}

		$this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
		$this->getResponse()->setBody(json_encode($response));
	}

}
