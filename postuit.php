<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('postuit'));
$oPage->setContent(createPostuitContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createPostuitContent( ) {
	global $oWebuser, $twig, $protect;

    // get id from the url
    $id = $protect->requestPositiveNumberOrEmpty('get', 'ID');
    $kenmerk = null;
    $submitValue = "Bewaar";
    $files_belonging_to_post = array();
    $selectedPost = array();
    $submitError = "";

    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
        $isValid = true;
        if ($_POST['date'] === "") {
            $isValid = false;
        } else if ($_POST['our_name'] === "") {
            $isValid = false;
        } else if ($_POST['their_name'] === "" && $_POST['their_organisation'] === "") {
            $isValid = false;
        } else if ($_POST['type_of_document'] === "") {
            $isValid = false;
        } else if ($_POST['subject'] === "") {
            $isValid = false;
        }

        if($isValid){
            $next = "";
            $_POST['in_out'] = 'out';
            if ( $_POST['submitValue'] === "Bewaar" ) {
                Posts::uploadPost($_POST, $_FILES);
                $next = 'postuit.php';
            } elseif ( $_POST['submitValue'] === "Pas aan" ) {
                Posts::editPost( $_POST, $_FILES);
                $next = $_SESSION['previous_location']; // gets the previous location (basic search)
            }
            Header("Location: " . $next);
        }else{
            $selectedPost = $_POST;
            $submitError = "* Not all fields have been filled in!";
        }
    } else if($_SERVER['REQUEST_METHOD'] == 'GET') {
        if ( $id !== "" ) {
            // EXISTING
	        $selectedPost = Posts::findPostById($id);

	        // find username
	        $a = new User( $selectedPost['registered_by'] );
	        $selectedPost['registered_by_name'] = $a->getName();

			//
            $kenmerk = $selectedPost['kenmerk'];
            $submitValue = "Pas aan";
            $files_belonging_to_post = Misc::getListOfFiles( Settings::get('attachment_directory') . $kenmerk );
        } else {
            // NEW
            $currentDate = date('y');
            $characteristicsCount = (Settings::get('post_characteristic_last_used_counter') + 1);
            for ( $i = strlen($characteristicsCount); $i < 3; $i++ ) {
                $currentDate.='0';
            }
            $kenmerk = $currentDate.$characteristicsCount;

	        //
	        $selectedPost['registered_by_name'] = $oWebuser->getName();
	        $selectedPost['registered_by'] = $oWebuser->getId();
        }
    }

	// Check whether the date in the database is correct, otherwise adjust both date and counter for characteristic
	if ( Settings::get('post_characteristic_year') !== date('y') ) {
		Settings::save('post_characteristic_year', date('y'));
		Settings::save('post_characteristic_last_used_counter', 1);
	}

	return $twig->render('postuit.html', array(
		'title' => Translations::get('menu_postuit')
		, 'characteristicsInfo' => Translations::get('lbl_post_characteristic')
		, 'dateSentInfo' => Translations::get('lbl_post_date_out')
		, 'senderNameInfo' => Translations::get('lbl_post_sender_name')
		, 'senderInstituteInfo' => Translations::get('lbl_post_sender_institute')
		, 'senderDepartmentInfo' => Translations::get('lbl_post_sender_department')
		, 'receiverNameInfo' => Translations::get('lbl_post_receiver_name')
		, 'receiverInstituteInfo' => Translations::get('lbl_post_receiver_organisation')
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
		, 'field_is_semi_required_receiver_name_and_institute' => Translations::get('field_is_semi_required_receiver_name_and_institute')
		, 'files_from_post' => $files_belonging_to_post
        , 'submitError' => $submitError
        , 'help_date' => Translations::get('help_date')
        , 'help_sender_name' => Translations::get('help_sender_name')
        , 'help_receiver_name' => Translations::get('help_receiver_name')
        , 'help_receiver_organisation' => Translations::get('help_receiver_organisation')
        , 'help_type_of_document' => Translations::get('help_type_of_document')
        , 'help_subject' => Translations::get('help_subject')
        , 'help_remarks' => Translations::get('help_remarks')
        , 'document_upload_comment' => Translations::get('document_upload_comment')
        , 'nr_of_files_upload' => Translations::get('nr_of_files_upload')
	));
}
