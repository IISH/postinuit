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

	// TOOD: recordsPerPage (from database)
	// TODO: page (from URL)
	// TODO: achterhaal hier de label van 'type document' en stuur dat door naar twig
	// TODO: datum formatten
	// TODO: maak van elke ID een link naar postin.php?ID=xxx of postout.php?ID=xxx

	// TODO: set recordsPerPage and page here
	$arr = Posts::getPosts(20,0);
//preprint($arr);
	$posts = array();
	foreach ( $arr as $post ) {
			$posts[] = array(
				'ID' => $post->getId()
				, 'inOut' => $post->getInOut()
				, 'kenmerk' => $post->getKenmerk()
				, 'date' => $post->getDate()
				, 'theirName' => $post->getTheirName()
				, 'theirOrganisation' => $post->getTheirOrganisation()
				, 'ourName' => $post->getOurName()
				, 'ourOrganisation' => $post->getOurOrganisation()
				, 'ourDepartment' => $post->getOurDepartment()
				, 'typeOfDocument' => 'aaa'
				, 'subject' => 'bbb'
				, 'remarks' => 'ccc'
			);
	}

	//
	return $twig->render('overzicht.html', array(
		'title' => Translations::get('menu_overzicht')
        , 'posts' => $posts
        , 'document_types' => DocumentTypes::getDocumentTypes()
	));
}
