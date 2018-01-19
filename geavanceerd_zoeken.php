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
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('menu_geavanceerd_zoeken'));
$oPage->setContent(createGeavanceerdZoekenContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createGeavanceerdZoekenContent( ) {
	global $oWebuser, $twig, $protect;

	//
	$backurl  = Misc::createBackurl('&backurl=');

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

	$orderBy = Misc::getAndProtectOrderBy();

    $type_of_documents_array = explode(',', $search['type_of_documents']);
    $in_or_out_array = explode(",", $search['in_or_out']);

	// records per page
	$recordsPerPage = Settings::get('records_per_page');

	// current page
	$page = $protect->requestPositiveNumberOrEmpty('get', 'page');
	if ( $page == '' ) {
		$page = 0;
	}

	//
	$documentTypes = DocumentTypes::getDocumentTypes();

	//
	$arr = Posts::findPostsAdvanced($search, $recordsPerPage, $page, $orderBy);

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

		$tmp = array(
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
            , 'numberOfFiles' => $post->getNumberOfFiles()
            , 'isMailed' => $post->getIsMailed() >= 1 ? true : false
		);

		//
		if ( $post->getInOut() == 'in' ) {
			$tmp['senderName'] = $post->getTheirName();
			$tmp['senderOrganisation'] = $post->getTheirOrganisation();
			$tmp['senderDepartment'] = '';
			$tmp['receiverName'] = $post->getOurName();
			$tmp['receiverOrganisation'] = $post->getOurOrganisation();
			$tmp['receiverDepartment'] = $post->getOurDepartment();
		} else {
			$tmp['receiverName'] = $post->getTheirName();
			$tmp['receiverOrganisation'] = $post->getTheirOrganisation();
			$tmp['receiverDepartment'] = '';
			$tmp['senderName'] = $post->getOurName();
			$tmp['senderOrganisation'] = $post->getOurOrganisation();
			$tmp['senderDepartment'] = $post->getOurDepartment();
		}

		$posts[] = $tmp;
	}

	// To save the location to go to for when an Post is updated.
    // TODO: check whether the safety of this is top notch!
    $output = implode('&', array_map(
        function ($v, $k) { return sprintf("%s=%s", $k, $v); },
        $_GET,
        array_keys($_GET)
    ));
//    $_SESSION['previous_location'] = 'geavanceerd_zoeken.php?'.$output;

	//
	return $twig->render('geavanceerd_zoeken.html', array(
		'title' => Translations::get('menu_geavanceerd_zoeken')
		, 'posts' => $posts
		, 'document_types' => DocumentTypes::getDocumentTypes()
		, 'current_page' => $page
		, 'backurl' => $backurl
		, 'max_pages' => $arr['maxPages']
		, 'search' => $_GET
		, 'in_uit_lbl' => Translations::get('lbl_in_out')
		, 'lbl_date' => Translations::get('lbl_date')
		, 'kenmerk_lbl' => Translations::get('lbl_post_characteristic')
		, 'date_search_lbl' => Translations::get('lbl_date_search')
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
		, 'lbl_sender' => Translations::get('lbl_sender')
		, 'lbl_receiver' => Translations::get('lbl_receiver')
        , 'lbl_current_page' => $page + 1
        , 'lbl_page_indicator_or' => Translations::get('search_page_indicator_or')
        , 'number_of_files_lbl' => Translations::get('number_of_files_lbl')
        , 'is_mailed_lbl' => Translations::get('lbl_is_mailed')
		, 'or_go_to' => Translations::get('or_go_to')
		, 'btn_simple_search' => Translations::get('btn_simple_search')
		, 'number_of_uploaded_files' => Translations::get('number_of_uploaded_files')
		, 'already_mailed' => Translations::get('already_mailed')
		, 'are_you_sure_you_want_to_mail_this' => Translations::get('are_you_sure_you_want_to_mail_this')
		, 'click_to_mail_this_correspondence_to' => Translations::get('click_to_mail_this_correspondence_to')
		, 'yes' => Translations::get('yes')
		, 'no' => Translations::get('no')
		, 'order_by' => $orderBy
	));
}
