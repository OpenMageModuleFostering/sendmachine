<?php

class Sendmachine_Sendmachine_Model_Email_Queue extends Mage_Core_Model_Email_Queue {

	/**
	 * Send all messages in a queue
	 *
	 * @return Mage_Core_Model_Email_Queue
	 */
	public function send() {

		/** @var $collection Mage_Core_Model_Resource_Email_Queue_Collection */
		$collection = Mage::getModel('core/email_queue')->getCollection()
				->addOnlyForSendingFilter()
				->setPageSize(self::MESSAGES_LIMIT_PER_CRON_RUN)
				->setCurPage(1)
				->load();


		$smInstance = Mage::registry('sm_model');
		$sm = $smInstance ? $smInstance : Mage::getModel('sendmachine/sendmachine');
		
		ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
		ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

		/** @var $message Mage_Core_Model_Email_Queue */
		foreach ($collection as $message) {
			if ($message->getId()) {
				$parameters = new Varien_Object($message->getMessageParameters());

				$mailer = new Zend_Mail('utf-8');

				if (!$sm->configureEmail()) {

					if ($parameters->getReturnPathEmail() !== null) {
						$mailTransport = new Zend_Mail_Transport_Sendmail("-f" . $parameters->getReturnPathEmail());
						Zend_Mail::setDefaultTransport($mailTransport);
					}
				}

				foreach ($message->getRecipients() as $recipient) {
					list($email, $name, $type) = $recipient;
					switch ($type) {
						case self::EMAIL_TYPE_BCC:
							$mailer->addBcc($email, '=?utf-8?B?' . base64_encode($name) . '?=');
							break;
						case self::EMAIL_TYPE_TO:
						case self::EMAIL_TYPE_CC:
						default:
							$mailer->addTo($email, '=?utf-8?B?' . base64_encode($name) . '?=');
							break;
					}
				}

				if ($parameters->getIsPlain()) {
					$mailer->setBodyText($message->getMessageBody());
				} else {
					$mailer->setBodyHTML($message->getMessageBody());
				}

				$mailer->setSubject('=?utf-8?B?' . base64_encode($parameters->getSubject()) . '?=');


				$_fromEmail = $sm->get('from_email');
				$_fromName = $sm->get('from_name');

				$fromEmail = $_fromEmail ? $_fromEmail : $parameters->getFromEmail();
				$fromName = $_fromName ? $_fromName : $parameters->getFromName();

				$mailer->setFrom($fromEmail, $fromName);

				if ($parameters->getReplyTo() !== null) {
					$mailer->setReplyTo($parameters->getReplyTo());
				}
				if ($parameters->getReturnTo() !== null) {
					$mailer->setReturnPath($parameters->getReturnTo());
				}

				try {
					$mailer->send();
					unset($mailer);
					$message->setProcessedAt(Varien_Date::formatDate(true));
					$message->save();
				} catch (Exception $e) {
					unset($mailer);
					$oldDevMode = Mage::getIsDeveloperMode();
					Mage::setIsDeveloperMode(true);
					Mage::logException($e);
					Mage::setIsDeveloperMode($oldDevMode);

					return false;
				}
			}
		}

		return $this;
	}

}
