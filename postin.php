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
	$kenmerk = null; $submitValue = "Bewaar"; $selectedPost = array(); $files_belonging_to_post = array();
	if($id !== ""){
		$kenmerk = Posts::findPostById($id);
		$selectedPost = $kenmerk;
		$kenmerk = $kenmerk['kenmerk'];
		$submitValue = "Pas aan";
		$files_belonging_to_post = Misc::getListOfFiles( "./documenten/" . $kenmerk );
	}else{
		$currentDate = date('y');
		$characteristicsCount = (Settings::get('post_characteristic_last_used_counter') + 1);
		for ( $i = strlen($characteristicsCount); $i < 3; $i++ ) {
			$currentDate.='0';
		}
		$kenmerk = $currentDate.$characteristicsCount;
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
	));
}
