<?php
//require_once dirname(__FILE__) . "/../classes/_misc_functions.inc.php";

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

    /**
     * Gets all the posts from the database (loaded in the settings variable)
     * @return null
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