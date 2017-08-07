<?php
require_once "classes/start.inc.php";

//
$_SESSION['language'] = 'nl';
$oWebuser->saveSetting('language', 'nl');

//
goBack();
