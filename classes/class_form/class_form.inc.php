<?php 
class class_form {
	private $m_form;
	private $m_array_of_fields = array();
	private $m_errors = array();
	protected $m_doc_id;
	private $m_old_doc_id;

	function __construct() {
	}

	// function calculate_document_id
	function calculate_document_id() {
		// document id
		$this->m_doc_id = ( isset($_GET[$this->m_form["primarykey"]]) ? $_GET[$this->m_form["primarykey"]] : '' );
		if ( $this->m_doc_id == '' ) {
			$this->m_doc_id = "0";
		}

		// remember the current document id
		$this->m_old_doc_id = $this->m_doc_id;

		return true;
	}

	function form_check_required_and_fieldtype() {
		$result = 1;

		// loop velden
		foreach ($this->m_array_of_fields as $one_field_in_array_of_fields) {
			$veldwaarde = $one_field_in_array_of_fields->get_form_value();

			// if required, then test if not empty
			if ( $one_field_in_array_of_fields->is_field_required() == 1 ) {

				if ( $veldwaarde == '' ) {
					// it's empty
					$result = 0;
					array_push($this->m_errors, "Field '" . $one_field_in_array_of_fields->get_fieldlabel() . "' is required.");
				}
			}

			if ( $veldwaarde <> "" ) {
				switch ( $one_field_in_array_of_fields->is_field_value_correct($veldwaarde) ) {
					case 0:
						$result = 0;
						array_push($this->m_errors, "Field '" . $one_field_in_array_of_fields->get_fieldlabel() . "' is not correct.");
						break;
					case -1:
						$result = 0;
						$minValue = $one_field_in_array_of_fields->getMinValue();
						$maxValue = $one_field_in_array_of_fields->getMaxValue();
						$message = "Field '" . $one_field_in_array_of_fields->get_fieldlabel() . "' is out of range (";
						if ( $minValue != null ) {
							$message .= "min.value: " . $minValue;
						}
						if ( $maxValue != null ) {
							if ( $minValue != null ) {
								$message .= ", ";
							}
							$message .= "max.value: " . $maxValue;
						}
						$message .= ")";
						array_push($this->m_errors, $message);
						break;
				}
			}

		}

		return $result;
	}

	//
	function form_save() {
		global $dbConn;

		$result = 1; // default = okay
		$query_fields = array();

		// loop velden
		foreach ($this->m_array_of_fields as $one_field_in_array_of_fields) {
			$query_fields = $one_field_in_array_of_fields->push_field_into_query_array($query_fields);
		}

		if ( $this->m_doc_id == "0" ) {
			$query = $this->MakeQuery($query_fields, "insert", '');
		} else {
			$query_where = " WHERE " . $this->m_form["primarykey"] . "=" . $_GET[$this->m_form["primarykey"]];
			$query = $this->MakeQuery($query_fields, "update", $query_where);
		}

		// execute query
		$stmt = $dbConn->getConnection()->prepare( $query );
		$stmt->execute();

		// if current id = 0
		// get the last id
		if ( $this->m_doc_id == "0" || $this->m_doc_id == '' ) {
			// tabelnaam moet als variable
			$this->m_doc_id = $this->mysqlInsertId($this->m_form["table"]);
		}

		// 
		$this->postSave();

		return $result;
	}

	function get_document_id($table) {
		// if current id = 0
		// get the last id
		if ( $this->m_doc_id == "0" || $this->m_doc_id == '' ) {
			$this->m_doc_id = $this->mysqlInsertId($table);
		}

		return $this->m_doc_id;
	}

	function mysqlInsertId($table) {
		global $dbConn;

		$retval = '0';

		$query = "SELECT ID FROM $table ORDER BY ID DESC LIMIT 0, 1 ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		if ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
			$retval = $row["ID"];
		}

		return $retval;
	}

	function MakeQuery($query_fields, $insert_or_update, $query_where) {
		$query = '';

		if ( $insert_or_update == "insert" ) {
			$query = "INSERT INTO " . $this->m_form["table"];
			$fields = '';
			$values = '';
			$separator = '';

			if ( is_array($query_fields) ) {
				foreach ($query_fields as $one_item_from_array) {
					foreach ( $one_item_from_array as $fieldname => $fieldvalue ) {
						if ( $fieldname != 'ID' ) {
							$fields .= $separator . $fieldname;
							$values .= $separator . $fieldvalue;
							$separator = ", ";
						}
					}
				}
			}

			//
			$query .= " (" . $fields . ") VALUES (" . $values . ") ";

		} elseif ( $insert_or_update == "update" ) {
			$query = "UPDATE " . $this->m_form["table"] . " SET ";

			$separator = '';

			if ( is_array($query_fields) ) {
				foreach ($query_fields as $one_item_from_array) {
					foreach ( $one_item_from_array as $fieldname => $fieldvalue ) {

						if ( $fieldname != 'ID' ) {
							$query .= $separator . $fieldname . "=" . $fieldvalue;
							$separator = ", ";
						}

					}
				}
			}

			// where (only for update)
			if ( $insert_or_update == "update" ) {
				$query .= $query_where;
			}
		}

		return $query;
	}

	// generate_form
	function generate_form() {
		global $protect, $oMisc, $dbConn;

		// document id
		$this->calculate_document_id();

		$return_value = '';
		$required_typecheck_result = -1;	// default, nog nix geprobeerd te bewaren
								// -1 nix, 0 errors bij bewaren, 1 bewaren okay

		// if form submitted try to save document
		if ( isset($_POST["issubmitted"]) && $_POST["issubmitted"] == "1" ) {
			// check first if all required fields are filled in
			// and also check if the values are of the correct type
			$required_typecheck_result = $this->form_check_required_and_fieldtype();

			// if everything correct, try to save the document
			if ( $required_typecheck_result == 1 ) {
				$saveresult = $this->form_save();
			}
		}

		// als form is gesubmit
		// en als save resultaat okay is
		// en als button close is aangeklikt
		// ga dan naar backurl
		if ( isset($_POST["issubmitted"]) && $_POST["issubmitted"] == "1" ) {

			if ( $required_typecheck_result == 1 ) {

				if ( isset($_POST["pressedbutton"]) && $_POST["pressedbutton"] == "saveclose" ) {

					$this->postSave();

					$backurl = getBackUrl();

					if ( strpos($backurl, "#") === false ) {
						if ( $this->m_doc_id <> "0" ) {
							$backurl .= "#" . $this->m_doc_id;
						}
					}

					header("Location: " . $backurl);
				} elseif ( isset($_POST["pressedbutton"]) && $_POST["pressedbutton"] == "delete" ) {

					$backurl = getBackUrl();

					// verwijder anchor
					$backurl = str_replace("#" . $this->m_doc_id, '', $backurl);
					header("Location: " . $backurl);

				}
			}
		}

		// default template for form
		$template_tr = "
<tr>
	<TD valign=\"top\"><span class=\"form_field_label\">::LABEL:: </span><span class=\"errormessage\">::REQUIRED::</span>&nbsp;</td>
	<td>::FIELD::</td>
</tr>
";
		$template_form = "
<form name=\"frmA\"  id=\"frmA\" action=\"::ACTION::\" method=\"POST\" onchange=\"setIsChanged();\">
<input type=\"hidden\" name=\"issubmitted\"  id=\"issubmitted\" value=\"1\">
<input type=\"hidden\" name=\"pressedbutton\" id=\"pressedbutton\" value=\"\">

::FORMFIELDS::

</form>
";
		$template_error_message = '<span class="errormessage">::ERROR::</span><br>';

		// show errors
		if ( count($this->m_errors) > 0 ) {
			foreach ($this->m_errors as $errormessage) {
				$return_value .= str_replace("::ERROR::", $errormessage, $template_error_message);
			}
			$return_value .= "<br>";
		}

		if ( $protect->requestPositiveNumberOrEmpty('get', "ID") == '' ) {
			$this->m_form["query"] = str_replace("[FLD:ID]", "0", $this->m_form["query"]);
		}

		// plaats url parameters in query
		$this->m_form["query"] = $oMisc->PlaceURLParametersInQuery($this->m_form["query"]);

		// execute query
		$stmt = $dbConn->getConnection()->prepare( $this->m_form["query"] );

		$stmt->execute();

		// start table
		$all_fields = "<table>";

		// get submit buttons
		$submitbuttons = $this->get_form_edit_buttons();

		$total_row = '';

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		foreach ($this->m_array_of_fields as $one_field_in_array_of_fields) {
			// get row template (label + input field)
			$tmp_data = $template_tr;

			$tmp_data = $one_field_in_array_of_fields->form_row($row, $tmp_data, $this->m_form, $required_typecheck_result);

			$total_row .= $tmp_data . "\n";
		}

		// voeg alle rijen toe aan tabel
		$all_fields .= $total_row;

		// add submit buttons to view (and extra empty row)
		$all_fields .= "<tr><td>&nbsp;</td></tr>" . $submitbuttons;

		// end table
		$all_fields .= "</table>";

		$return_value .= $all_fields;

		// get form_start
		$form_start = $template_form;
		$form_action = $_SERVER["SCRIPT_NAME"];
		$form_query_string = $_SERVER["QUERY_STRING"];
		if ( $form_query_string <> "" ) {
			if ( $this->m_doc_id <> $this->m_old_doc_id ) {
				$form_query_string = str_replace($this->m_form["primarykey"] . "=0", $this->m_form["primarykey"] . "=" . $this->m_doc_id, $form_query_string);

				$backurl = getBackUrl();

				if ( strpos($backurl, "#") === false ) {
					if ( $this->m_doc_id <> "" ) {
						$form_query_string .= "%23" . $this->m_doc_id;
					}
				}
			}
			$form_action .= "?" . $form_query_string;
		}
		$form_start = str_replace("::ACTION::", $form_action, $form_start);

		$return_value = str_replace("::FORMFIELDS::", $return_value, $form_start);

		// return result
		return $return_value;
	}

	function get_form_edit_buttons() {
		// place submit buttons
		$submitbuttons = "
<tr>
	<td colspan=\"2\" align=\"center\">

		<!-- cancelbutton -->
		<a href=\"::CANCELURL::\" class=\"button\">Cancel</a>
		&nbsp; &nbsp; &nbsp; &nbsp;
		<!-- /cancelbutton -->

		<!-- deletebutton -->
		<input type=\"button\" class=\"button\" name=\"deleteButton\" id=\"deleteButton\" value=\"Delete\" onClick=\"doc_delete('delete');\">
		&nbsp; &nbsp; &nbsp; &nbsp;
		<!-- /deletebutton -->

		<!-- savebutton -->
		<input type=\"button\" class=\"button\" name=\"saveButtonGoBack\" id=\"saveButtonGoBack\" value=\"Save\" onClick=\"doc_submit('saveclose');\">
		<!-- /savebutton -->

	</td>
</tr>
";
		if ( !isset($this->m_form["disallow_delete"]) ) {
			$this->m_form["disallow_delete"] = false;
		}

		if ( $this->m_doc_id == "0" || $this->m_form["disallow_delete"] === true ) {
			$searchstr = '@<!-- ' . 'deletebutton' . ' -->.*?<!-- /' . 'deletebutton' . ' -->@si';
			$submitbuttons = preg_replace ($searchstr, '', $submitbuttons);
		}

		$cancelurl = getBackUrl();

		if ( strpos ($cancelurl, "#") === false ) {
			if ( $this->m_doc_id <> "0" ) {
				$cancelurl .= "#" . $this->m_doc_id;
			}
		}
		$submitbuttons = str_replace("::CANCELURL::", $cancelurl, $submitbuttons);

		return $submitbuttons;
	}

	// set_form
	function set_form($aView) {
		$this->m_form = $aView;

		return 1;
	}

	// add_field
	function add_field($aField) {
		array_push($this->m_array_of_fields, $aField);
		return 1;
	}

	function postSave() {
		if ( $_GET[$this->m_form["primarykey"]] == "0" ) {
			if ( $this->m_doc_id != 0 ) {

				$url = $_SERVER["SCRIPT_NAME"] . "?" . $_SERVER["QUERY_STRING"];

				// vervang id 0 door nieuwe id
				$url = str_replace("?" . $this->m_form["primarykey"] . "=" . $_GET[$this->m_form["primarykey"]], "?" . $this->m_form["primarykey"] . "=" . $this->m_doc_id, $url);
				$url = str_replace("&" . $this->m_form["primarykey"] . "=" . $_GET[$this->m_form["primarykey"]], "?" . $this->m_form["primarykey"] . "=" . $this->m_doc_id, $url);

				header("Location: " . $url);
			}
		}

		return true;
	}
}
