<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$whichGroup = $catn->getVariable("whichGroup");

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

$members = $catn->getADGroupMembers($whichGroup);

// Cycle through the listings in AD
foreach($members as $value) {
	// Split into names.
	$fullName = explode(".", $value); // First Name = fullName[0], Last Name = fullName[1]
	$fullName[0] = mysql_escape_string(ucfirst(strtolower($fullName[0]))); // Make sure all names are lowercase with first letter capitalized
	$fullName[1] = mysql_escape_string(ucfirst(strtolower($fullName[1])));
	$value = mysql_escape_string($value);
	
	// Are they already in the CATNET user database?
	$query = "select * from catnet.user where firstName='".$fullName[0]."' and lastName='".$fullName[1]."'";
	$result = mysql_query($query);
	if (mysql_num_rows($result) == 0) {
		// If they are not already in the database, add them  - first and last name from fullName, login from $value, locationID=2[high school]:
		$query2 = "insert into catnet.user values('','".$fullName[0]."','".$fullName[1]."','2','','".$value."','','','','','')";
		mysql_query($query2);
			echo "<font color='red'>".$fullName[0] . " " . $fullName[1] . " has been added to CATNET.</font><br>";
	}
	
	// If they are teachers, are they already in the teachers database? If they are not teachers, they are students, see if they are in student database
	if ($whichGroup == 'CHS Teachers') {
		$query3 = "select userID from catnet.user where firstName='".$fullName[0]."' and lastName='".$fullName[1]."'";
		$result3 = mysql_query($query3);
		$row3 = mysql_fetch_array($result3);
		$query4 = "select * from pass.teachers where userID=".$row3['userID'];
		$result4 = mysql_query($query4);
		if (mysql_num_rows($result4) == 0) {
			$query5 = "insert into pass.teachers values('','".$row3['userID']."','7','0','18')";
			mysql_query($query5);
			echo "<font color='red'>".$fullName[0] . " " . $fullName[1] . " added to PASS.</font><br>";
		}
	} else {
		if ($whichGroup == "class of 2014") {
			$theGroup = "2014";
		}
		if ($whichGroup == "class of 2015") {
			$theGroup = "2015";
		}
		if ($whichGroup == "class of 2016") {
			$theGroup = "2016";
		}
		if ($whichGroup == "class of 2017") {
			$theGroup = "2017";
		}
		
		$query6 = "select userID from catnet.user where firstName='".$fullName[0]."' and lastName='".$fullName[1]."'";
		$result6 = mysql_query($query6);
		$row6 = mysql_fetch_array($result6);
		$query7 = "select * from pass.students where userID=".$row6['userID'];
		$result7 = mysql_query($query7);
		if (mysql_num_rows($result7) == 0) {
			$query8 = "insert into pass.students values('','".$row6['userID']."','".$theGroup."','1')";
			mysql_query($query8);
			echo "<font color='red'>".$fullName[0] . " " . $fullName[1] . " added to PASS.</font><br>";
		}
	}
}

echo "<font color='red'>".count($members)." people found. Sync complete</font><br>";
$catn->dbdisconnect();

?>