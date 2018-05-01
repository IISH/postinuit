<?php
die('disabled 5424652456');

$queries = array();
// example queries
$queries[] = "update post SET their_organisation = 'BB HuC' WHERE their_organisation = 'bb HuC'; ";
$queries[] = "update post SET their_organisation = 'BB HuC' WHERE their_organisation = 'bb'; ";
$queries[] = "update post SET their_organisation = 'UvA' WHERE their_organisation = 'uva'; ";
$queries[] = "update post SET their_organisation = 'KPN' WHERE their_organisation = 'kpn'; ";
$queries[] = "update post SET their_organisation = 'Huygens ING' WHERE their_organisation = 'HuygensING'; ";
$queries[] = "update post SET their_organisation = 'Meertens' WHERE their_organisation = 'Meertens Instituut'; ";
$queries[] = "update post SET our_department = '' WHERE our_department LIKE 'n.v.t%'; ";

// TODO TODOGCU
// run queries