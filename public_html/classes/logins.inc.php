<?php

class logins {
	public static function ping( $loginName, $loginStatus ) {
		global $dbConn;

		$date = date("Y-m-d H:i:s");
		$ip_address = Misc::get_remote_addr();

		//
		$query = "UPDATE `users` SET last_login = :last_login WHERE loginname = :loginname ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->bindParam(':loginname', $loginName, PDO::PARAM_STR);
		$stmt->bindParam(':last_login', $date, PDO::PARAM_STR);
		$stmt->execute();

		//
		$query = "INSERT INTO `login_attempts` (`ip_address`, `loginname`, `date`, `status`) VALUES (:ip_address, :loginname, :date, :status) ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
		$stmt->bindParam(':loginname', $loginName, PDO::PARAM_STR);
		$stmt->bindParam(':date', $date, PDO::PARAM_STR);
		$stmt->bindParam(':status', $loginStatus, PDO::PARAM_INT);
		$stmt->execute();
	}

	public static function howManyFailedAttempts($hours = -1 ) {
		global $dbConn;

		$hours = (int)$hours;

		if ( $hours == 0 ) {
			$hours = -1;
		}

		if ( $hours > 0 ) {
			$hours *= -1;
		}

		$date = date("Y-m-d H:i:s", strtotime($hours . ' hours'));
		$ip_address = Misc::get_remote_addr();

		$query = "SELECT COUNT(*) AS FAILED_ATTEMPTS FROM `login_attempts` WHERE ip_address = :ip_address AND `date` > :date AND `status` = 0 ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
		$stmt->bindParam(':date', $date, PDO::PARAM_STR);
		$stmt->execute();

		$rs = $stmt->fetch();
		$ret = $rs['FAILED_ATTEMPTS'];

		return $ret;
	}
}
