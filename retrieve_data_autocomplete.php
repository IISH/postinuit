<?php
require_once "classes/start.inc.php";

global $dbConn;

$searchterm = $_GET['term'];
$table = $_GET['table'];
$column = $_GET['column'];

$query = "SELECT * FROM ".$table." WHERE ".$column." LIKE '".$searchterm."%'";
$stmt = $dbConn->getConnection()->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll();

echo json_encode($result[0]);
