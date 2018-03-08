<?php
class RoleAuthorisation {
	private $id = 0;
	private $role = '';
	private $authorisation = '';
	private $isdeleted = 0;

	function __construct( $id ) {
		$this->initValues( $id );
	}

	private function initValues( $id ) {
		global $dbConn;

		$query = "SELECT * FROM user_authorisation WHERE id=" . $id . " ";

		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		if ( $r = $stmt->fetch() ) {
			$this->id = $id;
			$this->role = $r['role'];
			$this->authorisation = $r["authorisation"];
			$this->isdeleted = $r["isdeleted"];
		}
	}

	public function getId() {
		return $this->id;
	}

	public function getRole() {
		return $this->role;
	}

	public function getAuthorisation() {
		return $this->authorisation;
	}

	public function getIsDeleted() {
		return $this->isdeleted;
	}
}
