<?php 
require_once("./classes/class_form/fieldtypes/class_field.inc.php");

class class_field_textarea extends class_field {

	function __construct($fieldsettings) {
		parent::__construct($fieldsettings);

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only textarea specific parameters

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
//		$veldwaarde = stripslashes($veldwaarde);
		$veldwaarde = str_replace("\"", "&quot;", $veldwaarde);
		$veldwaarde = trim($veldwaarde);

		$inputfield = "<textarea id=\"FORM_::FIELDNAME::\" name=\"FORM_::FIELDNAME::\" class=\"::CLASS::\">::VALUE::</textarea>";

		$inputfield = str_replace("::FIELDNAME::", $this->get_fieldname(), $inputfield);
		$inputfield = str_replace("::VALUE::", $veldwaarde, $inputfield);
		$inputfield = str_replace("::CLASS::", $this->get_class(), $inputfield);

		return $inputfield;
	}

	function form_row($row, $row_design, $m_form, $required_typecheck_result = 0) {
		// place input field in row template
		$row_design = str_replace("::FIELD::", $this->form_field($row, $m_form, $required_typecheck_result), $row_design);

		// place fieldname in row template
		$row_design = str_replace("::LABEL::", $this->get_fieldlabel(), $row_design);

		// place if necessary required sign in row template
		$row_design = str_replace("::REQUIRED::", $this->get_required_sign(), $row_design);

		return $row_design;
	}

}
