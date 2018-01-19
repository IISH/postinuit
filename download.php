<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

//
$id = $protect->requestPositiveNumberOrEmpty('get', 'ID');
$file = $protect->request('get', 'file');
$file = str_replace(array('<', '>', '\\', '/', '*', '?', '"', ':'), '', $file);

if ( $id == '' || $file == '' ) {
	die('No ID or file in URL.');
}

// get post
$post = Posts::findPostById($id);

// check if logged in user is receiver (post in) or sender (post out)
if ( $post['our_loginname'] == $oWebuser->getLoginname() || $post['registered_by'] == $oWebuser->getId() || $oWebuser->isBeheerder() ) {
	// download file
	$file_to_download = Settings::get('attachment_directory') . $post['kenmerk'] . "/" . $file;
	Posts::getFileFromPost($file_to_download);
}
