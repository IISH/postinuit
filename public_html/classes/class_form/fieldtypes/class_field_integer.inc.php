<?php 
require_once("./classes/class_form/fieldtypes/class_field.inc.php");

class class_field_integer extends class_field {
	private $m_addquotes;
	private $m_min;
	private $m_max;

	function __construct($fieldsettings) {
		parent::__construct($fieldsettings);

		$this->m_addquotes = 0;
		$this->m_min = null;
		$this->m_max = null;

		if ( is_array( $fieldsettings ) ) {
			foreach ( $fieldsettings as $field => $value ) {
				switch ($field) {
					// only integer specific parameters

					case "min":
						$this->m_min = $fieldsettings["min"];
						break;

					case "max":
						$this->m_max = $fieldsettings["max"];
						break;

				}
			}
		}
	}

	public function getMinValue() {
		return $this->m_min;
	}

	public function getMaxValue() {
		return $this->m_max;
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

		$inputfield = "<input name=\"FORM_::FIELDNAME::\" id=\"FORM_::FIELDNAME::\" type=\"text\" value=\"::VALUE::\" size=\"::SIZE::\" ::CLASS::>";

		$inputfield = str_replace("::SIZE::", $this->m_size, $inputfield);

		$inputfield = str_replace("::FIELDNAME::", $this->get_fieldname(), $inputfield);
		$inputfield = str_replace("::VALUE::", $veldwaarde, $inputfield);

		return $inputfield;
	}

	function is_field_value_correct($veldwaarde = "") {
		if ( is_numeric($veldwaarde) === false ) {
			// not an integer
			return 0;
		} else {
			if ( $this->m_min != null ) {
				if ( ($veldwaarde+0) < $this->m_min ) {
					return -1;
				}
			}
			if ( $this->m_max != null ) {
				if ( ($veldwaarde+0) > $this->m_max ) {
					return -1;
				}
			}
		}

		return 1;
	}

	function push_field_into_query_array($query_fields) {
		$value = $this->get_form_value();

		if ( $value == '' ) {
			$value = "NULL";
		}

		array_push($query_fields, array($this->get_fieldname() => $value));

		return $query_fields;
	}
}
