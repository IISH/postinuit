<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

// create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('zoeken'));
$oPage->setContent(createZoekenContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createZoekenContent( ) {
	global $oWebuser, $twig, $protect;

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
			, 'inOut' => Translations::get($post->getInOut())
			, 'kenmerk' => $post->getKenmerk()
			, 'date' => date("d-m-Y", strtotime($post->getDate()))
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

    $_SESSION['previous_location'] = 'zoeken.php'.'?page='.$page;

	//
	return $twig->render('zoeken.html', array(
		'title' => Translations::get('menu_zoeken')
		, 'posts' => $posts
		, 'document_types' => DocumentTypes::getDocumentTypes()
		, 'current_page' => $page
		, 'max_pages' => $arr['maxPages']
		, 'search' => $search
		, 'lbl_date' => Translations::get('lbl_date')
		, 'in_uit_lbl' => Translations::get('lbl_in_out')
		, 'kenmerk_lbl' => Translations::get('lbl_post_characteristic')
		, 'type_of_document_lbl' => Translations::get('lbl_post_document_type')
		, 'lbl_tegenpartij' => Translations::get('lbl_tegenpartij')
		, 'lbl_onze_gegevens' => Translations::get('lbl_onze_gegevens')
		, 'subject_lbl' => Translations::get('lbl_post_subject')
        , 'lbl_current_page' => $page + 1
	));
}
