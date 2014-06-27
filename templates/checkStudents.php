<?php

$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

echo "<table>";
$query = "select * from pass.students, catnet.user where user.userID=students.studentID";
$result = mysql_query($query);
while ($row = mysql_fetch_array($result)) {
	$studentID = $row['studentID'];
	$classOf = $row['classOf'];
	$firstName = $row['firstName'];
	$lastName = $row['lastName'];
	
	$query2 = "select * from pass.homeroom where userIDStudent = ".$studentID;
	$result2 = mysql_query($query2);
	if (mysql_num_rows($result2) != 0 {
		while ($row2 = mysql_fetch_array($result)) {
			$teacherID = $row2['userIDTeacher'];
		}
		} else {
			$teacherID = 0;
		}
	echo "<tr><td>".$studentID."</td><td>".$firstName." ".$lastName."</td><td>".$classOf."</td><td>".$teacherID."</td></tr>";
	}
}
echo "</table>";
$catn->dbdisconnect();

?>