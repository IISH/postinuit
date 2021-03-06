<?php

// function hash_equals exists in PHP >= 5.6.0
if ( !function_exists('hash_equals') ) {
	function hash_equals($str1, $str2) {
		if(strlen($str1) != strlen($str2)) {
			return false;
		} else {
			$res = $str1 ^ $str2;
			$ret = 0;
			for($i = strlen($res) - 1; $i >= 0; $i--) {
				$ret |= ord($res[$i]);
			}
			return !$ret;
		}
	}
}

function preprint( $object ) {
	echo '<pre>';
	print_r( $object );
	echo '</pre>';
}

function getLanguage() {
	global $oWebuser;

	//
	if ( isset($_SESSION['language']) ) {
		$language = trim($_SESSION['language']);
	} else {
		$language = $oWebuser->getUserSetting('language');
		if ( !in_array($language, array('en', 'nl') ) ) {
			$language = 'nl';
		}
		$_SESSION['language'] = $language;
	}

	return $language;
}

function replaceDoubleTripleSpaces( $string ) {
	return preg_replace('!\s+!', ' ', $string);
}

function valueOr( $value, $or = '?' ) {
	return ( ( trim($value) != '' ) ? $value : $or );
}

function createUrl( $parts ) {
	$ret = "<a href=\"". $parts['url'] . "\">" . $parts["label"] . "</a>";

	return $ret;
}

function splitStringIntoArray( $text, $pattern = "/[^a-zA-Z0-9]/" ) {
	return preg_split($pattern, $text);
}

function stripDomainnameFromUrl( $url ) {
	$arr = parse_url( $url );

	$ret = $arr['path'];
	if ( isset( $arr['query'] ) && $arr['query'] != '' ) {
		$ret .= '?' . $arr['query'];
	}

	return $ret;
}

function goBack() {
	$url = 'index.php';

	$referer = ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '' );
	if ( $referer != '' ) {
		$url = $referer;
		$url = stripDomainnameFromUrl( $url );
	}

	Header("Location: " . $url);
}

function getAndProtectSearch($field = 's') {
	$s = '';

	if ( isset($_GET[$field]) ) {
		$s = $_GET[$field];
		$s = str_replace(array('?', "~", "`", "#", "$", "%", "^", "'", "\"", "(", ")", "<", ">", ":", ";", "*", "\n"), ' ', $s);
		while ( strpos($s, '  ') !== false ) {
			$s = str_replace('  ',' ', $s);
		}

		$s = trim($s);
		$s = substr($s, 0, 20);
	}

	return $s;
}

function Generate_Query($arrField, $arrSearch, $concat = ' AND ') {
	$retval = '';
	$separatorBetweenValues = '';

	foreach ( $arrSearch as $value ) {
		$separatorBetweenFields = '';
		$retval .= $separatorBetweenValues . " ( ";
		foreach ( $arrField as $field) {
			if ( trim($field) != '' ) {
				$retval .= $separatorBetweenFields . $field . " LIKE '%" . $value . "%' ";
				$separatorBetweenFields = " OR ";
			}
		}
		$retval .= " ) ";
		$separatorBetweenValues = " AND ";
	}

	if ( $retval != '' ) {
		$retval = " $concat " . $retval;
	}

	return $retval;
}

function createDateAsString($year, $month, $day = '') {
	$ret = $year;

	$ret .= substr('0' . $month, -2);

	if ( $day != '' ) {
		$ret .= substr('0' . $day, -2);
	}

	return $ret;
}

function getBackUrl( $defaultUrl = '' ) {
	global $protect;

	$ret = '';

	if ( $ret == '' ) {
		if ( isset( $_GET["backurl"] ) ) {
			$ret = $_GET["backurl"];
		}
	}

	if ( $ret == '' ) {
		if ( isset( $_GET["burl"] ) ) {
			$ret = $_GET["burl"];
		}
	}

	if ( $ret == '' ) {
		$scriptNameStrippedEdit = str_replace('_edit', '', $_SERVER['SCRIPT_NAME']);
		if ( $_SERVER['SCRIPT_NAME'] != $scriptNameStrippedEdit ) {
			$ret = $scriptNameStrippedEdit;
		}
	}

	// simple javascript protection
	$ret = str_replace('<', ' ', $ret);
	$ret = str_replace('>', ' ', $ret);

	$ret = trim($ret);

	$ret = $protect->get_left_part($ret);

	if ( $ret == '' ) {
		$ret = $defaultUrl;
	}

	return $ret;
}
