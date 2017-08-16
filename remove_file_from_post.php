<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

//
if ( $_POST['deleteFileFromServer'] !== "" ) {
	Posts::removeFileFromPost($_POST['deleteFileFromServer'], $_POST['kenmerk']);

    if(!empty($_SERVER['HTTP_REFERER'])) {
        Header("Location: {$_SERVER['HTTP_REFERER']}");
        exit;
    }else{
        Header("Location: index.php");
        exit;
    }
}