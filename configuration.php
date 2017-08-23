<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();


// create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('admin'));
$oPage->setContent(createAdminContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createAdminContent( ) {
	global $oWebuser, $twig;

	return $twig->render('admin.html', array(
		'title' => Translations::get('menu_configuration')
		, 'message' => 'Under construction'
	));
}
