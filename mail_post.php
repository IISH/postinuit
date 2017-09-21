<?php
/**
 * Created by IntelliJ IDEA.
 * User: Igor van der Bom
 * Date: 15-9-2017
 * Time: 10:50
 */

require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

$post_to_mail = Posts::findPostById($_GET['ID']);
$post_to_mail['user_sending'] = $oWebuser->getName();

Mail::updateMailSent($post_to_mail, $post_to_mail['kenmerk']);

if($_SERVER['HTTP_REFERER']) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
}else{
    header('Location: ' . 'zoeken.php');
}
