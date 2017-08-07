<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('overzicht'));
$oPage->setContent(createOverzichtContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createOverzichtContent( ) {
	global $oWebuser, $twig;

	return $twig->render('overzicht.html', array(
		'title' => Translations::get('menu_overzicht')
        , 'posts' => Post::getAllPost()
        , 'document_types' => DocumentTypes::getDocumentTypes()
	));
}
