<?php

/*
	This popluates various drop-down boxes in the program with either teacher names or student names, depending on what is passed to here.
*/

// FROM HERE (until below where I say UNTIL HERE) is the intranet user authentication stuff which would need changing 

$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

// UNTIL HERE - note that this includes the database connection, so that would have to be redone as well

$groupID = $catn->getVariable("groupid");
$outputType = $catn->getVariable("outputType");

if ($groupID=='4') {
	$whichClass='2014';
	}
if ($groupID=='5') {
	$whichClass='2015';
	}
if ($groupID=='6') {
	$whichClass='2016';
	}
if ($groupID=='7') {
	$whichClass='2017';
	}

//outputType - selectListHomeroom is for creating the dropdown of teachers (SearchByHomeroom); 
//selectListStudents is for creating dropdown of students (SearchByGrade or after SearchByHomeroom) 

//setup select field for Homeroom Search option, if chosen
if ($outputType == 'selectListHomeroom') {
	$groupMembers = "<select id='StudentSearchCriteria' onChange='getHomeroomStudents()'><option value='0' selected>Select a Teacher:</option>";
}

//setup select field for Students by Grade option, if chosen
if ($outputType == 'selectListStudents') {
	$groupMembers = "<select id='theStudent'><option value='0' selected>Select a Student:</option>";
}

//query to get userID from groupsmem and lastName, firstName from user - is student, query CATNET

if ($outputType == 'selectListHomeroom') {
	$query = "select teachers.userID, user.lastName, user.firstName from pass.teachers, catnet.user where teachers.userID=user.userID order by user.lastName, user.firstName";
} else {
	$query = "select students.userID, user.lastName, user.firstName from pass.students, catnet.user where students.classOf=".$whichClass." and students.userID=user.userID and students.isEnabled=1 order by user.lastName, user.firstName";
}
$result = mysql_query($query);
if (mysql_num_rows($result)!=0) { // If there are results, make a list
	while($row = mysql_fetch_array($result)) {
		$selectedUserID = $row['userID'];
		$selectedLastName = $row['lastName'];
		$selectedFirstName = $row['firstName'];

			$groupMembers = $groupMembers . "<option value='" . $selectedUserID . "'>" . $selectedLastName . ", " . $selectedFirstName . "</option>";
	}	
} else {      // otherwise, if no results, report that no members were found
	$groupMembers="No Results";
}
$catn->dbdisconnect();

//finish select field for Homeroom Search option, if chosen
if ($outputType == 'selectList') {
	$groupMembers = $groupMembers . "</select>";
}

//finish table for Students by Grade option, if chosen
if ($outputType == 'tableList') {
	$groupMembers = $groupMembers . "</select>";
}

echo $groupMembers;

?>
