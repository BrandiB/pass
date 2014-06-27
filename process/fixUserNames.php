<?php

$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

$query = "select firstName, lastName, userID from catnet.user";
$result = mysql_query($query);
while ($row = mysql_fetch_array($result)) {
	$newFirstName = ucfirst(strtolower($row['firstName']));
	$newLastName = ucfirst(strtolower($row['lastName']));
	if ($newFirstName != $row['firstName'] || $newLastName != $row['lastName']) {
		echo "<font color='red'>Change ".$row['firstName']." ".$row['lastName']."</font> ".$row['userID']."<br>";
	} else {
		echo "No need to change ".$row['firstName']." ".$row['lastName']." ".$row['userID']."<br>";
	}
	
}
$catn->dbdisconnect();

?>