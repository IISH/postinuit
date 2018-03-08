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
	global $protect, $oWebuser, $twig;

	$id = $protect->requestPositiveNumberOrEmpty('get', "ID");
	if ( $id == '' ) {
		$id = '0';
	}

	require_once("./classes/class_form/class_form.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_bit.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_integer.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_textarea.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_readonly.inc.php");

	$oForm = new class_form();

	$oForm->set_form( array(
		'query' => 'SELECT * FROM `type_of_document` WHERE ID=[FLD:ID] '
		, 'table' => 'type_of_document'
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
		'fieldname' => 'type_nl'
		, 'fieldlabel' => Translations::get('dutch')
		, 'required' => 0
		, 'class' => ''
		)));

	$oForm->add_field( new class_field_string ( array(
		'fieldname' => 'type_en'
		, 'fieldlabel' => Translations::get('english')
		, 'required' => 0
		, 'class' => ''
		)));

	$oForm->add_field( new class_field_integer ( array(
		'fieldname' => 'sort_order'
		, 'fieldlabel' => Translations::get('sort_order')
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
	return $twig->render('documenttypes.html', array(
		'title' => Translations::get('page_documenttypes_title')
		, 'message' => $ret
		));
}
