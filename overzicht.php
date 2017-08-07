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
	global $oWebuser, $twig, $protect;

    $recordsPerPage = Settings::get('post_records_per_page');
    $page = $protect->requestPositiveNumberOrEmpty('get', 'page');
    $documentTypes = DocumentTypes::getDocumentTypes();

	// TODO: datum formatten
	// TODO: maak van elke ID een link naar postin.php?ID=xxx of postout.php?ID=xxx

	// TODO: set recordsPerPage and page here
	$arr = Posts::getPosts($recordsPerPage,$page);
	$documentType = '';
    //preprint($arr);
	$posts = array();
	foreach ( $arr as $post ) {
        // Find out what document type needs to be displayed
	    foreach($documentTypes as $key => $docType){
            if($post->getTypeOfDocument() == $key){
                $documentType = $docType[0];
            }
        }
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
				, 'typeOfDocument' => $documentType
				, 'subject' => $post->getSubject()
				, 'remarks' => $post->getRemarks()
			);
	}

	//
	return $twig->render('overzicht.html', array(
		'title' => Translations::get('menu_overzicht')
        , 'posts' => $posts
        , 'document_types' => DocumentTypes::getDocumentTypes()
	));
}
