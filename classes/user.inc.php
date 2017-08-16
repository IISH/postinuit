<?php
require_once "role_authorisation.inc.php";

class staticUser {
	public static function getUserByLoginName( $loginname ) {
		global $dbConn;
		$id = array();

		//
		$loginname = trim($loginname);

		if ( $loginname != '' ) {

			// Remark: don't check date_out here, sometimes they make errors when a person is re-hired they forget to remove the date_out value
			$query = "SELECT * FROM users WHERE loginname = '" . addslashes($loginname) . "' ORDER BY ID DESC ";
			$stmt = $dbConn->getConnection()->prepare($query);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $row) {
				$id[] = $row['ID'];
			}
		}

		if ( count( $id ) == 0 ) {
			$id[] = 0;
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
	protected $roles = '';
	protected $arrRoles = array();
	protected $arrUserAuthorisation = array();
	protected $is_admin = false;

	function __construct($id) {
		if ( !is_array( $id ) ) {
			$id = array( $id );
		}

		if ( count( $id ) == 0 ) {
			$id[] = 0;
		}

		$this->getValues( $id );
	}

	public function getValues( $id ) {
		global $dbConn;
		// reset values
		$query = "SELECT * FROM users WHERE ID IN ( " . implode(',', $id) . " ) ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$this->id = $row['ID'];
			$this->password = '';
			$this->password_hash = trim($row['password_hash']);
			$this->loginname = trim($row['loginname']);
			$this->name = trim($row['name']);
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
		return ( in_array('admin', $this->arrUserAuthorisation) );
	}

	public function isLoggedIn() {
		if ( $_SESSION["loginname"] != '' ) {
			return true;
		}

		return false;
	}

	public function verifyPasswordIsCorrect( $password ) {
		return password_verify($password, $this->password_hash);
	}

	public function checkLoggedIn() {
		global $protect;

		// TODO: Opmerking: ook controleren of session loginname leeg is, want als de gebruiker wel in ActiveDirectory zit
		// maar niet in protime, dan heeft men wel een loginname maar geen id
		if ( $this->id < 1 && $_SESSION["loginname"] == '' ) {
			Header("Location: login.php?burl=" . URLencode($protect->getShortUrl()));
			die(Translations::get('go_to') . " <a href=\"login.php?burl=" . URLencode($protect->getShortUrl()) . "\">next</a>");
		}
	}

	public function setPassword($password) {
		$this->password = $password;
	}

	public function saveHash() {
		global $dbConn;

		$query = "UPDATE users SET password_hash='" . $this->cryptPassword($this->password) . "' WHERE ID=" . $this->id;
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
	}

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

	public function saveUserSetting($field, $value) {
		global $dbConn;

		$field = addslashes($field);
		$value = addslashes($value);

		$query_update = "INSERT INTO user_settings (`ID`, `setting`, `value`) VALUES (" . $this->id . ", '$field', '$value') ON DUPLICATE KEY UPDATE `value`='$value' ";
		$stmt = $dbConn->getConnection()->prepare($query_update);
		$stmt->execute();
	}

	// TODO
	public function getUserSetting( $setting, $default = '' ) {
		return '';
		//		return ( isset($this->arrUserSettings[$setting]) ) ? $this->arrUserSettings[$setting] : $default;
	}
}
