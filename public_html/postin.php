<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

// only for data entry
if ( !$oWebuser->isData() ) {
	die('Access denied. Only for data entry or higher.');
}

// create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('menu_postin'));
$oPage->setContent(createPostInOutContent( 'in' ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );