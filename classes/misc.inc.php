<?php
function preprint( $object ) {
	echo '<pre>';
	print_r( $object );
	echo '</pre>';
}

class Misc {

	public static function getListOfFiles( $directory  ) {
		$files = array();

		if ( file_exists($directory) ) {
			$files = array_diff(scandir($directory), array('..', '.'));
		}

		return $files;
	}

	public static function calculatePagesCount( $recordCount, $recordsPerPage ) {
		return ceil($recordCount / $recordsPerPage);
	}

	public static function cryptPassword( $password ) {
		// A higher "cost" is more secure but consumes more processing power
		$cost = 10;

		// Create a random salt
		$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

		// Prefix information about the hash so PHP knows how to verify it later.
		// "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
		$salt = sprintf("$2a$%02d$", $cost) . $salt;

		// Hash the password with the salt
		$hash = crypt($password, $salt);

		return $hash;
	}

	public static function get_remote_addr() {
		$retval = '';
		if ( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
			$retval = trim($_SERVER["HTTP_X_FORWARDED_FOR"]);
		}

		if ( $retval == '' ) {
			if ( isset( $_SERVER["REMOTE_ADDR"] ) ) {
				$retval = trim($_SERVER["REMOTE_ADDR"]);
			}
		}

		return $retval;
	}

	public static function stripLeftPart( $string, $strip ) {
		if ( strtolower(substr($string, 0, strlen($strip))) == strtolower($strip) ) {
			$string = substr($string, -(strlen($string)-strlen($strip)));
		}
		return $string;
	}

	public static function multiplyTag($tag, $code, $start, $end) {
		$ret = '';
		$separator = '';

		for ( $i = $start ; $i <= $end; $i++ ) {
			$ret .= $separator . str_replace($code, $i, $tag);
			$separator = ', ';
		}

		return $ret;
	}

	function PlaceURLParametersInQuery($query) {
		$return_value = $query;

		// vervang in de url, de FLD: door waardes
		$pattern = '/\[FLD\:[a-zA-Z0-9_]*\]/';
		preg_match($pattern, $return_value, $matches);
		while ( count($matches) > 0 ) {
			if ( isset($this->m_form["primarykey"]) && "[FLD:" . $this->m_form["primarykey"] . "]" == $matches[0] ) {
				$return_value = str_replace($matches[0], $this->m_doc_id, $return_value);
			} else {
				$return_value = str_replace($matches[0], addslashes($_GET[str_replace("]", '', str_replace("[FLD:", '', $matches[0]))]), $return_value);
			}

			$matches = null;
			preg_match($pattern, $return_value, $matches);
		}

		$return_value = str_replace("[BACKURL]", urlencode(getBackUrl()), $return_value);

		return $return_value;
	}

	public function ReplaceSpecialFieldsWithDatabaseValues($url, $row) {
		$return_value = $url;

		// vervang in de url, de FLD: door waardes
		$pattern = '/\[FLD\:[a-zA-Z0-9_]*\]/';
		preg_match($pattern, $return_value, $matches);
		while ( count($matches) > 0 ) { 
			$return_value = str_replace($matches[0], addslashes($row[str_replace("]", "", str_replace("[FLD:", "", $matches[0]))]), $return_value);
			$matches = null;
			preg_match($pattern, $return_value, $matches);
		}

		$backurl = $_SERVER["QUERY_STRING"];
		if ( $backurl <> "" ) {
			$backurl = "?" . $backurl;
		}
		$backurl = urlencode($_SERVER["SCRIPT_NAME"] . $backurl);
		$return_value = str_replace("[BACKURL]", $backurl, $return_value);

		return $return_value;
	}

	public function ReplaceSpecialFieldsWithQuerystringValues($url) {
		$return_value = $url;

		// vervang in de url, de FLD: door waardes
		$pattern = '/\[QUERYSTRING\:[a-zA-Z0-9_]*\]/';
		preg_match($pattern, $return_value, $matches);
		while ( count($matches) > 0 ) { 
			$return_value = str_replace($matches[0], addslashes($_GET[str_replace("]", "", str_replace("[QUERYSTRING:", "", $matches[0]))]), $return_value);
			$matches = null;
			preg_match($pattern, $return_value, $matches);
		}

		// calculate 'go back' url
		$backurl = $_SERVER["QUERY_STRING"];
		if ( $backurl <> "" ) {
			$backurl = "?" . $backurl;
		}
		$backurl = urlencode($_SERVER["SCRIPT_NAME"] . $backurl);
		// if there is a backurl then place the new blackurl into the string
		$return_value = str_replace("[BACKURL]", $backurl, $return_value);

		return $return_value;
	}
}
