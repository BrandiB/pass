<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

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