<?php
die('disabled by gcu');

require_once "classes/start.inc.php";

$a = new User(1);
$a->setPassword('123');
$a->saveHash();

echo 'einde';