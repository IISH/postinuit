<?php
require_once "classes/start.inc.php";

//
$_SESSION['language'] = 'en';
$oWebuser->saveUserSetting('language', 'en');

//
goBack();
