<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

$response = Posts::removeFileFromPost($_POST['file'], $_POST['kenmerk']);

// Send JSON Data to AJAX Request
echo json_encode($response);