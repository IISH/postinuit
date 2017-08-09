<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

global $dbConn;

$result = array();
// TODO: security!!!!
$searchterm =  addslashes(substr(trim($_GET['term']), 0,20));
$table = addslashes(trim($_GET['table']));
$column = addslashes(trim($_GET['column']));
// TODO: controleer of searchterm minimaal 3 characters lang is.
$query = "SELECT ".$column." FROM ".$table." WHERE ".$column." LIKE '%".$searchterm."%' GROUP BY ".$column." LIMIT 0,10";
$stmt = $dbConn->getConnection()->prepare($query);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $result[] = $row[$column];
}

echo json_encode($result);
