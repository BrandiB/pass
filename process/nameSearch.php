<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$searchLastName = $catn->getVariable("lastName");
$searchFirstName = $catn->getVariable("firstName");

//this will display the search results in a drop-down box:
	$studentList = "<select id='theStudent'><option value='0' selected>Select a Student:</option>";

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);
// set query depending on if last name or first name is null; this page should not be called if both are null
if ($searchLastName == '') {
	$query = "select user.userID, user.firstName, user.lastName, students.classOf from catnet.user, pass.students where user.firstName like '%".$searchFirstName."%' and students.userID=user.userID and students.isEnabled=1 order by user.lastName, user.firstName";
} else {
	if ($searchFirstName == '') {
		$query = "select user.userID, user.firstName, user.lastName, students.classOf from catnet.user, pass.students where user.lastName like '%".$searchLastName."%' and students.userID=user.userID and students.isEnabled=1 order by user.lastName, user.firstName";
	} else {
		$query = "select user.userID, user.firstName, user.lastName, students.classOf from catnet.user, pass.students where user.firstName like '%".$searchFirstName."%' and user.lastName like '%".$searchLastName."%' and students.userID=user.userID and students.isEnabled=1 order by user.lastName, user.firstName";
	}
}


//query to get student names
$result = mysql_query($query);
if (mysql_num_rows($result)!=0) { // If there are results, make a list
	while($row = mysql_fetch_array($result)) {
		$selectedUserID = $row['userID'];
		$selectedLastName = $row['lastName'];
		$selectedFirstName = $row['firstName'];
		$selectedGroup = $row['classOf'];

		$studentList = $studentList . "<option value='" . $selectedUserID . "'>" . $selectedLastName . ", " . $selectedFirstName . " (" . $selectedGroup . ")</option>";
		}	
} else {      // otherwise, if no results, report that no members were found
	$studentList="No Results";
}
$catn->dbdisconnect();

//finish select field 
	$studentList = $studentList . "</select>";

echo $studentList;

?>