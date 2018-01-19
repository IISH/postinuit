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
require_once dirname(__FILE__) . "/adserver.inc.php";
require_once dirname(__FILE__) . "/date.inc.php";
require_once dirname(__FILE__) . "/menu.inc.php";
require_once dirname(__FILE__) . "/misc.inc.php";
require_once dirname(__FILE__) . "/page.inc.php";
require_once dirname(__FILE__) . "/pdo.inc.php";
require_once dirname(__FILE__) . "/user.inc.php";
require_once dirname(__FILE__) . "/users.inc.php";
require_once dirname(__FILE__) . "/settings.inc.php";
require_once dirname(__FILE__) . "/tcdatetime.inc.php";
require_once dirname(__FILE__) . "/translations.inc.php";
require_once dirname(__FILE__) . "/website_protection.inc.php";
require_once dirname(__FILE__) . "/documentTypes.inc.php";
require_once dirname(__FILE__) . "/post.inc.php";
require_once dirname(__FILE__) . "/posts.inc.php";
require_once dirname(__FILE__) . "/postinout.inc.php";
require_once dirname(__FILE__) . "/mail.inc.php";
require_once dirname(__FILE__) . "/logins.inc.php";
require_once dirname(__FILE__) . "/wiki.inc.php";
require_once dirname(__FILE__) . "/wikis.inc.php";

//
$protect = new WebsiteProtection();

// connect to databases
$dbConn = new class_pdo( $databases['default'] );

//
if ( !defined('ENT_XHTML') ) {
	define('ENT_XHTML', 32);
}

//
$oWebuser = staticUser::getUserByLoginName( $_SESSION["loginname"] );

//
$menu = array();
$menu[] = new MenuItem(Translations::get('menu_zoeken'), 'zoeken.php');
$menu[] = new MenuItem(Translations::get('menu_postin'), 'postin.php');
$menu[] = new MenuItem(Translations::get('menu_postout'), 'postuit.php');
$menu[] = new MenuItem(Translations::get('menu_wiki'), 'wiki.php');
if ( $oWebuser->isAdmin() ) {
	$menu[] = new MenuItem(Translations::get('menu_configuration'), 'configuration.php');
}

// load twig
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment( $loader);

//
if ( !isset($settings) ) {
	$settings = array();
}

$oMisc = new Misc();
