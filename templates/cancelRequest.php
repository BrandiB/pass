<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);


$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

$whichRequest = $catn->getVariable("whichRequest");

$whichWeek = $catn->getVariable("whichWeek");

if ($whichWeek == '1') {
	$whichForm = 'viewRoster.php';
} else {
	$whichForm = 'manageRoster.php';
}
echo "<html>";
echo $whichForm;
?>
<script language="JavaScript">
//Cancel button hides the Request Student form:
	function hideStudentRequest() 	{
		$('#formdisplay').hide();
		$.ajax({
			type: 'POST',
			url: 'pass/templates/<?php echo $whichForm; ?>',
			data: { keyid: '<?php echo $keyid; ?>' },
			}).done(
			function(output) { $('#datadisplay').html(output).show(); });
	}
	
	function cancelRequest() {
		$.ajax({
			type: 'POST',
			url: 'pass/process/cancelRequest.php',
			data: { keyid: '<?php echo $keyid; ?>', whichRequest: '<?php echo $whichRequest; ?>' },
			}).done(
			function(output) { $('#requestProcessed').html(output).show(); });
	}
	
</script>
<?
echo "<body>";
echo "&nbsp;<br>";
echo "<table cellpadding='0' cellspacing='0' border='0' align='center' width='95%' bgcolor='#ededd7'>";
echo "<tr><td colspan='4' align='center'><font size='+1' color='#0000FF'>Cancel Request</font></td></tr>";
echo "<tr><td colspan='4'>&nbsp;</td></tr>";
echo "<tr><td width='10%'>&nbsp;</td><td colspan='2'>Are you sure you want to cancel this request?</td><td width='10%'>&nbsp;</td></tr>";
echo "<tr><td colspan='4'>&nbsp;</td></tr>";

$query = "select requestmatrix.dateRequested, user.lastName, user.firstName, category.categoryName from pass.requestmatrix, catnet.user, pass.category where requestmatrix.requestID=".$whichRequest." and user.userID=requestmatrix.studentID and category.categoryID=requestmatrix.categoryID";
$result = mysql_query($query);
$row = mysql_fetch_array($result);

$theDate = strtotime($row['dateRequested']);

echo "<tr><td></td><td width='35%' style='padding-left:20px'><b>Student:</b></td><td width='45%'>".$row['firstName']." ".$row['lastName']."</td><td></td></tr>";
echo "<tr><td></td><td width='35%' style='padding-left:20px'><b>Date:</b></td><td width='45%'>".date('l, F j, Y', $theDate)."</td><td></td></tr>";
echo "<tr><td></td><td width='35%' style='padding-left:20px'><b>Reason:</b></td><td width='45%'>".$row['categoryName']."</td><td></td></tr>";

echo "<tr><td colspan='4'>&nbsp;</td></tr>";
echo "<tr><td></td><td align='right'><input type='button' value='Cancel' onClick='hideStudentRequest()'> &nbsp;</td><td>&nbsp; <input type='button' value='Cancel Request' onClick='cancelRequest()'></td><td></td></tr>";
echo "<tr><td></td><td colspan='2'><div id='requestProcessed'></div></td><td></td></tr>";

echo "</table></body></html>";

$catn->dbdisconnect();

?>