<?php
require_once "classes/start.inc.php";

// TODO: add check to see everything has been filled out
$_POST['in_out'] = 'in';

if ( $_POST['submitValue'] === "Bewaar" ) {
	Posts::uploadPost( $_POST, $_FILES);
} elseif ( $_POST['submitValue'] === "Pas aan") {
	Posts::editPost( $_POST, $_FILES);
}

$next = 'postin.php';
//die('1111 go to <a href="' . $next . '">' . $next . '</a>');
Header("Location: " . $next);
