<?php 

require_once("./classes/class_view/fieldtypes/field.inc.php");

class FieldBit extends Field {
	protected $m_show_different_values;
	protected $m_different_true_value;
	protected $m_different_false_value;

	function __construct($fieldSettings) {
		parent::__construct($fieldSettings);

		$this->m_show_different_values = false;
		$this->m_different_true_value = '';
		$this->m_different_false_value = '';

		if ( is_array( $fieldSettings ) ) {
			foreach ( $fieldSettings as $field => $setting ) {
				switch ($field) {
					// only bit specific parameters

					case "show_different_values":
						$this->m_show_different_values = $setting;
						break;
					case "different_true_value":
						$this->m_different_true_value = $setting;
						break;
					case "different_false_value":
						$this->m_different_false_value = $setting;
						break;

				}
			}
		}
	}

	public function getValue($row) {
		$retval = parent::getValue($row);

		$href2otherpage = $this->get_href();
		$url_onclick = $this->get_onclick();

		if ( $this->m_show_different_values == true || $this->m_show_different_values == 1 ) {
			if ( $retval == "1" || $retval == true || $retval == "on" ) {
				$retval = $this->getDifferentTrueValue();
			} else {
				$retval = $this->getDifferentFalseValue();
			}
		}

		return $retval;
	}

	public function getDifferentTrueValue() {
		return $this->m_different_true_value;
	}

	public function getDifferentFalseValue() {
		return $this->m_different_false_value;
	}
}
