<?php 
/**
 * Class for loading and getting settings from a info file
 */
class IniSettings {
	private static $is_loaded = false;
	private static $settings = null;
	private static $settings_file = '../settings/config.php';

	/**
	 * Load the settings from the database
	 */
	private static function load() {
		self::$settings = parse_ini_file(self::$settings_file, true);
		self::$is_loaded = true;
	}

	/**
	 * Return the value of the setting
	 *
	 * @param string $setting_name The name of the setting
	 * @return string The value of the setting
	 */
	public static function get($level1, $level2 = '') {
		if ( !self::$is_loaded ) {
			self::load();
		}

		if ( $level2 != '' ) {
			$value = isset ( self::$settings[$level1][$level2] ) ? self::$settings[$level1][$level2] : '';
		} else {
			$value = isset ( self::$settings[$level1] ) ? self::$settings[$level1] : '';
		}

		return $value;
	}

	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}
}
