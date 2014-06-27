<?php

/*
	This adds a student to a particular homeroom and removes them from their previous homeroom. This is passed in a teacher and a student.
*/

// UNTIL HERE - note that this includes the database connection, so that would have to be redone as well
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

// which Teacher are we adding to?
$query6 = "select user.firstName, user.lastName from catnet.user where userID=".$whichTeacher;
$result6 = mysql_query($query6);
$row6 = mysql_fetch_array($result6);
$theTeacher = $row6['firstName'] . " " . $row6['lastName'];

// Is the student already in this teacher's homeroom?
$query2 = "select * from pass.homeroom where userIDStudent=".$whichStudent." and userIDTeacher=".$whichTeacher;
$result2 = mysql_query($query2);
if (mysql_num_rows($result2) != 0) { // already in the roster
	echo "<script language='JavaScript'>alert('Student is already in this homeroom!');</script>";
} else {
	// Is this student already in another teacher's homeroom?
	$query4 = "select homeroom.userIDTeacher, user.lastName, user.firstName from pass.homeroom, catnet.user where homeroom.userIDStudent=".$whichStudent." and user.userID=homeroom.userIDTeacher";
	$result4 = mysql_query($query4);
	if (mysql_num_rows($result4) != 0) {
		$row4 = mysql_fetch_array($result4);
		$alertMessage = "Student removed from ".$row4['firstName']." ".$row4['lastName']."'s homeroom and added to ".$theTeacher."'s homeroom.";
	} else {
		$alertMessage = "Student added to ".$theTeacher."'s homeroom.";
	}
	$query3 = "delete from pass.homeroom where userIDStudent=".$whichStudent;
	mysql_query($query3);
	$query = "insert into pass.homeroom values('','".$whichStudent."','".$whichTeacher."')";
	mysql_query($query);
?>
<script language='JavaScript'>
	alert("<?php echo $alertMessage; ?>");
	$.ajax({
	type: 'POST',
		url: 'pass/templates/manageTeachers.php',
		data: { keyid: '<?php echo $keyid; ?>', whichTeacher: whichTeacher },
		}).done(
		function(output) { $('#teacherDisplay').html(output).show(); });
	$('#formdisplay').hide();
</script>
<?php
}
$catn->dbdisconnect();
?>
