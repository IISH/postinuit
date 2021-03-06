<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('index_pagina'));
$oPage->setContent(createZoekenContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes( array( 'hide_menu' => 1 ) ) );

function createZoekenContent( ) {
	global $oWebuser, $twig;

	return $twig->render('index.html', array(
		'menu_zoeken' => Translations::get('menu_zoeken')
		, 'menu_postin' => Translations::get('menu_postin')
		, 'menu_postout' => Translations::get('menu_postout')
		, 'menu_configuration' => Translations::get('menu_configuration')
		, 'menu_wiki' => Translations::get('menu_wiki')
		, 'isAdmin' => $oWebuser->isAdmin()
		, 'isData' => $oWebuser->isData()
		));
}
