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

	require_once("./classes/class_form/class_form.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_readonly.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_bit.inc.php");

	$oForm = new class_form();

	$oForm->set_form( array(
		'query' => 'SELECT * FROM `users` WHERE ID=[FLD:ID] '
		, 'table' => 'users'
		, 'primarykey' => 'ID'
		, 'disallow_delete' => true
		));

	// required !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'ID'
		, 'fieldlabel' => '#'
		)));

	// required !!!
	$oForm->add_field( new class_field_hidden ( array(
		'fieldname' => 'is_deleted'
		, 'fieldlabel' => 'is deleted?'
		, 'onNew' => '0'
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'loginname'
		, 'fieldlabel' => Translations::get('knaw_loginname')
		, 'required' => 1
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'name'
		, 'fieldlabel' => Translations::get('name')
		, 'required' => 0
		)));

	$oForm->add_field(new class_field_readonly (array(
		'fieldname' => 'authentication_server'
		, 'fieldlabel' => Translations::get('authentication')
		, 'required' => 0
		, 'onNew' => 'knaw'
		)));

	$oForm->add_field(new class_field_bit (array(
		'fieldname' => 'is_beheerder'
		, 'fieldlabel' => 'Is data beheerder?'
		, 'required' => 0
		, 'class' => ''
		)));

	$oForm->add_field(new class_field_bit (array(
		'fieldname' => 'is_admin'
		, 'fieldlabel' => 'Is administrator?'
		, 'required' => 0
		, 'class' => ''
		)));

	$oForm->add_field(new class_field_bit (array(
		'fieldname' => 'is_disabled'
		, 'fieldlabel' => 'Is disabled?'
		, 'required' => 0
		, 'class' => ''
		)));

	// generate form
	$ret = $oForm->generate_form();

	//
	return $twig->render('gebruikers.html', array(
		'title' => Translations::get('page_users_title')
		, 'message' => $ret
		));
}
