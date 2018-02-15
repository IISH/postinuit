<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

// only for administrators
if ( !$oWebuser->isAdmin() ) {
	die('Access denied. Only for administrators.');
}

// create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('menu_configuration'));
$oPage->setContent(createAdminContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createAdminContent( ) {
	global $twig;

	return $twig->render('configuration.html', array(
		'title' => Translations::get('menu_configuration')
		, 'translations' => Translations::get('page_translations_title')
		, 'type_of_documents' => Translations::get('page_documenttypes_title')
		, 'settings' => Translations::get('page_settings_title')
		, 'users' => Translations::get('page_users_title')
		, 'users_extra_authorisation' => Translations::get('page_users_extra_authorisation_title')
		, 'their_organisation' => Translations::get('page_their_organisation_title')
		, 'wiki' => Translations::get('page_wiki_title')
	));
}
