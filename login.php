<?php 
require_once "classes/start.inc.php";

// create webpage
$oPage = new Page();
$oPage->setTitle(Translations::get('website_name') . ' | ' . Translations::get('login_pagina'));
$oPage->setContent(createLoginPage());

// show page
echo $twig->render('design.html', $oPage->getPageAttributes( array( 'hide_menu' => 1 ) ) );

function createLoginPage() {
	global $protect, $twig;

	$fldLogin = '';
	$error = '';

	//
	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		// get values
		$fldLogin = $protect->request('post', 'fldLogin');
		$fldPassword = $protect->request('post', 'fldPassword');

		// quick protect
		$fldLogin = str_replace(array(';', ':', '!', '<', '>', '(', ')', '%'), ' ', $fldLogin);

		// trim
		$fldLogin = trim($fldLogin);
		$fldPassword = trim($fldPassword);

		// use the left part until the space
		$fldLogin = $protect->get_left_part($fldLogin, ' ');

		// check if both field are entered
		if ( $fldLogin != '' && $fldPassword != '' ) {

			$result_login_check = Authentication::authenticate($fldLogin, $fldPassword);

			if ( $result_login_check == 1 ) {
				// retain login name
				$_SESSION["loginname"] = $fldLogin;
				$_SESSION["name"] = $fldLogin; // TODO

				//
				$burl = getBackUrl();
				if ( $burl == '' ) {
					$burl = 'presentornot.php';
				}
				Header("Location: " . $burl);
				die(Translations::get('go_to') . " <a href=\"" . $burl . "\">next</a>");
			} else {
				// show error
				$error .= "User/Password combination incorrect.";
			}
		} else {
			// show error
			$error .= "Both field are required.<br>";
		}
	}

	return $twig->render('login.html', array(
		'title' => Translations::get('please_log_in')
		, 'your_login_credentials_are' => Translations::get('your_login_credentials_are')
		, 'error' => $error
		, 'loginname' => $fldLogin
		, 'action' => "?" . $_SERVER["QUERY_STRING"]
		, 'btn_login' => Translations::get('btn_login')
		, 'loginname_placeholder' => Translations::get('loginname_placeholder')
		, 'loginname_help' => Translations::get('loginname_help')
		, 'password_placeholder' => Translations::get('password_placeholder')
		, 'lblPassword' => Translations::get('password')
		, 'lblLoginname' => Translations::get('loginname')
		, 'focusjavascriptcode' => "
<script language=\"javascript\">
<!--
document.frmA.fldLogin.focus();
// -->
</script>
"
	));
}
