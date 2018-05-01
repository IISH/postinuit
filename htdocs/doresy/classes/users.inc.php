<?php

class users {
	public static function insertNewUser( $loginName ) {
		global $dbConn;

		$query = "INSERT INTO `users` (loginname) VALUES (:loginname) ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->bindParam(':loginname', $loginName, PDO::PARAM_STR);
		$stmt->execute();
	}
}
