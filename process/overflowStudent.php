<?php

$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();
$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);
$whichWeek = $catn->getVariable("whichWeek");

//Times
if ($whichWeek == 1) {
	$whichDay[1] = date("Y-m-d", strtotime('Monday this week'));
	$whichDay[2] = date("Y-m-d", strtotime('Tuesday this week'));
	$whichDay[3] = date("Y-m-d", strtotime('Wednesday this week'));
	$whichDay[4] = date("Y-m-d", strtotime('Thursday this week'));
	$whichDay[5] = date("Y-m-d", strtotime('Friday this week'));
	$returnForm = 'viewRoster.php';
} else {
	$whichDay[1] = date("Y-m-d", strtotime('Monday next week'));
	$whichDay[2] = date("Y-m-d", strtotime('Tuesday next week'));
	$whichDay[3] = date("Y-m-d", strtotime('Wednesday next week'));
	$whichDay[4] = date("Y-m-d", strtotime('Thursday next week'));
	$whichDay[5] = date("Y-m-d", strtotime('Friday next week'));
	$returnForm = 'manageRoster.php';
}

$theDay = $catn->getVariable("theDay");
$theTeacher = $catn->getVariable("theTeacher");
$whichStudent = $catn->getVariable("whichStudent");

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

// Find out which categoryID is Overflow (in case it gets changed)
$query = "select categoryID from pass.category where categoryName = 'Overflow'";
$result = mysql_query($query);
$row = mysql_fetch_array($result);
$categoryID = $row['categoryID'];

// insert overflow request
$query2 = "insert into pass.requestmatrix values('', '".$whichStudent."', '".$categoryID."', '0', '".$whichDay[$theDay]."', '".$theTeacher."')";
mysql_query($query2);

$catn->dbdisconnect();
?>

<script language="JavaScript">
	alert('Overflow Request Submitted!');
	$.ajax({
			type: 'POST',
			url: 'pass/templates/<?php echo $returnForm; ?>',
			data: { keyid: '<?php echo $keyid; ?>' },
			}).done(
			function(output) { $('#datadisplay').html(output).show(); });
</script>