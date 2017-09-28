<?php

class Authentication {
	public static function authenticate( $login, $password ) {
		global $dbConn;

		//
		$login = trim($login);
		$password = trim($password);

		// default value is login correct
		$ret = 0;

		// find user
		$query = "SELECT * FROM users WHERE loginname = :login AND is_disabled = 0 AND is_deleted = 0 ";
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
					$ret = Authentication::check_ldap($login, $password, $row['authentication_server']);
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
	public static function check_ldap($user, $pw, $authenticationServer) {
        global $dbConn;
	    $login_correct = 0;

        // gets ldap settings
        $query = "SELECT * FROM server_authorisation WHERE code = :code";
        $stmt = $dbConn->getConnection()->prepare($query);
        $stmt->bindParam(':code', $authenticationServer, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();

        $prefix = $result['prefix'];
        $postfix = $result['postfix'];
        $servers = $result['servers'];
        $protocol = $result['protocol'];

		$user = str_replace('/', '\\', $user); // voor alle zekerheid
		$user = $prefix . $user;
		$user = str_replace($prefix . $prefix, $prefix, $user); // voor alle zekerheid
// user: IA\VoornaamA

        $activeDirectoryServers = unserialize($servers);

		// loop all Active Directory servers
		foreach ( $activeDirectoryServers as $server ) {
			if ( $login_correct == 0 ) {

				// try to connect to the ldap server
// ldaps://10.24....:636
				$ad = ldap_connect($protocol . $server['server'], $server['port']);

				// set some variables
				ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);

				// bind to the ldap directory
				#$bd = @ldap_bind($ad, $user, $pw);
				$bd = ldap_bind($ad, $user, $pw);

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
