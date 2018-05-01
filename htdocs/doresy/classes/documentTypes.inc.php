<?php
/** Class for loading and getting type of documents from the database... */

class DocumentTypes {
	private static $is_loaded = false;
	private static $settings = null;
	private static $document_types_table = 'type_of_document';

	/**
	 * Load the type of documents from the database
	 */
	private static function load() {
		global $dbConn;
		$language  = getLanguage();

		$arr = array();

		//
		$query = 'SELECT * FROM ' . self::$document_types_table . ' ORDER BY sort_order, type_' . $language;
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach($result as $row) {
			$arr[$row["ID"]] = array($row["type_" . $language], false);
		}

		self::$settings = $arr;
		self::$is_loaded = true;
	}

	/**
	 * Return the value of the type of document
	 *
	 * @param $document_type
	 * @return string
	 */
	public static function get($document_type) {
		if ( !self::$is_loaded ) {
			self::load();
		}

		$value = isset(self::$settings[$document_type]) ? self::$settings[$document_type] : '';

		return $value;
	}

	public static function getDocumentTypes() {
		if ( !self::$is_loaded ) {
			self::load();
		}

		$value = isset(self::$settings) ? self::$settings : '';

		return $value;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}