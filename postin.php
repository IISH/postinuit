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
	global $oWebuser, $twig;

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
        , 'senderInstituteInfo' => Translations::get('lbl_post_sender_institute')
        , 'receiverNameInfo' => Translations::get('lbl_post_receiver_name')
        , 'receiverInstituteInfo' => Translations::get('lbl_post_receiver_institute')
        , 'receiverDepartmentInfo' => Translations::get('lbl_post_receiver_department')
        , 'typeOfDocumentInfo' => Translations::get('lbl_post_document_type')
        , 'subjectInputInfo' => Translations::get('lbl_post_subject')
        , 'commentsInputInfo' => Translations::get('lbl_post_comments')
        , 'registeredByInfo' => Translations::get('lbl_post_registered_by')
        , 'documentInfo' => Translations::get('lbl_post_documents')
        , 'characteristicsValue' => (Settings::get('post_characteristic_last_used_counter') + 1)
        , 'characteristicsYear' => Settings::get('post_characteristic_year')
        , 'documentTypeOptions' => DocumentTypes::getDocumentTypes()
	));
}
