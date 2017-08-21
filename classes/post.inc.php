<?php
class Post{
	protected $ID = 0;
	protected $in_out = '';
	protected $kenmerk = '';
	protected $date = '';
	protected $their_name = '';
	protected $their_organisation = '';
	protected $our_name = '';
	protected $our_institute = '';
	protected $our_department = '';
	protected $type_of_document = '';
	protected $subject = '';
	protected $remarks = '';
	protected $registered_by = '';
	protected $number_of_files = 0;
	protected $our_loginname = '';

	function __construct( $row ) {
		$this->ID = $row['ID'];
		$this->in_out = $row["in_out"];
		$this->kenmerk = $row["kenmerk"];
		$this->date = $row["date"];
		$this->their_name = $row["their_name"];
		$this->their_organisation = $row["their_organisation"];
		$this->our_name = $row["our_name"];
		$this->our_institute = $row["our_institute"];
		$this->our_department = $row["our_department"];
		$this->type_of_document = $row["type_of_document"];
		$this->subject = $row["subject"];
		$this->remarks = $row["remarks"];
		$this->registered_by = $row["registered_by"];
		$this->number_of_files = $row["number_of_files"];
		$this->our_loginname = $row["our_loginname"];
	}

	public function getId() {
		return $this->ID;
	}

	public function getInOut() {
		return $this->in_out;
	}

	public function getKenmerk() {
		return $this->kenmerk;
	}

	public function getDate() {
		return $this->date;
	}

	public function getTheirName() {
		return $this->their_name;
	}

	public function getTheirOrganisation() {
		return $this->their_organisation;
	}

	public function getOurName() {
		return $this->our_name;
	}

	public function getOurOrganisation() {
		return $this->our_institute;
	}

	public function getOurDepartment() {
		return $this->our_department;
	}

	public function getTypeOfDocument() {
		return $this->type_of_document;
	}

	public function getSubject() {
		return $this->subject;
	}

	public function getRemarks() {
		return $this->remarks;
	}

	public function getRegisteredBy() {
		return $this->registered_by;
	}

	public function getNumberOfFiles() {
	    return $this->number_of_files;
  }

  public function getOurLoginname() {
	    return $this->our_loginname;
  }
}
