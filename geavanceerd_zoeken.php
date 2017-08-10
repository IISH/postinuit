<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('geavanceerd_zoeken'));
$oPage->setContent(createGeavanceerdZoekenContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createGeavanceerdZoekenContent( ) {
	global $oWebuser, $twig, $protect;

/*
	// get search value
	$searchOriginal = ( isset($_GET['search']) ? trim($_GET['search']) : '' );
	// easy protect
	$search = str_replace(array('\\', '/', '%'), '', $searchOriginal);
	$search = substr($search, 0, 30);

	// records per page
	$recordsPerPage = Settings::get('post_records_per_page');

	// current page
	$page = $protect->requestPositiveNumberOrEmpty('get', 'page');
	if ( $page == '' ) {
		$page = 0;
	}

	//
	$documentTypes = DocumentTypes::getDocumentTypes();

	//
	$arr = Posts::findPosts($search, $recordsPerPage,$page);

	$documentType = '';
	$posts = array();
	foreach ( $arr['data'] as $post ) {
		// Find out what document type needs to be displayed
		foreach($documentTypes as $key => $docType) {
			if ( $post->getTypeOfDocument() == $key) {
				$documentType = $docType[0];
			}
		}

		if ( $post->getInOut() == 'in' ) {
			$url = 'postin.php';
		} else {
			$url = 'postuit.php';
		}

		$posts[] = array(
			'ID' => $post->getId()
			, 'url' => $url
			, 'inOut' => $post->getInOut()
			, 'kenmerk' => $post->getKenmerk()
			, 'date' => $post->getDate() 	// TODO: datum formatten
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
*/

	//
	return $twig->render('geavanceerd_zoeken.html', array(
		'title' => Translations::get('menu_geavanceerd_zoeken')
//		, 'posts' => $posts
//		, 'document_types' => DocumentTypes::getDocumentTypes()
//		, 'cuurent_page' => $page
//		, 'max_pages' => $arr['maxPages']
//		, 'search' => $search
	));
}
