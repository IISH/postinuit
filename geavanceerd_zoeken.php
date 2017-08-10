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
//	$searchOriginal = ( isset($_GET['search']) ? trim($_GET['search']) : '' );
	//$keys = array('in_out', 'kenmerk', 'date_from', 'date_to', 'sender_name', 'receiver_name', 'type_of_document', 'department', 'subject', 'remarks', 'registered_by');
	$search = array();
    $original_type_of_documents = str_replace(array('\\', '/', '%'), '',isset($_GET['type_of_document'])?trim($_GET['type_of_document']) : '');
    $type_of_documents = "";
    for($i = 0; $i < strlen($original_type_of_documents); $i++){
        $type_of_documents .= substr($_GET['type_of_document'], $i, 1) . ",";
    }
    $type_of_documents = rtrim($type_of_documents, ",");
    $type_of_documents_array = explode(",", $type_of_documents);

    $original_in_or_out = str_replace(array('\\', '/', '%'), '',isset($_GET['in_out'])?trim($_GET['in_out']) : '');
    $in_or_out = rtrim($original_in_or_out, ",");
    $in_or_out_array = explode(",", $in_or_out);

	array_push($search, $in_or_out);
	array_push($search, str_replace(array('\\', '/', '%'), '',isset($_GET['kenmerk'])?trim($_GET['kenmerk']) : ''));
	array_push($search, str_replace(array('\\', '/', '%'), '',isset($_GET['date_from'])?trim($_GET['date_from']) : ''));
	array_push($search, str_replace(array('\\', '/', '%'), '',isset($_GET['date_to'])?trim($_GET['date_to']) : ''));
	array_push($search, str_replace(array('\\', '/', '%'), '',isset($_GET['sender_name'])?trim($_GET['sender_name']) : ''));
	array_push($search, str_replace(array('\\', '/', '%'), '',isset($_GET['receiver_name'])?trim($_GET['receiver_name']) : ''));
	array_push($search, $type_of_documents);
	array_push($search, str_replace(array('\\', '/', '%'), '',isset($_GET['department'])?trim($_GET['department']) : ''));
	array_push($search, str_replace(array('\\', '/', '%'), '',isset($_GET['subject'])?trim($_GET['subject']) : ''));
	array_push($search, str_replace(array('\\', '/', '%'), '',isset($_GET['remarks'])?trim($_GET['remarks']) : ''));
	array_push($search, str_replace(array('\\', '/', '%'), '',isset($_GET['registered_by'])?trim($_GET['registered_by']) : ''));

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


	//
	return $twig->render('geavanceerd_zoeken.html', array(
		'title' => Translations::get('menu_geavanceerd_zoeken')
		, 'posts' => $posts
		, 'document_types' => DocumentTypes::getDocumentTypes()
		, 'cuurent_page' => $page
		, 'max_pages' => $arr['maxPages']
		, 'search' => $_GET
        , 'in_uit_lbl' => Translations::get('lbl_in_out')
        , 'kenmerk_lbl' => Translations::get('lbl_post_characteristic')
        , 'date_search_lbl' => Translations::get('lbl_date_search')
        , 'sender_name_lbl' => Translations::get('lbl_post_sender_name')
        , 'receiver_name_lbl' => Translations::get('lbl_post_receiver_name')
        , 'type_of_document_lbl' => Translations::get('lbl_post_document_type')
        , 'department_lbl' => Translations::get('lbl_post_receiver_department')
        , 'subject_lbl' => Translations::get('lbl_post_subject')
        , 'remarks_lbl' => Translations::get('lbl_post_comments')
        , 'registered_by_lbl' => Translations::get('lbl_post_registered_by')
        , 'search_lbl' => Translations::get('lbl_search')
        , 'selected_document_types' => $type_of_documents_array
        , 'selected_in_or_out' => $in_or_out_array
        , 'in_outs' => array("in", "out")
	));
}
