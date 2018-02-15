<?php
/**
 * Class for loading and getting translations from the database
 */

//require_once dirname(__FILE__) . "/../classes/_misc_functions.inc.php";

class Translations {
	private static $is_loaded = false;
	private static $settings = null;
	private static $settings_table = 'translations';

	/**
	 * Load the settings from the database
	 */
	private static function load() {
		global $dbConn;
		$language = getLanguage();

		$arr = array();

		// which language are we using
		$query = 'SELECT * FROM ' . self::$settings_table;
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$arr[ $row["property"] ] = $row["lang_" . $language];
		}

		self::$settings = $arr;
		self::$is_loaded = true;
	}

	/**
	 * Return the value of the setting
	 *
	 * @param string $setting_name The name of the setting
	 * @return string The value of the setting
	 */
	public static function get($setting_name) {
		if ( !self::$is_loaded ) {
			self::load();
		}

		$value = isset( self::$settings[$setting_name] ) ? self::$settings[$setting_name] : '';

		return $value;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
