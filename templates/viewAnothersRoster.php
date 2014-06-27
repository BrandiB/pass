<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$today = date("F j, Y");
	$todayData = date("Y-m-d");
	$nextRoster = date("F j, Y", strtotime('next Monday'));
	$outgoingStudents = 0;

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

$whichTeacher = $catn->getVariable("whichTeacher");
$isAdmin = $catn->checkADGroups('pass admin',$catn->keydata['username']);
if ($isAdmin) {
	$absentButton = "1";
} else {
	$absentButton = "0"; 
}

echo "<script language='JavaScript'>

function makeAbsentO(whichStudent) {
	$.ajax({
				type: 'POST',
				url: 'pass/process/changeAbsence.php',
				data: { keyid: '".$keyid."', whichStudent: whichStudent, whichTeacher: '".$whichTeacher."', whichDay: '".$todayData."', whichProcess: 'A', whichUpdate: 'n' },
				}).done(
				function(output) { $('#workArea').html(output).show(); });
}
function makePresentO(whichStudent) {
	$.ajax({
				type: 'POST',
				url: 'pass/process/changeAbsence.php',
				data: { keyid: '".$keyid."', whichStudent: whichStudent, whichTeacher: '".$whichTeacher."', whichDay: '".$todayData."', whichProcess: 'P', whichUpdate: 'n' },
				}).done(
				function(output) { $('#workArea').html(output).show(); });
}
</script>";

//NORMAL HOMEROOM ROSTER for the day
	$query = "select * from pass.homeroom, catnet.user where homeroom.userIDTeacher=".$whichTeacher." and user.userID=homeroom.userIDStudent order by user.lastName, user.firstName";
	$result = mysql_query($query);
	
	echo "<table cellpadding='0' cellspacing='0' border='0' align='center' width='85%' bgcolor='#ededd7'>";
	echo "<tr><td colspan='6'><div id='workArea'></div></td></tr>";
	echo "<tr><td colspan='6' bgcolor='#C0FFFF'><font size='+1'><b>Homeroom Roster</b></font></td></tr>";
	echo "<tr><td colspan='6'>&nbsp;</td></tr>";
	
	//NORMAL HOMEROOM ROSTER for the day
	if (mysql_num_rows($result)!=0) { // If there are results, make a list
		echo "<tr><td width='20%'><u>Student</u></td><td width='15%'><u>Exception?</u></td><td width='15%'><u>Absent?</u></td><td width='20%'><u>Student</u></td><td width='15%'><u>Exception?</u></td><td width='15%'><u>Absent?</u></td></tr>";
		$columnCounter = 0; //display results in columns - will check if even (left side) or odd (right) + keeps students in array for later in page
		while($row = mysql_fetch_array($result)) {
				$query2 = "select * from pass.requestmatrix where studentID=".$row['userIDStudent']." and dateRequested='".$todayData."'";
				$result2 = mysql_query($query2);
				if (mysql_num_rows($result2) != 0) { // Exception exists
					$exceptionFront = "<font color='red'><b>";
					$exceptionText[$columnCounter] = "Yes";
					$exceptionBack = "</b></font>";
					// Get variables here for 'Leaving' Table below
					while ($row2 = mysql_fetch_array($result2)) {
						$student[$columnCounter]=$row['lastName'].", ".$row['firstName'];
						$studentID[$columnCounter] = $row['userIDStudent'];
						$whichCategory[$columnCounter]=$row2['categoryID'];
						$whichTeacher1[$columnCounter]=$row2['teacherID'];
						$outgoingStudents=1;
					}
				} else { // Exception does not exist
					$exceptionFront = "";
					$exceptionText[$columnCounter] = "No";
					$exceptionBack = "";
					$student[$columnCounter]=$row['lastName'].", ".$row['firstName'];
				}
				// Is the student absent?
				$query9 = "select * from pass.absentmatrix where studentID=".$row['userIDStudent']." and dateAbsent='".$todayData."'";
				$result9 = mysql_query($query9);
				if (mysql_num_rows($result9) != 0) { // Absent
					$isAbsent = 1;
					$row9 = mysql_fetch_array($result9);
					$absentID = $row9['absentID'];
					$exceptionFront = "<font color='red'><b>";
					$exceptionBack = "</b></font>";
				} else {
					$isAbsent = 0;
				}
			if ($columnCounter % 2 == 0) { //even - left
					echo "<tr><td bgcolor='#ffffff' style='padding-left:10px'>".$exceptionFront.$student[$columnCounter].$exceptionBack."</td><td bgcolor='#ffffff'>".$exceptionFront.$exceptionText[$columnCounter].$exceptionBack."</td>";
					if ($absentButton == '1') {
						if ($isAbsent == '1') {
							echo "<td bgcolor='#ffffff'><font color='red'><b>Absent</b></font> <input type='button' value='Change' onClick='makePresentO(".$row['userIDStudent'].")'></td>";
						} else {
							echo "<td bgcolor='#ffffff'>Present <input type='button' value='Change' onClick='makeAbsentO(".$row['userIDStudent'].")'></td>";
						}
					} else {
						if ($isAbsent == '1') {
							echo "<td bgcolor='#ffffff'><font color='red'><b>Absent</b></font></td>";
						} else {
							echo "<td bgcolor='#ffffff'>Present</td>";
						}
					}
					$columnCounter = $columnCounter + 1;
			} else { //odd - right
					echo "<td bgcolor='#ffffff' style='padding-left:10px'>".$exceptionFront.$student[$columnCounter].$exceptionBack."</td><td bgcolor='#ffffff'>".$exceptionFront.$exceptionText[$columnCounter].$exceptionBack."</td>";
					if ($absentButton == '1') {
						if ($isAbsent == '1') {
							echo "<td bgcolor='#ffffff'><font color='red'><b>Absent</b></font> <input type='button' value='Change' onClick='makePresentO(".$row['userIDStudent'].")'></td>";
						} else {
							echo "<td bgcolor='#ffffff'>Present <input type='button' value='Change' onClick='makeAbsentO(".$row['userIDStudent'].")'></td>";
						}
					} else {
						if ($isAbsent == '1') {
							echo "<td bgcolor='#ffffff'><font color='red'><b>Absent</b></font></td>";
						} else {
							echo "<td bgcolor='#ffffff'>Present</td>";
						}
					}
					echo "</tr><tr><td colspan='6'>&nbsp;</td></tr>";
					$columnCounter = $columnCounter + 1;
			}
		}
		if ($columnCounter % 2 != 0) { // if we are currently odd, end table row
			echo "<td colspan='3' bgcolor='#ffffff'>&nbsp;</td></tr>";
			echo "<tr><td></td><td colspan='4'>&nbsp;</td><td></td></tr>";
		}
	} else {
		$student[0]="NoStudents";
		echo "<tr><td></td><td colspan='4' bgcolor='#FFFFFF'>A roster could not be found.</td><td></td></tr>";
		echo "<tr><td colspan='5'>&nbsp;</td></tr>";
	}
	
	// EXCEPTIONS for the day
	// COMING - Students Requested by Logged-in Teacher
	echo "<tr><td colspan='6' bgcolor='#C0FFFF'><font size='+1'><b>Exceptions</b></font></td></tr>";
	echo "<tr><td colspan='6'>&nbsp;</td></tr>";
	echo "<tr><td colspan='6' align='center'><b>Coming:</b></td></tr>";
	
	//here is where the query to get Coming Students will go
	$query3="select user.userID, user.firstName, user.lastName, category.categoryName, homeroom.userIDTeacher from pass.requestmatrix, catnet.user, pass.category, pass.homeroom where requestmatrix.teacherID=".$whichTeacher." and requestmatrix.dateRequested='".$todayData."' and user.userID=requestMatrix.studentID and category.categoryID=requestMatrix.categoryID and homeroom.userIDStudent=requestMatrix.studentID order by user.lastName, user.firstName";
	$result3=mysql_query($query3);
	if (mysql_num_rows($result3)!=0) { // If there are results, make a list
		echo "<tr><td><u>Student</u></td><td colspan='2'><u>Normal Homeroom Teacher</u></td><td><u>Reason</u></td><td colspan='2'><u>Absent?</u></td></tr>";
		while ($row3 = mysql_fetch_array($result3)) {
			// query to get student's normal homeroom teacher name
			$query4="select user.lastName, user.firstName from catnet.user where user.userID=".$row3['userIDTeacher'];
			$result4=mysql_query($query4);
			if (mysql_num_rows($result4) != 0) {
				$row4 = mysql_fetch_array($result4);
				$teacherName = $row4['lastName'].", ".$row4['firstName'];
			} else {
				$teacherName = "Not Found";
			}
			
			// Is the student absent?
			$query8 =  "select * from pass.absentmatrix where studentID=".$row3['userID']." and dateAbsent='".$todayData."'";
			$result8 = mysql_query($query8);
			if (mysql_num_rows($result8) != 0) { // Absent
				echo "<tr><td bgcolor='#ffffff'><font color='#008000'><strike><b>".$row3['lastName'].", ".$row3['firstName']."</b></strike></font></td><td bgcolor='#ffffff' colspan='2'><strike>".$teacherName."</strike></td><td bgcolor='#ffffff'><strike>".$row3['categoryName']."</strike></td><td colspan='2' bgcolor='#ffffff'><font color='red'>Absent</font></td></tr>";
			} else { // Not Absent
				echo "<tr><td bgcolor='#ffffff'><font color='#008000'><b>".$row3['lastName'].", ".$row3['firstName']."</b></font></td><td colspan='2' bgcolor='#ffffff'>".$teacherName."</td><td bgcolor='#ffffff'>".$row3['categoryName']."</td><td colspan='2' bgcolor='#ffffff'>Present</td></tr>";
			}
			echo "<tr><td colspan='6'>&nbsp;</td></tr>";
		}
	} else { // If no incoming students today:
		echo "<tr><td></td><td colspan='4' align='center'>No incoming students today.</td><td></td></tr>";
		echo "<tr><td colspan='6'>&nbsp;</td></tr>";
	}
	
	// LEAVING - Students Requested by Other Teachers or OVERFLOW
	echo "<tr><td colspan='6' align='center'><b>Leaving:</b></td></tr>";
	
	//here is where the query to get Leaving Students will go
	if ($student[0]=="NoStudents" || $outgoingStudents==0) { // if there were no students found in roster or no students leaving
		echo "<tr><td></td><td colspan='4' align='center'>No outgoing students today.</td><td></td></tr>";
		echo "<tr><td colspan='6'>&nbsp;</td></tr>";
	} else {
		echo "<tr><td><u>Student</u></td><td colspan='2'><u>Normal Homeroom Teacher</u></td><td><u>Reason</u></td><td colspan='2'><u>Absent?</u></td></tr>";
		$i=0;
		while ($i < count($student)) {
			if ($exceptionText[$i]=="Yes") { // is there an exception? if no do nothing
				$query5="select category.categoryName, user.lastName, user.firstName from pass.category, catnet.user where category.categoryID=".$whichCategory[$i]." and user.userID=".$whichTeacher1[$i];
				$result5=mysql_query($query5);
				$row5=mysql_fetch_array($result5);
				$query8 =  "select * from pass.absentmatrix where studentID=".$studentID[$i]." and dateAbsent='".$todayData."'";
				$result8 = mysql_query($query8);
				if (mysql_num_rows($result8) != 0) { // Absent
					echo "<tr><td bgcolor='#ffffff'><font color='#ff0000'><strike><b>".$student[$i]."</b></strike></font></td><td bgcolor='#ffffff' colspan='2'><strike>".$row5['lastName'].", ".$row5['firstName']."</strike></td><td bgcolor='#ffffff'><strike>".$row5['categoryName']."</strike></td><td colspan='2' bgcolor='#ffffff'><font color='red'>Absent</font></td></tr>";
				} else {
					echo "<tr><td bgcolor='#ffffff'><font color='#ff0000'><b>".$student[$i]."</b></font></td><td bgcolor='#ffffff' colspan='2'>".$row5['lastName'].", ".$row5['firstName']."</td><td bgcolor='#ffffff'>".$row5['categoryName']."</td><td colspan='2' bgcolor='#ffffff'>Present</td></tr>";
				}
				echo "<tr><td colspan='6'>&nbsp;</td></tr>";
			}
			$i++;
		}
	}

	echo "</table>";

$catn->dbdisconnect();

?>