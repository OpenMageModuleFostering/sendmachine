<?php

class Sendmachine_Sendmachine_Model_Email_Template extends Mage_Core_Model_Email_Template {

	/**
	 * Send mail to recipient
	 *
	 * @param   array|string       $email        E-mail(s)
	 * @param   array|string|null  $name         receiver name(s)
	 * @param   array              $variables    template variables
	 * @return  boolean
	 * */
	public function send($email, $name = null, array $variables = array()) {

		if (!$this->isValidForSend()) {
			Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
			return false;
		}

		$emails = array_values((array) $email);
		$names = is_array($name) ? $name : (array) $name;
		$names = array_values($names);
		foreach ($emails as $key => $email) {
			if (!isset($names[$key])) {
				$names[$key] = substr($email, 0, strpos($email, '@'));
			}
		}

		$variables['email'] = reset($emails);
		$variables['name'] = reset($names);

		$this->setUseAbsoluteLinks(true);
		$text = $this->getProcessedTemplate($variables, true);
		$subject = $this->getProcessedTemplateSubject($variables);

		$setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
		switch ($setReturnPath) {
			case 1:
				$returnPathEmail = $this->getSenderEmail();
				break;
			case 2:
				$returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
				break;
			default:
				$returnPathEmail = null;
				break;
		}

		if ($this->hasQueue() && $this->getQueue() instanceof Mage_Core_Model_Email_Queue) {
			/** @var $emailQueue Mage_Core_Model_Email_Queue */
			$emailQueue = $this->getQueue();
			$emailQueue->setMessageBody($text);
			$emailQueue->setMessageParameters(array(
						'subject' => $subject,
						'return_path_email' => $returnPathEmail,
						'is_plain' => $this->isPlain(),
						'from_email' => $this->getSenderEmail(),
						'from_name' => $this->getSenderName(),
						'reply_to' => $this->getMail()->getReplyTo(),
						'return_to' => $this->getMail()->getReturnPath(),
					))
					->addRecipients($emails, $names, Mage_Core_Model_Email_Queue::EMAIL_TYPE_TO)
					->addRecipients($this->_bccEmails, array(), Mage_Core_Model_Email_Queue::EMAIL_TYPE_BCC);
			$emailQueue->addMessageToQueue();

			return true;
		}

		ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
		ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

		$mail = $this->getMail();
		$smInstance = Mage::registry('sm_model');
		$sm = $smInstance ? $smInstance : Mage::getModel('sendmachine/sendmachine');

		if (!$sm->configureEmail()) {

			if ($returnPathEmail !== null) {
				$mailTransport = new Zend_Mail_Transport_Sendmail("-f" . $returnPathEmail);
				Zend_Mail::setDefaultTransport($mailTransport);
			}
		}

		foreach ($emails as $key => $email) {
			$mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
		}

		if ($this->isPlain()) {
			$mail->setBodyText($text);
		} else {
			$mail->setBodyHTML($text);
		}

		$mail->setSubject('=?utf-8?B?' . base64_encode($subject) . '?=');

		/* take a look at this chunk of code */
		$_fromEmail = $sm->get('from_email');
		$_fromName = $sm->get('from_name');

		$fromEmail = $_fromEmail ? $_fromEmail : $this->getSenderEmail();
		$fromName = $_fromName ? $_fromName : $this->getSenderName();
		/* take a look at this chunk of code */

		$mail->setFrom($fromEmail, $fromName);

		/* take a look at this chunk of code */
		if ($trCmp = $sm->createTransactionalCampaign($this->getId())) {
			$mail->addHeader($trCmp['header_name'], $trCmp['header_value']);
		}
		/* take a look at this chunk of code */

		try {
			$mail->send();
			$this->_mail = null;
		} catch (Exception $e) {
			$this->_mail = null;
			Mage::logException($e);
			return false;
		}

		return true;
	}

}
