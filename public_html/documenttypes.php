<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

// only for administrators
if ( !$oWebuser->isAdmin() ) {
	die('Access denied. Only for administrators.');
}

// first
$content = createContent();

// then create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('page_documenttypes_title'));
$oPage->setContent( $content );

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createContent() {
	global $twig;

	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_bit.inc.php");

	$oView = new class_view();

	$oView->set_view( array(
		'query' => "SELECT * FROM `type_of_document` WHERE is_deleted=0 "
		, 'count_source_type' => 'query'
		, 'order_by' => 'sort_order ASC, type_nl ASC'
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		, 'add_new_button' => array(
				'url' => 'documenttypes-edit.php?ID=0&backurl=[BACKURL]'
				, 'label' => Translations::get('btn_add_new')
				)
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'type_nl'
		, 'fieldlabel' => Translations::get('dutch')
		, 'href' => 'documenttypes-edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
		, 'view_max_length' => 50
		, 'viewfilter' => array(
				'filter' => array (
					array (
						'fieldname' => 'type_nl'
						, 'search_in' => 'type_nl'
						, 'type' => 'string'
						, 'class' => 'quicksearch'
					)
				)
			)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'type_en'
		, 'fieldlabel' => Translations::get('english')
		, 'href' => 'documenttypes-edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
		, 'view_max_length' => 50
		, 'viewfilter' => array(
				'filter' => array (
					array (
						'fieldname' => 'type_en'
						, 'search_in' => 'type_en'
						, 'type' => 'string'
						, 'class' => 'quicksearch'
					)
				)
			)
		)));

	$oView->add_field( new class_field_bit ( array(
		'fieldname' => 'is_disabled'
		, 'fieldlabel' => '<a alt="Disabled" title="Disabled">X</a>'
		, 'show_different_values' => 1
		, 'different_true_value' => '<a alt="Disabled" title="Disabled">X</a>'
		, 'different_false_value' => ''
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'sort_order'
		, 'fieldlabel' => Translations::get('sort_order')
		)));

	// generate view
	$ret = $oView->generate_view();

	//
	return $twig->render('documenttypes.html', array(
			'title' => Translations::get('page_documenttypes_title')
			, 'message' => $ret
	));
}