<?php 
require_once "classes/start.inc.php";

// create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('contact'));
$oPage->setContent(createContactContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createContactContent( ) {
	global $oWebuser, $twig;

	$message = Translations::get('questions_bugs_comments');
	if ( $oWebuser->isLoggedIn() ) {
		$message = str_replace('::NAME::', "<a href=\"mailto:" . IniSettings::get('settings', "contact_email") . "\">" . IniSettings::get('settings', "contact_name") . "</a>", $message);
	} else {
		$message = str_replace('::NAME::', IniSettings::get('settings', "contact_name"), $message);
	}

	return $twig->render('contact.html', array(
		'title' => Translations::get('contact')
		, 'message' => $message
	));
}
