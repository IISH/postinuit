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
//preprint($query);
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
	public static function uploadPost($data, $files) {
		global $dbConn;

		// Update the characteristic in the database
		$settingsQuery = 'UPDATE settings SET value = value+1 WHERE property = "post_characteristic_last_used_counter"';
		$settingsStmt = $dbConn->getConnection()->prepare($settingsQuery);
		$settingsStmt->execute();

		// Get the updated value of characteristic from the database
		$last_used_counter_query = 'SELECT value FROM settings WHERE property = "post_characteristic_last_used_counter"';
		$last_used_counter_query_stmt = $dbConn->getConnection()->prepare($last_used_counter_query);
		$last_used_counter_query_stmt->execute();
		$result = $last_used_counter_query_stmt->fetchAll();
		$post_id = $result[0]['value'];

		// Set a variable with the value from the form combined with the characteristic from the database
		$new_kenmerk = substr($data['kenmerk'],0, 2);
		for ( $i = strlen($post_id); $i < 3; $i++ ) {
			$new_kenmerk .= '0';
		}
		$new_kenmerk .= $post_id;

		// get the directory to which to save the documents included in the post
        $directory_to_save = Settings::get('attachment_directory').$new_kenmerk."/";

        // check to see if the directory exists, otherwise create it
        if ( !file_exists( $directory_to_save ) ) {
            if ( !mkdir($directory_to_save, 0764, true ) ) {
                die('Failed to create documents directory');
            }
        }

        // check to see if the array with files is empty or not
        for ( $i = 0; $i < count($files['documentInput']['name']); $i++ ) {
            if ( $files['documentInput']['tmp_name'][$i] != '' ) {
                $fileData = file_get_contents($files['documentInput']['tmp_name'][$i]);
                file_put_contents($directory_to_save.$files['documentInput']['name'][$i], $fileData);
            }
        }

        // count number of files in kenmerk directory
        $number_of_existing_files = self::getNumberOfFilesFromPost($data['kenmerk']);

		$query = "INSERT INTO post (in_out, kenmerk, `date`, their_name, their_organisation, 
			our_name, our_institute, our_department, type_of_document, 
			subject, remarks, registered_by, number_of_files, our_loginname) 
			VALUES (:in_out, :kenmerk, :date, :their_name, :their_organisation,
			:our_name, :our_institute, :our_department, :type_of_document,
			:subject, :remarks, :registered_by, :number_of_files, :our_loginname) ";

		//
		$formattedDate = $data['date'];
		$formattedDate = date("Y-m-d", strtotime($formattedDate));

		$stmt = $dbConn->getConnection()->prepare($query);

		$stmt->bindParam(':in_out', $data['in_out'], PDO::PARAM_STR);
		$stmt->bindParam(':kenmerk', $new_kenmerk, PDO::PARAM_STR);
		$stmt->bindParam(':date', $formattedDate, PDO::PARAM_STR);
		$stmt->bindParam(':their_name', $data['their_name'], PDO::PARAM_STR);
		$stmt->bindParam(':their_organisation', $data['their_organisation'], PDO::PARAM_STR);
		$stmt->bindParam(':our_name', $data['our_name'], PDO::PARAM_STR);
		$stmt->bindParam(':our_institute', $data['our_institute'], PDO::PARAM_STR);
		$stmt->bindParam(':our_department', $data['our_department'], PDO::PARAM_STR);
		$stmt->bindParam(':type_of_document', $data['type_of_document'], PDO::PARAM_INT);
		$stmt->bindParam(':subject', $data['subject'], PDO::PARAM_STR);
		$stmt->bindParam(':remarks', $data['remarks'], PDO::PARAM_STR);
		$stmt->bindParam(':registered_by', $data['registered_by'], PDO::PARAM_STR);
        $stmt->bindParam(':number_of_files', $number_of_existing_files, PDO::PARAM_INT);
        $stmt->bindParam(':our_loginname', $data['our_loginname'], PDO::PARAM_STR);

		$stmt->execute();

		return $new_kenmerk;
	}

	/**
	 * Removes the given file from the given directory
	 * @param $filename string the file to remove
	 * @param $kenmerk string the folder where the file exists
     * @return boolean
	 */
	public static function removeFileFromPost($filename, $kenmerk){

        if( file_exists(Settings::get('attachment_directory').$kenmerk."/".$filename) ) {
            unlink(Settings::get('attachment_directory').$kenmerk."/".$filename);
            return true;
        }else{
            return false;
        }
	}

    /**
     * Gets the number of files from the post
     * @param $subFolder String the subfolder to look in
     * @return int Integer the number of files in the folder
     */
	public static function getNumberOfFilesFromPost($subFolder){
        $fi = new FilesystemIterator(Settings::get('attachment_directory').$subFolder, FilesystemIterator::SKIP_DOTS);
        return iterator_count($fi);
    }

    /**
     * Gets the file selected from the post
     * @param $file
     */
	public static function getFileFromPost($file){
        if (file_exists($file)) {
            if (false !== ($handler = fopen($file, 'r'))) {
                header('Content-Description: File Transfer');
                header('Content-Disposition: attachment; filename=' . basename($file));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file)); //Remove

                readfile($file);
            }
        }
    }

	/**
	 * Edit the data in the database with the updated information from the post
	 * @param $data array post data from the website
	 */
	public static function editPost($data, $files){
		global $dbConn;

        $directory_to_save = Settings::get('attachment_directory').$data['kenmerk']."/";
        $numberOfFiles = count($files['documentInput']['name']);

		if ( !file_exists( $directory_to_save ) ) {
			if ( !mkdir($directory_to_save, 0764, true ) ) {
				die('Failed to create documents directory');
			}
		}

		for ( $i = 0; $i < $numberOfFiles; $i++ ) {
			if ( $files['documentInput']['tmp_name'][$i] != '' ) {
				$fileData = file_get_contents($files['documentInput']['tmp_name'][$i]);
				file_put_contents($directory_to_save.$files['documentInput']['name'][$i], $fileData);
			}
		}

		// count number of files in kenmerk directory
		$number_of_existing_files = self::getNumberOfFilesFromPost($data['kenmerk']);

		//
		$query = "
			UPDATE `post` 
			SET in_out = :in_out,
				kenmerk = :kenmerk,
				`date` = :date,
				their_name = :their_name,
				their_organisation = :their_organisation,
				our_name = :our_name,
				our_institute = :our_institute,
				our_department = :our_department,
				type_of_document = :type_of_document,
				subject = :subject,
				remarks = :remarks,
				registered_by = :registered_by,
				number_of_files = :number_of_files,
				our_loginname = :our_loginname
			WHERE ID = :ID ";

		$stmt = $dbConn->getConnection()->prepare( $query );

		// format the date
		$formattedDate = $data['date'];
		$formattedDate = date("Y-m-d", strtotime($formattedDate));

		//
		$stmt->bindParam(':in_out', $data['in_out'], PDO::PARAM_STR);
		$stmt->bindParam(':kenmerk', $data['kenmerk'], PDO::PARAM_STR);
		$stmt->bindParam(':date', $formattedDate, PDO::PARAM_STR);
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
	    $stmt->bindParam(':number_of_files', $number_of_existing_files, PDO::PARAM_INT);
        $stmt->bindParam(':our_loginname', $data['our_loginname'], PDO::PARAM_STR);

		$stmt->execute();

		return $data['kenmerk'];
	}

    /**
     * Gets the information needed from the employee to fill in the automatic fields
     * @param $data
     * @return mixed
     */
	public static function getEmployeeInformation($data){
	    global $dbConn;

        $statement = $dbConn->getConnection()->prepare("SELECT clean_loginname, clean_institute, clean_department FROM employees WHERE clean_name = :clean_name AND import_status IN (0,1) ");
        $statement->execute(array(':clean_name' => $data['name']));
        $result = $statement->fetchAll();

        return $result;
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

		// start query building
		$query = "SELECT post.*, users.name FROM `post` LEFT JOIN users ON post.registered_by = users.ID WHERE 1 ";

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
			$query .= Generate_Query(array("users.name"), explode(' ', $data['registered_by']));
		}

		// set order
		$query .= " ORDER BY post.kenmerk DESC, post.ID DESC ";

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
			$criterium = Generate_Query(array('kenmerk', 'date', 'their_name', 'their_organisation', 'our_loginname', 'our_name', 'our_institute', 'our_department', 'subject', 'remarks', 'users.name'), explode(' ', $search));
		}

		//
		$query = "SELECT post.*, users.name FROM `post` LEFT JOIN users ON post.registered_by = users.ID WHERE 1=1 " . $criterium . " ORDER BY post.kenmerk DESC, post.ID DESC ";
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