<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

if ( $_POST['openFileFromServer'] !== '' ) {
	//
    $file_to_download = Settings::get('attachment_directory') . $_POST['kenmerk'] . "/" . $_POST['openFileFromServer'];
    Posts::getFileFromPost($file_to_download);
}