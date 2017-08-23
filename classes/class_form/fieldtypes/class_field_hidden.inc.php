<?php 
require_once("./classes/class_form/fieldtypes/class_field.inc.php");

class class_field_hidden extends class_field {

	function __construct($fieldsettings) {
		parent::__construct($fieldsettings);

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only hidden specific parameters

				}
			}
		}
	}

	function form_field($row, $m_form, $required_typecheck_result = 0 ) {
		// welke waarde moeten we gebruiken, uit de db? of uit de form?
		// indien niet goed bewaard gebruik dan de form waarde
		if ( $required_typecheck_result == 0 ) {
			$veldwaarde = $this->get_form_value();
		} else {
			$veldwaarde = $row[$this->get_fieldname()];

			$onNewValue = $this->get_onNew($m_form["primarykey"]);
			if ( $onNewValue != "" ) {
				$veldwaarde = $onNewValue;
			}
		}

		// strip slashes
		$veldwaarde = stripslashes($veldwaarde);
		$veldwaarde = str_replace("\"", "&quot;", $veldwaarde);

		$inputfield = "<input name=\"FORM_::FIELDNAME::\" id=\"FORM_::FIELDNAME::\" type=\"hidden\" value=\"::VALUE::\" ::CLASS::>";

		$inputfield = str_replace("::FIELDNAME::", $this->get_fieldname(), $inputfield);
		$inputfield = str_replace("::VALUE::", $veldwaarde, $inputfield);

		return $inputfield;
	}

	function form_row($row, $row_design, $m_form, $required_typecheck_result = 0) {
		$row_design = $this->form_field($row, $m_form, $required_typecheck_result);

		return $row_design;
	}

}
