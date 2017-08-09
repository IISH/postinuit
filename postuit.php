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
    $kenmerk = null; $submitValue = "Bewaar";
    if($id !== ""){
        $kenmerk = Posts::findPostById($id)['kenmerk'];
        $submitValue = "Pas aan";
    }else{
        $currentDate = date('y');
        $characteristicsCount = (Settings::get('post_characteristic_last_used_counter') + 1);
        for($i = strlen($characteristicsCount); $i < 3; $i++){$currentDate.='0';}
        $kenmerk = $currentDate.$characteristicsCount;
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
        , 'selectedPost' => Posts::findPostById($id)
        , 'submitValue' => $submitValue
		, 'field_is_required' => Translations::get('field_is_required')
		, 'field_is_semi_required' => Translations::get('field_is_semi_required')
		, 'field_is_semi_required_receiver_name_and_institute' => Translations::get('field_is_semi_required_receiver_name_and_institute')
	));
}
