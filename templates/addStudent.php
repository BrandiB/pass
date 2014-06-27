<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$whichTeacher = $catn->getVariable("whichTeacher");

echo "<html><head></head><body>";

?>
<script language='javascript'>

//Cancel button hides the Request Student form:
	function getTeachers() {
		$.ajax({ 
					type: 'POST', 
					url: 'pass/process/groupMembers.php', 
					data: { groupid: '14', outputType: 'selectListHomeroom' },
					}).done(
					function(output) { $('#StudentSearchOption').html(output).show();  });
	}

	function hideAddStudent()
	{
		$('#formdisplay').hide();
		$.ajax({
			type: 'POST',
			url: 'pass/templates/manageTeachers.php',
			data: { whichTeacher: '<?php echo $whichTeacher; ?>', keyid: '<?php echo $keyid; ?>' },
			}).done(
			function(output) { $('#teacherDisplay').html(output).show(); });
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

	// add Student to the class
	function addRequest() {
		//has a student been selected?
		if (document.getElementById('theStudent')) { // does the student field exist yet?
			whichStudent = document.getElementById('theStudent').value;
			if (whichStudent == '0') { // the student field exists, but has a name been selected yet?
				alert ('Please select a student!');
			} else {
				$.ajax ({
				type: 'POST',
				url: 'pass/process/addStudent.php',
				data: { whichStudent: whichStudent, whichTeacher: '<?php echo $whichTeacher; ?>', keyid: '<?php echo $keyid; ?>' },
				}).done(
				function(output) { $('#addStudentArea').html(output).show(); });
			}
		}
	}
</script>
	
<?php
echo "&nbsp;<br>";
echo "<table cellpadding='0' cellspacing='0' border='0' align='center' width='95%' bgcolor='#ededd7'>";
echo "<tr><td colspan='5' bgcolor='#ffffff' align='center'><font size='+1'><b>Add a Student</b></font></td></tr>";
echo "<tr><td colspan='5' bgcolor='#C0FFFF'><b>Select Student:</b></td></tr>";
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
// Search by group or search for a name
echo "<tr><td width='5%'></td><td width='25%'><select id='StudentSearchOptionSelect' onChange='showSearchBox()'><option value='0' selected>Search By:</option><option value='1'>Grade</option><option value='2'>Homeroom</option><option value='3'>Name</option></select></td><td><div id='StudentSearchOption'></div></td><td><div id='StudentSearchResults'></div></td><td width='5%'></td></tr>";
//once student selected add the request details:
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
echo "<tr><td></td><td colspan='3'><font color='red'>Student will be REMOVED from their current homeroom and ADDED to this one.</font></td><td></td></tr>";
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
echo "<tr><td></td><td></td><td colspan='2'><input type='button' value='Cancel' onClick='hideAddStudent()'> &nbsp; <input type='button' value='Add Student' onClick='addRequest()'></td><td></td><td></td></tr>";
echo "<tr><td></td><td colspan='3'><div id='addStudentArea'></div></td><td></td></tr>";
echo "</table>";
echo "</body></html>";