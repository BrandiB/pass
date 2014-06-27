<?php

/*
	This adds a new subject. It takes in whichSubject - the name of the subject - and whichTrumpDay - the day of the week when this subject will have trumping ability.
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

$whichSubject = $catn->getVariable("whichSubject");
$whichTrumpDay = $catn->getVariable("whichTrumpDay");

	$query5 = "select * from pass.subjects where subjectName='".$whichSubject."'";
	$result5 = mysql_query($query5);
	if (mysql_num_rows($result5) != 0) {
		echo "<font color='red'> This subject already exists.</font>";
	} else {
		$query = "insert into pass.subjects values('','".$whichSubject."')";
		mysql_query($query);
		$query2 = "select subjectID from pass.subjects where subjectName='".$whichSubject."'";
		$result2 = mysql_query($query2);
		$row2 = mysql_fetch_array($result2);
		$query4 = "insert into pass.trumpday values('','".$row2['subjectID']."','".$whichTrumpDay."')";
		mysql_query($query4);	
		?>
		<html><body>
		<script language='JavaScript'>
		alert('Subject added.');
		$.ajax({
			type: 'POST',
			url: 'pass/templates/pass.php',
			data: { keyid: '<?php echo $keyid; ?>' },
			}).done(
			function(output) { $('#datadisplay').html(output).show(); });
		$('#formdisplay').hide();
		</script>
		</body></html>
		<?php
	}
$catn->dbdisconnect();
?>
