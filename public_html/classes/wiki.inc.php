<?php
class Wiki {
	protected $ID = 0;
	protected $tilte_nl = '';
	protected $tilte_en = '';
	protected $description_nl = '';
	protected $description_en = '';
	protected $is_deleted = 0;

	function __construct( $row ) {
		$this->ID = $row['ID'];
		$this->title_nl = $row["title_nl"];
		$this->title_en = $row["title_en"];
		$this->description_nl = $row["description_nl"];
		$this->description_en = $row["description_en"];
		$this->is_deleted = $row["is_deleted"];
	}

	public function getId() {
		return $this->ID;
	}

	public function getTitle() {
		if ( getLanguage() == 'nl' ) {
			return $this->title_nl;
		} else {
			return $this->title_en;
		}
	}

	public function getDescription() {
		if ( getLanguage() == 'nl' ) {
			return $this->description_nl;
		} else {
			return $this->description_en;
		}
	}

	public function getIsdeleted() {
		return $this->is_deleted;
	}
}
