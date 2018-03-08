<?php
class Wiki {
	protected $ID = 0;
	protected $tilte_nl = '';
	protected $tilte_en = '';
	protected $description_nl = '';
	protected $description_en = '';
	protected $is_deleted = 0;
	protected $groupname_nl = '';
	protected $groupname_en = '';
	protected $language = '';

	function __construct( $row ) {
		$this->ID = $row['ID'];
		$this->title_nl = $row["title_nl"];
		$this->title_en = $row["title_en"];
		$this->description_nl = $row["description_nl"];
		$this->description_en = $row["description_en"];
		$this->is_deleted = $row["is_deleted"];
		$this->groupname_nl = $row["groupname_nl"];
		$this->groupname_en = $row["groupname_en"];

		// for now only dutch
//		$this->language = getLanguage();
		$this->language = 'nl';
	}

	public function getId() {
		return $this->ID;
	}

	public function getTitle() {
		if ( $this->language == 'nl' ) {
			return $this->title_nl;
		} else {
			return $this->title_en;
		}
	}

	public function getDescription() {
		if ( $this->language == 'nl' ) {
			return $this->description_nl;
		} else {
			return $this->description_en;
		}
	}

	public function getIsdeleted() {
		return $this->is_deleted;
	}

	public function getGroupname() {
		if ( $this->language == 'nl' ) {
			return $this->groupname_nl;
		} else {
			return $this->groupname_en;
		}
	}
}
