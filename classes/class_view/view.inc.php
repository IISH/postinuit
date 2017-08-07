<?php 
require_once("./classes/file.inc.php");
require_once("./classes/misc.inc.php");

class View {
	protected $oClassFile;
	protected $oClassMisc;

	protected $m_view;
	protected $m_array_of_fields = Array();

	function __construct() {
		$this->oClassFile = new class_file();
		$this->oClassMisc = new Misc();
	}

	public function generate_view_header() {
		$total_header = '';

		$row_template = "<tr>::TR::</tr>";
		$header_template = "\n<TH align=\"left\"><a class=\"nolink\">::TH::</a>&nbsp;</TH>\n";

		foreach ($this->m_array_of_fields as $one_field_in_array_of_fields) {
				$tmp_header = $header_template;

				// plaats label en buttons in header
				$tmp_header = str_replace("::TH::", $one_field_in_array_of_fields->getFieldlabel(), $tmp_header);

				$total_header .= $tmp_header;
		}

		$return_value = str_replace("::TR::", $total_header, $row_template);

		return $return_value;
	}

	// generate_view
	public function generate_view() {
		global $dbConn;

		$return_value = '';

		// get submit buttons (add new / go back)
		// show buttons at top
		$return_value .= $this->get_view_buttons();

		// execute query
		$stmt = $dbConn->getConnection()->prepare( $this->m_view["query"] );
		$stmt->execute();
		$result = $stmt->fetchAll();

            $return_value .= "<table";

            // extra tabel parameters
            if ( $this->m_view["table_parameters"] != '' ) {
                $return_value .= " " . $this->m_view["table_parameters"] . " ";
            }

            // sluit tabel
            $return_value .= ">";

            $total_row = '';

            // add header row
            $return_value .= $this->generate_view_header();

            // doorloop gevonden recordset
            foreach ($result as $row) {
                $total_data = '';

                foreach ($this->m_array_of_fields as $one_field_in_array_of_fields) {
                    $total_data .= str_replace("::TD::", $one_field_in_array_of_fields->getValue($row), "<TD class=\"recorditem\">::TD::&nbsp;</td>");
                }
                // plaats alle cellen in row template
                $total_row .= str_replace("::TR::", $total_data, "<tr>::TR::</tr>");
            }

            // voeg alle rijen toe aan tabel
            $return_value .= $total_row;

            // end table
            $return_value .= "</table>";


		// return result
		return $return_value;
	}

	// set_view
	public function set_view($aView) {
		$this->m_view = $aView;

		return 1;
	}

	// add_field
	public function add_field($aField) {
		array_push($this->m_array_of_fields, $aField);
		return 1;
	}

	function get_view_buttons() {
		// show add new button?
		$add_new_url = ( isset($this->m_view["add_new_url"]) ? $this->m_view["add_new_url"] : '' );;

		if ( $add_new_url == '' ) {
			return '';
		}

		// place submit buttons
		$submitbuttons = "<p style=\"line-height:20px\"><a href=\"::ADDNEWURL::\" class=\"button\">Add new</a></p>";

		$add_new_url = str_replace("\n", '', $add_new_url);
		$add_new_url = str_replace("\t", '', $add_new_url);
		$add_new_url = str_replace("\r", '', $add_new_url);

		$add_new_url = $this->oClassMisc->ReplaceSpecialFieldsWithQuerystringValues($add_new_url);

		// create add new button
		$submitbuttons = str_replace("::ADDNEWURL::", $add_new_url, $submitbuttons);

		return $submitbuttons;
	}
}
