<?php
/**
 * User: Igor van der Bom
 * Date: 18-8-2017
 * Time: 14:19
 */

require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

$result = Posts::getEmployeeInformation($_GET);

echo json_encode($result);