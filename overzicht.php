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

	// records per page
    $recordsPerPage = Settings::get('post_records_per_page');

    // current page
    $page = $protect->requestPositiveNumberOrEmpty('get', 'page');
    if ( $page == '' ) {
    	$page = 0;
    }

    // max pages
	$max_pages = Posts::getPostsPageCount($recordsPerPage);

	//
    $documentTypes = DocumentTypes::getDocumentTypes();

	// TODO: datum formatten

	//
	$arr = Posts::getPosts($recordsPerPage,$page);
	$documentType = '';
	$posts = array();
	foreach ( $arr as $post ) {
        // Find out what document type needs to be displayed
	    foreach($documentTypes as $key => $docType) {
            if ( $post->getTypeOfDocument() == $key) {
                $documentType = $docType[0];
            }
        }

        if ( $post->getInOut() == 'in' ) {
	        $url = 'postin.php';
        } else {
	        $url = 'postout.php';
        }

		$posts[] = array(
			'ID' => $post->getId()
			, 'url' => $url
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
		, 'cuurent_page' => $page
		, 'max_pages' => $max_pages
	));
}
