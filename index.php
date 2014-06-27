<!-- All this does is start up PASS within our district's existing intranet framework.  The actual program ("pass/templates/pass.php" <-- go here) is loaded into #datadsiplay, the main display frame in that framework.-->
<!-- The catnetapps.inc file (not included here) passes along the logged-in user session info-->

<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;
// instantiate class
$catn= NEW CatnetApps();
$keyid = $catn->getVariable("keyid");
echo "
<script language='javascript'>
	function displayPass()
	{
		$.ajax({ 
			type: 'POST', 
			url: 'pass/templates/pass.php', 
			data: { keyid: '".$keyid."' },
			}).done(
			function(output) { $('#datadisplay').html(output).show();  });
	}
</script>";
echo "<script>$('#displayheader').html('";
echo "<table width=\"100%\" cellpadding=0 cellspacing=0 border=0>";
echo "<tr><td align=\"center\"><font color=\"blue\" size=\"4\">PASS</font>";
echo "</td></table>');
	$('#calpanel').hide();
	$('#datapanel').hide();
	$('#leftpanel').hide();
	displayPass();
</script>";

?>
