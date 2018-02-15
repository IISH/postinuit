<?php
if ( !isset($what) ) {
	$what = 'uploaded';
}

require_once "classes/start.inc.php";

// check if an user is logged in
$oWebuser->checkLoggedIn();

// only for data entry
if ( !$oWebuser->isData() ) {
	die('Access denied. Only for data entry or higher.');
}

$kenmerk = $protect->requestPositiveNumberOrEmpty('get', 'kenmerk');
if ( $kenmerk == '' ) {
	die('Error 9856247');
}

$selectedPost = Posts::findPostByKenmerk( $kenmerk );
$hasRightsToEdit = ($oWebuser->getId() === $selectedPost->getRegisteredBy() || $oWebuser->isBeheerder() ) ? true : false;

if ( $what == 'uploaded' ) {
	File::ensureDirectoryExists( IniSettings::get('settings', 'attachment_directory') . $kenmerk, 'documents' );
	$directoryToSearch = IniSettings::get('settings', 'attachment_directory') . $kenmerk;
} elseif ( $what == 'deleted' ) {
	File::ensureDirectoryExists( IniSettings::get('settings', 'deleted_attachment_directory') . $kenmerk, 'deleted documents' );
	$directoryToSearch = IniSettings::get('settings', 'deleted_attachment_directory') . $kenmerk;
} else {
	die('Error 9514236');
}

$files = Misc::getListOfFiles( $directoryToSearch );

$renderArray = array();
$renderArray['files'] = $files;
$renderArray['geen'] = Translations::get('none');
$renderArray['has_rights_to_edit'] = $hasRightsToEdit;

// show page
echo $twig->render('uploadfiles_' . $what . '.html', $renderArray );
