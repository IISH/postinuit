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
		$login_correct = 0;

		// TODO: haal de settings op uit de database ipv, dat ze hier staan
		// zoek: authenticationServer is in dit voorbeeld knaw, zie tabel



		// TODO: load authentication server data
		// zie variable: authenticationServers

		$user = str_replace('/', '\\', $user); // voor alle zekerheid
		// TODO: move prefix to database
		$prefix = 'IA\\';
		$user = $prefix . $user;
		$user = str_replace($prefix . $prefix, $prefix, $user); // voor alle zekerheid
// user: IA\VoornaamA
		// TODO: move postfix to database

		// TODO: move to database
		$activeDirectoryServers = array(
				array( 'server' => '10.14.42.40', 'port' => 636)
				, array( 'server' => '10.14.42.39', 'port' => 636)
			);

		// loop all Active Directory servers
		foreach ( $activeDirectoryServers as $server ) {
			if ( $login_correct == 0 ) {

				// try to connect to the ldap server
				// TODO: move ldap / ldaps setting to database
// ldaps://10.24....:636
				$ad = ldap_connect('ldaps://' . $server['server'], $server['port']);

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
