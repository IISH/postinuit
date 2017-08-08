<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('index'));
$oPage->setContent(createZoekenContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes( array( 'hide_menu' => 1 ) ) );

function createZoekenContent( ) {
	global $oWebuser, $twig;

	return $twig->render('index.html', array(
	));
}
