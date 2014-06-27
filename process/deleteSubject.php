<?php

/*
	Deletes a subject from the database.
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

$someSubject = $catn->getVariable("someSubject");

	$query = "delete from pass.subjects where subjectID=".$someSubject;
	mysql_query($query);
?>
<html><body>
<script language='JavaScript'>
	alert('Subject deleted.');
	$.ajax({
		type: 'POST',
		url: 'pass/templates/pass.php',
		data: { keyid: '<?php echo $keyid; ?>' },
		}).done(
		function(output) { $('#datadisplay').html(output).show(); });
	</script>
	</body></html>
<?php
$catn->dbdisconnect();
?>
