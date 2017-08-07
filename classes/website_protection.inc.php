<?php

class WebsiteProtection {

	public function sendWarningMail($tekst) {
		$message = '';

		$recipients = trim(Settings::get("admin_email"));

		$recipients = str_replace(array(';', ':', ' '), ',', $recipients);

		// fix multiple commas
		if( strpos($recipients, ',,') !== false ) {
			$recipients = str_replace(',,', ',', $recipients);
		}

		if ( $recipients != '' ) {

			$fromname = trim(Settings::get("email_sender_name"));
			$fromaddress = trim(Settings::get("email_sender_email"));
			$eol = "\n";

			$headers = "From: " . $fromname . " <" . $fromaddress . ">";

			$subject = "IISG Website warning";

			$iplocator = "http://www.aboutmyip.com/AboutMyXApp/IP2Location.jsp?ip=";

			$message .= "Date: " . date("Y-m-d") . $eol;
			$message .= "Time: " . date("H:i:s") . $eol;
			$message .= "URL: " . $this->getLongUrl() . $eol;
			$message .= "IP address: " . Misc::get_remote_addr() . $eol;
			$message .= "IP Location: " . $iplocator . Misc::get_remote_addr() . $eol . $eol;
			$message .= "Warning: " . $tekst;

			// send email
			mail($recipients, $subject, $message, $headers);
		}
	}

	public function getShortUrl() {
		$ret = $_SERVER["QUERY_STRING"];
		if ( $ret != '' ) {
			$ret = "?" . $ret;
		}
		$ret = $_SERVER["SCRIPT_NAME"] . $ret;

		return $ret;
	}

	public function getLongUrl() {
		return 'https://' . ( isset($_SERVER["HTTP_X_FORWARDED_HOST"]) && $_SERVER["HTTP_X_FORWARDED_HOST"] != '' ? $_SERVER["HTTP_X_FORWARDED_HOST"] : $_SERVER["SERVER_NAME"] ) . $this->getShortUrl();
	}

	public function sendErrorToBrowser($tekst) {
		$val = $tekst;
		$val .= "<br>Please contact the webmaster/IT department.";
		$val .= "<br>We have logged your IP address.";
		$val .= "<br>";

		$val = '<span style="color:red;"><b>' . $val . '</b></span>';

		echo $val;
	}

	public function check_instr_xss($foundxss, $test, $searchvalue) {
		if ( $foundxss == 0 ) {
			if ( strpos($test, $searchvalue) !== false ) {
				$foundxss = 1;
			}
		}
		return $foundxss;
	}

	public function getValue($type = 'get', $field = '') {
		$type = strtolower(trim($type));

		switch ($type) {

			case 'get':

				if ($field == '') {
					$retval = $_GET;
					if ( is_array($retval) ) {
						$retval = implode(';', $retval);
					}
				} else {
					if (isset($_GET[$field])) {
						$retval = $_GET[$field];
					} else {
						$retval = '';
					}
				}

				break;

			case 'post':

				if ($field == '') {
					$retval = $_POST;
					if ( is_array($retval) ) {
						$retval = implode(';', $retval);
					}
				} else {
					if (isset($_POST[$field])) {
						$retval = $_POST[$field];
					} else {
						$retval = '';
					}
				}

				break;

			case 'cookie':

				if ($field == '') {
					$retval = $_COOKIE;
					if ( is_array($retval) ) {
						$retval = implode(';', $retval);
					}
				} else {
					if (isset($_COOKIE[$field])) {
						$retval = $_COOKIE[$field];
					} else {
						$retval = '';
					}
				}

				break;

			case 'value':

				$retval = $field;

				break;

			default:
				die('Error 85163274. Unknown type: ' . $type);
		}

		return $retval;
	}

	public function request($type = '', $field = '', $pattern = '') {
		$retval = $this->getValue($type, $field);

		if ($retval != '') {
			if ($pattern != '') {
				if ( preg_match($pattern, $retval) == 0) {
					// niet goed
					$this->sendErrorToBrowser("ERROR 8564125");
					$this->sendWarningMail("ERROR 8564125 - command: " . $type . " - value: " . $retval);
					die('');
				}
			}
		}

		return $retval;
	}

	public function requestPositiveNumberOrEmpty($type = '', $field = '') {
		$retval = $this->getValue($type, $field);

		$retval = trim($retval);

		if ($retval != '') {
			// check if only numbers
			$pattern = "/^[0-9]+$/";

			if ( preg_match($pattern, $retval) == 0) {
				// niet goed
				$this->sendErrorToBrowser("ERROR 5474582");
				$this->sendWarningMail("ERROR 5474582 - command: " . $type . " - value: " . $retval);
				die('');
			}
		}

		return $retval;
	}

	public function request_only_characters_or_numbers_or_empty($type = '', $field = '') {
		$retval = $this->getValue($type, $field);

		$retval = trim($retval);

		if ($retval != '') {
			// check if only numbers
			$pattern = "/^[0-9a-zA-Z]+$/";

			if ( preg_match($pattern, $retval) == 0) {
				// niet goed
				$this->sendErrorToBrowser("ERROR 9456725");
				$this->sendWarningMail("ERROR 9456725 - command: " . $type . " - value: " . $retval);
				die('');
			}
		}

		return $retval;
	}

	public function get_left_part($text, $search = ' ' ) {
		$pos = strpos($text, $search);
		if ( $pos !== false ) {
			$text = substr($text, 0, $pos);
		}

		return $text;
	}

	public function send_email( $recipients, $subject, $message ) {
		// check recipients value
		$recipients = str_replace(array(',', ' '), ';', trim($recipients));
		while ( strpos($recipients, ';;') !== false ) {
			$recipients = str_replace(';;', ';', $recipients);
		}

		if ( $recipients != '' ) {
			$headers = "From: " . Settings::get("email_sender_name") . " <" . Settings::get("email_sender_email") . ">";

			// send email
			mail($recipients, $subject, $message, $headers);
		}
	}
}
