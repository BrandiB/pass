<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$theTeacher = $catn->getVariable("teacher");
$outputType = $catn->getVariable("outputType");

//outputType - if 'selectBox', display results inside <option>; if 'tableList', display Homeroom Roster with whether exception exists for attendance

//setup if outputType is 'selectBox'
if ($outputType == "selectBox") {
	$studentList = "<select id='theStudent'><option value='0' selected>Select a Student:</option>";
}

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);
//query to get the students (id + names) that are in particular teacher's homeroom
$query = "select homeroom.userIDStudent, user.lastName, user.firstName from pass.homeroom, catnet.user where homeroom.userIDTeacher=".$theTeacher." and homeroom.userIDStudent=user.userID order by user.lastName, user.firstName";
$result = mysql_query($query);
if (mysql_num_rows($result)!=0) { // If there are results, make a list
	while($row = mysql_fetch_array($result)) {
		$selectedUserID = $row['userIDStudent'];
		$selectedLastName = $row['lastName'];
		$selectedFirstName = $row['firstName'];

		$studentList = $studentList . "<option value='" . $selectedUserID . "'>" . $selectedLastName . ", " . $selectedFirstName . "</option>";
		}	
} else {      // otherwise, if no results, report that no members were found
	$studentList="No Results";
}
$catn->dbdisconnect();

//finish select field for selectBox option, if chosen
if ($outputType == 'selectBox') {
	$studentList = $studentList . "</select>";
}

echo $studentList;

?>