<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

// first
$content = createContent();

// then create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('page_wiki_title'));
$oPage->setContent( $content );

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createContent() {
	global $twig, $oWebuser;

	//
	return $twig->render('wiki.html', array(
			'title' => Translations::get('page_wiki_title')
			, 'is_admin' => $oWebuser->isAdmin()
			, 'backurl' => 'wiki.php'
	));
}