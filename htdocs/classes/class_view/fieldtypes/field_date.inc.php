<?php 

require_once("./classes/class_view/fieldtypes/field.inc.php");

class FieldDate extends Field {
	protected $m_format;

	function __construct($fieldSettings) {
		parent::__construct($fieldSettings);

		$this->m_format = '';

		if ( is_array( $fieldSettings ) ) {
			foreach ( $fieldSettings as $field => $setting ) {
				switch ($field) {
					// only string specific parameters

					case "format":
						$this->m_format = $setting;
						break;

				}
			}
		}

	}

	public function getValue($row) {
		$retval = parent::getValue($row);

		$href2otherpage = $this->get_href();
		$url_onclick = $this->get_onclick();

		if ( $retval != '' ) {
			// verwijder tijd uit datum
			$retval = trim(str_replace('12:00:00:000AM', '', $retval));
			$retval = trim(str_replace('12:00AM', '', $retval));

			if ( $this->m_format != '' ) {
				$retval = date($this->m_format, strtotime($retval));
			}
		}

		return $retval;
	}

}
