<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

// Take time to take the time:
$nextRoster = date("F j, Y", strtotime('Monday this week'));
$whichDay[1] = date("Y-m-d", strtotime('Monday this week'));
$tableDay[1] = date("D, M j", strtotime('Monday this week')); //tableDay is formatted for table header
$tableDay[2] = date("D, M j", strtotime('Tuesday this week')); 
$whichDay[2] = date("Y-m-d", strtotime('Tuesday this week')); //whichDay is formatted for database
$tableDay[3] = date("D, M j", strtotime('Wednesday this week'));
$whichDay[3] = date("Y-m-d", strtotime('Wednesday this week'));
$tableDay[4] = date("D, M j", strtotime('Thursday this week'));
$whichDay[4] = date("Y-m-d", strtotime('Thursday this week'));
$tableDay[5] = date("D, M j", strtotime('Friday this week'));
$whichDay[5] = date("Y-m-d", strtotime('Friday this week'));
$tableDay[6] = "None";

// counters for each day
$dayNumber[1] = 0;
$dayNumber[2] = 0;
$dayNumber[3] = 0;
$dayNumber[4] = 0;
$dayNumber[5] = 0;

// what is the max number of students in the class?

echo "<style type='text/css'>
	.button {
		border: 1px solid #000000;
		background-color: #C0FFFF;
		color: #000000;
		font-weight: bold;
	}
	.button:hover {
		background-color: #000000;
		color: #C0FFFF;
		font-weight: bold;
		cursor: pointer;
	}
	.requestButton {
		border: 2px solid #000000;
		background-color: #C0FFFF;
		color: #000000;
		font-weight: bold;
		font-size: 110%;
	}
	.requestButton:hover {
		background-color: #000000;
		color: #C0FFFF;
		font-weight: bold;
		cursor: pointer;
	}
	.noRequest {
		border: 1px solid #C0C0C0;
		background-color: #ededd7;
		color: #000000;
		width: 120px;
	}
	.noRequest:hover {
		background-color: #C0C0C0;
		color: #000000;
		cursor: pointer;
	}
	.overflow {
		border: 1px solid #000000;
		background-color: #E8ADAA;
		color: #000000;
		width: 120px;
	}
	.overflow:hover {
		background-color: #C0C0C0;
		color: #000000;
		cursor: pointer;
	}
	.yesRequest {
		border: 1px solid #000000;
		background-color: #C88141;
		color: #000000;
		width: 120px;
	}
	.trump {
		border: 1px solid #000000;
		background-color: #8C001A;
		color: #ffffff;
		width: 120px;
	}
</style>";

echo "<script language='JavaScript'>

	// hide formdisplay if shown
	$('#formdisplay').hide();

	// see what their preferred max # of students is and what their trump day is
	displayMaxStudents();

	// Go back to main PASS page.
	function returnToday() {
		$.ajax({
			type: 'POST',
			url: 'pass/templates/pass.php',
			data: { keyid: '".$keyid."' },
			}).done(
			function(output) { $('#datadisplay').html(output).show(); });
	}
	
	// Display Max Number of Students
		function displayMaxStudents() {
		theNumber = '0';
		$.ajax({
			type: 'POST',
			url: 'pass/process/maximumStudents.php',
			data: { keyid: '".$keyid."', whichProcess: '1' },
			}).done(
			function(output) { $('#studentNumber').html(output).show(); });
	}
	
	// Change Max Number of Students
	function changeMaxStudents() {
		theMax = document.getElementById('whichMax').value;
		$.ajax({
			type: 'POST',
			url: 'pass/process/maximumStudents.php',
			data: { keyid: '".$keyid."', theMax: theMax, whichProcess: '2', },
			}).done(
			function(output) { $('#studentNumber').html(output).show(); });
	
	}
	
	// Overflow a student
	function overflowStudent(whichStudent, theDay) {
		$.ajax({
			type: 'POST',
			url: 'pass/templates/overflow.php',
			data: { keyid: '".$keyid."', whichStudent: whichStudent, theDay: theDay, whichWeek: '1' },
			}).done(
			function(output) { $('#formdisplay').html(output).show(); });
	}
	
	// Request a student
	function displayStudentRequest() {
		$.ajax({ 
			type: 'POST', 
			url: 'pass/templates/request.php', 
			data: { keyid: '".$keyid."', whichWeek: '1' },
			}).done(
			function(output) { $('#formdisplay').html(output).show();  });
	}
	
	// Remove a request
	function cancelRequest(whichRequest) {
		$.ajax({ 
			type: 'POST', 
			url: 'pass/templates/cancelRequest.php', 
			data: { whichRequest: whichRequest, keyid: '".$keyid."', whichWeek: '1' },
			}).done(
			function(output) { $('#formdisplay').html(output).show();  });
	}
	
	</script> ";

//what is the teacher's trump day?
$query4 = "select teachers.subjectID, teachers.maxNumberStudents, trumpday.whichDay from pass.teachers, pass.trumpday where teachers.userID=".$catn->userinfo[userID]." and trumpday.subjectID=teachers.subjectID";
$result4 = mysql_query($query4);
if (mysql_num_rows($result4) != 0) { //are they in PASS?
		$row4 = mysql_fetch_array($result4);
		$trumpDay = $row4['whichDay'];
		$maxStudents = $row4['maxNumberStudents'];
		$showRoster = 1;
} else {
	$showRoster = 0;
	$trumpDay=6;
	$maxStudents = 18;
}

echo "<center><font color='#0000FF' size='+1'>Manage Roster for the Week of <b>".$nextRoster."</b></font></center>";
echo "<table cellpadding='0' cellspacing='0' border='0' align='center' width='85%' bgcolor='#ededd7'>";
echo "<tr><td width='3%'>&nbsp;</td><td width='29%'>&nbsp;</td><td width='13%'>&nbsp;</td><td width='13%'>&nbsp;</td><td width='13%'>&nbsp;</td><td width='13%'>&nbsp;</td><td width='13%'>&nbsp;</td><td width='3%'>&nbsp;</td></tr>";
echo "<tr><td></td><td colspan='6' align='center'>Maximum Number of Students in Classroom: <div id='studentNumber' style='display:inline'></div></td><td></td></tr>";
echo "<tr><td></td><td colspan='6' align='center'>Your <b>Trump Day</b> this week is <b>".$tableDay[$trumpDay].".</b></td><td></td></tr>"; 
echo "<tr><td colspan='8'>&nbsp;</td></tr>";
echo "<tr><td></td><td colspan='6' align='center'><b>Table Key:</b> <input type='button' class='noRequest' value='No Request'> <input type='button' class='overflow' value='Overflow'> <input type='button' class='yesRequest' value='Normal Request'> <input type='button' class='trump' value='Trump Request'></td><td></td></tr>"; 
echo "<tr><td colspan='8'>&nbsp;</td></tr>";

//Display roster of normal homeroom students
echo "<tr><td></td><td colspan='6'><font size='+1'><b>Homeroom Roster</b></font></td><td></td></tr>";
$query2 = "select * from pass.homeroom, catnet.user where homeroom.userIDTeacher=".$catn->userinfo[userID]." and user.userid=homeroom.userIDStudent order by user.lastName, user.firstName";
$result2 = mysql_query($query2);
if (mysql_num_rows($result2) != 0) { // are there students listed in homeroom roster?
	$theDay = 1; // keep track of what day we are on. 1 = Monday, 5 = Friday
	echo "<tr><td></td><td><u>Student</u></td>"; // Column Headers
	while ($theDay < 6) {
		if ($trumpDay == $theDay) { // if it's their trump day, different color background
			echo "<td align='center'  bgcolor='#B5EAAA'><u>".$tableDay[$theDay]."</u></td>";
		} else {
			echo "<td align='center'><u>".$tableDay[$theDay]."</u></td>";
		}
		$theDay = $theDay + 1;
	}
	echo "<td></td></tr>"; 
	$theDay = 1; // reset to Monday
	while ($row2=mysql_fetch_array($result2)) {
		$whichStudent = $row2['userIDStudent'];
		echo "<tr><td></td><td style='border-bottom:1px solid #000000;padding-left:10px' bgcolor='#ffffff'>".$row2['lastName'].", ".$row2['firstName']."</td>";
		// Cycle through the week:
		while ($theDay < 6) {
			// Has this student been requested or overflowed yet?
			$query3 = "select requestmatrix.requestID, requestmatrix.categoryID, requestmatrix.isTrump, category.categoryLevel, user.lastName from pass.requestmatrix, pass.category, catnet.user where requestmatrix.studentID=".$whichStudent." and requestmatrix.dateRequested='".$whichDay[$theDay]."' and category.categoryID=requestMatrix.categoryID and user.userID=requestmatrix.teacherID"; 
			$result3 = mysql_query($query3);
			if (mysql_num_rows($result3) != 0) { // is there a request on this day?
				$row3 = mysql_fetch_array($result3);
				$isTrump = $row3['isTrump'];
				$categoryLevel = $row3['categoryLevel'];
				$whichTeacher = $row3['lastName'];
				if ($categoryLevel == '1') { // was this student overflowed?
					if ($trumpDay == $theDay) { // if it's their trump day, different color background
						echo "<td style='border-bottom:1px solid #000000' align='center' bgcolor='#B5EAAA'><input type='button' class='overflow' value='Remove Overflow?&#10;".$whichTeacher."' onClick='cancelRequest(".$row3['requestID'].")'></td>";
					} else {
						echo "<td style='border-bottom:1px solid #000000' align='center' bgcolor='#ffffff'><input type='button' class='overflow' value='Remove Overflow?&#10;".$whichTeacher."' onClick='cancelRequest(".$row3['requestID'].")'></td>";
					}
				} else { // student was requested by someone
					if ($isTrump == 1) { // trump request
						if ($trumpDay == $theDay) {
							echo "<td style='border-bottom:1px solid #000000' align='center' bgcolor='#B5EAAA'><input type='button' class='trump' value='Trump Request:&#10;".$whichTeacher."'></td>";
						} else {
							echo "<td style='border-bottom:1px solid #000000' align='center' bgcolor='#ffffff'><input type='button' class='trump' value='Trump Request:&#10;".$whichTeacher."'></td>";
						}
					} else {
						if ($trumpDay == $theDay) {
							echo "<td style='border-bottom:1px solid #000000' align='center' bgcolor='#B5EAAA'><input type='button' class='yesRequest' value='Request:&#10;".$whichTeacher."'></td>";
						} else {
							echo "<td style='border-bottom:1px solid #000000' align='center' bgcolor='#ffffff'><input type='button' class='yesRequest' value='Request:&#10;".$whichTeacher."'></td>";
						}
					}
				}
			} else {  // no request on this day
				$dayNumber[$theDay] = $dayNumber[$theDay] + 1; // add student to that day's count
				if ($trumpDay == $theDay) {
					echo "<td style='border-bottom:1px solid #000000' bgcolor='#B5EAAA' align='center'><input type='button' class='noRequest' value='Overflow&#10;Student?' onClick='overflowStudent(".$whichStudent.", ".$theDay.")'></td>";
				} else {
					echo "<td style='border-bottom:1px solid #000000' bgcolor='#ffffff' align='center'><input type='button' class='noRequest' value='Overflow&#10;Student?' onClick='overflowStudent(".$whichStudent.", ".$theDay.")'></td>";
				}
			}
			$theDay=$theDay+1;
		}
		echo "</tr>";
		$theDay = 1;
	}
}

	$theDay = 1; // keep track of what day we are on. 1 = Monday, 5 = Friday
	echo "<tr><td></td><td>&nbsp;</td>";
	while ($theDay < 6) {
		if ($trumpDay == $theDay) { // if it's their trump day, different color background
			echo "<td align='center'  bgcolor='#B5EAAA'></td>";
		} else {
			echo "<td align='center'></td>";
		}
		$theDay = $theDay + 1;
	}	echo "<td></td></tr>";
	$theDay = 1;

// Request Students
echo "<tr><td></td><td><font size='+1'><b>Requests</b></font></td>";
while ($theDay < 6) {
		if ($trumpDay == $theDay) { // if it's their trump day, different color background
			echo "<td align='center' bgcolor='#B5EAAA'></td>";
		} else {
			echo "<td align='center'></td>";
		}
		$theDay = $theDay + 1;
	}	
	$theDay = 1;
	echo "<td></td></tr>";
	echo "<tr><td></td><td><u>Student</u></td>"; // Column Headers
	while ($theDay < 6) {
		if ($trumpDay == $theDay) { // if it's their trump day, different color background
			echo "<td align='center'  bgcolor='#B5EAAA'><u>".$tableDay[$theDay]."</u></td>";
		} else {
			echo "<td align='center'><u>".$tableDay[$theDay]."</u></td>";
		}
		$theDay = $theDay + 1;
	}
	echo "<td></td></tr>"; 
	$theDay = 1;
	
// Does the teacher already have requests?
$query5="select user.lastName, user.firstName, requestmatrix.studentID from pass.requestmatrix, catnet.user where requestmatrix.teacherID=".$catn->userinfo[userID]." and user.userid=requestmatrix.studentID and (requestmatrix.dateRequested='".$whichDay[1]."' or requestmatrix.dateRequested='".$whichDay[2]."' or requestmatrix.dateRequested='".$whichDay[3]."' or requestmatrix.dateRequested='".$whichDay[4]."' or requestmatrix.dateRequested='".$whichDay[5]."') order by user.lastName, user.firstName, requestmatrix.dateRequested";
$result5=mysql_query($query5);
$lastStudent='testGuy'; //variable to make sure there are no repeats in our table
if (mysql_num_rows($result5) != 0) { // there are requests
	while ($row5=mysql_fetch_array($result5)) {
		$currentStudent = $row5['lastName'].", ".$row5['firstName'];
		$currentStudentID = $row5['studentID'];
		if ($lastStudent != $currentStudent) { // skip if repeat
			echo "<tr><td></td><td bgcolor='#ffffff' style='border-bottom:1px solid #000000;padding-left:10px'>".$currentStudent."</td>";
			while ($theDay < 6) {
				$query6 = "select requestmatrix.categoryID, requestmatrix.requestID, category.categoryName from pass.requestmatrix, pass.category where requestmatrix.teacherID=".$catn->userinfo[userID]." and requestmatrix.studentID=".$currentStudentID." and requestmatrix.dateRequested='".$whichDay[$theDay]."' and category.categoryID=requestmatrix.categoryID";
				$result6 = mysql_query($query6);
				if (mysql_num_rows($result6) != 0) { //request on this day
					while ($row6=mysql_fetch_array($result6)) {
						$currentRequest = $row6['requestID'];
						if ($trumpDay == $theDay) { // trump
							echo "<td align='center' bgcolor='#B5EAAA' style='border-bottom:1px solid #000000'><b>".$row6['categoryName']."</b><br><input type='button' class='button' value='Cancel Request?' onClick='cancelRequest(".$currentRequest.")'></td>";
							$dayNumber[$theDay] = $dayNumber[$theDay] + 1; // add student to that day's count
						} else { // not trump
							echo "<td align='center' bgcolor='#ffffff' style='border-bottom:1px solid #000000'><b>".$row6['categoryName']."</b><br><input type='button' class='button' value='Cancel Request?' onClick='cancelRequest(".$currentRequest.")'></td>";
							$dayNumber[$theDay] = $dayNumber[$theDay] + 1; //add student to that day's count
						}
					}
				} else { //no request on this day
					if ($trumpDay == $theDay) { // if it's their trump day, different color background
						echo "<td align='center' bgcolor='#B5EAAA' style='border-bottom:1px solid #000000'></td>";
					} else {
						echo "<td align='center' bgcolor='#ffffff' style='border-bottom:1px solid #000000'></td>";
					}
				}
				$theDay = $theDay + 1;
			}
			echo "<td></td></tr>";
			$theDay = 1;
		}
		$lastStudent = $currentStudent; // make sure there are no repeats of the student we just did
	}
	echo "<tr><td></td><td align='center'><input type='button' class='requestButton' value='Add Request' onClick='displayStudentRequest()'></td>";
	while ($theDay < 6) {
		if ($trumpDay == $theDay) { // if it's their trump day, different color background
			echo "<td align='center'  bgcolor='#B5EAAA'></td>";
		} else {
			echo "<td align='center'></td>";
		}
		$theDay = $theDay + 1;
	}	
	$theDay = 1;
	echo "<td></td></tr>";

} else { // there are no requests	
	echo "<tr><td></td><td align='center'><input type='button' class='requestButton' value='Add Request' onClick='displayStudentRequest()'></td>";
	while ($theDay < 6) {
		if ($trumpDay == $theDay) { // if it's their trump day, different color background
			echo "<td align='center'  bgcolor='#B5EAAA'></td>";
		} else {
			echo "<td align='center'></td>";
		}
		$theDay = $theDay + 1;
	}	
	$theDay = 1;
	echo "<td></td></tr>";
}

// Total # of students per day
echo "<tr><td></td><td>&nbsp;</td>";
	while ($theDay < 6) { // blank line
		if ($trumpDay == $theDay) { // if it's their trump day, different color background
			echo "<td align='center'  bgcolor='#B5EAAA'></td>";
		} else {
			echo "<td align='center'></td>";
		}
		$theDay = $theDay + 1;
	}	
	$theDay = 1;
	echo "<td></td></tr>";
	echo "<tr><td></td><td><font size'+1'><b>Total Count:</b></font></td>";
	while ($theDay < 6) {
		if ($trumpDay == $theDay) { // if it's their trump day, different color background
			if ($dayNumber[$theDay] > $maxStudents) {
				echo "<td align='center'  bgcolor='#B5EAAA'><font color='red'>".$dayNumber[$theDay]."</font></td>";
			} else {
				echo "<td align='center'  bgcolor='#B5EAAA'>".$dayNumber[$theDay]."</td>";
			}	
		} else {
			if ($dayNumber[$theDay] > $maxStudents) {
				echo "<td align='center'  bgcolor='#ffffff'><font color='red'>".$dayNumber[$theDay]."</font></td>";
			} else {
				echo "<td align='center'  bgcolor='#ffffff'>".$dayNumber[$theDay]."</td>";
			}
		}
		$theDay = $theDay + 1;
	}	
	$theDay = 1;
	echo "<td></td></tr>";
echo "<tr><td colspan='8'>&nbsp;</td></tr>";
echo "<tr><td></td><td colspan='6' align='center'><input type='button' class='button' value='&larr; Return to Today' onClick='returnToday()'></td><td></td></tr>";
echo "</table>";

$catn->dbdisconnect();

?>