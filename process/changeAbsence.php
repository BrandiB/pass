<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);
$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

$whichStudent = $catn->getVariable("whichStudent");
$whichDay = $catn->getVariable("whichDay");
$whichProcess = $catn->getVariable("whichProcess"); // n = viewAnothersRoster, o = viewRoster
$whichUpdate = $catn->getVariable("whichUpdate"); // A = Absent, P = Present
$whichTeacher = $catn->getVariable("whichTeacher");

if ($whichProcess == 'A') {
	$query = "insert into pass.absentmatrix values('','".$whichStudent."','".$whichDay."')";
	mysql_query($query);
	if ($whichUpdate == 'n') {
	echo "<script language='JavaScript'>
		$.ajax({
				type: 'POST',
				url: 'pass/templates/viewAnothersRoster.php',
				data: { keyid: '".$keyid."', whichTeacher: ".$whichTeacher." },
				}).done(
				function(output) { $('#rosterList').html(output).show(); });
	</script>";
	} else {
		echo "<script language='JavaScript'>
		$.ajax({
				type: 'POST',
				url: 'pass/templates/pass.php',
				data: { keyid: '".$keyid."' },
				}).done(
				function(output) { $('#datadisplay').html(output).show(); });
		</script>";
	}
} else {
	$query2 = "delete from pass.absentmatrix where studentID = ".$whichStudent." and dateAbsent = '".$whichDay."'";
	mysql_query($query2);
	if ($whichUpdate == 'n') {
	echo "<script language='JavaScript'>
		$.ajax({
				type: 'POST',
				url: 'pass/templates/viewAnothersRoster.php',
				data: { keyid: '".$keyid."', whichTeacher: ".$whichTeacher." },
				}).done(
				function(output) { $('#rosterList').html(output).show(); });
	</script>";
	} else {
		echo "<script language='JavaScript'>
		$.ajax({
				type: 'POST',
				url: 'pass/templates/pass.php',
				data: { keyid: '".$keyid."' },
				}).done(
				function(output) { $('#datadisplay').html(output).show(); });
		</script>";
	}
}
?>