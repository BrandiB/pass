<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$theDay = $catn->getVariable("theDay");
$whichStudent = $catn->getVariable("whichStudent");
$whichWeek = $catn->getVariable("whichWeek");

//Times
if ($whichWeek == 1) {
	$tableDay[1] = date("l, F j, Y", strtotime('Monday this week'));
	$tableDay[2] = date("l, F j, Y", strtotime('Tuesday this week'));
	$tableDay[3] = date("l, F j, Y", strtotime('Wednesday this week'));
	$tableDay[4] = date("l, F j, Y", strtotime('Thursday this week'));
	$tableDay[5] = date("l, F j, Y", strtotime('Friday this week'));
	$returnForm = 'viewRoster.php';
} else {
	$tableDay[1] = date("l, F j, Y", strtotime('Monday next week'));
	$tableDay[2] = date("l, F j, Y", strtotime('Tuesday next week'));
	$tableDay[3] = date("l, F j, Y", strtotime('Wednesday next week'));
	$tableDay[4] = date("l, F j, Y", strtotime('Thursday next week'));
	$tableDay[5] = date("l, F j, Y", strtotime('Friday next week'));
	$returnForm = 'manageRoster.php';
}

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

echo "<html><body>";

?>
<script language='javascript'>
	function hideStudentRequest() {
		$.ajax({
			type: 'POST',
			url: 'pass/templates/<?php echo $returnForm; ?>',
			data: { keyid: '<?php echo $keyid; ?>' },
			}).done(
			function(output) { $('#datadisplay').html(output).show(); });
			
	}
	
	function overflowStudent(whichStudent) {
		// Do we have a teacher selected?
		if (document.getElementById('whichTeacher').value==0) {
			alert ('Please select an overflow destination.');
		} else {
			theDay = <?php echo $theDay; ?>;
			theTeacher = document.getElementById('whichTeacher').value;
			$.ajax({
				type: 'POST',
				url: 'pass/process/overflowStudent.php',
				data: { keyid: '<?php echo $keyid; ?>', whichStudent: whichStudent, theDay: theDay, theTeacher: theTeacher, whichWeek: <?php echo $whichWeek; ?> },
				}).done(
				function(output) { $('#overflowProcess').html(output).show(); });
		}
	}
</script>

<?php

echo "&nbsp;<br>";
echo "<table cellpadding='0' cellspacing='0' border='0' align='center' width='95%' bgcolor='#ededd7'>";
echo "<tr><td colspan='4' align='center'><font size='+1' color='#0000FF'>Overflow Student</font></td></tr>";
echo "<tr><td colspan='4'>&nbsp;</td></tr>";
echo "<tr><td width='10%'>&nbsp;</td><td colspan='2'>Overflow this student?</td><td width='10%'>&nbsp;</td></tr>";
echo "<tr><td colspan='4'>&nbsp;</td></tr>";

$query = "select firstName, lastName from catnet.user where userID=".$whichStudent;
$request = mysql_query($query);
$row = mysql_fetch_array($request);

echo "<tr><td></td><td width='35%' style='padding-left:20px'><b>Student:</b></td><td width='45%'>".$row['firstName']." ".$row['lastName']."</td><td></td></tr>";
echo "<tr><td></td><td width='35%' style='padding-left:20px'><b>Date:</b></td><td width='45%'>".$tableDay[$theDay]."</td><td></td></tr>";
echo "<tr><td></td><td width='35%' style='padding-left:20px'><b>Overflow Destination:</b></td><td width='45%'><select id='whichTeacher'><option value='0'>Select a Teacher:</option>";

// Which teachers take overflow students?
$query2 = "select user.firstName, user.lastName, teachers.userID from pass.teachers, catnet.user where teachers.isOverflow=1 and user.userID=teachers.userID order by user.lastName, user.firstName";
$result2 = mysql_query($query2);
if (mysql_num_rows($result2) != 0) { // throw an error if overflow teachers can't be found
	while ($row2=mysql_fetch_array($result2)) {
		$theTeacher = $row2['lastName'].", ".$row2['firstName'];
		$theTeacherID = $row2['userID'];
		echo "<option value='".$theTeacherID."'>".$theTeacher."</option>";
	}
} else {
	echo "<script language='JavaScript'>alert('Could not find overflow teachers.');hideStudentRequest();</script>";
}
echo "</select></td><td></td></tr>";
echo "<tr><td colspan='4'>&nbsp;</td></tr>";

echo "<tr><td></td><td align='right'><input type='button' value='Cancel' onClick='hideStudentRequest()'> &nbsp;</td><td>&nbsp; <input type='button' value='Overflow Student' onClick='overflowStudent(".$whichStudent.")'></td><td></td></tr>";
echo "<tr><td colspan='4'><div id='overflowProcess'></div></td></tr>";
echo "</table></body></html>";

$catn->dbdisconnect();
?>