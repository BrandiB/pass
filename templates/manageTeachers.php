<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

$whichTeacher = $catn->getVariable("whichTeacher");

//time
$theDay[1] = "Monday";
$theDay[2] = "Tuesday";
$theDay[3] = "Wednesday";
$theDay[4] = "Thursday";
$theDay[5] = "Friday";
?>

<script language='JavaScript'>
	function deleteStudent(whichTeacher, whichStudent, studentName) {
		if (confirm("Remove "+studentName+" from this homeroom?")) {
			$.ajax({ 
				type: 'POST', 
				url: 'pass/process/deleteStudent.php', 
				data: { whichTeacher: whichTeacher, whichStudent: whichStudent, keyid: '<?php echo $keyid; ?>' },
				}).done(
				function(output) { $('#processArea').html(output).show();  });
		}
	}
	
	function addStudent(whichTeacher, whichStudent) {
		$.ajax({ 
				type: 'POST', 
				url: 'pass/templates/addStudent.php', 
				data: { whichTeacher: whichTeacher, whichStudent: whichStudent, keyid: '<?php echo $keyid; ?>' },
				}).done(
				function(output) { $('#formdisplay').html(output).show();  });
	}
	
	function changeOverflow(whichTeacher) {
		$.ajax({ 
				type: 'POST', 
				url: 'pass/process/changeOverflow.php', 
				data: { whichTeacher: whichTeacher, keyid: '<?php echo $keyid; ?>' },
				}).done(
				function(output) { $('#processArea').html(output).show();  });
	}
	
	function changeSubject() {
		$.ajax({ 
				type: 'POST', 
				url: 'pass/templates/changeSubject.php', 
				data: { whichTeacher: '<?php echo $whichTeacher; ?>', keyid: '<?php echo $keyid; ?>' },
				}).done(
				function(output) { $('#formdisplay').html(output).show();  });
	}
</script>

<?php
// List of Students
$columnCounter = 0; // keep track of columns
echo "<table width='50%' align='center'>";
echo "<tr><td colspan='2'><b>Homeroom Roster:</b></td></tr>";
$query = "select user.lastName, user.firstName, homeroom.userIDStudent from pass.homeroom, catnet.user where homeroom.userIDTeacher=".$whichTeacher." and user.userID=homeroom.userIDStudent order by user.lastName, user.firstName";
$result = mysql_query($query);
if (mysql_num_rows($result) != 0) { // students were found
		while ($row = mysql_fetch_array($result)) {
			$studentName = '"'.$row["firstName"].' '.$row["lastName"].'"';
			if ($columnCounter % 2 == 0) { //even - left
				echo "<tr><td bgcolor='#ffffff' width='50%'><a style='cursor:pointer' onClick='deleteStudent(".$whichTeacher.",".$row['userIDStudent'].",".$studentName.")'><img src='/peds/pass/images/delete.png'></a> &nbsp; ".$row['lastName'].", ".$row['firstName']."</td>";
				$columnCounter = $columnCounter + 1;
			} else { //odd - right
				echo "<td bgcolor='#ffffff' width='50%'><a style='cursor:pointer' onClick='deleteStudent(".$whichTeacher.",".$row['userIDStudent'].",".$studentName.")'><img src='/peds/pass/images/delete.png'></a> &nbsp; ".$row['lastName'].", ".$row['firstName']."</td></tr>";
				$columnCounter = $columnCounter + 1;
			}
		}
		if ($columnCounter % 2 != 0) { // if we are currently odd, end table row
			echo "<td bgcolor='#ffffff' width='50%'></td></tr>";
		}
} else {
	echo "<tr><td><font color='red'>No students found.</font></td></tr>";
}
// Option to Add Student
echo "<tr><td colspan='2' align='center'><input type='button' class='button' value='Add Student' onClick='addStudent(".$whichTeacher.")'></td></tr>";
echo "<tr><td colspan='2'>&nbsp;</td></tr>";
// Change Subject, Overflow Options:
$query2 = "select teachers.isOverflow, subjects.subjectName, trumpday.whichDay from pass.teachers, pass.subjects, pass.trumpday where teachers.userID=".$whichTeacher." and subjects.subjectID=teachers.subjectID and trumpday.subjectID=teachers.subjectID";
$result2 = mysql_query($query2);
$row2 = mysql_fetch_array($result2);
echo "<tr><td align='right'><b>Subject</b> and <b>Trump Day:</b> &nbsp; </td><td>".$row2['subjectName']." (".$theDay[$row2['whichDay']].") <input type='button' class='button' value='Change' onClick='changeSubject(".$whichTeacher.")'></td></tr>";
echo "<tr><td align='right'>Send <b>Overflow</b> to this teacher? &nbsp; </td><td>";
if ($row2['isOverflow'] == 1) {
	echo "Yes";
} else {
	echo "No";
}
echo " <input type='button' class='button' value='Change' onClick='changeOverflow(".$whichTeacher.")'></td></tr>";
echo "</table>";

$catn->dbdisconnect();
?>
