<?php
/**
 * Class for loading and getting translations from the database
 */

class TranslationsAll {
	private static $is_loaded = false;
	private static $settings = null;
	private static $settings_table = 'translations';

	/**
	 * Load the settings from the database
	 */
	private static function load() {
		global $dbConn;

		$arr = array();

		// which language are we using
		$query = 'SELECT * FROM ' . self::$settings_table;
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$arr[ $row["property"] ] = array( 'nl' => $row["lang_nl"], 'en' => $row["lang_en"] );
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
	public static function get($setting_name, $language = 'nl') {
		if ( !self::$is_loaded ) {
			self::load();
		}

		$value = isset( self::$settings[$setting_name][$language] ) ? self::$settings[$setting_name][$language] : '';

		return $value;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
