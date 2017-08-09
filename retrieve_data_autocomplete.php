<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

//
$minLengthSearchTerm = 2;
$result = array();

// TODO: security!!!!
// table en column moeten niet zichtbaar zijn/opgegeven worden in url, maar een cijfer (of code)
$searchterm =  substr(trim($_GET['term']), 0,20);
$table = trim($_GET['table']);
$column = trim($_GET['column']);

// conroleer of zoek term voldoet aan minimale lengte
if ( strlen($searchterm) >= $minLengthSearchTerm ) {
	//
	$query = "SELECT " . addslashes($column) . " FROM " . addslashes($table) . " WHERE " . addslashes($column) . " LIKE '%" . addslashes($searchterm) . "%' GROUP BY " . addslashes($column) . " LIMIT 0,15 ";
	$stmt = $dbConn->getConnection()->prepare($query);
	$stmt->execute();

	//
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$result[] = $row[$column];
	}
}

//
echo json_encode($result);
