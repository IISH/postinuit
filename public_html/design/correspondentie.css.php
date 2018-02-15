<?php 
Header('Content-type: text/css');
require_once "../classes/misc.inc.php";
require_once "../classes/website_protection.inc.php";

// menu/footer color (only 6 char/digit allowed)
$protect = new WebsiteProtection();
$c = $protect->request('get', 'c', '/^[0-9a-zA-Z]{6,6}$/');
if ( $c == '' ) {
	$c = '#707070';
//	$c = black;
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
	display: block;
	color: red;
	font-size: 110%;
	font-weight: bold;
	margin-bottom: 20px;
}

.warning {
	display: block;
	color: orange;
	font-size: 110%;
	font-weight: bold;
	margin-bottom: 20px;
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
	margin-bottom: 20px;
	font-size: 24px;
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
	clear: both;
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

#postHeaderRow > th {
	border: 1px solid black;
	padding: 5px;
}

.index_button_alignment {
	text-align: center;
}

.index_button_margin {
	margin-top: 30px;
	margin-bottom: 30px;
}

.btn {
	width: 300px;
}

table.login {
	border-collapse: collapse;
}

table.login td {
	padding: 10px 5px 10px 0px;
}

#postOverview tr td {
	border: 1px solid black;
	padding: 5px;
}

#postOverview{
	width: 100%;
}

.disabledField {
	background-color: #dfdfdf;
}

.notYetDisabledField {
	background-color: yellow;
}

#post_in_table
, #post_uit_table {
	border-collapse: collapse;
}

#post_in_table td
, #post_uit_table td {
	padding: 3px 3px 3px 0px;
}

.nobr {
	white-space: nowrap;
}

label {
	font-size: 93%;
	white-space: nowrap;
}

.required
, .required:visited
, .required:active
, .required:hover {
	color: red;
	font-size: 85%;
	vertical-align: super;
}

.semiRequired
, .semiRequired:visited
, .semiRequired:active
, .semiRequired:hover {
	color: orange;
	font-size: 85%;
	vertical-align: super;
}

.help
, .help:visited
, .help:active
, .help:hover {
	color: black;
	font-size: 85%;
	vertical-align: super;
}

.table-striped tr:nth-child(odd) > td,
.table-striped tr:nth-child(odd) > th {
	background-color: #dfdfdf;
}

td {
	color: black;
}

#advanced_search_table td {
	padding-right: 10px;
}

label.in_out
, label.type_of_document {
	padding-right: 20px;
}

ul.in_out {
    padding: 0px 0px 0px 16px;
}

div.border {
    clear: both;
    border: 1px solid black;
    margin-bottom: 10px;
	padding: 3px;
}

div.attachment {
    word-wrap: break-word;
}

input[type=file] {
	border: none;
}

.inputformsubmitbutton {
	margin-top: 20px;
	margin-bottom: 20px;
	text-align: center;
}

.attachment_button_images{
    max-height: 20px;
    max-width: 20px;
    font-size: 20px;
}

#attachment_list_item
, #deleted_list_item {
    list-style-type: none;
    padding: 2px 0px;
}

#attachment_list_item:hover
, #deleted_list_item:hover {
    background-color: #bfbfbf !important;
}

#attachment_list
, #deleted_list {
    padding-left: 0;
}

#attachment_list li:nth-child(odd)
, #deleted_list li:nth-child(odd) {
    background-color: #dfdfdf;
}

.attachment_add_button{
    max-height: 50px;
    max-width: 50px;
    font-size: 50px;
    padding-left: 2px;
}

.border > input{
    display: none;
}

.button_add_document {
    -webkit-appearance: button;
    background-color: buttonface;
    color: #333333;
    border-radius: 2px;
    border-width: 2px;
    border-style: outset;
    border-color: buttonface;
}

.button_add_document:hover {
    cursor: pointer;
}

#postOverview tr.no_files_present td {
    background-color: yellow !important;
}

#postOverview tr:hover td, #postOverview tr:hover td a, #postOverview tr:hover td a:hover  {
	background-color: #707070 !important;
	color: whitesmoke;
	text-decoration: none;
	border-bottom: 1px dotted whitesmoke;
}

.input-group-addon {
	background-color: white;
	border-color: white;
	font-weight: bold;
}

.translation-edit {
	width: 600px;
	height: 120px;
}

.onder_voorbehoud {
	font-size: 85%;
	font-style:italic;
	margin-left: 10px;
}

.btnOverzicht {
	padding: 0;
	border: 0;
	background-color: #dfdfdf;
	border-bottom:1px dashed black;
}