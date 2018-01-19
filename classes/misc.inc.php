<?php
class Misc {
	public static function calculateOntvangerAfzender($data) {
		$ret = array();

		$our_data = trim($data['our_name'] . ' ' .  $data['our_institute'] . ' ' .  $data['our_department']);
		$their_data = trim($data['their_name'] . ' ' .  $data['their_organisation']);

		if ( $data['in_out'] == 'in' ) {
			$ret['ontvanger'] = $our_data;
			$ret['afzender'] = $their_data;
		} elseif ( $data['in_out'] == 'out' ) {
			$ret['ontvanger'] = $their_data;
			$ret['afzender'] = $our_data;
		} else {
			$ret['ontvanger'] = '';
			$ret['afzender'] = '';
		}

		return $ret;
	}

	public static function createOrderByCriterium( $orderBy ) {
		switch ($orderBy) {
			case "kenmerk_asc":
				$orderBy = "post.kenmerk ASC, post.ID DESC";
				break;
			case "kenmerk_desc":
				$orderBy = "post.kenmerk DESC, post.ID DESC";
				break;
			case "date_asc":
				$orderBy = "post.date ASC, post.kenmerk DESC, post.ID DESC";
				break;
			case "date_desc":
				$orderBy = "post.date DESC, post.kenmerk DESC, post.ID DESC";
				break;
			case "afzender_asc":
				$orderBy = "post.calculated_afzender ASC, post.kenmerk DESC, post.ID DESC";
				break;
			case "afzender_desc":
				$orderBy = "post.calculated_afzender DESC, post.kenmerk DESC, post.ID DESC";
				break;
			case "ontvanger_asc":
				$orderBy = "post.calculated_ontvanger ASC, post.kenmerk DESC, post.ID DESC";
				break;
			case "ontvanger_desc":
				$orderBy = "post.calculated_ontvanger DESC, post.kenmerk DESC, post.ID DESC";
				break;
			case "type_asc":
				$orderBy = "type_of_document.type_" . getLanguage() . " ASC, post.kenmerk DESC, post.ID DESC";
				break;
			case "type_desc":
				$orderBy = "type_of_document.type_" . getLanguage() . " DESC, post.kenmerk DESC, post.ID DESC";
				break;
			default:
				$orderBy = "post.kenmerk DESC, post.ID DESC";
		}

		return $orderBy;
	}

	public static function getAndProtectOrderBy() {
		global $protect;

		$orderBy = $protect->request('get', 'order_by');
		if ( !in_array($orderBy, array('kenmerk_asc', 'kenmerk_desc', 'date_asc', 'date_desc', 'afzender_asc', 'afzender_desc', 'ontvanger_asc', 'ontvanger_desc', 'type_asc', 'type_desc') ) ) {
			$orderBy = 'kenmerk_desc';
		}

		return $orderBy;
	}

	// see: https://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
	public static function gen_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

	public static function createBackurl( $concat = '' ) {
		// loop through GET, and get only parameters with a value
		$tmp = array();
		foreach ( $_GET as $a => $b) {
			if ( $b != '' ) {
				$tmp[] =$a . '=' . $b;
			}
		}

		$url = basename($_SERVER["SCRIPT_NAME"]) . '?' . implode('&', $tmp);

		$ret = $concat . urlencode( $url );

		return $ret;
	}

//	public static function createPostInOutRecordUrl($inOrOut, $ID) {
//		$protocol = 'https://';
//		$host = $_SERVER['HTTP_HOST'];
//		$dir = dirname($_SERVER['PHP_SELF']);
//		if ( $inOrOut == 'in' ) {
//			$script = '/postin.php';
//		} else {
//			$script = '/postuit.php';
//		}
//
//		$ret = $host . $dir . $script . '?ID=' . $ID;
//
//		$ret = $protocol . str_replace('//', '/', $ret);
//
//		return $ret;
//	}

	public static function convertDateTimeToNice( $datetime, $format = '' ) {
		$format = trim($format);

		if ( $format == '' ) {
			$format = "d-m-Y H:i:s";
		}
		return date($format, strtotime($datetime));
	}

	public static function getListOfFiles( $directory  ) {
		$files = array();

		if ( file_exists($directory) ) {
			$files = array_diff(scandir($directory), array('..', '.'));
		}

		sort($files);

		return $files;
	}

	public static function calculatePagesCount( $recordCount, $recordsPerPage ) {
		return ceil($recordCount / $recordsPerPage);
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
