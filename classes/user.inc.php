<?php
require_once "role_authorisation.inc.php";

class staticUser {
	public static function getUserByLoginName( $loginname ) {
		global $dbConn;

		//
		$id = 0;

		//
		$loginname = trim($loginname);

		if ( $loginname != '' ) {

			//
			$query = "SELECT * FROM users WHERE loginname = '" . addslashes($loginname) . "' ORDER BY ID DESC ";
			$stmt = $dbConn->getConnection()->prepare($query);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $row) {
				$id = $row['ID'];
				break;
			}
		}

		return new User( $id );
	}
}

class User {
	protected $id = 0;
	protected $loginname = '';
	protected $name = '';
	protected $password = '';
	protected $password_hash = '';
	protected $arrUserSettings = array();
	protected $isBeheerder = 0;
	protected $isAdmin = 0;

	function __construct($id) {
		// get user data for specified id
		$this->getValues( $id );

		// load user settings
		$this->loadUserSettings();
	}

	public function getValues( $id ) {
		global $dbConn;

		//
		$query = "SELECT * FROM users WHERE ID=" . $id;
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$this->id = $row['ID'];
			$this->password = '';
			$this->password_hash = trim($row['password_hash']);
			$this->loginname = trim($row['loginname']);
			$this->name = trim($row['name']);
			$this->isBeheerder = $row['is_beheerder'];
			$this->isAdmin = $row['is_admin'];
		}
	}

	//
	public function getId() {
		return $this->id;
	}

	//
	public function getLoginname() {
		return trim($this->loginname);
	}

	//
	public function getName() {
		return trim($this->name);
	}

	//
	public function isAdmin() {
		return $this->isAdmin;
	}

	//
	public function isBeheerder() {
		return ( $this->isBeheerder || $this->isAdmin() );
	}

	// DEPRECATED
	public function isFb() {
		return $this->isBeheerder();
	}

	//
	public function isLoggedIn() {
		if ( $_SESSION["loginname"] != '' ) {
			return true;
		}

		return false;
	}

	//
	public function checkLoggedIn() {
		global $protect;

		//
		if ( $this->id < 1 && $_SESSION["loginname"] == '' ) {
			Header("Location: login.php?burl=" . URLencode($protect->getShortUrl()));
			die(Translations::get('go_to') . " <a href=\"login.php?burl=" . URLencode($protect->getShortUrl()) . "\">next</a>");
		}
	}

	//
	public function setPassword($password) {
		$this->password = $password;
	}

	//
	public function saveHash() {
		global $dbConn;

		$query = "UPDATE users SET password_hash='" . $this->cryptPassword($this->password) . "' WHERE ID=" . $this->id;
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
	}

	//
	public function cryptPassword( $password ) {
		// A higher "cost" is more secure but consumes more processing power
		$cost = 10;

		// Create a random salt
		$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

		// Prefix information about the hash so PHP knows how to verify it later.
		// "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
		$salt = sprintf("$2a$%02d$", $cost) . $salt;

		// Hash the password with the salt
		$hash = crypt($password, $salt);

		return $hash;
	}

	//
	public function verifyPasswordIsCorrect( $password ) {
		return hash_equals($this->password_hash, crypt($password, $this->password_hash));
	}

	//
	public function saveUserSetting($field, $value) {
		global $dbConn;

		if ( $this->id > 0 ) {
			$field = addslashes($field);
			$value = addslashes($value);

			$query_update = "INSERT INTO user_settings (`ID`, `setting`, `value`) VALUES (" . $this->id . ", '$field', '$value') ON DUPLICATE KEY UPDATE `value`='$value' ";
			$stmt = $dbConn->getConnection()->prepare($query_update);
			$stmt->execute();
		}
	}

	//
	public function getUserSetting( $setting, $default = '' ) {
		return ( isset($this->arrUserSettings[$setting]) ) ? $this->arrUserSettings[$setting] : $default;
	}

	//
	public function loadUserSettings() {
		global $dbConn;

		//
		$query = "SELECT * FROM user_settings WHERE ID=" . $this->id;
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$this->arrUserSettings[$row['setting']] = $row['value'];
		}
	}
}
