<?php

class Wikis {
	public static function search( $search ) {
		global $dbConn;

		$ret = array();

		$query = "SELECT * FROM `wiki` WHERE is_deleted = 0 ORDER BY title ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$ret[] = new Wiki( $row );
		}

		return $ret;
	}
}