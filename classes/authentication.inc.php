<?php

class Authentication {
	public static function authenticate( $login, $password ) {
		global $dbConn;

		// check how many failed attempsts
		if ( logins::howManyFailedAttempts() >= 10 ) {
			die('Too many failed attempts from your IP address within the last hour. Please come back in an hour.');
		}

		//
		$login = trim($login);
		$password = trim($password);

		// default value is login correct
		$ret = 0;

		// find user
		$query = "SELECT * FROM users WHERE loginname = :login ORDER BY ID DESC LIMIT 0, 1 ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetchAll();
		$isUserFound = false;
		$authenticationServer = '';
		$userId = 0;

		foreach ($result as $row) {
			$isUserFound = true;

			$authenticationServer = $row['authentication_server'];
			$userId = $row['ID'];

			//
			if ( $row['is_disabled'] == 1 || $row['is_deleted'] == 1 ) {
				$ret = 3;
			}
		}

		// if not disabled/deleted
		if ( $ret == 0 ) {
			// try to authenticate
			switch ( $authenticationServer ) {
				case "local":
					$ret = Authentication::check_local($userId, $password);
					break;
				case "knaw":
					$ret = Authentication::check_ldap($login, $password, 'knaw');
					break;
				default:
					$ret = Authentication::check_ldap($login, $password, 'knaw');
			}
		}

		// if authorisation okay and if new user
		if ( $ret != 0 && !$isUserFound ) {
			// add user to local user table with minimal rights
			users::insertNewUser($login);
		}

		//
		logins::ping( $login, $ret );

		// statussen:
		// 0 - incorrect login
		// 1 - knaw authenticated & membor_of
		// 2 - knaw authenticated but NOT membor_of
		// 3 - disabled/deleted

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

		// get settings
		$auth = Authentication::getServerAuthorisationInfo($authenticationServer);

		//
		$user = trim($user);

		//
		$sAMAccountName = $user;

		// add prefix
		$user = trim( $auth['prefix'] ) . $user;
		// remove double prefix
		$user = str_replace(trim ( $auth['prefix'] ) . trim( $auth['prefix'] ), trim( $auth['prefix'] ), $user);

		// loop all Active Directory servers
		//foreach ( unserialize($auth['servers']) as $server ) {
		foreach ( AdServerStatic::getAdServers($auth['ad_servers']) as $server ) {
			if ( $login_correct == 0 ) {

				// try to connect to the ldap server
				$ad = ldap_connect($server->getProtocolAndServer(), $server->getPort());

				// set some variables
				ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
				ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);

				// bind to the ldap directory
				$bd = @ldap_bind($ad, $user, $pw);

				// verify binding, if binding succeeds then login is correct
				if ( $bd ) {
					$whitelist = trim($auth['whitelist']);
					$blacklist = trim($auth['blacklist']);

					if ( $whitelist == '' && $blacklist == '' ) {
						// there is no white/blacklist, so everyone who logged in via this server is okay
						$login_correct = 1; // authenticated & authorised
					} else {
						$result = ldap_search($ad, $auth['ldap_dn'], "(sAMAccountName=$sAMAccountName)", array("memberof")) or exit("Unable to search LDAP server");
						$entries = ldap_get_entries($ad, $result);

						// check whitelist
						if ( $whitelist == '' ) {
							// there is no whitelist, so everyone who logged in via this server is whitelisted
							$login_correct = 1; // authenticated & authorised
						} else {
							$login_correct = 2; // autheniticated but not authorised
							$whitelist = explode("\n", $whitelist);
							foreach ( $whitelist as $wl ) {
								foreach($entries[0]['memberof'] as $grps) {
									if ( $grps == trim($wl)) {
										$login_correct = 1; // authenticated & authorised
										break;
									}
								}
							}
						}

						// check blacklist
						if ( $login_correct == 1 ) {
							if ( $blacklist != '' ) {
								$blacklist = explode("\n", $blacklist);
								foreach ( $blacklist as $bl ) {
									foreach($entries[0]['memberof'] as $grps) {
										if ( $grps == trim($bl)) {
											$login_correct = 2; // authenticated but not authorised
											error_log("AUTHORISATION FAILED $user from " . Misc::get_remote_addr() . " (LDAP: " . $server->getProtocolAndServer() . ")");
											break;
										}
									}
								}
							}
						}

					}
				} else {
					error_log("AUTHENTICATION FAILED $user from " . Misc::get_remote_addr() . " (LDAP: " . $server->getProtocolAndServer() . ")");
				}
				// never forget to unbind!
				ldap_unbind($ad);
			}
		}

		return $login_correct;
	}

	public static function getServerAuthorisationInfo( $authenticationServer ) {
		global $dbConn;

		// get settings
		$query = "SELECT * FROM server_authorisation WHERE code = :code ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->bindParam(':code', $authenticationServer, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();

		return $result;
	}
}
