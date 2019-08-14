<?php

class Sendmachine_Sendmachine_SendmachineController extends Mage_Adminhtml_Controller_Action {

	private $sm;

	public function __construct($request, $response, $invokeArgs = array()) {

		parent::__construct($request, $response, $invokeArgs);

		$smInstance = Mage::registry('sm_model');
		$this->sm = $smInstance ? $smInstance : Mage::getModel('sendmachine/sendmachine');
	}
	
	/* MAGENTO PLUGIN CONFIGURATION ACTIONS */

	private function _renderApp($tab = '') {

		Mage::register('sm_model', $this->sm);
		
		$request = Mage::app()->getRequest();
		$website = $request->getParam('website');
		$store = $request->getParam('store');
		$button_data = null;

		$this->sm->setWebsite($website);
		$this->sm->setStore($store, false);
		
		$conflicts = array();
		$rewrites = Mage::getConfig()->getNode()->xpath('//global//rewrite');
		if (is_array($rewrites) && !empty($rewrites)) {
			foreach ($rewrites as $v) {
				if (array_key_exists('email', $v)) {
					array_push($conflicts, (string)$v->email);
				}
			}
		}
		
		if($conflicts) {
			$conflicts = implode("</li><li>", $conflicts);
			Mage::getSingleton('core/session')->addError("<span>Detected overwrite conflict:</span><ul style='list-style-type: disc;margin-top: 5px;'><li>$conflicts</li></ul>");
		}
		
		if ($website AND $store) {
			$button_data = array(
				'label' => $this->__('Reset values to website'),
				'class' => 'reset_to_parent',
				'onclick' => "resetToParent('" . Mage::helper('adminhtml')->getUrl('adminhtml/sendmachine/resetoparent') . "', '$website', '$store', 'website')"
			);
		} elseif ($website) {
			$button_data = array(
				'label' => $this->__('Reset values to default'),
				'class' => 'reset_to_parent',
				'onclick' => "resetToParent('" . Mage::helper('adminhtml')->getUrl('adminhtml/sendmachine/resetoparent') . "', '$website', '$store', 'default')"
			);
		}

		$this->loadLayout();
		$this->_setActiveMenu('system/sendmachine');
		$this->_addContent($this->getLayout()->createBlock('adminhtml/system_config_switcher'));
		if($button_data) {
			$this->_addContent($this->getLayout()->createBlock('sendmachine/appContainer_main', 'smMainFormContainer', array('tab' => $tab))->addButton('reset_to_parent', $button_data, -1, 1));
		}
		else $this->_addContent($this->getLayout()->createBlock('sendmachine/appContainer_main', 'smMainFormContainer', array('tab' => $tab)));
		$this->_addLeft($this->getLayout()->createBlock('sendmachine/appContainer_tabs'));
		$this->getLayout()->getBlock('head')->addJs("sendmachine/admin.js");
		$this->renderLayout();
	}

	public function indexAction() {

		$this->_title($this->__('System'))->_title($this->__('Sendmachine - General settings'));
		$this->_renderApp('general');
	}

	public function listAction() {

		$this->_title($this->__('System'))->_title($this->__('Sendmachine - List settings'));
		$this->_renderApp('lists');
	}

	public function emailAction() {

		$this->_title($this->__('System'))->_title($this->__('Sendmachine - Email settings'));
		$this->_renderApp('email');
	}

	public function saveAction() {

		$params = $this->getRequest()->getParams();
		$tab = isset($params['tab']) ? $params['tab'] : "index";
		$website = $params['website'];
		$store = $params['store'];
		
		unset($params['tab']);
		unset($params['limit']);
		unset($params['page']);
		unset($params['key']);
		unset($params['form_key']);
		unset($params['website']);
		unset($params['store']);
		
		if(isset($params['reset_to_parent']) AND $params['reset_to_parent'] AND ($website OR $store)) {
			
			if($store AND $website) {
				$this->sm->setWebsite($website);
				$this->sm->setStore(null, false);				
			}
			else {
				$this->sm->setWebsite(null);
				$this->sm->setStore(null, false);
			}
			
			$_params = $this->sm->get();
			foreach($params as $k => &$v) {
				$v = isset($_params[$k]) ? $_params[$k] : "";
			}
		}
		
		$this->sm->setWebsite($website);
		$this->sm->setStore($store);

		$initial_credentials = $this->sm->getCredentials();
		$initial_listid = $this->sm->get('selected_contact_list');
		$initial_smtp_encryption = $this->sm->get('smtp_encryption');
		$this->sm->set($params, NULL, false, true);
		$credentials = $this->sm->getCredentials();
		$listid = $this->sm->get('selected_contact_list');
		$this->smtp_encryption = $this->sm->get('smtp_encryption');
		$errorHandled = false;

		if ($initial_credentials != $credentials) {

			if (($connectApi = $this->sm->connectApi()) === true) {
				$this->_initApp();
			}

			else {

				if (!$connectApi) {
					$connectApi = "Unexpected error occurred!";
				}

				$errorHandled = true;
				Mage::getSingleton('adminhtml/session')->addError($this->__($connectApi));
				$this->_resetApp();
			}
		}

		if ($initial_listid != $listid) {

			$fields = $this->sm->fetchCustomFields($listid);
			$this->sm->set('list_custom_fields', Mage::helper('sendmachine')->prepareCustomFields($fields));
		}

		if ($initial_smtp_encryption != $this->smtp_encryption) {

			$smtp_settings = $this->sm->get("provider_smtp_settings");
			$email_settings = Mage::helper('sendmachine')->initEmailConfig($smtp_settings, $this->smtp_encryption);
			$this->sm->set('email_config', $email_settings);
		}

		$this->sm->commit();

		if (!$errorHandled) {
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Successfully saved'));
		}
		
		$suffix = "";
		if($website) $suffix .= "/website/$website";
		if($store) $suffix .= "/store/$store";
		
		$this->getResponse()->setRedirect($this->getUrl("*/*/" . $tab . $suffix));
	}

	
	/* MAGENTO ADMIN ACTIONS */
	
	public function sendtestmailAction() {
		
		$request = Mage::app()->getRequest();
		
		$this->sm->setWebsite($request->getParam('website'));
		$this->sm->setStore($request->getParam('store'));
		
		if (!$this->sm->apiConnected()) {

			Mage::getSingleton('adminhtml/session')->addError($this->__('Api not connected'));
			return false;
		}
		if (!$this->sm->get('email_enabled')) {

			Mage::getSingleton('adminhtml/session')->addError($this->__("'email sending' not enabled"));
			return false;
		}

		$mailer = Mage::getModel('core/email_template');

		$recipientEmail = $request->getParam('emailAddress');

		$sender['name'] = Mage::getStoreConfig('trans_email/ident_general/name');
		$sender['email'] = Mage::getStoreConfig('trans_email/ident_general/email');

		$result = $mailer->sendTransactional("smSendTestEmail", $sender, $recipientEmail, null, array(), $this->sm->getStore());

		if ($result->sent_success) {
			Mage::getSingleton('adminhtml/session')->addSuccess(sprintf($this->__("Test message to '%s' sent successfully"), $recipientEmail));
		} else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Something went wrong, message not sent'));
		}
	}

	public function importToNewsletterAction() {

		$store = $this->getRequest()->getParam('store');
		$this->sm->setStore($store);

		if ($this->sm->apiConnected()) {

			$logId = $this->sm->addImportExportLog('import');
			$listId = $this->sm->get('selected_contact_list');

			$this->sm->addCronjob('importToNewsletter', [$store, $logId, $listId]);

			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Import queued'));
		} else {

			Mage::getSingleton('adminhtml/session')->addError($this->__('Api not connected'));
		}
	}

	public function exportToSendmachineAction() {
		
		$store = $this->getRequest()->getParam('store');
		$this->sm->setStore($store);

		if ($this->sm->apiConnected()) {

			$subscribe_status = array(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);

			$logId = $this->sm->addImportExportLog('export');

			$limit = $this->sm->get('export_subscribers_limit');
			$subscribers = Mage::getModel('newsletter/subscriber')->getCollection()->setPageSize((int) $limit);
			$_subscribers = array();

			if ($subscribers && count($subscribers)) {

				foreach ($subscribers as $v) {

					$data = $v->get();
					if (($store && $store != $data['store_id']) || !in_array($data['subscriber_status'], $subscribe_status)) {
						continue;
					}
					$_subscribers[] = $data['subscriber_email'];
				}
			}

			$listId = $this->sm->get('selected_contact_list');
			$subscribeStatus = $this->sm->subscribeToList($_subscribers, $listId);

			$state = $subscribeStatus ? "completed" : "failed";
			$count = $subscribeStatus ? count($_subscribers) : 0;

			$this->sm->addImportExportLog('export', $state, $count, $logId);

			if ($subscribeStatus) {
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Export successful'));
			} else {
				Mage::getSingleton('adminhtml/session')->addError($this->__('Something went wrong, export not completed'));
			}
		} else {

			Mage::getSingleton('adminhtml/session')->addError($this->__('Api not connected'));
		}
	}

	public function refreshCachedListsAction() {

		if ($this->sm->apiConnected()) {

			if ($resp = $this->sm->fetchContactLists()) {

				$this->sm->set("contact_lists", $resp);

				$listid = $this->sm->get('selected_contact_list');
				$fields = $this->sm->fetchCustomFields($listid);
				$this->sm->set('list_custom_fields', Mage::helper('sendmachine')->prepareCustomFields($fields), true);

				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Lists refreshed'));
			} else {
				Mage::getSingleton('adminhtml/session')->addError($this->__('Something went wrong, lists not refreshed'));
			}
		} else {

			Mage::getSingleton('adminhtml/session')->addError($this->__('Api not connected'));
		}
	}

	private function _initApp() {

		$this->sm->set('api_connected', true);
		$this->sm->set('plugin_enabled', true);
		$this->sm->set('email_enabled', true);
		$this->sm->set('hide_after_subscribe', 15);
		$this->sm->set('sm_cronjob', NULL);
		$this->sm->set('import_subscribers_limit', 100);
		$this->sm->set('export_subscribers_limit', 1000);
		$this->sm->set('popup_show_after_page', 2);
		$this->sm->set('popup_delay', 300);
		$this->sm->set('selected_contact_list', NULL);
		$this->sm->set('list_custom_fields', NULL);
		$this->sm->set('keep_users_synced', true);

		$smtpSettings = $this->sm->fetchSmtpSettings();
		$this->sm->set("provider_smtp_settings", $smtpSettings ? $smtpSettings : "");

		$contactLists = $this->sm->fetchContactLists();
		$this->sm->set("contact_lists", $contactLists ? $contactLists : "");

		$smtp_settings = $this->sm->get("provider_smtp_settings");
		$email_settings = Mage::helper('sendmachine')->initEmailConfig($smtp_settings, 'OPEN');
		$this->sm->set('email_config', $email_settings);

		$this->sm->commit('system/smtp/disable', 0);
	}

	private function _resetApp() {

		$this->sm->set('api_connected', false);
		$this->sm->set('email_config', NULL);
		$this->sm->set('sm_cronjob', NULL);
		$this->sm->set('contact_lists', NULL);
		$this->sm->set('smtp', NULL);
		$this->sm->set('listId', NULL);
		$this->sm->set('provider_smtp_settings', NULL);
	}

}
