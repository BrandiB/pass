<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

$whichTeacher = $catn->getVariable("whichTeacher");
$theSubject = $catn->getVariable("whichSubject");

// update Subject
	$query = "update pass.teachers set subjectID=".$theSubject." where userID=".$whichTeacher;
	mysql_query($query);

?>
<html><body>
<script language='JavaScript'>
	alert('Subject changed.');
	$.ajax({
		type: 'POST',
		url: 'pass/templates/manageTeachers.php',
		data: { keyid: '<?php echo $keyid; ?>', whichTeacher: '<?php echo $whichTeacher; ?>' },
		}).done(
		function(output) { $('#teacherDisplay').html(output).show(); });
	$('#formdisplay').hide();
	</script>
	</body></html>
<?php
$catn->dbdisconnect();
?>