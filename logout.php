<?php 
require_once "classes/start.inc.php";

//session_unset();
//session_destroy();
$_SESSION['loginname'] = '';

Header("Location: login.php");
