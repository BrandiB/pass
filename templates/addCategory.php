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
	function hideAddCategory()
	{
		$('#formdisplay').hide();
		$.ajax({
			type: 'POST',
			url: 'pass/templates/pass.php',
			data: { keyid: '<?php echo $keyid; ?>' },
			}).done(
			function(output) { $('#datadisplay').html(output).show(); });
	}

// add the category
	function addCategory() {
		whichCategory = document.getElementById('theCategory').value;
		if (whichSubject == '') {
			alert('Enter a category.');
		} else {
			$.ajax ({
				type: 'POST',
				url: 'pass/process/addCategory.php',
				data: { whichCategory: whichCategory, keyid: '<?php echo $keyid; ?>' },
				}).done(
				function(output) { $('#addStudentArea').html(output).show(); });
		}
	}
</script>
<?php
echo "&nbsp;<br>";


echo "<table cellpadding='0' cellspacing='0' border='0' align='center' width='95%' bgcolor='#ededd7'>";
echo "<tr><td></td><td colspan='3'><div id='addStudentArea'></div></td><td></td></tr>";
echo "<tr><td colspan='5' bgcolor='#ffffff' align='center'><font size='+1'><b>Add Category</b></font></td></tr>";
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
echo "<tr><td width='5%'>&nbsp;</td><td colspan='3' align='center'><b>Category:</b> <input type='text' id='theCategory'></td></tr>";
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
echo "<tr><td></td><td></td><td colspan='2' align='center'><input type='button' value='Cancel' onClick='hideAddCategory()'> &nbsp; <input type='button' value='Add Category' onClick='addCategory()'></td><td></td><td></td></tr>";
echo "</table>";
echo "</body></html>";
$catn->dbdisconnect();
?>