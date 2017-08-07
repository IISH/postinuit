<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

//
require_once dirname(__FILE__) . "/../vendor/autoload.php";

//
$settings = array();
require_once dirname(__FILE__) . "/../sites/default/correspondentie.settings.php";

//
if ( !isset($_SESSION["loginname"]) ) {
	$_SESSION["loginname"] = '';
}

//
require_once dirname(__FILE__) . "/_misc_functions.inc.php";
require_once dirname(__FILE__) . "/authentication.inc.php";
require_once dirname(__FILE__) . "/date.inc.php";
require_once dirname(__FILE__) . "/datetime.inc.php";
require_once dirname(__FILE__) . "/menu.inc.php";
require_once dirname(__FILE__) . "/page.inc.php";
require_once dirname(__FILE__) . "/pdo.inc.php";
require_once dirname(__FILE__) . "/user.inc.php";
require_once dirname(__FILE__) . "/settings.inc.php";
require_once dirname(__FILE__) . "/tcdatetime.inc.php";
require_once dirname(__FILE__) . "/translations.inc.php";
require_once dirname(__FILE__) . "/website_protection.inc.php";
require_once dirname(__FILE__) . "/Mobile_Detect.php";
require_once dirname(__FILE__) . "/documentTypes.inc.php";
require_once dirname(__FILE__) . "/posts.inc.php";

//
$protect = new WebsiteProtection();

// connect to database
$dbConn = new class_pdo( $databases['default'] );

//
if ( !defined('ENT_XHTML') ) {
	define('ENT_XHTML', 32);
}

//
$oWebuser = staticUser::getUserByLoginName( $_SESSION["loginname"] );

$detect = new Mobile_Detect;
$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

//
$menu = array();
$menu[] = new MenuItem(Translations::get('menu_overzicht'), 'overzicht.php');
$menu[] = new MenuItem(Translations::get('menu_postin'), 'postin.php');
$menu[] = new MenuItem(Translations::get('menu_postuit'), 'postuit.php');
$menu[] = new MenuItem(Translations::get('menu_zoeken'), 'zoeken.php');
if ( $oWebuser->isAdmin() ) {
	$menu[] = new MenuItem(Translations::get('menu_admin'), 'admin.php');
}

// load twig
try{
    $loader = new Twig_Loader_Filesystem('templates');
}catch(Exception $e){
    $loader = new Twig_Loader_Filesystem('/templates');
}
//$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment( $loader);

//
if ( !isset($settings) ) {
	$settings = array();
}
