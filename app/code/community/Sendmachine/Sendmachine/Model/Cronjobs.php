<?php

class Sendmachine_Sendmachine_Model_Cronjobs {

	const IMPORT_LIMIT = 1000;

	public function __construct() {

		$smInstance = Mage::registry('sm_model');
		$this->sm = $smInstance ? $smInstance : Mage::getModel('sendmachine/sendmachine');

		Mage::register('sm_model', $this->sm);
	}

	public function importToNewsletter($store, $logId, $listId) {

		$this->sm->importMode = true;

		$_limit = $this->sm->get('import_subscribers_limit');
		$limit = ($_limit > self::IMPORT_LIMIT) ? self::IMPORT_LIMIT : $_limit;

		if (!$listId) {
			$this->sm->addImportExportLog('import', 'failed', 0, $logId);
			return false;
		}

		$members = $this->sm->fetchListSubscribers($listId, $limit);

		$sm_subscribers = false;
		if ($members && count($members)) {

			foreach ($members as $val) {
				$sm_subscribers[] = $val['email'];
			}
		}

		Mage::app()->setCurrentStore((int) $store);

		if ($sm_subscribers && count($sm_subscribers)) {

			$subscribe_model = Mage::getModel('newsletter/subscriber');

			foreach ($sm_subscribers as $email) {

				$subscribe_model->setImportMode(true)->subscribe($email);
				$subscriber = $subscribe_model->loadByEmail($email);
				$subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
				$subscriber->save();
			}
		}

		$this->sm->addImportExportLog('import', 'completed', count($sm_subscribers), $logId);
		$this->sm->importMode = false;
	}

}
