<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);
$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

echo "<html><head></head><body>";
?>
<script language='javascript'>

//Cancel button hides the Request Student form:
	function hideAddSubject()
	{
		$('#formdisplay').hide();
		$.ajax({
			type: 'POST',
			url: 'pass/templates/pass.php',
			data: { keyid: '<?php echo $keyid; ?>' },
			}).done(
			function(output) { $('#datadisplay').html(output).show(); });
	}

// change the subject
	function addSubject() {
		whichSubject = document.getElementById('theSubject').value;
		whichTrumpDay = document.getElementById('theDay').value;
		if (whichSubject == '') {
			alert('Enter a subject.');
		} else {
			$.ajax ({
				type: 'POST',
				url: 'pass/process/addSubject.php',
				data: { whichSubject: whichSubject, whichTrumpDay: whichTrumpDay, keyid: '<?php echo $keyid; ?>' },
				}).done(
				function(output) { $('#addStudentArea').html(output).show(); });
		}
	}
</script>
<?php
echo "&nbsp;<br>";


echo "<table cellpadding='0' cellspacing='0' border='0' align='center' width='95%' bgcolor='#ededd7'>";
echo "<tr><td></td><td colspan='3'><div id='addStudentArea'></div></td><td></td></tr>";
echo "<tr><td colspan='5' bgcolor='#ffffff' align='center'><font size='+1'><b>Add Subject</b></font></td></tr>";
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
echo "<tr><td width='5%'>&nbsp;</td><td colspan='3' align='center'><b>Subject:</b> <input type='text' id='theSubject'></td></tr>";
echo "<tr><td></td><td colspan='3' align='center'><b>Trump Day</b> <select id='theDay'><option value='6'>None</option><option value='1'>Monday</option><option value='2'>Tuesday</option><option value='3'>Wednesday</option><option value='4'>Thursday</option><option value='5'>Friday</option>";
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
echo "<tr><td></td><td></td><td colspan='2' align='center'><input type='button' value='Cancel' onClick='hideAddSubject()'> &nbsp; <input type='button' value='Add Subject' onClick='addSubject()'></td><td></td><td></td></tr>";
echo "</table>";
echo "</body></html>";
$catn->dbdisconnect();
?>