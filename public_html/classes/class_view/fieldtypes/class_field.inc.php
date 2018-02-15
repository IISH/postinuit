<?php 
class class_field {
	private $m_fieldname;
	private $m_fieldlabel;
	private $m_href;
	private $m_href_target;
	private $m_view_max_length;
	private $m_view_max_length_extension;
	private $m_if_no_value;
	public $m_viewfilter;
	private $m_show_different_value = '';
	private $m_class = '';

	function __construct($fieldsettings) {
		$this->m_fieldname = '';
		$this->m_fieldlabel = '';
		$this->m_href = '';
		$this->m_href_target = '';
		$this->m_view_max_length = 0;
		$this->m_view_max_length_extension = '&hellip;';
		$this->m_if_no_value = '';
		$this->m_viewfilter = '';
		$this->m_show_different_value = '';
		$this->m_class = '';

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					case "fieldname":
						$this->m_fieldname = $fieldsettings["fieldname"];
						break;
					case "fieldlabel":
						$this->m_fieldlabel = $fieldsettings["fieldlabel"];
						break;
					case "href":
						$this->m_href = $fieldsettings["href"];
						break;
					case "href_target":
						$this->m_href_target = $fieldsettings["href_target"];
						break;
					case "view_max_length":
						$this->m_view_max_length = $fieldsettings["view_max_length"];
						break;
					case "view_max_length_extension":
						$this->m_view_max_length_extension = $fieldsettings["view_max_length_extension"];
						break;
					case "if_no_value":
						$this->m_if_no_value = $fieldsettings["if_no_value"];
						break;
					case "viewfilter":
						$this->m_viewfilter = $fieldsettings["viewfilter"];
						break;
					case "show_different_value":
						$this->m_show_different_value = $fieldsettings["show_different_value"];
						break;
					case "class":
						$this->m_class = $fieldsettings["class"];
						break;
				}
			}
		}
	}

	function get_if_no_value($retval) {
		$retval = trim($retval);
		if ( strlen($retval) == 0 ) {
			$retval = trim($this->m_if_no_value);
			if ( strlen($retval) == 0 ) {
				$retval = "..no value..";
			}
		}
		return $retval;
	}

	function get_fieldname() {
		return $this->m_fieldname;
	}

	function get_fieldlabel() {
		return $this->m_fieldlabel;
	}

	function get_href() {
		return $this->m_href;
	}

	function get_href_target() {
		return $this->m_href_target;
	}

	function get_viewfilter() {
		return $this->m_viewfilter;
	}

	function get_value($row) {
		$retval = array();
		$separator = '';

		$fieldnames = $this->get_fieldname();
		if ( !is_array($fieldnames) ) {
			$fieldnames = array($fieldnames);
		}

		foreach ( $fieldnames as $fieldname ) {
			if ( $fieldname != '' ) {
				$retval[] = stripslashes($row[$fieldname]);
				$separator = '<br>';
			}
		}

		return implode($separator, $retval);
	}

	function view_field($row) {
		$retval = array();

		$fieldnames = $this->get_fieldname();

		if ( !is_array( $fieldnames ) ) {
			$fieldnames = array($fieldnames);
		}

		foreach ( $fieldnames as $fieldname ) {
			$tmpValue = stripslashes($row[$fieldname]);

			// toon andere waarde
			if ( is_array($this->m_show_different_value) ) {
				if ( $tmpValue == $this->m_show_different_value["value"] ) {
					if ( isset($this->m_show_different_value["showvalue"]) ) {
						$tmpValue = $this->m_show_different_value["showvalue"];
					}
				} else {
					if ( isset($this->m_show_different_value["showelsevalue"]) ) {
						$tmpValue = $this->m_show_different_value["showelsevalue"];
					}
				}
			}

			if ( $this->m_view_max_length != 0 ) {
				if ( strlen($tmpValue) > $this->m_view_max_length ) {

					$tmp_retval = $tmpValue;

					// omdat we gaan knippen, vervang dan paar speciale html characters door gewone characters
					$tmp_retval = str_replace("&ndash;", "-", $tmp_retval);
					$tmp_retval = str_replace("&mdash;", "-", $tmp_retval);

					// neem de eerste x karakters
					$tmp_retval = substr($tmp_retval, 0, $this->m_view_max_length);

					// moeten er nog extra puntjes achter de string geplaatst worden
					if ( $this->m_view_max_length_extension !== false ) {
						$tmp_retval .= $this->m_view_max_length_extension;
					}

					if ( !isset( $_GET["vf_" . $fieldname ] ) ) {
						$_GET["vf_" . $fieldname ] = '';
					}

					$tmp_searchstring = strtolower(trim($_GET["vf_" . $fieldname ]));
					if ( $tmp_searchstring != '' ) {
						$all_search_found_in_max_length_value = 1;
						$tmp_searchstring_array = explode(' ', $tmp_searchstring);

						foreach ( $tmp_searchstring_array as $array_value) {

							$pos = strpos(strtolower($tmp_retval), $array_value);
							if ( $pos === false ) {
								$all_search_found_in_max_length_value = 0;
							}
						}

						if ( $all_search_found_in_max_length_value != 1 ) {
							$tmp_retval = $tmpValue;
						}

					}

					$tmpValue = $tmp_retval;

				} else {
					// controleer of string langer is dan de maximale opgegeven lengte
					if ( strlen($tmpValue) > $this->m_view_max_length ) {
						// ja, neem dan alleen maximaal x karakters

						// omdat we gaan knippen, vervang dan paar speciale html characters door gewone characters
						$tmpValue = str_replace("&ndash;", "-", $tmpValue);
						$tmpValue = str_replace("&mdash;", "-", $tmpValue);

						// neem de eerste x karakters
						$tmpValue = substr($tmpValue, 0, $this->m_view_max_length);

						// moeten er nog extra puntjes achter de string geplaatst worden
						if ( $this->m_view_max_length_extension !== false ) {
							$tmpValue .= $this->m_view_max_length_extension;
						}
					}
				}
			}

			// als veld geen waarde heeft, toon dan de -empty- waarde
			if ( $tmpValue == '' ) {
				if ( $this->m_if_no_value != '' ) {
					$tmpValue = $this->m_if_no_value;
				}
			}

			$retval[] = $tmpValue;
		}

		return implode('<br>', $retval);
	}

	public function getHtmlTarget() {
		$target = $this->get_href_target();
		if ( $target != '' ) {
			$target = ' target="' . $target . '" ';
		}

		return $target;
	}
}
