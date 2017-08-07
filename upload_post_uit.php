<?php
require_once "classes/start.inc.php";

print_r($_POST);
echo "<br>";

// Add element to $_POST array to notify the post is post that is coming in.
$_POST['postInOrOut'] = 'out';

// TODO: add check to see everything has been filled out
if(count($_POST) === 12){
    echo Posts::uploadPost($_POST);
    echo "<br>";
    header("Location: postuit.php");
    exit;
}else{
    echo "Not everything has been filled out!"."<br>";
    echo count($_POST);
}