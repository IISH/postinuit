<?php
//require_once "classes/start.inc.php";
//
//// TODO: add check to see everything has been filled out
//$_POST['in_out'] = 'in';
//
//$isValid = true;
//if($_POST['date'] === ""){
//    $isValid = false;
//}else if($_POST['their_name'] === "" && $_POST['their_organisation'] === ""){
//    $isValid = false;
//}else if($_POST['our_name'] === ""){
//    $isValid = false;
//}else if($_POST['type_of_document'] === ""){
//    $isValid = false;
//}else if($_POST['subject'] === ""){
//    $isValid = false;
//}
//
//if($isValid){
//    if ( $_POST['submitValue'] === "Bewaar" ) {
//        Posts::uploadPost($_POST, $_FILES);
//    } elseif ( $_POST['submitValue'] === "Pas aan" ) {
//        Posts::editPost( $_POST, $_FILES);
//    }
//
//    $next = 'postin.php';
//    //die('1111 go to <a href="' . $next . '">' . $next . '</a>');
//    Header("Location: " . $next);
//}else{
//    preprint("oeps, toch iets fout gedaan!");
//}
