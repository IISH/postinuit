<?php
/**
 * User: Igor van der Bom
 * Date: 15-9-2017
 * Time: 10:50
 */

require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

//
$post_to_mail = Posts::findPostById($_GET['ID']);

// try to mail
if ( Mail::sendEmailPost($post_to_mail, $post_to_mail['kenmerk']) ) {
	// Saves the data of the mail to the database with information to be set as sent
	Mail::insertIntoMailLog($post_to_mail);
	Mail::updateMailSent($post_to_mail['kenmerk']);
}

if ( $_SERVER['HTTP_REFERER'] ) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: zoeken.php');
}
