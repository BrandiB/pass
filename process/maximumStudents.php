<?php

/*
	This changes a teacher's preferred maximum number of students.
*/

// FROM HERE (until below where I say UNTIL HERE) is the intranet user authentication stuff which would need changing 

$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$whichProcess = $catn->getVariable("whichProcess");

$catn->setUserInfo($keyid);

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

// UNTIL HERE - note that this includes the database connection, so that would have to be redone as well

echo "<script language='JavaScript'>
	function displayRosterManage() {
		$.ajax({
			type: 'POST',
			url: 'pass/templates/manageRoster.php',
			data: { keyid: '".$keyid."' },
			}).done(
			function(output) { $('#datadisplay').html(output).show(); });
	}
	</script>";


//if Process = 1, display current Max
if ($whichProcess == '1') {
	$query = "select teachers.maxNumberStudents from pass.teachers where teachers.userID =".$catn->userinfo[userID];
	$result = mysql_query($query);
	if (mysql_num_rows($result) != 0) { // if result
	$row = mysql_fetch_array($result);
		echo "<input type='text' id='whichMax' style='width:25px' value='".$row['maxNumberStudents']."'><input type='button' class='button' value='Change Max' onClick='changeMaxStudents()'>";
	} else {
		echo "18";
	}
} else {
// if Process = 2, change Max
	// Is it a number?
	$theMax = $catn->getVariable("theMax");
	if (is_numeric($theMax)) {
		// Is it above 16?
		if ($theMax >= 16) {
			$query2 = "update pass.teachers set maxNumberStudents=".$theMax." where userID=".$catn->userinfo[userID];
			$result2 = mysql_query($query2);		
			echo "<script language='JavaScript'>
				alert('Changed.');
				displayRosterManage();
			</script>";
		} else {
			echo "<script language='JavaScript'>
				alert('Requested number is below 16.');
				displayMaxStudents();
			</script>";

		}
	} else {
		echo "<script language='JavaScript'>
			alert('Please enter a number.');
			displayMaxStudents();
		</script>";
	}
}


$catn->dbdisconnect();
?>
