<?php

class Sendmachine_Sendmachine_Model_Sendmachine extends Mage_Core_Model_Abstract {

	const SM_APP = "sendmachine_application";

	protected $api = NULL;
	protected $appData = NULL;
	
	private $website = null;
	private $store = null;

	protected function _construct() {

		parent::_construct();
		$this->_init('sendmachine/sendmachine');
	}
	
	public function setWebsite($website = null) {
		$this->website = $website;
	}
	
	public function getWebsite() {
		return $this->website;
	}

	public function setStore($store = null, $setCurrentStore = true) {
		$this->store = $store;
		if($this->store AND $setCurrentStore) Mage::app()->setCurrentStore($store);
	}
	
	public function getStore() {
		return $this->store;
	}

	public function get($key = NULL, $forceFetch = false) {

		if ($this->appData === NULL || $forceFetch) {

			if ($this->website AND !$this->store) {
				$tmpData = Mage::app()->getWebsite($this->website)->getConfig(self::SM_APP);
			} else {
				$tmpData = Mage::getStoreConfig(self::SM_APP, $this->store);
			}

			$this->appData = json_decode($tmpData, true);
		}

		if ($key) {
			if (isset($this->appData[$key])) {
				return $this->appData[$key];
			}
			return false;
		}

		return $this->appData;
	}

	public function set($key = "", $value = "", $commit = false, $walk_array = false) {

		if ($key) {

			if (is_null($this->appData)) {
				$this->get();
            }

			if (is_array($key)) {

				foreach ($key as $k => $v) {

					if (is_array($v) && $walk_array)
						$this->_walkArray($this->appData, array($k => $v));
					else
						$this->appData[$k] = $v;
				}
			} else {

				if (count($value) && $walk_array) {
					$this->_walkArray($this->appData, $value);
                } else {
					$this->appData[$key] = $value;
                }
			}

			if ($commit) {
				$this->commit();
            }

			return true;
		}
		return false;
	}

	private function _walkArray(&$master_array, $merge_values) {

		if (is_array($merge_values)) {

			foreach ($merge_values as $k => $v) {
				$this->_walkArray($master_array[$k], $v);
			}
		} else {
			$master_array = $merge_values;
		}
	}

	public function commit($path = self::SM_APP, $value = NULL) {
		
		$scope = "default";
		$scope_id = 0;

		if ($this->website AND $this->store) {
			$scope = "stores";
			$scope_id = Mage::getModel('core/store')->load($this->store, 'code')->getId();
		} elseif ($this->website) {
			$websites = Mage::app()->getWebsites();
			foreach ($websites as $id => $website) {
				if($website->getCode() == $this->website) {
					$website_id = $id;
					continue;
				}
			}
			
			if(!$website_id) $website_id = 0;
			
			$scope = "websites";
			$scope_id = $website_id;
		}

		if (is_null($value)) {
			$value = json_encode($this->appData);
		}

		Mage::getModel('core/config')->saveConfig($path, $value, $scope, $scope_id);
		
		Mage::app()->getCacheInstance()->cleanType('config');
	}

	public function apiConnected() {

		return $this->get("api_connected");
	}

	protected function initApiClass() {

		set_include_path(get_include_path() . PATH_SEPARATOR . Mage::getBaseDir('lib') . '/SendmachineApi');
		require_once(Mage::getBaseDir('lib') . '/SendmachineApi/SendmachineApiClient.php');

		$credentials = $this->getCredentials();

		if (!empty($credentials['username']) && !empty($credentials['password'])) {
			$this->api = new SendmachineApiClient($credentials['username'], $credentials['password']);
		}
	}

	public function connectApi($credentials = NULL) {

		if ($credentials === NULL) {
			$credentials = $this->getCredentials();
		}

		if (!empty($credentials['username']) && !empty($credentials['password'])) {
			try {
				$this->initApiClass();
				$this->api = new SendmachineApiClient($credentials['username'], $credentials['password']);
				$res = $this->api->account->details();
				if (isset($res['user']))
					return true;
				else
					return false;
			} catch (Sendmachine_Error $ex) {
				return $ex->getMessage();
			} catch (Http_Error $ex) {
				
			}
		}

		return NULL;
	}

	public function addCronjob($method = NULL, $args = array()) {

		if ($method) {

			$savedQueueData = $this->get('sm_cronjob');
			if (!is_array($savedQueueData)) {
				$savedQueueData = array();
			}

			array_push($savedQueueData, array('method' => $method, 'args' => $args));

			$this->set('sm_cronjob', $savedQueueData, true);
		}
	}

	public function executeCronjob() {

		$sqd = $this->get('sm_cronjob');

		$this->set('sm_cronjob', NULL, true);

		if ($sqd && is_array($sqd) && count($sqd)) {

			foreach ($sqd as $v) {
				call_user_func_array(array(Mage::getModel('sendmachine/cronjobs'), $v['method']), $v['args']);
			}
		}
	}

	public function fetchSmtpSettings() {

		try {
			$this->initApiClass();
			$resp = $this->api->account->smtp();
			if (isset($resp['smtp'])) {
				return $resp['smtp'];
			} else
				return false;
		} catch (Sendmachine_Error $ex) {
			return NULL;
		} catch (Http_Error $ex) {
			return NULL;
		}
	}

	public function fetchContactLists() {

		try {
			$this->initApiClass();
			$resp = $this->api->lists->get();
			if (isset($resp['contactlists'])) {
				return $resp['contactlists'];
			} else {
				return false;
			}
		} catch (Sendmachine_Error $ex) {
			return NULL;
		} catch (Http_Error $ex) {
			return NULL;
		}
	}

	public function fetchListMembers($listId = NULL, $limit = 25) {

		try {
			$this->initApiClass();
			$resp = $this->api->lists->recipients($listId, (int) $limit, 0, 'subscribed', 'added');
			if (isset($resp['contacts'])) {
				return $resp['contacts'];
			} else {
				return false;
			}
		} catch (Sendmachine_Error $ex) {
			return NULL;
		} catch (Http_Error $ex) {
			return NULL;
		}
	}

	public function fetchCustomFields($listId = NULL) {

		try {
			$this->initApiClass();
			$resp = $this->api->lists->custom_fields($listId);
			if (isset($resp['custom_fields'])) {
				return $resp['custom_fields'];
			} else {
				return false;
			}
		} catch (Sendmachine_Error $ex) {
			return NULL;
		} catch (Http_Error $ex) {
			return NULL;
		}
	}

	public function fetchListSubscribers($listId = NULL, $limit = 25) {

		try {
			$this->initApiClass();
			$resp = $this->api->lists->recipients($listId, (int) $limit, 0, 'subscribed', 'added');
			if (isset($resp['contacts'])) {
				return $resp['contacts'];
			} else {
				return false;
			}
		} catch (Sendmachine_Error $ex) {
			return NULL;
		} catch (Http_Error $ex) {
			return NULL;
		}
	}

	public function subscribeToList($subscribers = array(), $listId = NULL) {

		try {
			$this->initApiClass();
			$resp = $this->api->lists->manage_contacts($listId, $subscribers);
			if (isset($resp['status']) && ($resp['status'] == "saved" || $resp['status'] == "queued")) {
				return true;
			} else {
				return false;
			}
		} catch (Sendmachine_Error $ex) {
			return NULL;
		} catch (Http_Error $ex) {
			return NULL;
		}
	}

	public function configureEmail() {

		if ($this->apiConnected() && $this->get('plugin_enabled') && $this->get('email_enabled')) {

			$config = $this->get('email_config');
			$config['auth'] = "login";
			$host = "";
			if (isset($config['host'])) {
				$host = $config['host'];
				unset($config['host']);
			}

			$transport = new Zend_Mail_Transport_Smtp($host, $config);
			Zend_Mail::setDefaultTransport($transport);
			return true;
		}
		return false;
	}

	public function createTransactionalCampaign($template_id = "") {

		if ($this->get('transactional_campaigns_enabled')) {

			$trCmpAreas = $this->get('transactional_campaign_areas');
			$trCmpPrexif = $this->get('transactional_campaign_prefix');
			$trCmpSuffix = $this->get('transactional_campaign_suffix');

			if (in_array($template_id, $trCmpAreas)) {

				$template_list = Mage::getModel('core/email_template')->getDefaultTemplates();
				$template_name = $template_list[trim($template_id)]['label'];

				return array(
					"header_name" => "X-Sendmachine-Tag",
					"header_value" => $trCmpPrexif . $template_name . $trCmpSuffix
				);
			}
			return false;
		}
		return NULL;
	}

	public function getCredentials() {

		return array(
			"username" => $this->get('api_username'),
			"password" => $this->get('api_password')
		);
	}

	public function addImportExportLog($action, $state = 'pending', $number = 0, $logId = NULL) {

		if (is_null($logId)) {
			$listName = "";
			$listId = $this->get('selected_contact_list');
			$contactlists = $this->get('contact_lists');
			foreach ($contactlists as $v) {
				if ($v['list_id'] == $listId) {
					$listName = $v['name'];
				}
			}
			
			$store_id = Mage::getModel('core/store')->load($this->store, 'code')->getId();

			$data = array('store' => $store_id, 'action' => $action, 'state' => $state, 'list_name' => $listName, 'sdate' => date('Y-m-d H:i:s'));
			$this->setData($data);
			return $this->save()->getId();
		} else {
			$data = array('state' => $state, 'number' => $number, 'edate' => date('Y-m-d H:i:s'));
			$this->load($logId)->addData($data);
			try {
				$this->setId($logId)->save();
				return $logId;
			} catch (Exception $e) {
				return false;
			}
		}
	}

}
