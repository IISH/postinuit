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
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('page_wiki_title'));
$oPage->setContent( $content );

// show page
echo $twig->render('design.html', $oPage->getPageAttributes() );

function createContent() {
	global $twig;

	require_once("./classes/class_form/class_form.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_hidden.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_string.inc.php");
	require_once("./classes/class_form/fieldtypes/class_field_textarea.inc.php");

	$oForm = new class_form();

	$oForm->set_form( array(
		'query' => 'SELECT * FROM `wiki` WHERE ID=[FLD:ID] '
		, 'table' => 'wiki'
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
		'fieldname' => 'title'
		, 'fieldlabel' => 'Title'
		, 'required' => 1
		)));

	$oForm->add_field( new class_field_textarea ( array(
		'fieldname' => 'description'
		, 'fieldlabel' => 'Description'
		, 'required' => 1
		, 'class' => 'translation-edit'
		)));

	// generate form
	$ret = $oForm->generate_form();

	//
	return $twig->render('wiki_edit.html', array(
		'title' => Translations::get('page_wiki_title')
		, 'message' => $ret
		));
}
