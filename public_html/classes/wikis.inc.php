<?php

class Wikis {
	public static function search( $search = '') {
		global $dbConn;

		$ret = array();
		$extraCriterium = '';

		$language = getLanguage();

		$arrField = array('title_' . $language, 'description_' . $language);
		$arrSearch = explode(' ', $search);

		if ( count($arrSearch) > 0 ) {
			$extraCriterium = Generate_Query($arrField, $arrSearch, $concat = ' AND ');
		}

		$query = "SELECT * FROM `wiki` WHERE is_deleted = 0 " . $extraCriterium . " ORDER BY title_" . $language;

		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$ret[] = new Wiki( $row );
		}

		return $ret;
	}
}