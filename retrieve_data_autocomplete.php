<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

//
$minLengthSearchTerm = 2;
$result = array();

//
$searchterm =  substr(trim($_GET['term']), 0,20);
$type = trim($_GET['type']);
switch ( $type ) {
	case "our_name":
		$table = 'vw_filter_our_name';
		$column = 'our_name';
		break;
	case "their_name":
		$table = 'vw_filter_their_name';
		$column = 'their_name';
		break;
	case "their_organisation":
		$table = 'vw_filter_their_organisation';
		$column = 'their_organisation';
		break;
	default:
		echo json_encode($result);
		die();
}

// conroleer of zoek term voldoet aan minimale lengte
if ( strlen($searchterm) >= $minLengthSearchTerm ) {
	// use different database for employee (our) names
	switch ( $type ) {
		case "our_name":
			$dbConn = new class_pdo( $databases['sync_knaw_ad'] );
			break;
	}

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
