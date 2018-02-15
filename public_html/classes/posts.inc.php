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
		for ( $i = strlen($post_id); $i < (Settings::get('length_of_kenmerk')-2); $i++ ) {
			$new_kenmerk .= '0';
		}
		$new_kenmerk .= $post_id;

		// get the directory to which to save the documents included in the post
		$directory_to_save = IniSettings::get('settings', 'attachment_directory') . $new_kenmerk . "/";
		$directory_deleted_files = IniSettings::get('settings', 'deleted_attachment_directory') . $new_kenmerk . "/";

		// check to see if the directory exists, otherwise create it
		File::ensureDirectoryExists( $directory_to_save, 'documents' );
		File::ensureDirectoryExists( $directory_deleted_files, 'deleted documents' );

		//
		File::uploadFilesToServer($files['documentInput'], $new_kenmerk);

        // count number of files in kenmerk directory
        $number_of_existing_files = self::getNumberOfFilesFromPost($data['kenmerk']);

        //
		$query = "INSERT INTO `post` (in_out, kenmerk, `date`, their_name, their_organisation, our_name, our_institute,
			our_department, type_of_document, 	subject, remarks, registered_by, number_of_files, our_loginname,
			calculated_ontvanger, calculated_afzender) 
			VALUES (:in_out, :kenmerk, :date, :their_name, :their_organisation, :our_name, :our_institute,
			:our_department, :type_of_document,	:subject, :remarks, :registered_by, :number_of_files, :our_loginname,
			:calculated_ontvanger, :calculated_afzender) ";

		$calculated = Misc::calculateOntvangerAfzender($data);

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
		$stmt->bindParam(':calculated_ontvanger', $calculated['ontvanger'], PDO::PARAM_STR);
		$stmt->bindParam(':calculated_afzender', $calculated['afzender'], PDO::PARAM_STR);

		$stmt->execute();

		return $new_kenmerk;
	}

	/**
	 * Removes the given file from the given directory
	 * @param $filename string the file to remove
	 * @param $kenmerk string the folder where the file exists
     * @return boolean
	 */
	 // TODO TODOGCU2
	public static function moveFile($filename, $kenmerk, $source = '', $target = ''){
		if ( $source == '' || $target == '' ) {
			die('Error moving file, source or taget missing.');
		}

		if ( file_exists($source . $kenmerk . '/' . $filename) ) {
			if ( Settings::get('allow_overwrite_on_upload') == 1 || !file_exists($target . $kenmerk . '/' . $filename) ) {
				// if new or if overwrite allowed
				rename( $source . $kenmerk . '/' . $filename, $target . $kenmerk . '/' . $filename );
			} else {
				// if existing and NO overwrite
				$newFileName = File::findNewFilename($target, $filename);
				rename( $source . $kenmerk . '/' . $filename, $target . $kenmerk . '/' . $newFileName );
			}

			return true;
		} else {
			return false;
		}
	}

    /**
     * Gets the number of files from the post
     * @param $subFolder String the subfolder to look in
     * @return int Integer the number of files in the folder
     */
	public static function getNumberOfFilesFromPost($subFolder){
		$ret = 0;

		if ( file_exists( IniSettings::get('settings', 'attachment_directory') . $subFolder ) ) {
			$fi = new FilesystemIterator(IniSettings::get('settings', 'attachment_directory') . $subFolder, FilesystemIterator::SKIP_DOTS);
			$ret = iterator_count($fi);
		}

		return $ret;
    }

    /**
     * Gets the file selected from the post
     * @param $file
     */
	public static function getFileFromPost($file){
        if (file_exists($file)) {
            if (false !== ($handler = fopen($file, 'r'))) {
                header('Content-Description: File Transfer');
                header('Content-Disposition: attachment; filename="' . basename($file) . '"');
	            header('Content-Transfer-Encoding: binary');
	            header('Content-Type: ' . mime_content_type($file));
	            header('Content-Length: ' . filesize($file));
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');

                readfile($file);
            }
        } else {
            echo "Cannot find file: " . basename($file);
        }
    }

	/**
	 * Save the number of files for the post
	 */
	public static function saveNumberOfFiles($id, $numberOfFiles = 0 ) {
		global $dbConn;

		if ( $id == '' || $id == '0' ) {
			return false;
		}

		//
		$query = "UPDATE `post` SET number_of_files = :number_of_files WHERE ID = :ID ";

		$stmt = $dbConn->getConnection()->prepare( $query );

		$stmt->bindParam(':number_of_files', $numberOfFiles, PDO::PARAM_INT);
		$stmt->bindParam(':ID', $id, PDO::PARAM_INT);
		$stmt->execute();

		return true;
	}

	/**
	 * Edit the data in the database with the updated information from the post
	 * @param $data array post data from the website
	 */
	public static function editPost($data, $files){
		global $dbConn;

		// get the directory to which to save the documents included in the post
        $directory_to_save = IniSettings::get('settings', 'attachment_directory') . $data['kenmerk'] . "/";
		$directory_deleted_files = IniSettings::get('settings', 'deleted_attachment_directory') . $data['kenmerk'] . "/";

		// check to see if the directory exists, otherwise create it
		File::ensureDirectoryExists( $directory_to_save, 'documents' );
		File::ensureDirectoryExists( $directory_deleted_files, 'deleted documents' );

		// TODO TODOGCU EERST DOCUMENT BEWAREN EN DAN PAS FILES UPLOADEN

		//
		File::uploadFilesToServer($files['documentInput'], $data['kenmerk']);

		// count number of files in kenmerk directory
		$number_of_existing_files = self::getNumberOfFilesFromPost($data['kenmerk']);

		//
		$query = "UPDATE `post` 
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
				our_loginname = :our_loginname,
				calculated_ontvanger = :calculated_ontvanger, 
				calculated_afzender = :calculated_afzender 
			WHERE ID = :ID ";

		$stmt = $dbConn->getConnection()->prepare( $query );

		$calculated = Misc::calculateOntvangerAfzender($data);

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
		$stmt->bindParam(':calculated_ontvanger', $calculated['ontvanger'], PDO::PARAM_STR);
		$stmt->bindParam(':calculated_afzender', $calculated['afzender'], PDO::PARAM_STR);

		$stmt->execute();

		return $data['kenmerk'];
	}

    /**
     * Gets the information needed from the employee to fill in the automatic fields
     * @param $data
     * @return mixed
     */
	public static function getEmployeeInformation($data){
		$dbEmployees = new class_pdo( IniSettings::get('db_sync_knaw_ad') );

        $statement = $dbEmployees->getConnection()->prepare("SELECT clean_loginname, clean_institute, clean_department FROM employees WHERE clean_name = :clean_name AND import_status IN (0,1) ");
        $statement->execute(array(':clean_name' => $data['name']));
        $result = $statement->fetchAll();

        return $result;
    }

	public static function findPostsAdvanced($data, $recordsPerPage, $page, $orderBy = ''){
		global $dbConn;

		$arr = array();

		$orderByCriterium = Misc::createOrderByCriterium($orderBy);

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
		$query = "
				SELECT post.*, users.name
				FROM `post`
					LEFT JOIN users ON post.registered_by = users.ID
					LEFT JOIN type_of_document ON post.type_of_document = type_of_document.ID
					WHERE 1 ";

		// kenmerk
		if ( $data['kenmerk'] != '' ) {
			$query .= " AND kenmerk LIKE '%" . $data['kenmerk'] . "%'";
		}

		// in or out
		$query .= $in_or_out_query;

		// date from to
		$query .= $dateFrom;
		$query .= $dateTo;

		// afzender
		if ( $data['tegenpartij'] != '' ) {
			// als we de afzender controleren
			// dan moeten we bij Post IN berichten controleren of 'tegenpartij' in de velden 'their_..." zitten
			// en dan moeten we ook controleren of bij Post UIT 'tegenpartij' in de velden 'our_...' zitten
			// dit heeft te maken met de feit dat de bij Post IN de afzender de externe partij is, en bij Post UIT de ontvangende
			$tmpAfzender = " (" . Generate_Query(array("their_name", "their_organisation"), explode(' ', $data['tegenpartij']), '') . " AND in_out='in' ) ";
			$tmpOntvanger = " (" . Generate_Query(array("our_name", "our_institute", "our_department"), explode(' ', $data['tegenpartij']), '') . " AND in_out='out' ) ";
			$query .= ' AND ( ' . $tmpAfzender . ' OR ' . $tmpOntvanger . ' ) ';
		}

		// ontvanger
		if ( $data['onze_gegevens'] != '' ) {
			// als we de ontvanger controleren
			// dan moeten we bij Post UIT berichten controleren of 'onze_gegevens' in de velden 'their_..." zitten
			// en dan moeten we ook controleren of bij Post IN 'onze_gegevens' in de velden 'our_...' zitten
			// dit heeft te maken met de feit dat de bij Post IN de ontvanger wij zijn, en bij Post UIT de afzender
			$tmpAfzender = " (" . Generate_Query(array("their_name", "their_organisation"), explode(' ', $data['onze_gegevens']), '') . " AND in_out='out' ) ";
			$tmpOntvanger = " (" . Generate_Query(array("our_name", "our_institute", "our_department"), explode(' ', $data['onze_gegevens']), '') . " AND in_out='in' ) ";
			$query .= ' AND ( ' . $tmpAfzender . ' OR ' . $tmpOntvanger . ' ) ';
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
		$query .= " ORDER BY $orderByCriterium";

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
	public static function findPosts($search, $recordsPerPage, $page, $orderBy = '') {
		global $dbConn;

		$search = trim($search);

		$arr = array();
		$criterium = '';
		if ( $search != '' ) {
			$criterium = Generate_Query(array('kenmerk', 'date', 'their_name', 'their_organisation', 'our_loginname', 'our_name', 'our_institute', 'our_department', 'subject', 'remarks', 'users.name'), explode(' ', $search));
		}

		$orderByCriterium = Misc::createOrderByCriterium($orderBy);

		//
		$query = "
			SELECT post.*, users.name
			FROM `post`
				LEFT JOIN users ON post.registered_by = users.ID
				LEFT JOIN type_of_document ON post.type_of_document = type_of_document.ID
			WHERE 1=1 " . $criterium . "
			ORDER BY $orderByCriterium
			";
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
	 * @return object
	 */
	public static function findPostById($id) {
		if ( !self::$is_loaded ) {
			self:self::load();
		}

		$value = isset(self::$settings[$id]) ? self::$settings[$id] : '';

		return $value;
	}

	/**
	 * Returns the post corresponding to the kenmerk given
	 * @param $kenmerk
	 * @return object
	 */
	public static function findPostByKenmerk($kenmerk) {
		global $dbConn;

		$ret = null;

		//
		$query = "SELECT * FROM `post` WHERE kenmerk='" . addslashes($kenmerk) . "' LIMIT 0,1 ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll();
		foreach ($result as $row) {
			$ret = new Post( $row );
		}

		return $ret;
	}

//	public static function decreaseNumberOfFiles( $kenmerk ) {
//		global $dbConn;
//
//		//
//		$query = "UPDATE `post` SET number_of_files = number_of_files - 1 WHERE kenmerk = :kenmerk AND number_of_files > 0 ";
//		$stmt = $dbConn->getConnection()->prepare( $query );
//		$stmt->bindParam(':kenmerk', $kenmerk, PDO::PARAM_STR);
//		$stmt->execute();
//	}

	/**
	 * toString method for the current class
	 * @return string
	 */
	public function __toString() {
		return "Class: " . get_class($this) . "\n";
	}

}