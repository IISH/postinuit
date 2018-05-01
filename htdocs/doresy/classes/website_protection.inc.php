<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class WebsiteProtection {

	public function sendWarningMail($tekst) {
		$message = '';

		$recipients = trim(IniSettings::get('settings', 'admin_email'));
		$recipients = str_replace(array(';', ':', ' '), ',', $recipients);
		// fix multiple commas
		if ( strpos($recipients, ',,') !== false ) {
			$recipients = str_replace(',,', ',', $recipients);
		}
		$arrRecipients = explode(',', $recipients);

		if ( $recipients != '' ) {
			$subject = trim(Translations::get("website_name")) . " warning";

			$iplocator = "http://www.aboutmyip.com/AboutMyXApp/IP2Location.jsp?ip=";

			$message .= "Date: " . date("Y-m-d") . PHP_EOL;
			$message .= "Time: " . date("H:i:s") . PHP_EOL;
			$message .= "URL: " . $this->getLongUrl() . PHP_EOL;
			$message .= "IP address: " . Misc::get_remote_addr() . PHP_EOL;
			$message .= "IP Location: " . $iplocator . Misc::get_remote_addr() . PHP_EOL . PHP_EOL;
			$message .= "Warning: " . $tekst;

			// try to send email
			$mail = new PHPMailer(true);
			try {
				// server settings for the mail
				$mail->SMTPDebug = 0;                                         // Enable verbose debug output
				$mail->isSMTP();                                              // Set mailer to use SMTP
	            $mail->Host = IniSettings::get('mail_server', 'host');              // Specify main and backup SMTP servers
	            $mail->SMTPAuth = true;                                       // Enable SMTP authentication
	            $mail->Username = IniSettings::get('mail_server', 'smtp_username'); // SMTP username
	            $mail->Password = IniSettings::get('mail_server', 'smtp_password'); // SMTP password
				$mail->Port = IniSettings::get('mail_server', 'port');              // TCP port to connect to

				//$mail->SMTPSecure = 'tls';                                  // Enable TLS encryption
				//$mail->SMTPSecure = 'ssl';                                  // Enable SSL encryption
				$mail->SMTPSecure = false;                                  // als geen smtp secure, dan ook geen smtpautotls
				$mail->SMTPAutoTLS = false;

				// set recipients and senders on the mail
				$mail->setFrom(trim(IniSettings::get('settings', "from_email")), trim(Translations::get("website_name")));
				foreach ( $arrRecipients as $rec ) {
					$mail->addAddress($rec);
				}
				$mail->isHTML(false);
				$mail->Subject = $subject;
				$mail->Body    = $message;

				if ( !$mail->send() ) {
					// log error
					error_log('Error 954278: failed sending mail to: ' . '...');
					echo 'Error 954278: failed sending mail to: ';
				} else {
				}
			} catch (Exception $e) {
				// log exception
				error_log( 'Caught exception (error 347893): ' . $e->getMessage() );
				echo 'Caught exception (error 347893): ' . $e->getMessage();
			}
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
				error_log('Error 85163274. Unknown type: ' . $type);
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
					error_log("ERROR 8564125 - command: " . $type . " - value: " . $retval);
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
				error_log("ERROR 5474582 - command: " . $type . " - value: " . $retval);
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
				error_log("ERROR 9456725 - command: " . $type . " - value: " . $retval);
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
}