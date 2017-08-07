<?php 
header('Content-type: text/css');
require_once "../classes/misc.inc.php";
require_once "../classes/website_protection.inc.php";

// menu/footer color (only 6 char/digit allowed)
$protect = new WebsiteProtection();
$c = $protect->request('get', 'c', '/^[0-9a-zA-Z]{6,6}$/');
if ( $c == '' ) {
	$c = '#707070';
} else {
	$c = '#' . $c;
}
?>
html, body, input, select {
	font-family: Verdana;
	font-size: 95%;
}

.bold {
	font-weight: bold;
}

.login, .password {
	width: 175px;
}

.error {
	color: red;
}

a
, a:visited
, a:active
, a:hover {
	color: <?php echo $c; ?>;
	text-decoration: none;
	border-bottom: 1px dotted <?php echo $c; ?>;
}

a.nolink
, a.nolink:visited
, a.nolink:active
, a.nolink:hover {
	text-decoration: none;
	border-bottom: 0px;
}

a.add
, a.add:visited
, a.add:active
, a.add:hover {
	font-size: 90%;
	font-style:italic;
}

input, select {
	border-width: thin;
	border-style: solid;
	border-color: <?php echo $c; ?>;
}

.button, .button_login {
	color: <?php echo $c; ?>;
	background-color: white;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
	padding: 3px;
	width: 75px;
	border: thin solid <?php echo $c; ?>;
}

h1 {
	margin-top: 0px;
	margin-bottom: 0px;
	font-size: 15px;
}

hr {
	color: <?php echo $c; ?>;
	border: thin solid;
}

.contenttitle {
	font-size: 18px;
	font-weight: bold;
}

div {
	border: 0px solid;
}

div.main {
	max-width: 1200px;
	margin-left: auto;
	margin-right: auto;
}

div.header {
	margin-top: auto;
	margin-bottom: auto;
}

div.title {
	position: absolute;
	margin-left: 117px;
	top: 7px;
}

span.name {
	font-size: 18px;
	font-weight: bold;
	color: <?php echo $c; ?>;
}

span.logout {
	font-size: 14px;
	font-style: italic;
	color: <?php echo $c; ?>;
	text-align: right;
}

div.content {
	border: thin solid #AAAAAA;
	margin-bottom: 5px;
	padding-top: 5px;
	padding-bottom: 15px;
	padding-left: 5px;
	padding-right: 5px;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
}

div.footer {
	background-color: #707070;
	border: thin solid #AAAAAA;
	padding: 5px;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
}

div.footer a
, div.footer a:visited
, div.footer a:active
, div.footer a:hover {
	color: white;
}

div.footer a {
	text-decoration: none;
}

div.footer a:visited
, div.footer a:active
, div.footer a:hover {
	text-decoration: underline;
	text-decoration-style: dotted;
}

span.title {
	display: block;
	font-size: 32px;
	font-weight: bold;
	color: <?php echo $c; ?>;
}

span.subtitle {
	display: block;
	font-size: 16px;
	font-weight: bold;
	color: <?php echo $c; ?>;
}

.comment {
	line-height: 95%;
	font-size: 85%;
	font-style:italic;
}

.clearBoth {
	clear: both;
}

/* START MENU */

div.menu {
	background-color: #707070;
	border: thin solid #AAAAAA;
	margin-bottom: 5px;
	padding: 5px;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
}

.menu ul {
	margin-top: 0px;
	margin-bottom: 1px;
	margin-left: 0px;
	margin-right: 0px;
	list-style-type: none;
	padding-left: 0px;
}

.menu li {
	display: inline;
	margin-right: 20px;
	padding: 3px 6px;
	border-bottom: 0px;
	font-size: 120%;
}

.menu li a
, .menu ul li a:visited
, .menu ul li a:active
, .menu ul li a:hover {
	color: white;
	border-bottom: 0px;
	font-weight: bold;
}

.menu li a {
	text-decoration: none;
}

.menu ul li a:visited
, .menu ul li a:active
, .menu ul li a:hover {
	text-decoration: underline;
	text-decoration-style: dotted;
}

/* END MENU */

::-webkit-input-placeholder { /* Chrome/Opera/Safari */
	color: darkgrey;
}
::-moz-placeholder { /* Firefox 19+ */
	color: darkgrey;
}
:-ms-input-placeholder { /* IE 10+ */
    color: darkgrey;
}
:-moz-placeholder { /* Firefox 18- */
    color: darkgrey;
}

@keyframes blinker {
    50% { opacity: 0; }
}

#post_in_table{
    width:80%;
}

#post_uit_table{
    width:80%;
}

#postHeaderRow > th{
    border: 1px solid black;
    padding: 5px;
}

.index_button_alignment {
	text-align: center;
}

.index_button_margin {
	margin-bottom: 30px;
}

.btn {
	width: 300px;
}
