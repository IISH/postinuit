<?php 

require_once("./classes/misc.inc.php");

class Field {
	protected $oClassMisc;
	protected $m_fieldname;
	protected $m_fieldlabel;
	protected $m_href;
	protected $m_onclick;
	protected $m_target;
	protected $m_alttitle;

	function __construct($fieldSettings) {
		$this->oClassMisc = new Misc();
		$this->m_fieldname = '';
		$this->m_fieldlabel = '';
		$this->m_href = '';
		$this->m_onclick = '';
		$this->m_target = '';
		$this->m_alttitle = '';

		if ( is_array( $fieldSettings ) ) {
			foreach ( $fieldSettings as $field => $setting ) {
				switch ($field) {
					case "fieldname":
						$this->m_fieldname = $setting;
						break;
					case "fieldlabel":
						$this->m_fieldlabel = $setting;
						break;
					case "href":
						$this->m_href = $setting;
						break;
					case "onclick":
						$this->m_onclick = $setting;
						break;
					case "target":
						$this->m_target = $setting;
						break;
					case "alttitle":
						$this->m_alttitle = $setting;
						break;
				}
			}
		}
	}

	public function getFieldname() {
		return $this->m_fieldname;
	}

	public function getFieldlabel() {
		return $this->m_fieldlabel;
	}

	public function getValue($row) {
		$ret = stripslashes($row[$this->getFieldname()]);

		$href2otherpage = $this->get_href();
		$url_onclick = $this->get_onclick();

		if ( !isset($_POST["form_fld_pressed_button"]) ) {
			$_POST["form_fld_pressed_button"] = '';
		}

		if ( $_POST["form_fld_pressed_button"] != '-delete-' && $_POST["form_fld_pressed_button"] != '-delete-now-' ) {

			if ( $href2otherpage <> "" ) {
				$ret = $this->get_if_no_value($ret);

				$no_href = 0;

				$href2otherpage = $this->oClassMisc->ReplaceSpecialFieldsWithDatabaseValues($href2otherpage, $row);
				$href2otherpage = $this->oClassMisc->ReplaceSpecialFieldsWithQuerystringValues($href2otherpage);
				$url_onclick = $this->oClassMisc->ReplaceSpecialFieldsWithDatabaseValues($url_onclick, $row);
				$url_onclick = $this->oClassMisc->ReplaceSpecialFieldsWithQuerystringValues($url_onclick);

				if ( $url_onclick <> "" ) {
					$url_onclick = " onClick=\"" . $url_onclick . "\"";
				}

				$target = $this->get_target();
				if ( $target <> "" ) {
					$target = "target=\"" . $target . "\"";
				}

				$alttitle = $this->get_alttitle();
				if ( $alttitle != '' ) {
					$alttitle = " title=\"" . $alttitle . "\" ";
				}

				if ( $no_href == 0 ) {
					$ret = "<A HREF=\"" . $href2otherpage . "\" " . $url_onclick . " " . $target . $alttitle . ">" . $ret . "</a>";
				}

			}

			$fieldname = $this->getFieldname();
			$fieldname_pointer = ''; //$this->get_fieldname_pointer();

			if ( $fieldname_pointer <> "" ) {
				$fieldname_pointer = $this->oClassMisc->ReplaceSpecialFieldsWithDatabaseValues($fieldname_pointer, $row);
				$fieldname_pointer = $this->oClassMisc->ReplaceSpecialFieldsWithQuerystringValues($fieldname_pointer);
			}
		} else {
			if ( $href2otherpage <> "" ) {
				$ret = $this->get_if_no_value($ret);
			}
		}

		return $ret;
	}

	function get_href() {
		return $this->m_href;
	}

	function get_onclick() {
		return $this->m_onclick;
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

	function get_target() {
		return $this->m_target;
	}

	function get_alttitle() {
		return $this->m_alttitle;
	}
}
