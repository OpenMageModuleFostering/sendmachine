<?php

class Sendmachine_Sendmachine_Helper_Data extends Mage_Core_Helper_Abstract {

	public function initEmailConfig($smtp_settings, $smtpEncryptionType) {

		if ($smtp_settings) {

			$config = array(
				"host" => $smtp_settings['hostname'],
				"username" => $smtp_settings["username"],
				"password" => $smtp_settings['password'],
			);

			switch ($smtpEncryptionType) {
				case "SSL":
					$config["port"] = $smtp_settings['ssl_tls_port'];
					$config["ssl"] = "ssl";
					break;
				case "TLS":
					$config["port"] = $smtp_settings['starttls_port'];
					$config["ssl"] = "tls";
					break;
				default:
					$config["port"] = $smtp_settings['port'];
					break;
			}
			return $config;
		} else {
			return false;
		}
	}

	public function customfields2form($fields = NULL) {

		if (!$fields) {
			return false;
		}

		$list_fields = "";

		foreach ($fields as $k => $v) {

			if ($v['visible']) {

				$required = ($v['required']) ? "*" : "";

				$list_fields .= $v['label'] . " $required<br>";

				if (in_array($v['type'], array("text", "number", "email", "date", "birthday"))) {
					$placeholder = "";

					if ($v['type'] == "date")
						$placeholder = "yyyy/mm/dd";
					elseif ($v['type'] == "birthday")
						$placeholder = "mm/dd";

					$list_fields .= "<input placeholder='$placeholder' type='text' name='" . $k . "'/><br><br>";
				}
				elseif ($v['type'] == "radiobutton") {

					foreach ($v['options'] as $option) {

						$list_fields .= "<label><input type='radio' value='" . $option . "' name='" . $k . "' /> " . $option . "</label><br>";
					}
					$list_fields .="<br>";
				} elseif ($v['type'] == "dropdown") {

					$list_fields .= "<select class='sm_list_dropdown' name='" . $k . "'>";
					$list_fields .= "<option></option>";

					foreach ($v['options'] as $option) {

						$list_fields .= "<option value='" . $option . "'>" . $option . "</option>";
					}

					$list_fields .= "</select><br><br>";
				}
			}
		}

		return $list_fields;
	}

	public function prepareCustomFields($fields) {

		if (!$fields || !count($fields))
			return NULL;

		$_fields = array();
		foreach ($fields as $v) {

			if (!isset($v['form_name']))
				$v['form_name'] = uc_words(strtolower($v['name']));

			$_fields[$v['name']] = array('label' => $v['form_name'], 'visible' => $v['visible'] ? 1 : 0, 'required' => $v['required'] ? 1 : 0, 'type' => $v['cf_type']);

			if (isset($v['options']))
				$_fields[$v['name']]['options'] = $v['options'];
		}

		return $_fields;
	}

	public function customfields2checkbox($fields) {

		if (!$fields) {
			return false;
		}

		$data = "<table style='width:100%' cellspacing='0' cellpadding='0'><thead><th>" . $this->__('Label') . "</th><th>" . $this->__('Name') . "</th><th>" . $this->__('Visible') . "</th></thead><tbody>";

		foreach ($fields as $k => $v) {

			$checked = $v['visible'] || $v['required'] ? "checked" : "";
			$disabled = $v['required'] ? 'disabled' : "";
			$note = $v['required'] ? '*' : "";
			$value = $v['required'] ? '1' : "0";
			$input = "<input type='hidden' name='list_custom_fields[$k][visible]' value='" . $value . "'/>";
			$input .= "<input $checked $disabled name='list_custom_fields[$k][visible]' value='1' type='checkbox'/> $note";

			$data .= "<tr><td style='border-top:1px solid grey' >" . $v['label'] . "</td><td style='border-top:1px solid grey;'>$k</td><td style='border-top:1px solid grey;'>$input</td></tr>";
		}
		$data .= "<tr><td style='padding-top:8px;' colspan='3'>".$this->__("* field is required, can't be hidden")."</td></tr>";
		$data .= "</tbody></table>";

		return $data;
	}

}
