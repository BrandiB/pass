<?php

/*
	Sets a teacher to receive overflow students or not.
*/

// FROM HERE (until below where I say UNTIL HERE) is the intranet user authentication stuff which would need changing 

$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

// UNTIL HERE - note that this includes the database connection, so that would have to be redone as well

$whichTeacher = $catn->getVariable("whichTeacher");
$theSubject = $catn->getVariable("whichSubject");

// which overflow option do they currently have?
$query = "select isOverflow from pass.teachers where userID=".$whichTeacher;
$result = mysql_query($query);
$row = mysql_fetch_array($result);
$isOverflow = $row['isOverflow'];

if ($isOverflow == 0) {
	$query2 = "update pass.teachers set isOverflow=1 where userID=".$whichTeacher;
} else {
	$query2 = "update pass.teachers set isOverflow=0 where userID=".$whichTeacher;
}
mysql_query($query2);

?>
<html><body>
<script language='JavaScript'>
	alert('Overflow changed.');
	$.ajax({
		type: 'POST',
		url: 'pass/templates/manageTeachers.php',
		data: { keyid: '<?php echo $keyid; ?>', whichTeacher: '<?php echo $whichTeacher; ?>' },
		}).done(
		function(output) { $('#teacherDisplay').html(output).show(); });
	</script>
	</body></html>
<?php
$catn->dbdisconnect();
?>
