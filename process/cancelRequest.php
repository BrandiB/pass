<?php

/*
	This cancels a previously-made request.
*/

// FROM HERE (until below where I say UNTIL HERE) is the intranet user authentication stuff which would need changing 

$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();
$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

// UNTIL HERE - note that this includes the database connection, so that would have to be redone as well

$whichRequest = $catn->getVariable("whichRequest");


// Delete request from database
$query = "delete from pass.requestmatrix where requestID=".$whichRequest;
mysql_query($query);

$catn->dbdisconnect();
?>

<script language='JavaScript'>
	alert('Request cancelled.');
	hideStudentRequest();
</script>
