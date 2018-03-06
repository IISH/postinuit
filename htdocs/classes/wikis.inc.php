<?php

class Wikis {
	public static function search( $search = '') {
		global $dbConn;

		$ret = array();
		$extraCriterium = '';

		// only dutch for now
//		$language = getLanguage();
		$language = 'nl';

		$arrField = array('groupname_' . $language, 'title_' . $language, 'description_' . $language);
		$arrSearch = explode(' ', $search);

		if ( count($arrSearch) > 0 ) {
			$extraCriterium = Generate_Query($arrField, $arrSearch, $concat = ' AND ');
		}

		$query = "SELECT wiki.*, wiki_group.groupname_nl, wiki_group.groupname_en  FROM `wiki` INNER JOIN wiki_group ON wiki.wiki_group_id = wiki_group.ID WHERE wiki.is_deleted = 0 AND wiki_group.is_deleted = 0 " . $extraCriterium . " ORDER BY wiki_group.sort_order, wiki.sort_order, wiki.title_" . $language;
//preprint( $query );

		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$ret[] = new Wiki( $row );
		}

		return $ret;
	}
}