<?php
/**
 * Class for loading and getting translations from the database
 */

//require_once dirname(__FILE__) . "/../classes/_misc_functions.inc.php";

class Post{
    private static $is_loaded = false;
    private static $settings = null;
    private static $settings_table = 'post';

    /**
     * Load the settings from the database
     */
    private static function load() {
        global $dbConn;

        $arr = array();

        // which language are we using
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
    public static function uploadPost($data){
        global $dbConn;

        $settingsQuery = 'UPDATE settings SET value = value+1 WHERE property = "post_characteristic_last_used_counter"';
        $settingsStmt = $dbConn->getConnection()->prepare($settingsQuery);
        $settingsStmt->execute();

        // TODO: get latest value of post_characteristic_last_used_counter and insert in kenmerk

        $stmt = $dbConn->getConnection()->prepare("INSERT INTO REGISTRY 
            (in_out, kenmerk, date, their_name, their_organisation, 
            our_name, our_institute, our_department, type_of_document, 
            subject, remarks, registered_by) 
            VALUES (:in_out, :kenmerk, :date, :their_name, :their_organisation,
            :our_name, :our_institute, :our_department, :type_of_document,
            :subject, :remarks, :registered_by)");
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

        $stmt->execute();
    }

    /**
     * Gets all the posts from the database (loaded in the settings variable)
     * @return null
     */
    public static function getAllPost(){
        if(!self::$is_loaded){
            self::load();
        }

        return self::$settings;
    }

    /**
     * Returns the post corresponding to the id given
     * @param $id
     * @return string
     */
    public static function findPostById($id){
        if(!self::$is_loaded){
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