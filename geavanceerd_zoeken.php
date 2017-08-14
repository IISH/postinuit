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

	// get search value
	$search = array();
	$search['kenmerk'] = str_replace(array('\\', '/', '%'), '',isset($_GET['kenmerk'])?trim($_GET['kenmerk']) : '');
	$search['in_or_out'] = str_replace(array('\\', '/', '%'), '',isset($_GET['in_out'])?trim($_GET['in_out']) : '');
	$search['date_from'] = str_replace(array('\\', '/', '%'), '',isset($_GET['date_from'])?trim($_GET['date_from']) : '');
	$search['date_to'] = str_replace(array('\\', '/', '%'), '',isset($_GET['date_to'])?trim($_GET['date_to']) : '');
	$search['tegenpartij'] = str_replace(array('\\', '/', '%'), '',isset($_GET['sender_name'])?trim($_GET['sender_name']) : '');
	$search['onze_gegevens'] = str_replace(array('\\', '/', '%'), '',isset($_GET['receiver_name'])?trim($_GET['receiver_name']) : '');
	$search['type_of_documents'] = str_replace(array('\\', '/', '%'), '',isset($_GET['type_of_document'])?trim($_GET['type_of_document']) : '');
	$search['subject'] = str_replace(array('\\', '/', '%'), '',isset($_GET['subject'])?trim($_GET['subject']) : '');
	$search['remarks'] = str_replace(array('\\', '/', '%'), '',isset($_GET['remarks'])?trim($_GET['remarks']) : '');
	$search['registered_by'] = str_replace(array('\\', '/', '%'), '',isset($_GET['registered_by'])?trim($_GET['registered_by']) : '');

    $type_of_documents_array = explode(',',$search['type_of_documents']);
    $in_or_out_array = explode(",", $search['in_or_out']);

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
	$arr = Posts::findPostsAdvanced($search, $recordsPerPage,$page);

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

	// To save the location to go to for when an Post is updated.
    // TODO: check whether the safety of this is top notch!
    $output = implode('&', array_map(
        function ($v, $k) { return sprintf("%s=%s", $k, $v); },
        $_GET,
        array_keys($_GET)
    ));
    $_SESSION['previous_location'] = 'geavanceerd_zoeken.php?'.$output;

	//
	return $twig->render('geavanceerd_zoeken.html', array(
		'title' => Translations::get('menu_geavanceerd_zoeken')
		, 'posts' => $posts
		, 'document_types' => DocumentTypes::getDocumentTypes()
		, 'cuurent_page' => $page
		, 'max_pages' => $arr['maxPages']
		, 'search' => $_GET
		, 'in_uit_lbl' => Translations::get('lbl_in_out')
		, 'lbl_date' => Translations::get('lbl_date')
		, 'kenmerk_lbl' => Translations::get('lbl_post_characteristic')
		, 'date_search_lbl' => Translations::get('lbl_date_search')
		, 'sender_name_lbl' => Translations::get('lbl_tegenpartij')
		, 'receiver_name_lbl' => Translations::get('lbl_onze_gegevens')
		, 'type_of_document_lbl' => Translations::get('lbl_post_document_type')
		, 'department_lbl' => Translations::get('lbl_post_receiver_department')
		, 'subject_lbl' => Translations::get('lbl_post_subject')
		, 'remarks_lbl' => Translations::get('lbl_post_comments')
		, 'registered_by_lbl' => Translations::get('lbl_post_registered_by')
		, 'search_lbl' => Translations::get('lbl_search')
		, 'selected_document_types' => $type_of_documents_array
		, 'selected_in_or_out' => $in_or_out_array
		, 'in_outs' => array(
				array('key' => "in", 'label' => Translations::get('in'))
				, array('key' => "out", 'label' => Translations::get('out'))
			)
		, 'lbl_tegenpartij' => Translations::get('lbl_tegenpartij')
		, 'lbl_onze_gegevens' => Translations::get('lbl_onze_gegevens')
	));
}
