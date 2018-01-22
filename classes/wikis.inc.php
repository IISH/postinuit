<?php

class Wikis {
	public static function search( $search = '') {
		global $dbConn;

		$ret = array();
		$extraCriterium = '';

		$arrField = array('title', 'description');
		$arrSearch = explode(' ', $search);

		if ( count($arrSearch) > 0 ) {
			$extraCriterium = Generate_Query($arrField, $arrSearch, $concat = ' AND ');
		}

		$query = "SELECT * FROM `wiki` WHERE is_deleted = 0 " . $extraCriterium . " ORDER BY title ";

		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$ret[] = new Wiki( $row );
		}

		return $ret;
	}
}