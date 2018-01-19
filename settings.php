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
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('page_settings_title'));
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
		'query' => "SELECT * FROM `settings` WHERE property NOT LIKE '%password%' "
		, 'count_source_type' => 'query'
		, 'order_by' => 'property'
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'property'
		, 'fieldlabel' => 'property'
		, 'view_max_length' => 30
		, 'viewfilter' => array(
				'filter' => array (
						array (
							'fieldname' => 'property'
							, 'search_in' => 'property'
							, 'type' => 'string'
							, 'class' => 'quicksearch'
							)
					)
			)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'value'
		, 'fieldlabel' => 'value'
		, 'view_max_length' => 50
		, 'viewfilter' => array(
				'filter' => array (
						array (
							'fieldname' => 'lang_nl'
							, 'search_in' => 'lang_nl'
							, 'type' => 'string'
							, 'class' => 'quicksearch'
							)
					)
			)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'comment'
		, 'fieldlabel' => 'comment'
		, 'view_max_length' => 50
		, 'viewfilter' => array(
				'filter' => array (
					array (
						'fieldname' => 'lang_en'
						, 'search_in' => 'lang_en'
						, 'type' => 'string'
						, 'class' => 'quicksearch'
						)
				)
			)
		)));

	// generate view
	$ret = $oView->generate_view();

	//
	return $twig->render('settings.html', array(
			'title' => Translations::get('page_settings_title')
			, 'message' => $ret
	));
}