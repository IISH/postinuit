<?php
require_once "classes/start.inc.php";

preprint($_POST['deleteFileFromServer']);
preprint($_POST);

if($_POST['deleteFileFromServer'] !== ""){
    Posts::removeFileFromPost($_POST['deleteFileFromServer'], $_POST['kenmerk']);
    header("Location: {$_SERVER['HTTP_REFERER']}");
    exit;
}