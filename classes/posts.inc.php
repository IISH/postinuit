<?php

class Posts{
    private static $is_loaded = false;
    private static $settings = null;
    private static $settings_table = 'post';

    /**
     * Load the settings from the database
     */
    public static function load() {
        global $dbConn;

        $arr = array();

        //
        $query = 'SELECT * FROM ' . self::$settings_table . ' ORDER BY kenmerk DESC, ID DESC';
        $stmt = $dbConn->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        foreach ($result as $row) {
            $arr[ $row["ID"] ] = $row; // or use kenmerk? -> has to be unique though!
        }

        self::$settings = $arr;
        self::$is_loaded = true;
    }

    /**
     * Return the value of the setting
     *
     * @param string $setting_name The name of the setting
     * @return string The value of the setting
     */
    public static function get($post_character) {
        if ( !self::$is_loaded ) {
            self::load();
        }

        $value = isset( self::$settings[$post_character] ) ? self::$settings[$post_character] : '';

        return $value;
    }

    /**
     * Upload the data entered in the post_in/post_uit form
     * @param $data array with data to be uploaded
     */
    public static function uploadPost($data) {
        global $dbConn;

        $settingsQuery = 'UPDATE settings SET value = value+1 WHERE property = "post_characteristic_last_used_counter"';
        $settingsStmt = $dbConn->getConnection()->prepare($settingsQuery);
        $settingsStmt->execute();

        // TODO: get latest value of post_characteristic_last_used_counter and insert in kenmerk
        $last_used_counter_query = 'SELECT value FROM settings WHERE property = "post_characteristic_last_used_counter"';
        $last_used_counter_query_stmt = $dbConn->getConnection()->prepare($last_used_counter_query);
        $last_used_counter_query_stmt->execute();
        $result = $last_used_counter_query_stmt->fetchAll();
        $post_id = $result[0]['value'];

        $new_kenmerk = substr($data['kenmerk'],0, 2);
        for($i = strlen($post_id); $i < 3; $i++){
            $new_kenmerk .= '0';
        }
        $new_kenmerk .= $post_id;

        $stmt = $dbConn->getConnection()->prepare("INSERT INTO post 
            (in_out, kenmerk, date, their_name, their_organisation, 
            our_name, our_institute, our_department, type_of_document, 
            subject, remarks, registered_by) 
            VALUES (:in_out, :kenmerk, :date, :their_name, :their_organisation,
            :our_name, :our_institute, :our_department, :type_of_document,
            :subject, :remarks, :registered_by)");
        $stmt->bindParam(':in_out', $data['in_out'], PDO::PARAM_STR);
        $stmt->bindParam(':kenmerk', $new_kenmerk, PDO::PARAM_INT);
        $stmt->bindParam(':date', $data['date'], PDO::PARAM_INT);
        $stmt->bindParam(':their_name', $data['their_name'], PDO::PARAM_STR);
        $stmt->bindParam(':their_organisation', $data['their_organisation'], PDO::PARAM_STR);
        $stmt->bindParam(':our_name', $data['our_name'], PDO::PARAM_STR);
        $stmt->bindParam(':our_institute', $data['our_institute'], PDO::PARAM_STR);
        $stmt->bindParam(':our_department', $data['our_department'], PDO::PARAM_STR);
        $stmt->bindParam(':type_of_document', $data['type_of_document'], PDO::PARAM_INT);
        $stmt->bindParam(':subject', $data['subject'], PDO::PARAM_STR);
        $stmt->bindParam(':remarks', $data['remarks'], PDO::PARAM_STR);
        $stmt->bindParam(':registered_by', $data['registered_by'], PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     * Edit the data in the database with the updated information from the post
     * @param $data array post data from the website
     */
    public static function editPost($data){
        global $dbConn;

        $stmt = $dbConn->getConnection()->prepare(
            "UPDATE post 
            SET in_out = :in_out,
                kenmerk = :kenmerk,
                date = :date,
                their_name = :their_name,
                their_organisation = :their_organisation,
                our_name = :our_name,
                our_institute = :our_institute,
                our_department = :our_department,
                type_of_document = :type_of_document,
                subject = :subject,
                remarks = :remarks,
                registered_by = :registered_by
            WHERE ID = :ID");
        $stmt->bindParam(':in_out', $data['in_out'], PDO::PARAM_STR);
        $stmt->bindParam(':kenmerk', $data['kenmerk'], PDO::PARAM_INT);
        $stmt->bindParam(':date', $data['date'], PDO::PARAM_INT);
        $stmt->bindParam(':their_name', $data['their_name'], PDO::PARAM_STR);
        $stmt->bindParam(':their_organisation', $data['their_organisation'], PDO::PARAM_STR);
        $stmt->bindParam(':our_name', $data['our_name'], PDO::PARAM_STR);
        $stmt->bindParam(':our_institute', $data['our_institute'], PDO::PARAM_STR);
        $stmt->bindParam(':our_department', $data['our_department'], PDO::PARAM_STR);
        $stmt->bindParam(':type_of_document', $data['type_of_document'], PDO::PARAM_INT);
        $stmt->bindParam(':subject', $data['subject'], PDO::PARAM_STR);
        $stmt->bindParam(':remarks', $data['remarks'], PDO::PARAM_STR);
        $stmt->bindParam(':registered_by', $data['registered_by'], PDO::PARAM_STR);
        $stmt->bindParam(':ID', $data['ID'], PDO::PARAM_INT);

        $stmt->execute();
    }

    public static function findPostsAdvanced($data, $recordsPerPage, $page){
        global $dbConn;

        $arr = array();

        // date from to
        $dateFrom = !empty($data['date_from']) ? " AND date >= '" . date('Y-m-d', strtotime($data['date_from'])) . "'" : "";
        $dateTo = !empty($data['date_to']) ? " AND date <= '" . date('Y-m-d', strtotime($data['date_to'])) . "'" : "";

        // in or out
        $in_or_out_query = "";
		if ( $data['in_or_out']!= '' ) {
			$in_or_out = explode(",", $data['in_or_out']);
			$in_or_out_query = " AND in_out IN ( ";
			$separator = '';
			foreach ($in_or_out as $value) {
				$in_or_out_query .= $separator . " '" . $value . "' ";
				$separator = ', ';
			}
			$in_or_out_query .= " ) ";
		}

	    // type of documents
	    $type_of_document_query = "";
	    if ( $data['type_of_documents'] != '' ) {
		    $type_of_document_query = " AND type_of_document IN (" . $data['type_of_documents'] . ") ";
	    }

	    // start query buildign
        $query = "SELECT * FROM `post` WHERE 1 ";

	    // kenmerk
		if ( $data['kenmerk'] != '' ) {
		    $query .= " AND kenmerk LIKE '%" . $data['kenmerk'] . "%'";
	    }

	    // in or out
	    $query .= $in_or_out_query;

		// date from to
		$query .= $dateFrom;
		$query .= $dateTo;

	    // tegenpartij
	    if ( $data['tegenpartij'] != '' ) {
		    $query .= Generate_Query(array("their_name", "their_organisation"), explode(' ', $data['tegenpartij']));
	    }

	    // onze gegevens
	    if ( $data['onze_gegevens'] != '' ) {
		    $query .= Generate_Query(array("our_name", "our_institute", "our_department"), explode(' ', $data['onze_gegevens']));
	    }

	    // type of document
		$query .= $type_of_document_query;

	    // subject
	    if ( $data['subject'] != '' ) {
		    $query .= Generate_Query(array("subject"), explode(' ', $data['subject']));
	    }

	    // remarks
	    if ( $data['remarks'] != '' ) {
		    $query .= Generate_Query(array("remarks"), explode(' ', $data['remarks']));
	    }

	    // registered by
	    if ( $data['registered_by'] != '' ) {
		    $query .= Generate_Query(array("registered_by"), explode(' ', $data['registered_by']));
	    }

	    // set order
		$query .= " ORDER BY kenmerk DESC, ID DESC ";

//preprint($query);

		//
        $stmt = $dbConn->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        $nrOfRecords = count($result);
        $skipCounter = 0;
        $saveCounter = 0;
        foreach ($result as $row) {
            // skip X records
            if ( $skipCounter < $page*$recordsPerPage ) {
                $skipCounter++;
            } else {
                // and then take Y records
                $arr[] = new Post( $row );
                $saveCounter++;

                if ( $saveCounter >= $recordsPerPage ) {
                    break;
                }
            }
        }

        return array(
                'data' => $arr
	            , 'page' => $page
                , 'recordsPerPage' => $recordsPerPage
                , 'maxPages' => Misc::calculatePagesCount($nrOfRecords, $recordsPerPage)
	        );

        //TODO: Not sure if code below needs to be used or the current way is safe enough
//        $stmt = $dbConn->getConnection()->prepare("SELECT * FROM post
//            WHERE in_out LIKE '%:in_out%' AND kenmerk LIKE '%:kenmerk%' AND date >= :datefrom AND date <= :dateto AND
//            their_name LIKE '%:their_name%' AND our_name LIKE '%:our_name%' AND our_department LIKE '%:our_department%'
//            AND type_of_document LIKE '%:type_of_document%' AND subject LIKE '%:subject%'
//            AND remarks LIKE '%:remarks%' AND registered_by LIKE '%:registered_by%'");
//        $stmt->bindParam(':in_out', $data[0], PDO::PARAM_STR);
//        $stmt->bindParam(':kenmerk', $data[1], PDO::PARAM_INT);
//        $stmt->bindParam(':datefrom', $data[2], PDO::PARAM_STR);
//        $stmt->bindParam(':dateto', $data[3], PDO::PARAM_STR);
//        $stmt->bindParam(':their_name', $data[4], PDO::PARAM_STR);
//        $stmt->bindParam(':our_name', $data[5], PDO::PARAM_STR);
//        $stmt->bindParam(':our_department', $data[6], PDO::PARAM_STR);
//        $stmt->bindParam(':type_of_document', $data[7], PDO::PARAM_INT);
//        $stmt->bindParam(':subject', $data[8], PDO::PARAM_STR);
//        $stmt->bindParam(':remarks', $data[9], PDO::PARAM_STR);
//        $stmt->bindParam(':registered_by', $data[10], PDO::PARAM_STR);
//
//        preprint($stmt);
//
//        $stmt->execute();
//
//        preprint($stmt->fetchAll());
    }

	/**
	 * Find the posts
	 * @return array
	 */
	public static function findPosts($search, $recordsPerPage, $page) {
		global $dbConn;

		$search = trim($search);

		$arr = array();
		$criterium = '';
		if ( $search != '' ) {
			$criterium = Generate_Query(array('kenmerk', 'date', 'their_name', 'their_organisation', 'our_loginname', 'our_name', 'our_institute', 'our_department', 'subject', 'remarks', 'registered_by'), explode(' ', $search));
		}

		//
		$query = "SELECT * FROM `post` WHERE 1=1 " . $criterium . " ORDER BY kenmerk DESC, ID DESC ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		$nrOfRecords = count($result);
		$skipCounter = 0;
		$saveCounter = 0;
		foreach ($result as $row) {
			// skip X records
			if ( $skipCounter < $page*$recordsPerPage ) {
				$skipCounter++;
			} else {
				// and then take Y records
				$arr[] = new Post( $row );
				$saveCounter++;

				if ( $saveCounter >= $recordsPerPage ) {
					break;
				}
			}
		}

		return array(
				'data' => $arr
				, 'page' => $page
				, 'recordsPerPage' => $recordsPerPage
				, 'maxPages' => Misc::calculatePagesCount($nrOfRecords, $recordsPerPage)
			);
	}

    /**
     * Gets all the posts from the database (loaded in the settings variable)
     * @return array
     */
    public static function getPosts($recordsPerPage, $page) {
        global $dbConn;

        $arr = array();

	    //
	    $query = "SELECT * FROM `post` ORDER BY kenmerk DESC, ID DESC LIMIT " . $page*$recordsPerPage . ", $recordsPerPage ";
	    $stmt = $dbConn->getConnection()->prepare($query);
	    $stmt->execute();
	    $result = $stmt->fetchAll();
	    foreach ($result as $row) {
		    $arr[] = new Post( $row );
	    }

		return $arr;
    }

	/**
	 * Returns the number of pages for Posts
	 * @param $recordsPerPage
	 * @return integer
	 */
	public static function getPostsPageCount($recordsPerPage) {
		global $dbConn;

		$ret = 0;

		//
		$query = "SELECT count(*) AS NrOfRecords FROM `post` ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$ret = ceil($row['NrOfRecords'] / $recordsPerPage);
		}

		return $ret;
	}

    /**
     * Returns the post corresponding to the id given
     * @param $id
     * @return string
     */
    public static function findPostById($id) {
        if(!self::$is_loaded) {
            self:self::load();
        }

        $value = isset(self::$settings[$id]) ? self::$settings[$id] : '';

        return $value;
    }

    /**
     * toString method for the current class
     * @return string
     */
    public function __toString() {
        return "Class: " . get_class($this) . "\n";
    }

}