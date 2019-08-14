<?php

class Account {

	public function __construct(SendmachineApiClient $master) {
		$this->master = $master;
	}

	/**
	 * Get details about the current active package of the user
	 * @return array
	 * {
	 * 	"package": {
	 * 		"name",
	 * 		"state",
	 * 		"credits",
	 * 		"interval",
	 * 		"price",
	 * 		"currency",
	 * 		"custom_fields",
	 * 		"period_min",
	 * 		"period_max",
	 * 		"contract_type",
	 * 		"max_credit",
	 * 		"mcountsent",
	 * 		"prod_id",
	 * 		"info_type"
	 * 	}
	 * }
	 */
	public function package() {

		return $this->master->request('/account/package', 'GET');
	}

	/**
	 * Get details about the current rating
	 * @return array
	 * {
	 * 	"score"
	 * }
	 */
	public function rating() {

		return $this->master->request('/account/rating', 'GET');
	}

	/**
	 * The SMTP user and password are also used for API Auth. 
	 * @return array
	 * {
	 *    "smtp": {
	 *        "hostname",
	 *        "port",
	 *        "ssl_tls_port",
	 *        "starttls_port",
	 *        "username",
	 *        "password",
	 *        "state"
	 *    }
	 * }
	 */
	public function smtp() {

		return $this->master->request('/account/smtp', 'GET');
	}

	/**
	 * get user details
	 * @return array
	 * {
	 *    "user": {
	 *        "email",
	 *        "sex",
	 *        "first_name",
	 *        "last_name",
	 *        "country",
	 *        "phone_number",
	 *        "mobile_number",
	 *        "state",
	 *        "language"
	 *    }
	 * }
	 */
	public function details() {

		return $this->master->request('/account/user', 'GET');
	}

}
