<?php

/*
	"Deletes" a student by marking them as disabled in the database
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

$whichTeacher = $catn->getVariable("whichTeacher");
$whichStudent = $catn->getVariable("whichStudent");

$query = "delete from pass.homeroom where userIDStudent=".$whichStudent." and userIDTeacher=".$whichTeacher;
mysql_query($query);

$catn->dbdisconnect();
?>

<script language='JavaScript'>
	alert('Student removed.');
	selectTeacher();
</script>
