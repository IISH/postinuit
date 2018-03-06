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
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('menu_zoeken'));
$oPage->setContent(createZoekenContent( ));

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createZoekenContent( ) {
	global $oWebuser, $twig, $protect;

	//
	$backurl  = Misc::createBackurl('&backurl=');

	// get search value
	$searchOriginal = ( isset($_GET['search']) ? trim($_GET['search']) : '' );
	// easy protect
	$search = str_replace(array('\\', '/', '%'), '', $searchOriginal);
	$search = substr($search, 0, 30);

	$orderBy = Misc::getAndProtectOrderBy();

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
	$arr = Posts::findPosts($search, $recordsPerPage, $page, $orderBy);

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

	//
	return $twig->render('zoeken.html', array(
		'title' => Translations::get('menu_zoeken')
		, 'posts' => $posts
		, 'document_types' => DocumentTypes::getDocumentTypes()
		, 'current_page' => $page
		, 'backurl' => $backurl
		, 'max_pages' => $arr['maxPages']
		, 'search' => $search
		, 'lbl_date' => Translations::get('lbl_date')
		, 'in_uit_lbl' => Translations::get('lbl_in_out')
		, 'kenmerk_lbl' => Translations::get('lbl_post_characteristic')
		, 'type_of_document_lbl' => Translations::get('lbl_post_document_type')
		, 'lbl_sender' => Translations::get('lbl_sender')
		, 'lbl_receiver' => Translations::get('lbl_receiver')
		, 'subject_lbl' => Translations::get('lbl_post_subject')
        , 'lbl_current_page' => $page + 1
        , 'lbl_page_indicator_or' => Translations::get('search_page_indicator_or')
        , 'number_of_files_lbl' => Translations::get('number_of_files_lbl')
        , 'is_mailed_lbl' => Translations::get('lbl_is_mailed')
		, 'or_go_to' => Translations::get('or_go_to')
		, 'btn_advanced_search' => Translations::get('btn_advanced_search')
		, 'btn_search' => Translations::get('btn_search')
		, 'lbl_search_for' => Translations::get('lbl_search_for')
		, 'number_of_uploaded_files' => Translations::get('number_of_uploaded_files')
		, 'already_mailed' => Translations::get('already_mailed')
		, 'are_you_sure_you_want_to_mail_this' => Translations::get('are_you_sure_you_want_to_mail_this')
		, 'click_to_mail_this_correspondence_to' => Translations::get('click_to_mail_this_correspondence_to')
		, 'yes' => Translations::get('yes')
		, 'no' => Translations::get('no')
		, 'nothing_found' => Translations::get('nothing_found')
		, 'order_by' => $orderBy
	));
}
