<?php
require_once "classes/start.inc.php";

// Add element to $_POST array to notify the post is post that is coming in.
$_POST['in_out'] = 'out';

// TODO: add check to see everything has been filled out
if ( count($_POST) >= 12 ) {
	if($_POST['submitValue'] === "Bewaar") {
		Posts::uploadPost($_POST, $_FILES);
	}
	else if($_POST['submitValue'] === "Pas aan") {
		Posts::editPost($_POST, $_FILES);
	}
	header("Location: postuit.php");
	exit;
}else{
	echo "Not everything has been filled out!"."<br>";
	echo count($_POST);
}
