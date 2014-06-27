<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

$whichDay = $catn->getVariable("whichDay");
$theSubject = $catn->getVariable("whichSubject");

// update Subject
	$query = "update pass.trumpday set whichDay=".$whichDay." where subjectID=".$theSubject;
	mysql_query($query);

?>
<html><body>
<script language='JavaScript'>
	alert('Trump day changed.');
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
$catn->dbdisconnect();
?>