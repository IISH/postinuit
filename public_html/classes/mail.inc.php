<?php
/**
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
        if ( !self::$is_loaded ){
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

        $res = '';

        $query = "SELECT date_sent FROM mail WHERE post_id = " . $postID . " ORDER BY ID DESC ";
        $stmt = $dbConn->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();

		if ( $result ) {
			$res = $result[0];
		}

        return $res;
    }

    /**
     * Uploads the information of the mail to the database
     * @param $data array The data needed to be saved in the database
     * @param $kenmerk_of_post string The characteristic of the post that corresponds to the mail
     * @param $mail_sent boolean Whether the mail is already sent
     */
    public static function insertIntoMailLog($data){
        global $dbConn, $oWebuser;

        $post_query = "SELECT ID FROM post WHERE kenmerk='" . $data['kenmerk'] . "'";
        $post_stmt = $dbConn->getConnection()->prepare($post_query);
        $post_stmt->execute();
        $post_id = $post_stmt->fetch();

        $query = "INSERT INTO mail (date_sent, sent_by, sent_to, sending_user, post_id, post_kenmerk) 
			              VALUES (:date_sent, :sent_by, :sent_to, :sending_user, :post_id, :post_kenmerk) ";

        $stmt = $dbConn->getConnection()->prepare($query);

        $stmt->bindParam(':date_sent', date("Y-m-d H:i:s"), PDO::PARAM_BOOL);
        $stmt->bindParam(':sent_by', $data['their_name'], PDO::PARAM_STR);
        $stmt->bindParam(':sent_to', $data['our_name'], PDO::PARAM_STR);
        $stmt->bindParam(':sending_user', $oWebuser->getName(), PDO::PARAM_STR);
        $stmt->bindParam(':post_id', $post_id[0], PDO::PARAM_INT);
        $stmt->bindParam(':post_kenmerk', $data['kenmerk'], PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     * Mails the information about the post to the person for whom the post is
     * @param $data array The information about the post to be sent to the person
     * @return bool Whether the mail has been sent successfully
     */
    public static function sendEmailPost($data, $kenmerk_of_post){
	    global $dbConn;

		// statusses
	    // 0 - not sent
	    // 1 - sent
	    // 2 - not sent due to no email address
	    $mailSent = 0;

	    $files = Misc::getListOfFiles( IniSettings::get('settings', 'attachment_directory') . $kenmerk_of_post);

        // get the mail address of the employee
	    $dbEmployees = new class_pdo( IniSettings::get('db_sync_knaw_ad') );
        $query = "SELECT mail FROM employees WHERE cn='" . $data['our_name'] . "' ";
        $stmt = $dbEmployees->getConnection()->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();

	    // controleer of email veld niet leeg is
		if ( trim($result['mail'] == '' ) ) {
			return 2;
		}

		//
		if ( $data['ID'] == '' ) {
			// Get the post id corresponding to the given kenmerk
			$post_query = "SELECT ID FROM post WHERE kenmerk='" . $kenmerk_of_post . "' ";
			$post_stmt = $dbConn->getConnection()->prepare($post_query);
			$post_stmt->execute();
			$post_id = $post_stmt->fetch();
			$data['ID'] = $post_id['ID'];
		}

	    // try to send email
	    $mail = new PHPMailer(true);
        try {
	        // server settings for the mail
	        $mail->SMTPDebug = false;                                     // Enable verbose debug output (true/false)
	                                                                      // on message body to big error, check max allowed mail size
	        $mail->isSMTP();                                              // Set mailer to use SMTP
	        $mail->Host = IniSettings::get('mail_server', 'host');              // Specify main and backup SMTP servers
	        $mail->SMTPAuth = true;                                       // Enable SMTP authentication
	        $mail->Username = IniSettings::get('mail_server', 'smtp_username'); // SMTP username
	        $mail->Password = IniSettings::get('mail_server', 'smtp_password'); // SMTP password
	        //$mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
	        $mail->Port = IniSettings::get('mail_server', 'port');              // TCP port to connect to

            // set recipients and senders on the mail
	        $mail->setFrom(trim(IniSettings::get('settings', "from_email")), trim(Translations::get("website_name")));
	        $mail->addAddress($result['mail']);
			if ( IniSettings::get('settings', "bcc_email") != '' ) {
				$mail->addBCC(IniSettings::get('settings', "bcc_email"));
			}

            /**
             * Add attachments to the mail
             */
	        $maxMailSize = Settings::get('max_mail_size') * 1024 * 1024;
			$attachmentsSize = 0;
	        $skippedAttachments = array();
	        $attachmentsInMail = array();

            foreach($files as $file){
				$fileSize = filesize( IniSettings::get('settings', 'attachment_directory') . $data['kenmerk'] . "/". $file );
				if ( $fileSize + $attachmentsSize < $maxMailSize ) {
					$mail->addAttachment(IniSettings::get('settings', 'attachment_directory') . $data['kenmerk'] . "/". $file);
					$attachmentsSize += $fileSize;
					$attachmentsInMail[] = "$file";
				} else {
					$skippedAttachments[] = "$file";
				}
            }

	        if ( count($skippedAttachments) > 0 ) {
		        $sk1 = Translations::get('email_skipped_attachments_1') . "<br />\n";

		        $sk2 = Translations::get('email_skipped_attachments_2');
				if ( $sk2 != '' ) {
					$sk2 = str_replace('{s}', ( count($attachmentsInMail) > 1 ? 's': ''), $sk2);
					$sk2 = "<br />\n" . $sk2 . "<br />\n";
					foreach ( $attachmentsInMail as $file ) {
						$sk2 .= "- $file<br />\n";
					}
				}

		        $sk3 = Translations::get('email_skipped_attachments_3');
		        if ( $sk3 != '' ) {
			        $sk3 = str_replace('{s}', ( count($skippedAttachments) > 1 ? 's': ''), $sk3);
			        $sk3 = "<br />\n" . $sk3 . "<br />\n";
			        foreach ( $skippedAttachments as $file ) {
				        $sk3 .= "- <a href=\"" . IniSettings::get('settings', 'url') . "download.php?ID=" . $data['ID'] . "&file=" . urlencode($file) . "\">$file</a><br />\n";
			        }
		        }

				$data['skipped'] = "<br />\n" . $sk1 . $sk2 . $sk3;
			}

            //
            $mail->isHTML(true);
            $mail->Subject = Mail::fillMailValues(Translations::get('post' . $data['in_out'] . '_mail_subject'), $data);
            $mail->Body = Mail::fillMailValues(Translations::get('post' . $data['in_out'] . '_mail_body'), $data);
			$altBody = strip_tags ( str_replace(array('<br>', '<br />'), "\n", $mail->Body) );
            $mail->AltBody = $altBody;

            if ( !$mail->send() ) {
                // log error
                error_log('Error 954278: failed sending mail to: ' . $result['mail']);

				//
                die( 'Error 954278: failed sending mail to: ' . $result['mail'] );
            } else {
                $mailSent = 1;
            }
        } catch (Exception $e) {
	        // log exception
	        error_log( 'Caught exception (error 347283): ' . $e->getMessage() );

			//
	        die( 'Caught exception (error 347283): ' . $e->getMessage() );
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

    public static function fillMailValues( $template, $data ) {
        $ret = $template;

	    $ret = str_replace('[kenmerk]', $data['kenmerk'], $ret);
	    $ret = str_replace('[date]', Misc::convertDateTimeToNice( $data['date'], "d-m-Y"), $ret);
	    $ret = str_replace('[their_organisation]', $data['their_organisation'], $ret);
	    $ret = str_replace('[their_name]', $data['their_name'], $ret);
	    $ret = str_replace('[subject]', $data['subject'], $ret);
	    $ret = str_replace('[website_name]', Translations::get('website_name'), $ret);

		//
		if ( isset($data['skipped']) && $data['skipped'] != '' ) {
			$ret = str_replace('[skipped]', $data['skipped'], $ret);
		} else {
			$ret = str_replace('[skipped]', '', $ret);
		}

		//
	    $tmpTOD = DocumentTypes::get($data['type_of_document']);
	    $ret = str_replace('[type_of_document]', $tmpTOD[0], $ret);

		return $ret;
    }

	public static function updateMailSent($kenmerk_of_post){
		global $dbConn;
		$query = "UPDATE `post` SET is_mailed = is_mailed+1 WHERE kenmerk = :post_kenmerk ";
		$stmt = $dbConn->getConnection()->prepare($query);
		$stmt->bindParam(':post_kenmerk', $kenmerk_of_post, PDO::PARAM_STR);
		$stmt->execute();
	}
}