<?php 
require_once "classes/start.inc.php";

session_unset();
session_destroy();

Header("Location: login.php");
die();
