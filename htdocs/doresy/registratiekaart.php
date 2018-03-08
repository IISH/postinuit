<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

$file = 'Registratiekaart.pdf';

header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=\"$file\"");
header("Content-Type: application/pdf");
header("Content-Transfer-Encoding: binary");
readfile(IniSettings::get('settings', 'wiki_directory') . $file);
