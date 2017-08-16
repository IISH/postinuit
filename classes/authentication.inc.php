<?php

class Authentication {
	public static function authenticate( $login, $password ) {
		global $active_directories,  $dbConn;

		//
		$login = trim($login);
		$password = trim($password);

		// default value is login correct
		$ret = 0;

		// find users
		$query = "SELECT * FROM users WHERE loginname = :login ORDER BY ID desc ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			switch ( $row['authentication_server'] ) {
				case "local":
					$ret = Authentication::check_local($row['ID'], $password);
					break;
				case "knaw":
					$ret = Authentication::check_ldap($login, $password, $active_directories);
					break;
			}

			//
			if ( $ret == 1 ) {
				break;
			}
		}

		return $ret;
	}

	//
	public static function check_local( $id, $password ) {
		$loginCorrect = 0;

		$a = new User($id);
		if ( $a->verifyPasswordIsCorrect($password) ) {
			$loginCorrect = 1;
		}

		return $loginCorrect;
	}

	//
	public static function check_ldap($user, $pw, $servers) {
		$login_correct = 0;

		// LDAP AUTHENTICATION VIA PHP-LDAP
		// php-ldap must be installed on the server

		foreach ( $servers as $server ) {
			if ( $login_correct == 0 ) {

				// try to connect to ldap server
				$ad = ldap_connect($server);

				// set some variables
				ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);

				// bind to the ldap directory
				$bd = @ldap_bind($ad, $user, $pw);

				// verify binding, if binding succeeds then login is correct
				if ($bd) {
					$login_correct = 1;
				} else {
					error_log("LOGIN FAILED $user from " . Misc::get_remote_addr() . " (AD: " . $server . ")");
				}

				// never forget to unbind!
				ldap_unbind($ad);
			}
		}

		return $login_correct;
	}
}
