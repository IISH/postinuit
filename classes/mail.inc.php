<?php
/**
 * Created by IntelliJ IDEA.
 * User: Igor van der Bom
 * Date: 18-9-2017
 * Time: 15:55
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail{
    private static $is_loaded = false;
    private static $mail = null;
    private static $mail_table = 'mail';

    /**
     * Load the data from the database
     */
    private static function load(){
        global $dbConn;
        $language = getLanguage();

        $arr = array();

        // which language are we using
        $query = 'SELECT * FROM ' . self::$mail_table;
        $stmt = $dbConn->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        foreach ($result as $row) {
            $arr[ $row["property"] ] = $row["lang_" . $language];
        }

        self::$mail = $arr;
        self::$is_loaded = true;
    }

    /**
     * Get a specific mail from the database
     * @param $mail_name
     * @return string
     */
    public static function get($mail_name){
        if(!self::$is_loaded){
            self::load();
        }

        $value = isset(self::$mail[$mail_name]) ? self::$mail[$mail_name] : '';

        return $value;
    }

    /**
     * Returns the last time the mail has been mailed, in datetime format
     * @param $postID integer the ID of the post it belongs to
     * @return mixed date the last time the mail is sent
     */
    public static function getLastTimeMailed($postID){
        global $dbConn;
        $query = "SELECT date_sent FROM mail WHERE post_id = " . $postID;
        $stmt = $dbConn->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result[0];
    }

    /**
     * Returns whether the mail has been sent or not
     * @param $postID integer the ID of the post it belongs to
     * @return mixed boolean the state of the is_sent variable
     */
    public static function isSent($postID){
        global $dbConn;
        $query = "SELECT is_sent FROM mail WHERE post_id = " . $postID;
        $stmt = $dbConn->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result;
    }

    /**
     * Uploads the information of the mail to the database
     * @param $data array The data needed to be saved in the database
     * @param $kenmerk_of_post string The characteristic of the post that corresponds to the mail
     * @param $mail_sent boolean Whether the mail is already sent
     */
    public static function uploadMail($data, $kenmerk_of_post, $mail_sent){
        global $dbConn;

        $post_query = "SELECT ID FROM post WHERE kenmerk LIKE '" . $kenmerk_of_post . "'";
        $post_stmt = $dbConn->getConnection()->prepare($post_query);
        $post_stmt->execute();
        $post_id =$post_stmt->fetch();

        $query = "INSERT INTO mail (is_sent, sent_by, sent_to, sending_user, post_id, 
			              post_kenmerk, post_link) 
			              VALUES (:is_sent, :sent_by, :sent_to, :sending_user, :post_id,
			              :post_kenmerk, :post_link) ";

        $stmt = $dbConn->getConnection()->prepare($query);

        $post_link = $_SERVER['REQUEST_URI'].'?ID='.$post_id[0];
        $stmt->bindParam(':is_sent', $mail_sent, PDO::PARAM_BOOL);
        $stmt->bindParam(':sent_by', $data['their_name'], PDO::PARAM_STR);
        $stmt->bindParam(':sent_to', $data['our_name'], PDO::PARAM_STR);
        $stmt->bindParam(':sending_user', $data['user_sending'], PDO::PARAM_STR);
        $stmt->bindParam(':post_id', $post_id[0], PDO::PARAM_INT);
        $stmt->bindParam(':post_kenmerk', $kenmerk_of_post, PDO::PARAM_STR);
        $stmt->bindParam(':post_link', $post_link, PDO::PARAM_STR);

        $stmt->execute();

        if($mail_sent){
            self::updateMailSent($data, $kenmerk_of_post);
        }
    }

    /**
     * Updates the data of the mail in the database
     * @param $data array The data needed to be saved in the database
     * @param $kenmerk_of_post string The characteristic of the post that corresponds to the mail
     */
    public static function updateMail($data, $kenmerk_of_post){
        global $dbConn;

        $query = "UPDATE mail 
        SET sent_by = :sent_by,
            sent_to = :sent_to,
            sending_user = :sending_user  
        WHERE post_kenmerk = :post_kenmerk";

        $stmt = $dbConn->getConnection()->prepare($query);

        $stmt->bindParam(':sent_by', $data['their_name'], PDO::PARAM_BOOL);
        $stmt->bindParam(':sent_to', $data['our_name'], PDO::PARAM_STR);
        $stmt->bindParam(':sending_user', $data['user_sending'], PDO::PARAM_STR);
        $stmt->bindParam(':post_kenmerk', $kenmerk_of_post, PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     * Updates the data of the mail in the database for the mail has been sent
     * @param $data array The data needed to be saved in the database
     * @param $kenmerk_of_post string The characteristic of the post that corresponds to the mail
     */
    public static function updateMailSent($data, $kenmerk_of_post){
        global $dbConn;

        $times_sent_query = "SELECT times_sent FROM mail WHERE post_kenmerk = '" . $kenmerk_of_post . "'";
        $times_sent_stmt = $dbConn->getConnection()->prepare($times_sent_query);
        $times_sent_stmt->execute();
        $times_sent = $times_sent_stmt->fetch();

        $times_sent = $times_sent[0] + 1;

        $query = "UPDATE mail 
        SET is_sent = :is_sent,
            date_sent = :date_sent,
            sending_user = :sending_user,
            times_sent = :times_sent
        WHERE post_kenmerk = :post_kenmerk";

        $stmt = $dbConn->getConnection()->prepare($query);

        $isSent = 1;
        $currentDate = date('Y-m-d H:i:s');
        $stmt->bindParam(':is_sent', $isSent, PDO::PARAM_BOOL);
        $stmt->bindParam(':date_sent', $currentDate, PDO::PARAM_STR);
        $stmt->bindParam(':sending_user', $data['user_sending'], PDO::PARAM_STR);
        $stmt->bindParam(':times_sent', $times_sent, PDO::PARAM_INT);
        $stmt->bindParam(':post_kenmerk', $kenmerk_of_post, PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     * Mails the information about the post to the person for whom the post is
     * @param $data array The information about the post to be sent to the person
     * @return bool Whether the mail has been sent successfully
     */
    public static function mailPost($data, $files, $kenmerk_of_post){
        $mail = new PHPMailer(true);
        $mailSent = false;

        global $dbConn;

        // get the mail address of the employee
        $query = "SELECT mail FROM employees WHERE cn LIKE '" . $data['our_name'] . "'";
        $stmt = $dbConn->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();

        // Get the post id corresponding to the given kenmerk
        $post_query = "SELECT ID FROM post WHERE kenmerk LIKE '" . $kenmerk_of_post . "'";
        $post_stmt = $dbConn->getConnection()->prepare($post_query);
        $post_stmt->execute();
        $post_id =$post_stmt->fetch();

        try{
            /**
             * Server settings for the mail
             */
            $mail->SMTPDebug = 0;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
//            $mail->Host = 'localhost';  // Specify main and backup SMTP servers
//            $mail->SMTPAuth = true;                               // Enable SMTP authentication
//            $mail->Username = 'user@example.com';                 // SMTP username
//            $mail->Password = 'secret';                           // SMTP password
//            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 2525;                                   // TCP port to connect to

            /**
             * Set recipients and senders on the mail
             */
            $mail->setFrom('from@example.com', $data['their_name']);
            $mail->addAddress($result['mail'], $data['our_name']);     // Add a recipient
//            $mail->addReplyTo('info@example.com', 'Information');
//            $mail->addCC('cc@example.com');
//            $mail->addBCC('bcc@example.com');

            /**
             * Add attachments to the mail
             */
            foreach($files as $file){
                $mail->addAttachment(Settings::get('attachment_directory') . $data['kenmerk'] . "/". $file);
            }

            /**
             * Preparing the content for the Body and altBody
             */
            $subjectHeader = Translations::get('lbl_mail_header_subject');
            $openingLine = Translations::get('lbl_mail_openingline');
            $dateOfArrival = Translations::get('lbl_mail_date_of_arrival');
            $senderOrganisation = Translations::get('lbl_mail_sender_organisation');
            $senderName = Translations::get('lbl_mail_sender_name');
            $typeOfDocument = Translations::get('lbl_mail_type_of_document');
            $subject = Translations::get('lbl_mail_subject');
            $postLink = Translations::get('lbl_mail_post_link');
            $closingLine = Translations::get('lbl_mail_closing_line');
            $kindRegards = Translations::get('lbl_mail_kind_regards');
            $mailIISG = Translations::get('lbl_mail_IISG');

            /**
             * Content of the mail
             */
            $mail->isHTML(true);    // Set email format to HTML
            $mail->Subject = $subjectHeader;
            $type_of_document = DocumentTypes::get($data['type_of_document'])[0];

            /**
             * Setting the Body of the mail
             */
            $mail->Body    = $openingLine.'<br>'.'<br>'.
                $dateOfArrival .': ' . $data['date'] . '<br>'.
                $senderOrganisation . ': ' . $data['their_organisation'].'<br>'.
                $senderName . ': ' . $data['their_name'].'<br>'.
                $typeOfDocument . ': ' . $type_of_document . '<br>'.
                $subject . ': '. $data['subject'] . '<br>'.
                $postLink . ': ' . $_SERVER['REQUEST_URI'].'?ID='.$post_id[0] .'<br>' . '<br>'.
                $closingLine .'<br>'.'<br>'.
                $kindRegards .'<br>'.
                $mailIISG;

/**
 * Setting the altBody in case the receiver doesnt have html supported
 * PS. Leave it like this for markup of the text
 */
$altBody = "{$openingLine}
            
{$dateOfArrival}: {$data['date']}
{$senderOrganisation}: {$data['their_organisation']}
{$senderName}: {$data['their_name']}
{$typeOfDocument}: {$type_of_document}
{$subject}: {$data['subject']}
{$postLink}: {$_SERVER['REQUEST_URI']}?ID={$post_id[0]}

{$closingLine}

{$kindRegards}
{$mailIISG}";

            $mail->AltBody = $altBody;

            if(!$mail->send()){
                $mailSent = false;
                file_put_contents('log.txt', 'Meh!!!', FILE_APPEND);
            }else{
                $mailSent = true;
            }
        }catch (Exception $e){
            // TODO: Figure something out for exception handling! Like an exception page or something.
        }
        return $mailSent;
    }

    /**
     * @return string the class name of the object
     */
    public function __toString()
    {
        return "Class: " . get_class($this) . "\n";
    }


}