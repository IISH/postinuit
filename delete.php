<?php
require_once "classes/start.inc.php";

if ( !isset($direction) ) {
	$direction = 'delete';
}

// check if an user is logged in
$oWebuser->checkLoggedIn();

$kenmerk = $protect->request('post', 'kenmerk');
$file = $protect->request('post', 'file');

// TODOGCU TODO TIJDELIJK VERWIJDEREN
if ( $kenmerk == '' ) {
	$kenmerk = $protect->request('get', 'kenmerk');
}
if ( $file == '' ) {
	$file = $protect->request('get', 'file');
}

// check if user is allowed to edit, if not, then also not allowed to delete the file
$selectedPost = Posts::findPostByKenmerk( $kenmerk );
$hasRightsToEdit = ($oWebuser->getId() === $selectedPost->getRegisteredBy() || $oWebuser->isBeheerder() ) ? true : false;
if ( !$hasRightsToEdit ) {
	return false;
}

//
if ( $direction == 'delete' ) {
	// delete file (move to deleted directory)
	$response = Posts::moveFile($file, $kenmerk, Settings::get('attachment_directory'), Settings::get('deleted_attachment_directory'));
} elseif ( $direction == 'undelete' ) {
	// UNdelete file (move from deleted directory to attachment direcotry)
	$response = Posts::moveFile($file, $kenmerk, Settings::get('deleted_attachment_directory'), Settings::get('attachment_directory'));
}

if ( $response ) {
//	Posts::decreaseNumberOfFiles( $kenmerk );
	// save new number of files
	$numberOfFiles = Posts::getNumberOfFilesFromPost( $selectedPost->getKenmerk() );
	Posts::saveNumberOfFiles($selectedPost->getId(), $numberOfFiles);
}

// Send JSON Data to AJAX Request
echo json_encode($response);
