<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

// check if user is allowed to edit, if not, then also not allowed to delete the file
$selectedPost = Posts::findPostByKenmerk( $_POST['kenmerk'] );
$hasRightsToEdit = ($oWebuser->getId() === $selectedPost->getRegisteredBy() || $oWebuser->isBeheerder() ) ? true : false;
if ( !$hasRightsToEdit ) {
	return false;
}

// remove file from post
$response = Posts::removeFileFromPost($_POST['file'], $_POST['kenmerk']);

if ( $response ) {
	Posts::decreaseNumberOfFiles( $_POST['kenmerk'] );
}

// Send JSON Data to AJAX Request
echo json_encode($response);
