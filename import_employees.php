<?php
/**
 * User: Igor van der Bom
 * Date: 21-8-2017
 * Time: 10:52
 */

require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

// only for administrators
if ( !$oWebuser->isAdmin() ) {
	die('Access denied. Only for administrators.');
}

// Generice variables
class updateEnum {
    const OKAY = 0;
    const UPDATING = 1;
    const OLD = 2;
};

$filesToImport = array_diff(scandir(Settings::get('employee_import_directory')), array('..', '.'));

echo "Starting importing...<br>";

$newCounter = 0;
$updateCounter = 0;
/**
 * Loop the files to be imported. For each of these files the data will be saved in the database.
 */
foreach($filesToImport as $fileToImport) {
	echo 'File: ' . $fileToImport . "<br>";

	//
	$fldSource = pathinfo($fileToImport, PATHINFO_FILENAME);

	/** Setting all the records to update_status 1 (current dataset), to mark it as being updated
	 * This to see on the end which record is actually updated and which is not.
	 */
	$updateQuery = 'UPDATE employees SET import_status = ' . updateEnum::UPDATING . ' WHERE import_status = ' . updateEnum::OKAY . ' AND source=\'' . $fldSource . '\' ';
	$updateStmt = $dbConn->getConnection()->prepare($updateQuery);
	$updateStmt->execute();

    // Load the file and put each user in the array as a separate value
    $file_to_read = Settings::get('employee_import_directory') . $fileToImport;
    $import_file = file_get_contents($file_to_read);
    $import_file = preg_split("#\n\s*\n#Uis", $import_file);

    // split the values in the array into a separate array, ergo 2D array
    $import_array = array();
    foreach ($import_file as $file) {
        $temp = preg_split("/\r\n|\r|\n/", $file);
        $temp['original'] = $file;
        $import_array[] = $temp;
    }

    // convert the array to a new array and add the default value to that record in the array.
    $import_result_array = array();
    foreach ($import_array as $array) {
        $result_array = array();
        foreach ($array as $itemKey => $item) {
            if ($itemKey !== 'original') {
                $arr = explode(":", $item);
                $key = $arr[0];
                if (!empty($arr[1])) {
                    $value = trim($arr[1]);
                } else {
                    $value = "None";
                }
                $result_array[$key] = $value;
            } else {
                $result_array[$itemKey] = $item;
            }
        }
        $import_result_array[] = $result_array;
    }

    /**
     * Loop the loaded data from the files to insert it into the database (if not already exists)
     */
    foreach ($import_result_array as $data) {

        // Check if the data from the files is valid and usable for the database.
        if (isset($data['dn']) && isset($data['cn'])) {
            if ($data['dn'] !== 'None') {
                // Check whether the data to be inserted already exists in the database.
                // This by searching the database to a record that corresponds to the given value.
                $query = 'SELECT * FROM employees WHERE dn = "' . $data['dn'] . '" ';
                $checkStmt = $dbConn->getConnection()->prepare($query);
                $checkStmt->execute();
                $result = $checkStmt->fetchAll();
                // Check whether the query returns something
                if (count($result) === 0) {
                    // NEW EMPLOYEE
                    $newCounter++;

                    // Preparation of the query to run on the database.
                    $stmt = $dbConn->getConnection()->prepare(
                        "INSERT INTO employees
                      (dn, cn, sn, c, l, physicalDeliveryOfficeName, telephoneNumber, givenName, company,
                      department, sAMAccountName, mail, original, source, clean_loginname, clean_name,
                      clean_institute, clean_department, import_status)
                      VALUES (
                      :dn, :cn, :sn, :c, :l, :physicalDeliveryOfficeName, :telephoneNumber, :givenName,
                      :company, :department, :sAMAccountName, :mail, :original, :source, :clean_loginname,
                      :clean_name, :clean_institute, :clean_department, :import_status
                      ) ");

                    // Needed because not possible to pass parameter by reference
                    $okayValue = updateEnum::OKAY;

                    $stmt->bindParam(':dn', $data['dn'], PDO::PARAM_STR);
                    $stmt->bindParam(':cn', $data['cn'], PDO::PARAM_STR);
                    $stmt->bindParam(':sn', $data['sn'], PDO::PARAM_STR);
                    $stmt->bindParam(':c', $data['c'], PDO::PARAM_STR);
                    $stmt->bindParam(':l', $data['l'], PDO::PARAM_STR);
                    $stmt->bindParam(':physicalDeliveryOfficeName', $data['physicalDeliveryOfficeName'], PDO::PARAM_STR);
                    $stmt->bindParam(':telephoneNumber', $data['telephoneNumber'], PDO::PARAM_STR);
                    $stmt->bindParam(':givenName', $data['givenName'], PDO::PARAM_STR);
                    $stmt->bindParam(':company', $data['company'], PDO::PARAM_STR);
                    $stmt->bindParam(':department', $data['department'], PDO::PARAM_STR);
                    $stmt->bindParam(':sAMAccountName', $data['sAMAccountName'], PDO::PARAM_STR);
                    $stmt->bindParam(':mail', $data['mail'], PDO::PARAM_STR);
                    $stmt->bindParam(':original', $data['original'], PDO::PARAM_LOB);
                    $stmt->bindParam(':source', $fldSource, PDO::PARAM_STR);
                    $stmt->bindParam(':clean_loginname', $data['sAMAccountName'], PDO::PARAM_STR);
                    $stmt->bindParam(':clean_name', $data['name'], PDO::PARAM_STR);
                    $stmt->bindParam(':clean_institute', $data['company'], PDO::PARAM_STR);
                    $stmt->bindParam(':clean_department', $data['department'], PDO::PARAM_STR);
                    $stmt->bindParam(':import_status', $okayValue, PDO::PARAM_INT);

					$stmt->execute();
                } else {
                    // EXISTING EMPLOYEE
                    $updateCounter++;

                    // Preparation of the query to run on the database.
                    $stmt = $dbConn->getConnection()->prepare(
                        "UPDATE employees SET
                      dn = :dn, cn = :cn, sn = :sn, c = :c, l = :l, physicalDeliveryOfficeName = :physicalDeliveryOfficeName, telephoneNumber = :telephoneNumber, givenName = :givenName, company = :company,
                      department = :department, sAMAccountName = :sAMAccountName, mail = :mail, original = :original, source = :source, clean_loginname = :clean_loginname, clean_name = :clean_name,
                      clean_institute = :clean_institute, clean_department = :clean_department, import_status = :import_status
                      WHERE dn = :dn");

                    // Needed because not possible to pass parameter by reference
                    $okayValue = updateEnum::OKAY;

                    $stmt->bindParam(':dn', $data['dn'], PDO::PARAM_STR);
                    $stmt->bindParam(':cn', $data['cn'], PDO::PARAM_STR);
                    $stmt->bindParam(':sn', $data['sn'], PDO::PARAM_STR);
                    $stmt->bindParam(':c', $data['c'], PDO::PARAM_STR);
                    $stmt->bindParam(':l', $data['l'], PDO::PARAM_STR);
                    $stmt->bindParam(':physicalDeliveryOfficeName', $data['physicalDeliveryOfficeName'], PDO::PARAM_STR);
                    $stmt->bindParam(':telephoneNumber', $data['telephoneNumber'], PDO::PARAM_STR);
                    $stmt->bindParam(':givenName', $data['givenName'], PDO::PARAM_STR);
                    $stmt->bindParam(':company', $data['company'], PDO::PARAM_STR);
                    $stmt->bindParam(':department', $data['department'], PDO::PARAM_STR);
                    $stmt->bindParam(':sAMAccountName', $data['sAMAccountName'], PDO::PARAM_STR);
                    $stmt->bindParam(':mail', $data['mail'], PDO::PARAM_STR);
                    $stmt->bindParam(':original', $data['original'], PDO::PARAM_LOB);
                    $stmt->bindParam(':source', $fldSource, PDO::PARAM_STR);
                    $stmt->bindParam(':clean_loginname', $data['sAMAccountName'], PDO::PARAM_STR);
                    $stmt->bindParam(':clean_name', $data['name'], PDO::PARAM_STR);
                    $stmt->bindParam(':clean_institute', $data['company'], PDO::PARAM_STR);
                    $stmt->bindParam(':clean_department', $data['department'], PDO::PARAM_STR);
                    $stmt->bindParam(':import_status', $okayValue, PDO::PARAM_INT);

                    $stmt->execute();
                }
            }
        }
    }

	// Set the value of import_status to 2 (for current dataset/source) if the import status hasn't been updated due to the record not being updated
	$updateQuery = 'UPDATE employees SET import_status = '. updateEnum::OLD . ' WHERE import_status = ' . updateEnum::UPDATING . ' AND source=\'' . $fldSource . '\' ';
	$updateStmt = $dbConn->getConnection()->prepare($updateQuery);
	$updateStmt->execute();
}

echo "New employees: " . $newCounter . "<br>";
echo "Updated employees: " . $updateCounter . "<br>";
echo "Script completed!";
