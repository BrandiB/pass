<?php

/*
	This adjusts a category's position in the list of categories, which affects request priority.
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

$whichCategory = $catn->getVariable("whichCategory");
$moveDirection = $catn->getVariable("moveDirection");

// This is the one we've picked:
$query = "select * from pass.category where categoryID=".$whichCategory;
$result = mysql_query($query);
$row = mysql_fetch_array($result);
$currentCategoryLevel = $row['categoryLevel'];

// Switch with this category:
if ($moveDirection == 'up') {
	$switchCategoryLevel = $currentCategoryLevel + 5;
} else {
	$switchCategoryLevel = $currentCategoryLevel - 5;
}
$query2 = "select * from pass.category where categoryLevel=".$switchCategoryLevel;
$result2 = mysql_query($query2);
$row2 = mysql_fetch_array($result2);

// Perform the swap:
$query3 = "update pass.category set categoryLevel=".$currentCategoryLevel." where categoryID=".$row2['categoryID'];
$query4 = "update pass.category set categoryLevel=".$switchCategoryLevel." where categoryID=".$whichCategory;
mysql_query($query3);
mysql_query($query4);
	?>
		<html><body>
		<script language='JavaScript'>
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
