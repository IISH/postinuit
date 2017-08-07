<?php

class Authentication {
	public static function authenticate( $login, $password ) {
		global $active_directories;
		return Authentication::check_ldap('iisgnet\\' . $login, $password, $active_directories);
	}

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
