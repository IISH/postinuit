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
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('page_their_organisation_title'));
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
		'query' => "SELECT their_organisation, CAST(their_organisation as BINARY) as their_binary, count(*) AS aantal FROM `post` WHERE 1=1 "
		, 'count_source_type' => 'query'
		, 'order_by' => 'their_organisation'
		, 'group_by' => 'their_organisation, their_binary'
		, 'anchor_field' => ''
		, 'viewfilter' => true
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'their_binary'
		, 'fieldlabel' => Translations::get('organisation')
		, 'view_max_length' => 30
		, 'viewfilter' => array(
				'filter' => array (
						array (
							'fieldname' => 'their_organisation'
							, 'search_in' => 'their_organisation'
							, 'type' => 'string'
							, 'class' => 'quicksearch'
							)
					)
			)
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'aantal'
		, 'fieldlabel' => Translations::get('amount')
		)));

	// generate view
	$ret = $oView->generate_view();

	//
	return $twig->render('settings.html', array(
			'title' => Translations::get('page_their_organisation_title')
			, 'message' => $ret
	));
}