<?php
class Wiki {
	protected $ID = 0;
	protected $tilte = '';
	protected $description = '';
	protected $is_deleted = 0;

	function __construct( $row ) {
		$this->ID = $row['ID'];
		$this->title = $row["title"];
		$this->description = $row["description"];
		$this->is_deleted = $row["is_deleted"];
	}

	public function getId() {
		return $this->ID;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getDescription() {
		return $this->description;
	}

	public function getIsdeleted() {
		return $this->is_deleted;
	}
}
