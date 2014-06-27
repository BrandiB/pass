<?php

/*
	deletes a request category from the database
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

$someCategory = $catn->getVariable("someCategory");

	$query = "delete from pass.category where categoryID=".$someCategory;
	mysql_query($query);
	$query6 = "select * from pass.category where categoryName not in ('Overflow') order by categoryLevel desc";
		$result6 = mysql_query($query6);
		// Move level values down now that we're short one (one at bottom = 5)
		$theMultiplier = mysql_num_rows($result6);
		while ($row6 = mysql_fetch_array($result6)) {
			$whatLevel = $theMultiplier * 5;
			$query7 = "update pass.category set categoryLevel=".$whatLevel." where categoryID=".$row6['categoryID'];
			mysql_query($query7);
			$theMultiplier = $theMultiplier - 1;
		}
?>
<html><body>
<script language='JavaScript'>
	alert('Category deleted.');
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
