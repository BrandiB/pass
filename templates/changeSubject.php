<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);
$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

$whichTeacher = $catn->getVariable("whichTeacher");

echo "<html><head></head><body>";
?>
<script language='javascript'>

//Cancel button hides the Request Student form:
	function hideChangeSubject()
	{
		$('#formdisplay').hide();
		$.ajax({
			type: 'POST',
			url: 'pass/templates/manageTeachers.php',
			data: { whichTeacher: '<?php echo $whichTeacher; ?>', keyid: '<?php echo $keyid; ?>' },
			}).done(
			function(output) { $('#teacherDisplay').html(output).show(); });
	}

// change the subject
	function changeSubject(whichTeacher) {
		whichSubject = document.getElementById('theSubject').value;
		$.ajax ({
			type: 'POST',
			url: 'pass/process/changeSubject.php',
			data: { whichSubject: whichSubject, whichTeacher: whichTeacher, keyid: '<?php echo $keyid; ?>' },
			}).done(
			function(output) { $('#addStudentArea').html(output).show(); });
	}
</script>
<?php
echo "&nbsp;<br>";


echo "<table cellpadding='0' cellspacing='0' border='0' align='center' width='95%' bgcolor='#ededd7'>";
echo "<tr><td></td><td colspan='3'><div id='addStudentArea'></div></td><td></td></tr>";
echo "<tr><td colspan='5' bgcolor='#ffffff' align='center'><font size='+1'><b>Change Subject</b></font></td></tr>";
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
echo "<tr><td width='5%'>&nbsp;</td><td colspan='3'><select id='theSubject'><option value='0'>Select Subject:</option>";

$query2 = "select subjectID, subjectName from pass.subjects order by subjectName";
$result2 = mysql_query($query2);
while ($row2 = mysql_fetch_array($result2)) {
	echo "<option value='".$row2['subjectID']."'>".$row2['subjectName']."</option>";
}

echo "</select></td><td width='5%'></td></tr>";
echo "<tr><td></td><td></td><td colspan='2'><input type='button' value='Cancel' onClick='hideChangeSubject()'> &nbsp; <input type='button' value='Change Subject' onClick='changeSubject(".$whichTeacher.")'></td><td></td><td></td></tr>";
echo "</table>";
echo "</body></html>";
$catn->dbdisconnect();
?>