<?php

class Sendmachine_Sendmachine_Model_Observer {

	public function handleSubscriber(Varien_Event_Observer $observer) {

		$smInstance = Mage::registry('sm_model');
		$sm = $smInstance ? $smInstance : Mage::getModel('sendmachine/sendmachine');

		if ($sm->get('api_connected') && $sm->get('keep_users_synced') && !$sm->importMode) {

			file_put_contents('/home/septi/work/logs/log', PHP_EOL . "Observer hit!!", FILE_APPEND);

			$event = $observer->getEvent();
			$subscriber = $event->getDataObject();
			$data = $subscriber->getData();

			if ($data['subscriber_status'] == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {

				$listId = $sm->get('selected_contact_list');
				$sm->subscribeToList([$data['subscriber_email']], $listId);
			}
		}

		return $observer;
	}

}
