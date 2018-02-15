<?php 
require_once("./classes/class_view/fieldtypes/class_field.inc.php");

class class_field_integer extends class_field {

	function __construct($fieldsettings) {
		parent::__construct($fieldsettings);

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only integer specific parameters

				}
			}
		}
	}

	function view_field($row) {
		global $oMisc;

		$retval = parent::view_field($row);

		$href2otherpage = $this->get_href();

		if ( $href2otherpage <> "" ) {
			$retval = $this->get_if_no_value($retval);

			$href2otherpage = $oMisc->ReplaceSpecialFieldsWithDatabaseValues($href2otherpage, $row);
			$href2otherpage = $oMisc->ReplaceSpecialFieldsWithQuerystringValues($href2otherpage);

			$retval = "<A HREF=\"" . $href2otherpage . "\" " . $this->getHtmlTarget() . " >" . $retval . "</a>";
		}

		return $retval;
	}
}
