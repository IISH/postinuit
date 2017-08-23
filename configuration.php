<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

// only for functional maintainers and administrators
if ( !$oWebuser->isBeheerder() ) {
	die('Access denied. Only for functional maintainers and administrators.');
}

// create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('admin'));
$oPage->setContent(createAdminContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createAdminContent( ) {
	global $twig;

	return $twig->render('configuration.html', array(
		'title' => Translations::get('menu_configuration')
		, 'translations' => Translations::get('page_translations_title')
		, 'users' => Translations::get('page_users_title')
		, 'users_extra_authorisation' => Translations::get('page_users_extra_authorisation_title')
	));
}
