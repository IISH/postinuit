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
	$kenmerk = null;
	$submitValue = "Bewaar";
	$selectedPost = array();
	$files_belonging_to_post = array();
	$submitError = "";

	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
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

        // This was used to check whether the error values in the array would return something, but alas
//        file_put_contents('./log.txt', date('c')."\n", FILE_APPEND | LOCK_EX);
//        file_put_contents('./log.txt', $_FILES["documentInput"]["error"], FILE_APPEND | LOCK_EX);
//        file_put_contents('./log.txt', "\n", FILE_APPEND | LOCK_EX);
//
//        if($_FILES["documentInput"]["error"] == 4){
//            file_put_contents('./log.txt', "A\n", FILE_APPEND | LOCK_EX);
//        }
//        else if($_FILES["documentInput"]["error"] == '4'){
//            file_put_contents('./log.txt', "B\n", FILE_APPEND | LOCK_EX);
//        }
//        else if($_FILES["documentInput"]["error"] === '4'){
//            file_put_contents('./log.txt', "C\n", FILE_APPEND | LOCK_EX);
//        }
//        else if(!isset($_FILES['documentInput']['name'])){
//            file_put_contents('./log.txt', "D\n", FILE_APPEND | LOCK_EX);
//        }
//        else if($_FILES["documentInput"]["name"] == ''){
//            file_put_contents('./log.txt', "F\n", FILE_APPEND | LOCK_EX);
//        }
//        else if($_FILES["documentInput"]["error"] != 0) {  // ------------ This is the one that works!!!! ------------
//            file_put_contents('./log.txt', "E\n", FILE_APPEND | LOCK_EX);
//            file_put_contents('./log.txt', $_FILES["documentInput"]["error"], FILE_APPEND | LOCK_EX);
//            file_put_contents('./log.txt', "\n", FILE_APPEND | LOCK_EX);
//        }
//        else {
//            file_put_contents('./log.txt', "MEH\n", FILE_APPEND | LOCK_EX);
//            file_put_contents('./log.txt', $_FILES['documentInput']['name'], FILE_APPEND | LOCK_EX);
//            file_put_contents('./log.txt', "\n", FILE_APPEND | LOCK_EX);
//        }

        if($isValid){
            $next = "";
            $_POST['in_out'] = 'in';
            if ( $_POST['submitValue'] === "Bewaar" ) {
                Posts::uploadPost($_POST, $_FILES);
                $next = 'postin.php';
            } else if ( $_POST['submitValue'] === "Pas aan" ) {
                $next = $_SESSION['previous_location']; // gets the previous location (basic search)
                Posts::editPost( $_POST, $_FILES);
            }
            Header("Location: " . $next);
        }else{
            $selectedPost = $_POST;
            $submitError = "* Not all fields have been filled in!";
        }

	} else if($_SERVER['REQUEST_METHOD'] == 'GET') {
        if ( $id !== "" ) {
            // EXISTING

            // find record
	        $selectedPost = Posts::findPostById($id);

			// find username
            $a = new User( $selectedPost['registered_by'] );
            $selectedPost['registered_by_name'] = $a->getName();

			//
            $kenmerk = $selectedPost['kenmerk'];
	        $files_belonging_to_post = Misc::getListOfFiles( Settings::get('attachment_directory') . $kenmerk );

			//
            $submitValue = "Pas aan";
        }else{
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
	));
}
