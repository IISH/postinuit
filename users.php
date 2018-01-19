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
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('page_users_title'));
$oPage->setContent( $content );

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createContent() {
	global $twig;

	require_once("./classes/class_view/class_view.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_view/fieldtypes/class_field_bit.inc.php");

	$oView = new class_view();

//AND authentication_server='knaw'
	$oView->set_view( array(
		'query' => "SELECT * FROM `users` WHERE is_deleted=0 "
		, 'count_source_type' => 'query'
		, 'order_by' => 'loginname'
		, 'anchor_field' => 'ID'
		, 'viewfilter' => true
		, 'add_new_button' => array(
				'url' => 'users-edit.php?ID=0&backurl=[BACKURL]'
				, 'label' => Translations::get('btn_add_new')
				)
		));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'loginname'
		, 'fieldlabel' => Translations::get('knaw_loginname')
		, 'view_max_length' => 30
		, 'href' => 'users-edit.php?ID=[FLD:ID]&backurl=[BACKURL]'
		, 'viewfilter' => array(
				'filter' => array (
						array (
							'fieldname' => 'loginname'
							, 'search_in' => 'loginname'
							, 'type' => 'string'
							, 'class' => 'quicksearch'
							)
					)
			)
		)));

	$oView->add_field( new class_field_bit ( array(
		'fieldname' => 'is_disabled'
		, 'fieldlabel' => '<a alt="Disabled" title="Disabled">Disabled</a>'
		, 'show_different_values' => 1
		, 'different_true_value' => '<a alt="Disabled" title="Disabled">Disabled</a>'
		, 'different_false_value' => ''
		)));

	$oView->add_field( new class_field_bit ( array(
		'fieldname' => 'is_data'
		, 'fieldlabel' => '<a alt="Data Invoerder" title="Data Invoerder">Invoerder</a>'
		, 'show_different_values' => 1
		, 'different_true_value' => '<a alt="Data Invoerder" title="Data Invoerder">Invoerder</a>'
		, 'different_false_value' => ''
		)));

	$oView->add_field( new class_field_bit ( array(
		'fieldname' => 'is_beheerder'
		, 'fieldlabel' => '<a alt="Data Beheerder" title="Data Beheerder">Beheerder</a>'
		, 'show_different_values' => 1
		, 'different_true_value' => '<a alt="Data Beheerder" title="Data Beheerder">Beheerder</a>'
		, 'different_false_value' => ''
		)));

	$oView->add_field( new class_field_bit ( array(
		'fieldname' => 'is_admin'
		, 'fieldlabel' => '<a alt="Admin" title="Admin">Admin</a>'
		, 'show_different_values' => 1
		, 'different_true_value' => '<a alt="Admin" title="Admin">Admin</a>'
		, 'different_false_value' => ''
		)));

	$oView->add_field( new class_field_string ( array(
		'fieldname' => 'name'
		, 'fieldlabel' => Translations::get('name')
		, 'view_max_length' => 50
		, 'viewfilter' => array(
				'filter' => array (
						array (
							'fieldname' => 'name'
							, 'search_in' => 'name'
							, 'type' => 'string'
							, 'class' => 'quicksearch'
							)
					)
			)
		)));

	// generate view
	$ret = $oView->generate_view();

	//
	return $twig->render('gebruikers.html', array(
			'title' => Translations::get('page_users_title')
			, 'message' => $ret
	));
}