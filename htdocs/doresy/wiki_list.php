<?php
require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

$search = $protect->request('get', 's');
$search = str_replace(array('<', '>', '?', '!', '%', '#', '+', "\r", "\n", "\t"), ' ', $search);
$search = trim($search);

$records = array();

//
$arrOfWikis = Wikis::search($search);
foreach ( $arrOfWikis as $wiki ) {
	$records[] = array(
		'ID' => $wiki->getId()
		, 'groupname' => $wiki->getGroupname()
		, 'title' => $wiki->getTitle()
		, 'description' => $wiki->getDescription()
		);
}

//
echo $twig->render('wiki_list.html', array(
		'title' => Translations::get('page_wiki_title')
		, 'records' => $records
		, 'is_admin' => $oWebuser->isAdmin()
		, 'backurl' => 'wiki.php'
));
