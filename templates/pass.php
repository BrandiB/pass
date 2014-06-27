<?php
//test of save
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$isAuthorized = $catn->checkADGroups('pass',$catn->keydata['username']);
$isAdmin = $catn->checkADGroups('pass admin',$catn->keydata['username']);
//$isAdmin = 1;

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

	echo "<html><head></head><body>";

	//style
	echo "<style type='text/css'>
		.button2 {
			border: 1px solid #000000;
			background-color: #C0FFFF;
			color: #000000;
			font-weight: bold;
			cursor: pointer;
		}
		.button2:hover {
			background-color: #000000;
			color: #C0FFFF;
			font-weight: bold;
			cursor: pointer;
		}
	</style>";

if ($isAdmin == 1) { // Display administrative page

	echo "<script language='JavaScript'>
	
		function selectTeacher() {
			whichTeacher = document.getElementById('theTeacher').value;
			if (whichTeacher != 0) {
				$.ajax({
					type: 'POST',
					url: 'pass/templates/manageTeachers.php',
					data: { keyid: '".$keyid."', whichTeacher: whichTeacher },
					}).done(
					function(output) { $('#teacherDisplay').html(output).show(); });
			}
		}
		
		function performSync() {
			whichGroup = document.getElementById('whichGroup').value;
			if (whichGroup==0) {
				alert('Select a group to sync!');
			} else {
				$.ajax({
					type: 'POST',
					url: 'pass/process/syncDatabase.php',
					data: { keyid: '".$keyid."', whichGroup: whichGroup },
					}).done(
					function(output) { $('#syncDisplay').html(output).show(); });
			}
		}
	
		function createReport() {
			whichUser = document.getElementById('whichReport').value;
			if (whichUser==0) {
				alert('Select a user!');
			} else {
				$.ajax({
					type: 'POST',
					url: 'pass/templates/createReport.php',
					data: { keyid: '".$keyid."', whichUser: whichUser },
					}).done(
					function(output) { $('#reportDownload').html(output).show(); });
			}
		}
		
		function createGeneral() {
			$.ajax({
					type: 'POST',
					url: 'pass/templates/createGeneral.php',
					data: { keyid: '".$keyid."', whichUser: whichUser },
					}).done(
					function(output) { $('#reportDownload').html(output).show(); });
		}
	
	
		function changeTrumpDay(theSubject) {
			$.ajax({
				type: 'POST',
				url: 'pass/templates/changeTrumpDay.php',
				data: { keyid: '".$keyid."', theSubject: theSubject },
				}).done(
				function(output) { $('#formdisplay').html(output).show(); });
		}
		
		function deleteSubject() {
			someSubject = 0;
			for (i = 0; i < document.getElementsByName('selectedSubject').length; i++) {
				if (document.getElementsByName('selectedSubject')[i].checked) {
					someSubject = document.getElementsByName('selectedSubject')[i].value;
				}
			}
			if (someSubject == 0) {
				alert('Please select a subject to delete.');
			} else {
				if (confirm('Are you sure you want to delete the selected subject?')) {
						$.ajax({
							type: 'POST',
							url: 'pass/process/deleteSubject.php',
							data: { keyid: '".$keyid."', someSubject: someSubject },
							}).done(
							function(output) { $('#subjectProcessArea').html(output).show(); });
				}
			}
		}
		
		function addSubject() {
			$.ajax({
				type: 'POST',
				url: 'pass/templates/addSubject.php',
				data: { keyid: '".$keyid."' },
				}).done(
				function(output) { $('#formdisplay').html(output).show(); });
		}
	
		function moveCategoryUp(whichCategory) {
			moveDirection = 'up';
			$.ajax({
				type: 'POST',
				url: 'pass/process/moveCategory.php',
				data: { keyid: '".$keyid."', moveDirection: moveDirection, whichCategory: whichCategory },
				}).done(
				function(output) { $('#subjectProcessArea').html(output).show(); });
		}
		
		function moveCategoryDown(whichCategory) {
			moveDirection = 'down';
				$.ajax({
					type: 'POST',
					url: 'pass/process/moveCategory.php',
					data: { keyid: '".$keyid."', whichCategory: whichCategory, moveDirection: moveDirection },
					}).done(
					function(output) { $('#subjectProcessArea').html(output).show(); });
		}
		
		function deleteCategory() {
			someCategory = 0;
			for (i = 0; i < document.getElementsByName('selectedCategory').length; i++) {
				if (document.getElementsByName('selectedCategory')[i].checked) {
					someCategory = document.getElementsByName('selectedCategory')[i].value;
				}
			}
			if (someCategory == 0) {
				alert('Please select a category to delete.');
			} else {
				if (confirm('Are you sure you want to delete the selected category?')) {
						$.ajax({
							type: 'POST',
							url: 'pass/process/deleteCategory.php',
							data: { keyid: '".$keyid."', someCategory: someCategory },
							}).done(
							function(output) { $('#subjectProcessArea').html(output).show(); });
				}
			}
		}
		
		function addCategory() {
			$.ajax({
				type: 'POST',
				url: 'pass/templates/addCategory.php',
				data: { keyid: '".$keyid."' },
				}).done(
				function(output) { $('#formdisplay').html(output).show(); });
		}
	</script>";

	echo "<table cellpadding='0' cellspacing='0' border='0' align='center' width='85%' bgcolor='#ededd7'>";
	echo "<tr><td bgcolor='#ffffff' colspan='3' align='center'><font size='+1' color='blue'>Administrative Page</font></td></tr>";
	echo "<tr><td bgcolor='#ffffff' colspan='3'>&nbsp;</td></tr>";
	
	// Manage Teachers Section
	echo "<tr><td bgcolor='#C0FFFF' colspan='3'><font size='+1'><b>Manage Teachers:</b></font></td></tr>";
	echo "<tr><td width='5%'>&nbsp;</td><td width='90%'></td><td width='5%'></td></tr>";
	//Teacher Select Box
	echo "<tr><td></td><td>Teacher: <select id='theTeacher' onChange='selectTeacher()'><option value='0'>Select Teacher:</option>";
	
	$query10 = "select teachers.userID, user.lastName, user.firstName from pass.teachers, catnet.user where user.userID=teachers.userID order by user.lastName, user.firstName";
	$result10 = mysql_query($query10);
	if (mysql_num_rows($result10) != 0) { // teachers were found
		while ($row10 = mysql_fetch_array($result10)) {
			echo "<option value='".$row10['userID']."'>".$row10['lastName'].", ".$row10['firstName']."</option>";
		}
	}	
	echo "</select></td><td></td></tr>";
	echo "<tr><td></td><td><div id='teacherDisplay' style='display:inline'></div></td><td></td></tr>";
	echo "<tr><td colspan='3'>&nbsp;</td></tr>";
	
	// Manage Subjects Section
	echo "<tr><td bgcolor='#C0FFFF' colspan='3'><font size='+1'><b>Manage Subjects:</b></font></td></tr>";
	echo "<tr><td colspan='3'>&nbsp;</td></tr>";
	echo "<tr><td></td><td align='center'><table>";
	$query11 = "select * from pass.subjects, pass.trumpday where subjects.subjectID=trumpday.subjectID order by subjectName";
	$result11 = mysql_query($query11);
	if (mysql_num_rows($result11) == 0) { // where any subjects found?
		echo "No subjects found.";
	} else {
		echo "<tr><td></td><td><u>Subject</u></td><td><u>Trump Day</u></td><td></td></tr>";
		while ($row11 = mysql_fetch_array($result11)) {
			// which day 
			if ($row11['whichDay']==1) {
				$theTrumpDay = "Monday";
			}
			if ($row11['whichDay']==2) {
				$theTrumpDay = "Tuesday";
			}
			if ($row11['whichDay']==3) {
				$theTrumpDay = "Wednesday";
			}
			if ($row11['whichDay']==4) {
				$theTrumpDay = "Thursday";
			}
			if ($row11['whichDay']==5) {
				$theTrumpDay = "Friday";
			}
			if ($row11['whichDay'] != 1 && $row11['whichDay'] != 2 && $row11['whichDay'] != 3 && $row11['whichDay'] != 4 && $row11['whichDay'] != 5) {
				$theTrumpDay = "None";
			}
			echo "<tr><td><input type='radio' name='selectedSubject' value='".$row11['subjectID']."'></td><td><b>".$row11['subjectName']."</b></td><td>".$theTrumpDay."</td><td><input type='button' class='button2' value='Change Trump Day' onClick='changeTrumpDay(".$row11['subjectID'].")'></td></tr>";
		}
	}
	echo "</table></td><td></td></tr>";
	echo "<tr><td></td><td align='center'><input type='button' value='Delete Selected' onClick='deleteSubject()' class='button2'> &nbsp; <input type='button' value='Add Subject' onClick='addSubject()' class='button2'></td><td></td></tr>";
	echo "<tr><td colspan='3'><div id='subjectProcessArea'>&nbsp;</div></td></tr>";
	echo "<tr><td colspan='3'>&nbsp;</td></tr>";
	
	// Manage Request Categories Section
	echo "<tr><td bgcolor='#C0FFFF' colspan='3'><font size='+1'><b>Manage Request Categories:</b></font></td></tr>";
	echo "<tr><td colspan='3'>&nbsp;</td></tr>";
	echo "<tr><td></td><td align='center'><table>";
	$query12 = "select * from pass.category where categoryName not in ('Overflow') order by categoryLevel desc";
	$result12 = mysql_query($query12);
	if (mysql_num_rows($result12) == 0) { // where any subjects found?
		echo "No subjects found.";
	} else {
		echo "<tr><td colspan='3' align='center'><b>Most Important</b></td></tr>";
		$howMany = mysql_num_rows($result12);
		$start = 1;
		while ($row12 = mysql_fetch_array($result12)) {
			if ($start == 1) { // first row
				echo "<tr><td><input type='radio' name='selectedCategory' value='".$row12['categoryID']."'></td><td>".$row12['categoryName']."</td><td><img src='/peds/pass/images/blank-arrow.png'> <a onClick='moveCategoryDown(".$row12['categoryID'].")' style='cursor:pointer'><img src='/peds/pass/images/down-arrow.png'></a></td></tr>";
			} else {
				if ($start == $howMany) { // last row
					echo "<tr><td><input type='radio' name='selectedCategory' id='whichCategory' value='".$row12['categoryID']."'></td><td>".$row12['categoryName']."</td><td><a onClick='moveCategoryUp(".$row12['categoryID'].")' style='cursor:pointer'><img src='/peds/pass/images/up-arrow.png'> <img src='/peds/pass/images/blank-arrow.png'></td></tr>";
				} else { // middle rows
					echo "<tr><td><input type='radio' name='selectedCategory' id='whichCategory' value='".$row12['categoryID']."'></td><td>".$row12['categoryName']."</td><td><a onClick='moveCategoryUp(".$row12['categoryID'].")' style='cursor:pointer'><img src='/peds/pass/images/up-arrow.png'></a> <a onClick='moveCategoryDown(".$row12['categoryID'].")' style='cursor:pointer'><img src='/peds/pass/images/down-arrow.png'></a></td></tr>";
				}
			}
			$start = $start + 1;
		}
		echo "<tr><td colspan='3' align='center'><b>Least Important</b></td></tr>";
	}
	echo "</table></td><td></td></tr>";
	echo "<tr><td></td><td align='center'><input type='button' value='Delete Selected' class='button2' onClick='deleteCategory()'> &nbsp; <input type='button' class='button2' value='Add Category' onClick='addCategory()'></td><td></td></tr>";
	echo "<tr><td colspan='3'>&nbsp;</td></tr>";
	
	// Generate Reports
	echo "<tr><td bgcolor='#C0FFFF' colspan='3'><font size='+1'><b>Generate Reports</b></font></td></tr>";
	echo "<tr><td colspan='3'>&nbsp;</td></tr>";
	echo "<tr><td></td><td align='center'><b>Overall PASS Report:</b> <input type='button' value='Generate Report' onClick='createGeneral()' class='button2'><br>&nbsp;</td><td></td></tr>";
	echo "<tr><td></td><td align='center'><b>Individual User Reports:</b> <select id='whichReport'><option value='0'>Select a User:</option><option value='0' style='background-color:#C0FFFF'>-- TEACHERS --</option>";
	$queryReport = "select user.userID, user.firstName, user.lastName from catnet.user, pass.teachers where user.userID=teachers.userID order by user.lastName, user.firstName";
	$resultReport = mysql_query($queryReport);
	while ($rowReport = mysql_fetch_array($resultReport)) {
		echo "<option value='".$rowReport['userID']."'>".$rowReport['lastName'].", ".$rowReport['firstName']."</option>";
	}
	echo "<option value='0' style='background-color:#C0FFFF'>-- STUDENTS --</option>";
	$queryReport2 = "select user.userID, user.firstName, user.lastName from catnet.user, pass.students where user.userID=students.userID and students.isEnabled=1 order by user.lastName, user.firstName";
	$resultReport2 = mysql_query($queryReport2);
	while ($rowReport2 = mysql_fetch_array($resultReport2)) {
		echo "<option value='".$rowReport2['userID']."'>".$rowReport2['lastName'].", ".$rowReport2['firstName']."</option>";
	}
	echo "</select> <input type='button' value='Generate Report' onClick='createReport()' class='button2'></td><td></td></tr>";
	echo "<tr><td colspan='3' align='center'><div id='reportDownload' style='display:inline'>&nbsp;</div></td></tr>";

	// Sync PASS teachers and students to ADGroups
	echo "<tr><td bgcolor='#C0FFFF' colspan='3'><font size='+1'><b>Sync PASS Teachers and Students to Network</b></font></td></tr>";
	echo "<tr><td colspan='3'>&nbsp;</td></tr>";
	echo "<tr><td colspan='3' align='center'>Perform a sync after a new teacher or a new student has been given their computer login name.</td></tr>";
	echo "<tr><td></td><td align='center'><b>Select a Group:</b> <select id='whichGroup'><option value='0'>Select a Group:</option><option value='CHS Teachers'>Teachers</option><option value='class of 2014'>Class of 2014</option><option value='class of 2015'>Class of 2015</option><option value='class of 2016'>Class of 2016</option><option value='class of 2017'>Class of 2017</option></select> <input type='button' value='Sync' onClick='performSync()' class='button2'></td><td></td></tr>";
	echo "<tr><td></td><td><div id='syncDisplay' style='display:inline'></div></td><td></td></tr>";

	// End
	echo "<tr><td colspan='3'>&nbsp;</td></tr>";
	echo "<tr><td colspan='3'><div id='processArea' style='display:inline'></div></td></tr>";
	echo "</table>";
	echo "&nbsp;<br>";

//VIEW ALL STUDENTS	- use for debugging
//	echo "<table>";
//$queryq = "select * from pass.students, catnet.user where user.userID=students.userID";
//$resultq = mysql_query($queryq);
//while ($rowq = mysql_fetch_array($resultq)) {
//	$studentID = $rowq['userID'];
//	$classOf = $rowq['classOf'];
//	$firstName = $rowq['firstName'];
//	$lastName = $rowq['lastName'];
	
//	$query2q = "select * from pass.homeroom where userIDStudent = ".$studentID;
//	$result2q = mysql_query($query2q);
//	if (mysql_num_rows($result2q) != 0) {
//		while ($row2q = mysql_fetch_array($result2q)) {
//			$teacherIDq = $row2q['userIDTeacher'];
//		}
//	} else {
//			$queryx = "insert into pass.homeroom values('','".$studentID."','2076')";
//			mysql_query($queryx);
//			$teacherIDq = 0;
//	}
//	echo "<tr><td>".$studentID."</td><td>".$firstName." ".$lastName."</td><td>".$classOf."</td><td>".$teacherIDq."</td></tr>";
//}
//echo "</table>";
	
} 

if ($isAuthorized || $isAdmin) { // Display user page

if ($isAdmin || (strtolower($catn->keydata[username]) == 'substitute')) { // Is this person the substitute teacher or administrator?

	echo "<script language='JavaScript'>
		function viewRoster() {
			whichTeacher = document.getElementById('rosterSelect').value;
			$.ajax({
				type: 'POST',
				url: 'pass/templates/viewAnothersRoster.php',
				data: { keyid: '".$keyid."', whichTeacher: whichTeacher },
				}).done(
				function(output) { $('#rosterList').html(output).show(); });
		}
	</script>";
	
	echo "<table cellpadding='0' cellspacing='0' border='0' align='center' width='85%' bgcolor='#ededd7'>";
	echo "<tr><td colspan='4' bgcolor='#ffffff' align='center'><font size='+1' color='blue'>Today's PASS Rosters</font></td></tr>";
	echo "<tr><td colspan='4' bgcolor='#ffffff'>&nbsp;</td></tr>";
	echo "<tr><td colspan='4' bgcolor='#c0ffff'><font size='+1'><b>View Roster:</font></b></td></tr>";
	echo "<tr><td width='5%'>&nbsp;</td><td align='right'>Select Teacher: &nbsp;</td><td>&nbsp; <select id='rosterSelect' onChange='viewRoster()'><option value='0'>Select Teacher:</option>";
	$query="select user.firstName, user.lastName, user.userID from pass.teachers, catnet.user where teachers.userID=user.userID order by user.lastName, user.firstName ";
	$result=mysql_query($query);
	while ($row = mysql_fetch_array($result)) {
		echo "<option value='".$row['userID']."'>".$row['lastName'].", ".$row['firstName']."</option>";
	}
	echo "</select></td><td width='5%'>&nbsp;</td></tr>";
	echo "<tr><td></td><td colspan='2'><div id='rosterList'></div></td><td></td>";
	echo "</table><br><div id='rosterListProcess'></div>&nbsp;";
	
}  

// all other teachers
if (!(strtolower($catn->keydata[username]) == 'substitute')) {
	$today = date("F j, Y");
	$todayData = date("Y-m-d");
	$nextRoster = date("F j, Y", strtotime('next Monday'));
	$outgoingStudents = 0;

	
	// Manage Roster Button
	echo "<script language='javascript'>
		function displayRosterManage() {
			$.ajax({
				type: 'POST',
				url: 'pass/templates/manageRoster.php',
				data: { keyid: '".$keyid."' },
				}).done(
				function(output) { $('#datadisplay').html(output).show(); });
		}

		function viewCurrentWeek() {
			$.ajax({
				type: 'POST',
				url: 'pass/templates/viewRoster.php',
				data: { keyid: '".$keyid."' },
				}).done(
				function(output) { $('#datadisplay').html(output).show(); });
		}
	
		function makeAbsent(whichStudent) {
			$.ajax({
				type: 'POST',
				url: 'pass/process/changeAbsence.php',
				data: { keyid: '".$keyid."', whichStudent: whichStudent, whichTeacher: '".$whichTeacher."', whichDay: '".$todayData."', whichProcess: 'A', whichUpdate: 'o' },
				}).done(
				function(output) { $('#workArea2').html(output).show(); });
		}
		
		function makePresent(whichStudent) {
			$.ajax({
				type: 'POST',
				url: 'pass/process/changeAbsence.php',
				data: { keyid: '".$keyid."', whichStudent: whichStudent, whichTeacher: '".$whichTeacher."', whichDay: '".$todayData."', whichProcess: 'P', whichUpdate: 'o' },
				}).done(
				function(output) { $('#workArea2').html(output).show(); });
		}
	</script>";
	
	//NORMAL HOMEROOM ROSTER for the day
	$query = "select * from pass.homeroom, catnet.user where homeroom.userIDTeacher=".$catn->userinfo[userID]." and user.userID=homeroom.userIDStudent order by user.lastName, user.firstName";
	$result = mysql_query($query);
	
	
	echo "<table cellpadding='0' cellspacing='0' border='0' align='center' width='85%' bgcolor='#ededd7'>";
	echo "<tr><td colspan='6' bgcolor='#ffffff' align='center'><font size='+1' color='blue'>Teacher Page for ".$catn->userinfo[firstName]." ".$catn->userinfo[lastName]."</font></td></tr>";
	echo "<tr><td bgcolor='#ffffff' width='4%'></td><td bgcolor='#ffffff' width='23%'><font color='#0000FF'><b>".$today."</b></font></td><td bgcolor='#ffffff' width='23%'>&nbsp;</td><td bgcolor='#ffffff' colspan='2' align='right'>";
	if (mysql_num_rows($result) !=0) { // if results show manage roster option
		echo "<input type='button' class='button2' value='Manage This Week' onClick='viewCurrentWeek()'> &nbsp; <input type='button' class='button2' value='Manage Next Week' onClick='displayRosterManage()'>";
	} else {
		echo "<input type='button' class='button2' value='Manage This Week' onClick='viewCurrentWeek()'> &nbsp; <input type='button' class='button2' style='width:350px' value='Manage Next Week' onClick='displayRosterManage()'>";
	}
	echo "</td><td bgcolor='#ffffff' width='4%'></td></tr>";
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
						$whichTeacher[$columnCounter]=$row2['teacherID'];
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
					if ($isAbsent == '1') {
							echo "<td bgcolor='#ffffff'><font color='red'><b>Absent</b></font> <input type='button' value='Change' onClick='makePresent(".$row['userIDStudent'].")'></td>";
						} else {
							echo "<td bgcolor='#ffffff'>Present <input type='button' value='Change' onClick='makeAbsent(".$row['userIDStudent'].")'></td>";
						}
					$columnCounter = $columnCounter + 1;
			} else { //odd - right
					echo "<td bgcolor='#ffffff' style='padding-left:10px'>".$exceptionFront.$student[$columnCounter].$exceptionBack."</td><td bgcolor='#ffffff'>".$exceptionFront.$exceptionText[$columnCounter].$exceptionBack."</td>";
					if ($isAbsent == '1') {
							echo "<td bgcolor='#ffffff'><font color='red'><b>Absent</b></font> <input type='button' value='Change' onClick='makePresent(".$row['userIDStudent'].")'></td>";
						} else {
							echo "<td bgcolor='#ffffff'>Present <input type='button' value='Change' onClick='makeAbsent(".$row['userIDStudent'].")'></td>";
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
	$query3="select user.userID, user.firstName, user.lastName, category.categoryName, homeroom.userIDTeacher from pass.requestmatrix, catnet.user, pass.category, pass.homeroom where requestmatrix.teacherID=".$catn->userinfo[userID]." and requestmatrix.dateRequested='".$todayData."' and user.userID=requestMatrix.studentID and category.categoryID=requestMatrix.categoryID and homeroom.userIDStudent=requestMatrix.studentID order by user.lastName, user.firstName";
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
				$query5="select category.categoryName, user.lastName, user.firstName from pass.category, catnet.user where category.categoryID=".$whichCategory[$i]." and user.userID=".$whichTeacher[$i];
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

	echo "</table><div id='workArea2'></div>";
	echo "</body></html>";

}
}
$catn->dbdisconnect();

?>