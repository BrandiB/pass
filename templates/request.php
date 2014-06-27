<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$whichWeek = $catn->getVariable("whichWeek");

// set up times 
if ($whichWeek == '1') {
	$tableDay[1] = date("n/j", strtotime('Monday this week')); //labels for checkboxes
	$tableDay[2] = date("n/j", strtotime('Tuesday this week'));
	$tableDay[3] = date("n/j", strtotime('Wednesday this week'));
	$tableDay[4] = date("n/j", strtotime('Thursday this week'));
	$tableDay[5] = date("n/j", strtotime('Friday this week'));
	$whichDay[1] = date("Y-m-d", strtotime('Monday this week')); // values for database
	$whichDay[2] = date("Y-m-d", strtotime('Tuesday this week'));
	$whichDay[3] = date("Y-m-d", strtotime('Wednesday this week'));
	$whichDay[4] = date("Y-m-d", strtotime('Thursday this week'));
	$whichDay[5] = date("Y-m-d", strtotime('Friday this week'));
	$whichForm = 'viewRoster.php';
} else {
	$tableDay[1] = date("n/j", strtotime('Monday next week')); //labels for checkboxes
	$tableDay[2] = date("n/j", strtotime('Tuesday next week'));
	$tableDay[3] = date("n/j", strtotime('Wednesday next week'));
	$tableDay[4] = date("n/j", strtotime('Thursday next week'));
	$tableDay[5] = date("n/j", strtotime('Friday next week'));
	$whichDay[1] = date("Y-m-d", strtotime('Monday next week')); // values for database
	$whichDay[2] = date("Y-m-d", strtotime('Tuesday next week'));
	$whichDay[3] = date("Y-m-d", strtotime('Wednesday next week'));
	$whichDay[4] = date("Y-m-d", strtotime('Thursday next week'));
	$whichDay[5] = date("Y-m-d", strtotime('Friday next week'));
	$whichForm = 'manageRoster.php';
}

echo "<html><head></head><body>";
?>
<script language='javascript'>

//Cancel button hides the Request Student form:
	function hideStudentRequest()
	{
		$('#formdisplay').hide();
		$.ajax({
			type: 'POST',
			url: 'pass/templates/<?php echo $whichForm; ?>',
			data: { keyid: '<?php echo $keyid; ?>' },
			}).done(
			function(output) { $('#datadisplay').html(output).show(); });
	}

	function getTeachers() {
		$.ajax({ 
					type: 'POST', 
					url: 'pass/process/groupMembers.php', 
					data: { groupid: '14', outputType: 'selectListHomeroom' },
					}).done(
					function(output) { $('#StudentSearchOption').html(output).show();  });
	}
	
	function getHomeroomStudents() {
		theTeacher = document.getElementById("StudentSearchCriteria").value;
		$.ajax({
					type: 'POST',
					url: 'pass/process/homeroomQuery.php',
					data: { teacher: theTeacher, outputType: 'selectBox' },
					}).done(
					function(output) { $('#StudentSearchResults').html(output).show(); });
	}
	
	function searchByGrade() {
		groupid = document.getElementById("StudentSearchCriteria").value;
		if (groupid == 0) {
			alert ('Please select a grade!');
		} else {
		$.ajax({ 
					type: 'POST', 
					url: 'pass/process/groupMembers.php', 
					data: { groupid: groupid, outputType: 'selectListStudents' },
					}).done(
					function(output) { $('#StudentSearchResults').html(output).show();  });
		}
	}
	
	//Display appropriate search box based on search type:
	function showSearchBox()
	{
		searchType = document.getElementById("StudentSearchOptionSelect").value;
		switch (searchType) {
			case "0":   // Nothing selected.
				document.getElementById("StudentSearchResults").innerHTML = "";
				document.getElementById("StudentSearchOption").innerHTML = "";
				break;
			case "1":  // Group Search
				document.getElementById("StudentSearchResults").innerHTML = "";
				groupSearch = "<select id='StudentSearchCriteria' onChange='searchByGrade()'><option value='0' selected>Select a Grade:</option><option value='4'>2014</option><option value='5'>2015</option><option value='6'>2016</option><option value='7'>2017</option></select>";
				document.getElementById("StudentSearchOption").innerHTML = groupSearch;
				break;
			case "2": // Homeroom Search
				document.getElementById("StudentSearchResults").innerHTML = "";
				getTeachers();
				break;
			case "3": // Name Search
				document.getElementById("StudentSearchResults").innerHTML = "";
				nameSearch = "First Name: <input type='text' id='studentFirstName' size='15'><br>Last Name: <input type='text' id='studentLastName' size='15'><br><input type='button' onClick='searchForName()' value='Search'> ";
				document.getElementById("StudentSearchOption").innerHTML = nameSearch;
				break;
			}
	}	
	
	// perform a name search for a particular student
	function searchForName() {
		searchLastName = document.getElementById("studentLastName").value;
		searchFirstName = document.getElementById("studentFirstName").value;
		// is there something to search for?
		if (searchLastName == '' && searchFirstName == '') {
			alert('Enter a name into the First Name and/or Last Name box.'); 
		} else {
			$.ajax ({
					type: 'POST', 
					url: 'pass/process/nameSearch.php', 
					data: { lastName: searchLastName, firstName: searchFirstName },
					}).done(
					function(output) { $('#StudentSearchResults').html(output).show();  });
		}
	}
	
	// check to see that a student is selected, as well as a category and date, then check to see if there is a previously made request for that student on that date by another teacher, if not add request 
	function addRequest() {
		if (document.getElementById('theStudent')) { // does the student field exist yet?
			whichStudent = document.getElementById('theStudent').value;
			if (whichStudent == '0') { // the student field exists, but has a name been selected yet?
				alert ('Please select a student!');
			} else {
				//the student has been select, so now check if a categroy has been selected
				if (document.getElementById('RequestCategory').value == '0') {
					alert ('Please select a category!');
				} else {
					whichCategory = document.getElementById('RequestCategory').value;
					//the student and category have been selected; has at least one day been selected?
					if (document.getElementById('requestMonday').checked) {
						requestMonday = document.getElementById('requestMonday').value;
					} else {
						requestMonday = 0;
					}
					if (document.getElementById('requestTuesday').checked) {
						requestTuesday = document.getElementById('requestTuesday').value;
					} else {
						requestTuesday = 0;
					}
					if (document.getElementById('requestWednesday').checked) {
						requestWednesday = document.getElementById('requestWednesday').value;
					} else {
						requestWednesday = 0;
					}
					if (document.getElementById('requestThursday').checked) {
						requestThursday = document.getElementById('requestThursday').value;
					} else {
						requestThursday = 0;
					}
					if (document.getElementById('requestFriday').checked) {
						requestFriday = document.getElementById('requestFriday').value;
					} else {
						requestFriday = 0;
					}
					if (requestMonday == 0 && requestTuesday == 0 && requestWednesday == 0 && requestThursday == 0 && requestFriday == 0) {
						alert('Please select a date!');
					} else {
						//call the php file to check for outstanding requests and to submit request to database
						$.ajax ({
							type: 'POST', 
							url: 'pass/process/insertRequest.php', 
							data: { whichWeek: <?php echo $whichWeek; ?>, whichStudent: whichStudent, whichCategory: whichCategory, requestMonday: requestMonday, requestTuesday: requestTuesday, requestWednesday: requestWednesday, requestThursday: requestThursday, requestFriday: requestFriday, keyid: '<?php echo $keyid; ?>' },
							}).done(
							function(output) { $('#requestProccessed').html(output).show();  });
					
					}
				}
			}
		} else {
			alert('Please select a student!');
		}
	}
</script>
<?php
echo "&nbsp;<br>";
echo "<table cellpadding='0' cellspacing='0' border='0' align='center' width='95%' bgcolor='#ededd7'>";
echo "<tr><td colspan='5' bgcolor='#ffffff' align='center'><font size='+1'><b>Request a Student</b></font></td></tr>";
echo "<tr><td colspan='5' bgcolor='#C0FFFF'><b>Select Student:</b></td></tr>";
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
// Search by group, homeroom, or search for a name
echo "<tr><td width='5%'></td><td width='25%'><select id='StudentSearchOptionSelect' onChange='showSearchBox()'><option value='0' selected>Search By:</option><option value='1'>Grade</option><option value='2'>Homeroom</option><option value='3'>Name</option></select></td><td><div id='StudentSearchOption'></div></td><td><div id='StudentSearchResults'></div></td><td width='5%'></td></tr>";
//once student selected add the request details:
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
echo "<tr><td colspan='5' bgcolor='#C0FFFF'><b>Request Details:</b></td></tr>";
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
echo "<tr><td></td><td>Request Category:</td><td colspan='2'><select id='RequestCategory'><option value='0'>Select a Category:</option>";

//populate category box
$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);
$query = "select * from pass.category where categoryName not in ('Overflow') order by categoryName";
$result = mysql_query($query);
if (mysql_num_rows($result)!=0) { // If there are results, make a list
	while($row = mysql_fetch_array($result)) {
		echo "<option value='" . $row['categoryID'] . "'>" . $row['categoryName'] . "</option>";
	}	
} else {      // otherwise, if no results, report that no members were found
	echo "No Results";
}

$catn->dbdisconnect();

echo "</select></td><td></td></tr>";
echo "<tr><td></td><td>Request Days:</td><td colspan='2' align='center'><table border='0' width='100%'><tr><td align='center'>Mon.<br>".$tableDay[1]."</td><td align='center'>Tues.<br>".$tableDay[2]."</td><td align='center'>Wed.<br>".$tableDay[3]."</td><td align='center'>Thur.<br>".$tableDay[4]."</td><td align='center'>Fri.<br>".$tableDay[5]."</td></tr><tr><td align='center'><input type='checkbox' id='requestMonday' value='".$whichDay[1]."'></td><td align='center'><input type='checkbox' id='requestTuesday' value='".$whichDay[2]."'></td><td align='center'><input type='checkbox' id='requestWednesday' value='".$whichDay[3]."'></td><td align='center'><input type='checkbox' id='requestThursday' value='".$whichDay[4]."'></td><td align='center'><input type='checkbox' id='requestFriday' value='".$whichDay[5]."'></td></tr></table></td></tr>";
echo "<tr><td></td><td></td><td colspan='2'><input type='button' value='Cancel' onClick='hideStudentRequest()'> &nbsp; <input type='button' value='Submit' onClick='addRequest()'></td><td></td><td></td></tr>";
echo "<tr><td></td><td colspan='3'><div id='requestProccessed'></div></td><td></td></tr>";
echo "</table>";
echo "</body></html>";
?>