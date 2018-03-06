<?php
require_once "classes/start.inc.php";

//
$_SESSION['language'] = 'nl';
$oWebuser->saveUserSetting('language', 'nl');

//
goBack();
