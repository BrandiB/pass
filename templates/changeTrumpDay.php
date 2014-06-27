<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);
$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

$whichSubject = $catn->getVariable("theSubject");
echo "<html><head></head><body>";
?>
<script language='javascript'>

//Cancel button hides the Request Student form:
	function hideChangeTrumpDay()
	{
		$('#formdisplay').hide();
		$.ajax({
			type: 'POST',
			url: 'pass/templates/pass.php',
			data: { keyid: '<?php echo $keyid; ?>' },
			}).done(
			function(output) { $('#datadisplay').html(output).show(); });
	}

// change the trump day
	function changeTrumpDay(whichSubject) {
		whichDay = document.getElementById('theDay').value;
		$.ajax ({
			type: 'POST',
			url: 'pass/process/changeTrumpDay.php',
			data: { whichSubject: whichSubject, whichDay: whichDay, keyid: '<?php echo $keyid; ?>' },
			}).done(
			function(output) { $('#addStudentArea').html(output).show(); });
	}
</script>
<?php
echo "&nbsp;<br>";
echo "<table cellpadding='0' cellspacing='0' border='0' align='center' width='95%' bgcolor='#ededd7'>";
echo "<tr><td></td><td colspan='3'><div id='addStudentArea'></div></td><td></td></tr>";
echo "<tr><td colspan='5' bgcolor='#ffffff' align='center'><font size='+1'><b>Change Trump Day</b></font></td></tr>";
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
$query2 = "select subjectName from pass.subjects where subjectID=".$whichSubject;
$result2 = mysql_query($query2);
$row2 = mysql_fetch_array($result2);
echo "<tr><td></td><td colspan='3' align='center'><b>Subject:</b> ".$row2['subjectName']."</td><td></td></tr>"; 
echo "<tr><td width='5%'>&nbsp;</td><td colspan='3' align='center'><b>Trump Day:</b> <select id='theDay'><option value='0'>Select Day:</option>";
echo "<option value='1'>Monday</option><option value='2'>Tuesday</option><option value='3'>Wednesday</option><option value='4'>Thursday</option><option value='5'>Friday</option>";
echo "</select></td><td width='5%'></td></tr>";
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
echo "<tr><td></td><td colspan='3' align='center'><input type='button' value='Cancel' onClick='hideChangeTrumpDay()'> &nbsp; <input type='button' value='Change Trump Day' onClick='changeTrumpDay(".$whichSubject.")'></td><td></td><td></td></tr>";
echo "</table>";
echo "</body></html>";
$catn->dbdisconnect();
?>