<?php

$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();
$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$whichRequest = $catn->getVariable("whichRequest");

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

// Delete request from database
$query = "delete from pass.requestmatrix where requestID=".$whichRequest;
mysql_query($query);

$catn->dbdisconnect();
?>

<script language='JavaScript'>
	alert('Request cancelled.');
	hideStudentRequest();
</script>
