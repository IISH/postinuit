<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();


// create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('postin'));
$oPage->setContent(createPostinContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createPostinContent( ) {
	global $oWebuser, $twig, $protect;

	// get id from the url
	$id = $protect->requestPositiveNumberOrEmpty('get', 'ID');
	// Setting the variables used during the function's runtime
	$kenmerk = null;
	$submitValue = Translations::get('lbl_submit_post');
	$submitAndMailValue = Translations::get('lbl_submit_and_mail_post');
	$selectedPost = array();
	$files_belonging_to_post = array();
	$submitError = "";
    $hasRightsToEdit = true;
    $lastTimeMailSent = Translations::get('lbl_not_yet_mailed');

    /**
     * The action coming from the postin page is a POST Action
     */
	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

	    // Checking whether all required fields are filled
        $isValid = true;
        if($_POST['date'] === ""){
            $isValid = false;
        }else if($_POST['their_name'] === "" && $_POST['their_organisation'] === ""){
            $isValid = false;
        }else if($_POST['our_name'] === ""){
            $isValid = false;
        }else if($_POST['type_of_document'] === ""){
            $isValid = false;
        }else if($_POST['subject'] === ""){
            $isValid = false;
        }

        // Check if all required fields have been filled in, by using the code above
        if($isValid){

            $next = "";
            $_POST['in_out'] = 'in';

            /**
             * The post is being saved to the database
             */
            if ( $_POST['submitValue'] === "Bewaar" || $_POST['submitValue'] === "Save" ) {
                $kenmerk_of_post = Posts::uploadPost($_POST, $_FILES);
                $_POST['user_sending'] = $oWebuser->getName(); // TODO: Is this needed in the initial save?
                // Saves the data of the mail to the database with information to be set as not sent
                Mail::uploadMail($_POST, $kenmerk_of_post, false);
                // Set the location to go to on completion
                $next = 'zoeken.php';
            }
            /**
             * The post is being adjusted and saved to the database
             */
            else if ( $_POST['submitValue'] === "Pas aan" || $_POST['submitValue'] === "Update" ) {
                // Check is the user has the rights to adjust the post
                if($oWebuser->getName() === $_POST['registered_by_name'] || $oWebuser->isBeheerder() ) {
                    $kenmerk_of_post = Posts::editPost( $_POST, $_FILES);
                    $_POST['user_sending'] = $oWebuser->getName(); // TODO: Is this needed in the initial save?
                    // Updates the information of the mail to the database
                    Mail::updateMail($_POST, $kenmerk_of_post);
                    // gets the previous location (basic search)
                    $next = $_SESSION['previous_location'];
                }
                // The user has no rights and will be notified on the page in case attempts are made (e.g. hacking)
                else{
                    $selectedPost = $_POST;
                    $submitError = "* You don't have the rights to edit this post";
                    $kenmerk = $selectedPost['kenmerk'];
                    $submitValue = Translations::get('lbl_update_post');
                    $submitAndMailValue = Translations::get('lbl_update_and_mail_post');
                    $files_belonging_to_post = Misc::getListOfFiles( Settings::get('attachment_directory') . $kenmerk );
                }
            }
            /**
             * The post is being saved to the database and mailed to the receiver of the post
             */
            else if ($_POST['submitValue'] === "Bewaar en mail" || $_POST['submitValue'] === "Save and mail") {
                $kenmerk_of_post = Posts::uploadPost($_POST, $_FILES);
                // Check if the mail has been sent before the data of the mail is adjusted to set being sent
                if(Mail::mailPost($_POST, Misc::getListOfFiles( Settings::get('attachment_directory') . $kenmerk_of_post), $kenmerk_of_post)){
                    $_POST['user_sending'] = $oWebuser->getName();
                    // Saves the data of the mail to the database with information to be set as sent
                    Mail::uploadMail($_POST, $kenmerk_of_post, true);
                }
                // Set the location to go to on completion
                $next = 'zoeken.php';
            }
            /**
             * The post is being adjusted and saved to the database, and mailed to the receiver of the post
             */
            else if ($_POST['submitValue'] === "Pas aan en mail" || $_POST['submitValue'] === "Update and mail") {
                // Check whether the user has the rights to adjust the post
                if($oWebuser->getName() === $_POST['registered_by_name'] || $oWebuser->isBeheerder() ) {
                    $kenmerk_of_post = Posts::editPost( $_POST, $_FILES);
                    // Check if the mail has been sent before the data of the mail is adjusted to set being sent
                    if(Mail::mailPost($_POST, Misc::getListOfFiles( Settings::get('attachment_directory') . $kenmerk_of_post), $kenmerk_of_post)){
                        $_POST['user_sending'] = $oWebuser->getName();
                        // Updates the information of the mail to the database
                        Mail::updateMailSent($_POST, $kenmerk_of_post);
                    }
                    // gets the previous location (basic search)
                    $next = $_SESSION['previous_location'];
                }
                // The user has no rights and will be notified on the page in case attempts are made (e.g. hacking)
                else{
                    $selectedPost = $_POST;
                    $submitError = "* You don't have the rights to edit this post";
                    $kenmerk = $selectedPost['kenmerk'];
                    $submitValue = Translations::get('lbl_update_post');
                    $submitAndMailValue = Translations::get('lbl_update_and_mail_post');
                    $files_belonging_to_post = Misc::getListOfFiles( Settings::get('attachment_directory') . $kenmerk );
                }
            }
            // Set the Location header to the given location in the code above
            Header("Location: " . $next);
        }
        // Not all required fields have been filled with data
        else{
            $selectedPost = $_POST;
            $submitError = "* Not all fields have been filled in!";
        }
	}
	/**
     * The action coming from the postin page is a GET Action
     */
	else if($_SERVER['REQUEST_METHOD'] == 'GET') {
	    // Check if the id given is not empty, thus an existing post
        if ( $id !== "" ) {
            // EXISTING

            // find record
	        $selectedPost = Posts::findPostById($id);

			// find username
            $a = new User( $selectedPost['registered_by'] );
            $selectedPost['registered_by_name'] = $a->getName();

            //
            $hasRightsToEdit = ($oWebuser->getName() === $selectedPost['registered_by_name'] || $oWebuser->isBeheerder() ) ? true : false;

			//
            $kenmerk = $selectedPost['kenmerk'];
	        $files_belonging_to_post = Misc::getListOfFiles( Settings::get('attachment_directory') . $kenmerk );

			//
//            $submitValue = "Pas aan";
            $submitValue = Translations::get('lbl_update_post');
            $submitAndMailValue = Translations::get('lbl_update_and_mail_post');

            $lastTimeMailSent = Mail::getLastTimeMailed($id) ? Mail::getLastTimeMailed($id) : Translations::get('lbl_not_yet_mailed');

        }
        // The post is non existing, thus setting the information according to a new post
        else{
            // NEW
            $currentDate = date('y');
            $characteristicsCount = (Settings::get('post_characteristic_last_used_counter') + 1);
            for ( $i = strlen($characteristicsCount); $i < 3; $i++ ) {
                $currentDate.='0';
            }
            $kenmerk = $currentDate.$characteristicsCount;

			//
	        $selectedPost['kenmerk'] = $kenmerk;
	        $selectedPost['registered_by_name'] = $oWebuser->getName();
	        $selectedPost['registered_by'] = $oWebuser->getId();
	        $selectedPost['date'] = date("Y-m-d");
        }
	}

	// Check whether the date in the database is correct, otherwise adjust both date and counter for characteristic
	if ( Settings::get('post_characteristic_year') !== date('y') ) {
		Settings::save('post_characteristic_year', date('y'));
		Settings::save('post_characteristic_last_used_counter', 1);
	}

	return $twig->render('postin.html', array(
		'title' => Translations::get('menu_postin')
		, 'characteristicsInfo' => Translations::get('lbl_post_characteristic')
		, 'dateArrivedInfo' => Translations::get('lbl_post_date_in')
		, 'senderNameInfo' => Translations::get('lbl_post_sender_name')
		, 'senderInstituteInfo' => Translations::get('lbl_post_sender_organisation')
		, 'receiverNameInfo' => Translations::get('lbl_post_receiver_name')
		, 'receiverInstituteInfo' => Translations::get('lbl_post_receiver_institute')
		, 'receiverDepartmentInfo' => Translations::get('lbl_post_receiver_department')
		, 'typeOfDocumentInfo' => Translations::get('lbl_post_document_type')
		, 'subjectInputInfo' => Translations::get('lbl_post_subject')
		, 'commentsInputInfo' => Translations::get('lbl_post_comments')
		, 'registeredByInfo' => Translations::get('lbl_post_registered_by')
		, 'documentInfo' => Translations::get('lbl_post_documents')
		, 'characteristicsValue' => $kenmerk
		, 'characteristicsYear' => Settings::get('post_characteristic_year')
		, 'documentTypeOptions' => DocumentTypes::getDocumentTypes()
		, 'selectedPost' => $selectedPost
		, 'submitValue' => $submitValue
		, 'field_is_required' => Translations::get('field_is_required')
		, 'field_is_semi_required' => Translations::get('field_is_semi_required')
		, 'field_is_semi_required_sender_name_and_institute' => Translations::get('field_is_semi_required_sender_name_and_institute')
		, 'files_from_post' => $files_belonging_to_post
        , 'submitError' => $submitError
        , 'help_date' => Translations::get('help_date')
        , 'help_sender_name' => Translations::get('help_sender_name')
        , 'help_sender_organisation' => Translations::get('help_sender_organisation')
        , 'help_receiver_name' => Translations::get('help_receiver_name')
        , 'help_type_of_document' => Translations::get('help_type_of_document')
        , 'help_subject' => Translations::get('help_subject')
        , 'help_remarks' => Translations::get('help_remarks')
        , 'document_upload_comment' => Translations::get('document_upload_comment')
        , 'nr_of_files_upload' => Translations::get('nr_of_files_upload')
		, 'lbl_upload_files' => Translations::get('lbl_upload_files')
		, 'lbl_already_uploaded_files' => Translations::get('lbl_already_uploaded_files')
		, 'are_you_sure_delete' => Translations::get('are_you_sure_delete')
		, 'removed' => Translations::get('removed')
        , 'has_rights_to_edit' => $hasRightsToEdit
        , 'submitAndMailValue' => $submitAndMailValue
        , 'lastTimeMailed' => Translations::get('lbl_last_time_mailed')
        , 'lastTimeMailSent' => $lastTimeMailSent
	));
}
