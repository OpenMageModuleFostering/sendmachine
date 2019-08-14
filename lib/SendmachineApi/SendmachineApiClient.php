<?php

require_once __dir__ . '/library/Account.php';
require_once __dir__ . '/library/Sender.php';
require_once __dir__ . '/library/Campaigns.php';
require_once __dir__ . '/library/Lists.php';
require_once __dir__ . '/library/Templates.php';

class SendmachineApiClient {

	/**
	 * api host
	 * @var string
	 */
	private $api_host = 'https://api.sendmachine.com';

	/**
	 * api username
	 * @var string 
	 */
	private $username;

	/**
	 * api password
	 * @var string 
	 */
	private $password;

	/**
	 * Curl resource
	 * @var resource
	 */
	private $curl;

	/*
	 * for debugging
	 */
	private $debug = false;

	/**
	 * connect to api
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($username = null, $password = null) {

		if (!$username || !$password) {

			list($username, $password) = $this->check_config();
		}

		if (!$username || !$password) {

			throw new Sendmachine_Error("You must provide a username and password", "no_username_password");
		}

		$this->username = $username;
		$this->password = $password;

		$this->curl = curl_init();

		$this->campaigns = new Campaigns($this);
		$this->sender = new Sender($this);
		$this->lists = new Lists($this);
		$this->account = new Account($this);
		$this->templates = new Templates($this);
	}

	public function request($url, $method, $params = array()) {

		$ch = $this->curl;
		
		switch (strtoupper($method)) {
			case 'GET':
				if (count($params)) {
					$url .= "?" . http_build_query($params);
				}
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
				break;
			case 'PUT':
			case 'POST':
				$params = json_encode($params);

				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
				curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($params)]);
				break;
			case 'DELETE':
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
				break;
		}
		
		$final_url = $this->api_host . $url;
		
		curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
		curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $final_url);
		curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);

		if ($this->debug) {
			$start = microtime(true);
			$this->log('URL: ' . $this->api_host . $url . (is_string($params) ? ", params: " . $params : ""));
		}

		$response = curl_exec($ch);
		$info = curl_getinfo($ch);

		if ($this->debug) {
			$time = microtime(true) - $start;
			$this->log('Completed in ' . number_format($time * 1000, 2) . 'ms');
			$this->log('Response: ' . $response);
		}

		if (curl_error($ch)) {

			throw new Http_Error("API call to $this->api_host$url failed.Reason: " . curl_error($ch));
		}

		$result = json_decode($response, true);
		if ($response && !$result)
			$result = $response;

		if ($info['http_code'] >= 400) {

			$this->set_error($result);
		}

		return $result;
	}

	public function __destruct() {

		if (is_resource($this->curl)) {

			curl_close($this->curl);
		}
	}

	public function log($msg) {
		error_log($msg);
	}

	public function check_config() {

		$config_paths = [".sendmachine.conf", "/etc/.sendmachine.conf"];
		$username = null;
		$password = null;

		foreach ($config_paths as $path) {

			if (file_exists($path)) {

				if (!is_readable($path)) {

					throw new Sendmachine_Error("Configuration file ($path) does not have read access.", "config_not_readable");
				}

				$config = parse_ini_file($path);

				$username = empty($config['username']) ? null : $config['username'];
				$password = empty($config['password']) ? null : $config['password'];
				break;
			}
		}

		return [$username, $password];
	}

	public function set_error($result) {

		if (is_array($result)) {

			if (empty($result['error_reason'])) {

				if (!empty($result['status']))
					$result['error_reason'] = $result['status'];
				else
					$result['error_reason'] = "Unexpected error";
			}

			throw new Sendmachine_Error($result['error_reason'], $result['status']);
		}
	}

}

class Sendmachine_Error extends Exception {

	private $err_status;

	public function __construct($error_reason = "", $error_status = "") {

		parent::__construct($error_reason);

		$this->err_status = $error_status;
	}

	public function getSendmachineStatus() {
		return $this->err_status;
	}

}

class Http_Error extends Sendmachine_Error {}
