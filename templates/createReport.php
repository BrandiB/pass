<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;
require('fpdf/fpdf.php');

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

$whichUser = $catn->getVariable("whichUser");

// Gather Data for the PDF

// Name
$query1 = "select firstName, lastName from catnet.user where userID=".$whichUser;
$result1 = mysql_query($query1);
while ($row1 = mysql_fetch_array($result1)) {
	$lastName = $row1['lastName'];
	$firstName = $row1['firstName'];
}

// Student or Teacher?
$query2 = "select students.classOf, homeroom.userIDTeacher, user.lastName, user.firstName from pass.students, pass.homeroom, catnet.user where students.userID=".$whichUser." and homeroom.userIDStudent=".$whichUser." and user.userID=homeroom.userIDTeacher";
$result2 = mysql_query($query2);
if (mysql_num_rows($result2) != 0) {
	// Student
	$whatRecordType = "Student";
	while ($row2 = mysql_fetch_array($result2)) {
		$classOf = $row2['classOf'];
		$homeroomTeacher = $row2['firstName']." ".$row2['lastName'];
	}
} else {
	// Teacher
	$whatRecordType = "Teacher";
	$query3 = "select teachers.subjectID, teachers.isOverflow, subjects.subjectName from pass.teachers, pass.subjects where teachers.userID=".$whichUser." and teachers.subjectID=subjects.subjectID";
	$result3 = mysql_query($query3);
	while ($row3 = mysql_fetch_array($result3)) {
		$isTeacherOverflow = ($row3['isOverflow'] == 1) ? "Yes" : "No";
		$teacherSubject = $row3['subjectName'];
	} 
}

// Analyze Requests - How Many? Top 5 Categories? Top 5 Requesting Teachers or Requested Students?
// First, create the category variables we will analyze.
$theCategoryCounter = 0; //this will keep track of how big our arrays are
$query4 = "select * from pass.category";
$result4 = mysql_query($query4);
while ($row4 = mysql_fetch_array($result4)) {
	$theCategoryID[$theCategoryCounter]=$row4['categoryID'];  // Category ID
	$theCategoryName[$theCategoryCounter]=$row4['categoryName']; // Category Name
	$theCategoryTotal[$theCategoryCounter]=0; // Total Requests in this Category
	// DEBUG: echo $theCategoryCounter." ".$theCategoryID[$theCategoryCounter]." ".$theCategoryName[$theCategoryCounter]." ".$theCategoryTotal[$theCategoryCounter];
	$theCategoryCounter = $theCategoryCounter + 1;
}
$categoryFinalCount = $theCategoryCounter;
$theCategoryCounter = 0; // reset

if ($whatRecordType == "Teacher") {
	//TEACHER
	
	// Create a Student Array to match the Category Array
	$theStudentCounter = 0; //this will keep track of how big our arrays are
	$query6 = "select students.userID, user.firstName, user.lastName from pass.students, catnet.user where students.userID=user.userID";
	$result6 = mysql_query($query6);
	while ($row6 = mysql_fetch_array($result6)) {
		$theStudentListID[$theStudentCounter]=$row6['userID']; // Student ID
		$theStudentListName[$theStudentCounter]=$row6['firstName']." ".$row6['lastName']; //Student Name
		$theStudentListTotal[$theStudentCounter]=0; //Total Requests for this Student
		// DEBUG: echo $theStudentCounter." ".$theStudentListID[$theStudentCounter]." ".$theStudentListName[$theStudentCounter]." ".$theStudentListTotal[$theStudentCounter]."<br>";
		$theStudentCounter = $theStudentCounter + 1;
	}
	$studentFinalCount = $theStudentCounter;
	$theStudentCounter = 0; // reset
	
	// Request Counter
	$numRequests = 0;
	
	$query5 = "select * from pass.requestMatrix where teacherID=".$whichUser;
	$result5 = mysql_query($query5);
	if (mysql_num_rows($result5) != 0) {
		while ($row5 = mysql_fetch_array($result5)) {
			$numRequests = $numRequests + 1; // Add Request
			// Add Request Category
			while ($theCategoryCounter <= $categoryFinalCount) { //cycle through our categories
				if ($theCategoryID[$theCategoryCounter] == $row5['categoryID']) {
					$theCategoryTotal[$theCategoryCounter] = $theCategoryTotal[$theCategoryCounter] + 1;
				}
				$theCategoryCounter = $theCategoryCounter + 1;
			}
			$theCategoryCounter = 0; //reset
			
			// Add Request Student
			while ($theStudentCounter <= $studentFinalCount) {
				if ($theStudentListID[$theStudentCounter] == $row5['studentID']) {
					$theStudentListTotal[$theStudentCounter] = $theStudentListTotal[$theStudentCounter] + 1;
				}
				$theStudentCounter = $theStudentCounter + 1;
			}
			$theStudentCounter = 0; //reset
		} 
	} else {
		$numRequests = 0;
	}
	
	// Calculate Top 5 Request Categories and Percentages
	// Establish Variables
	
	$theTopCategoryNumber[0] = 0; //#1 - 0
	$theTopCategoryName[0] = '';
	$theTopCategoryPercent[0] = '';
	$theTopCategoryNumber[1] = 0; //#2 - 0
	$theTopCategoryName[1] = '';
	$theTopCategoryPercent[1] = '';
	$theTopCategoryNumber[2] = 0; //#3 - 0
	$theTopCategoryName[2] = '';
	$theTopCategoryPercent[2] = '';
	$theTopCategoryNumber[3] = 0; //#4 - 0
	$theTopCategoryName[3] = '';
	$theTopCategoryPercent[3] = '';
	$theTopCategoryNumber[4] = 0; //#5 - 0
	$theTopCategoryName[4] = '';
	$theTopCategoryPercent[4] = '';

	if ($numRequests != 0) { //Let's avoid dividing by 0, shall we?
		while ($theCategoryCounter <= $categoryFinalCount) { //cycle through our categories
			//Is it more than #1?
			if ($theCategoryTotal[$theCategoryCounter] > $theTopCategoryNumber[0]) {
				//Cycle Through, Replace
				// 4 becomes 5
				$theTopCategoryNumber[4] = $theTopCategoryNumber[3];
				$theTopCategoryName[4] = $theTopCategoryName[3];
				$theTopCategoryPercent[4] = $theTopCategoryPercent[3];
				// 3 becomes 4
				$theTopCategoryNumber[3] = $theTopCategoryNumber[2];
				$theTopCategoryName[3] = $theTopCategoryName[2];
				$theTopCategoryPercent[3] = $theTopCategoryPercent[2];
				// 2 becomes 3
				$theTopCategoryNumber[2] = $theTopCategoryNumber[1];
				$theTopCategoryName[2] = $theTopCategoryName[1];
				$theTopCategoryPercent[2] = $theTopCategoryPercent[1];
				// 1 becomes 2
				$theTopCategoryNumber[1] = $theTopCategoryNumber[0];
				$theTopCategoryName[1] = $theTopCategoryName[0];
				$theTopCategoryPercent[1] = $theTopCategoryPercent[0];
				// This new one becomes 2 = REPLACE
				$theTopCategoryNumber[0] = $theCategoryTotal[$theCategoryCounter];
				$theTopCategoryName[0] = $theCategoryName[$theCategoryCounter];
				$theTopCategoryPercent[0] = number_format(($theTopCategoryNumber[0] / $numRequests), 2, '.', '') * 100;
			} else {
				//Is it more than #2? 
				if ($theCategoryTotal[$theCategoryCounter] > $theTopCategoryNumber[1]) {
					//Cycle Through, Replace
					// 4 becomes 5
					$theTopCategoryNumber[4] = $theTopCategoryNumber[3];
					$theTopCategoryName[4] = $theTopCategoryName[3];
					$theTopCategoryPercent[4] = $theTopCategoryPercent[3];
					// 3 becomes 4
					$theTopCategoryNumber[3] = $theTopCategoryNumber[2];
					$theTopCategoryName[3] = $theTopCategoryName[2];
					$theTopCategoryPercent[3] = $theTopCategoryPercent[2];
					// 2 becomes 3
					$theTopCategoryNumber[2] = $theTopCategoryNumber[1];
					$theTopCategoryName[2] = $theTopCategoryName[1];
					$theTopCategoryPercent[2] = $theTopCategoryPercent[1];
					// This new one becomes 2 = REPLACE
					$theTopCategoryNumber[1] = $theCategoryTotal[$theCategoryCounter];
					$theTopCategoryName[1] = $theCategoryName[$theCategoryCounter];
					$theTopCategoryPercent[1] = number_format(($theTopCategoryNumber[1] / $numRequests), 2, '.', '') * 100;
				} else {
					//Is it more than #3?
					if ($theCategoryTotal[$theCategoryCounter] > $theTopCategoryNumber[2]) {
						//Cycle Through, Replace
						// 4 becomes 5
						$theTopCategoryNumber[4] = $theTopCategoryNumber[3];
						$theTopCategoryName[4] = $theTopCategoryName[3];
						$theTopCategoryPercent[4] = $theTopCategoryPercent[3];
						// 3 becomes 4
						$theTopCategoryNumber[3] = $theTopCategoryNumber[2];
						$theTopCategoryName[3] = $theTopCategoryName[2];
						$theTopCategoryPercent[3] = $theTopCategoryPercent[2];
						// This new one becomes 3 = REPLACE
						$theTopCategoryNumber[2] = $theCategoryTotal[$theCategoryCounter];
						$theTopCategoryName[2] = $theCategoryName[$theCategoryCounter];
						$theTopCategoryPercent[2] = number_format(($theTopCategoryNumber[2] / $numRequests), 2, '.', '') * 100;
					} else {
						//Is it more than #4?
						if ($theCategoryTotal[$theCategoryCounter] > $theTopCategoryNumber[3]) {
							//Cycle Through, Replace
							// 4 becomes 5
							$theTopCategoryNumber[4] = $theTopCategoryNumber[3];
							$theTopCategoryName[4] = $theTopCategoryName[3];
							$theTopCategoryPercent[4] = $theTopCategoryPercent[3];
							// This new one becomes 4 = REPLACE
							$theTopCategoryNumber[3] = $theCategoryTotal[$theCategoryCounter];
							$theTopCategoryName[3] = $theCategoryName[$theCategoryCounter];
							$theTopCategoryPercent[3] = number_format(($theTopCategoryNumber[3] / $numRequests), 2, '.', '') * 100;
						} else {
							//Is it more than #5?
							if ($theCategoryTotal[$theCategoryCounter] > $theTopCategoryNumber[4]) {
								// Replace
								$theTopCategoryNumber[4] = $theCategoryTotal[$theCategoryCounter];
								$theTopCategoryName[4] = $theCategoryName[$theCategoryCounter];
								$theTopCategoryPercent[4] = number_format(($theTopCategoryNumber[4] / $numRequests), 2, '.', '') * 100;
							} else {
								//Do Nothing
							}
						}
					}
				}
			}
			$theCategoryCounter = $theCategoryCounter + 1;
		}
	}
	$theCategoryCounter = 0; //reset
	
	// Calculate Top 5 Requested Students and Percentages
	// Establish Variables
	
	$theTopStudentNumber[0] = 0; //#1 - 0
	$theTopStudentName[0] = '';
	$theTopStudentPercent[0] = '';
	$theTopStudentNumber[1] = 0; //#2 - 0
	$theTopStudentName[1] = '';
	$theTopStudentPercent[1] = '';
	$theTopStudentNumber[2] = 0; //#3 - 0
	$theTopStudentName[2] = '';
	$theTopStudentPercent[2] = '';
	$theTopStudentNumber[3] = 0; //#4 - 0
	$theTopStudentName[3] = '';
	$theTopStudentPercent[3] = '';
	$theTopStudentNumber[4] = 0; //#5 - 0
	$theTopStudentName[4] = '';
	$theTopStudentPercent[4] = '';

	if ($numRequests != 0) { //Let's avoid dividing by 0, shall we?
		while ($theStudentCounter <= $studentFinalCount) { //cycle through our categories
			//Is it more than #1?
			if ($theStudentListTotal[$theStudentCounter] > $theTopStudentNumber[0]) {
				//Cycle Through, Replace
				// 4 becomes 5
				$theTopStudentNumber[4] = $theTopStudentNumber[3];
				$theTopStudentName[4] = $theTopStudentName[3];
				$theTopStudentPercent[4] = $theTopStudentPercent[3];
				// 3 becomes 4
				$theTopStudentNumber[3] = $theTopStudentNumber[2];
				$theTopStudentName[3] = $theTopStudentName[2];
				$theTopStudentPercent[3] = $theTopStudentPercent[2];
				// 2 becomes 3
				$theTopStudentNumber[2] = $theTopStudentNumber[1];
				$theTopStudentName[2] = $theTopStudentName[1];
				$theTopStudentPercent[2] = $theTopStudentPercent[1];
				// 1 becomes 2
				$theTopStudentNumber[1] = $theTopStudentNumber[0];
				$theTopStudentName[1] = $theTopStudentName[0];
				$theTopStudentPercent[1] = $theTopStudentPercent[0];
				// This new one becomes 1 = REPLACE
				$theTopStudentNumber[0] = $theStudentListTotal[$theStudentCounter];
				$theTopStudentName[0] = $theStudentListName[$theStudentCounter];
				$theTopStudentPercent[0] = number_format(($theTopStudentNumber[0] / $numRequests), 2, '.', '') * 100;
			} else {
				//Is it more than #2? 
				if ($theStudentListTotal[$theStudentCounter] > $theTopStudentNumber[1]) {
					//Cycle Through, Replace
					// 4 becomes 5
					$theTopStudentNumber[4] = $theTopStudentNumber[3];
					$theTopStudentName[4] = $theTopStudentName[3];
					$theTopStudentPercent[4] = $theTopStudentPercent[3];
					// 3 becomes 4
					$theTopStudentNumber[3] = $theTopStudentNumber[2];
					$theTopStudentName[3] = $theTopStudentName[2];
					$theTopStudentPercent[3] = $theTopStudentPercent[2];
					// 2 becomes 3
					$theTopStudentNumber[2] = $theTopStudentNumber[1];
					$theTopStudentName[2] = $theTopStudentName[1];
					$theTopStudentPercent[2] = $theTopStudentPercent[1];
					// This new one becomes 2 = REPLACE
					$theTopStudentNumber[1] = $theStudentListTotal[$theStudentCounter];
					$theTopStudentName[1] = $theStudentListName[$theStudentCounter];
					$theTopStudentPercent[1] = number_format(($theTopStudentNumber[1] / $numRequests), 2, '.', '') * 100;
				} else {
					//Is it more than #3?
					if ($theStudentListTotal[$theStudentCounter] > $theTopStudentNumber[2]) {
						//Cycle Through, Replace
						// 4 becomes 5
						$theTopStudentNumber[4] = $theTopStudentNumber[3];
						$theTopStudentName[4] = $theTopStudentName[3];
						$theTopStudentPercent[4] = $theTopStudentPercent[3];
						// 3 becomes 4
						$theTopStudentNumber[3] = $theTopStudentNumber[2];
						$theTopStudentName[3] = $theTopStudentName[2];
						$theTopStudentPercent[3] = $theTopStudentPercent[2];
						// This new one becomes 3 = REPLACE
						$theTopStudentNumber[2] = $theStudentListTotal[$theStudentCounter];
						$theTopStudentName[2] = $theStudentListName[$theStudentCounter];
						$theTopStudentPercent[2] = number_format(($theTopStudentNumber[2] / $numRequests), 2, '.', '') * 100;
					} else {
						//Is it more than #4?
						if ($theStudentListTotal[$theStudentCounter] > $theTopStudentNumber[3]) {
							//Cycle Through, Replace
							// 4 becomes 5
							$theTopStudentNumber[4] = $theTopStudentNumber[3];
							$theTopStudentName[4] = $theTopStudentName[3];
							$theTopStudentPercent[4] = $theTopStudentPercent[3];
							// This new one becomes 4 = REPLACE
							$theTopStudentNumber[3] = $theStudentListTotal[$theStudentCounter];
							$theTopStudentName[3] = $theStudentListName[$theStudentCounter];
							$theTopStudentPercent[3] = number_format(($theTopStudentNumber[3] / $numRequests), 2, '.', '') * 100;
						} else {
							//Is it more than #5?
							if ($theStudentListTotal[$theStudentCounter] > $theTopStudentNumber[4]) {
								// Replace
								$theTopStudentNumber[4] = $theStudentListTotal[$theStudentCounter];
								$theTopStudentName[4] = $theStudentListName[$theStudentCounter];
								$theTopStudentPercent[4] = number_format(($theTopStudentNumber[4] / $numRequests), 2, '.', '') * 100;
							} else {
								//Do Nothing
							}
						}
					}
				}
			}
			$theStudentCounter = $theStudentCounter + 1;
		}
	}
	$theStudentCounter = 0; //reset

	// Does a report already exist?
	if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/peds/pass/reports/' . $whichUser . '.pdf')) {
		unlink($_SERVER['DOCUMENT_ROOT'] . '/peds/pass/reports/' . $whichUser . '.pdf');
	}
	
	// Generate the PDF Report and output the link to it
	$pdf = new FPDF('P','mm','Letter');
	$pdf->AddPage();
	$pdf->SetFont('Courier','',10);
	
	$pdf->Write(5,'+ - - - - - - - - - - - - - - - - - - - PASS REPORT - - - - - - - - - - - - - - - - - - - +');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Write(5,'Teacher: '.$firstName.' '.$lastName);
	$pdf->Ln();
	$pdf->Write(5,'Subject: '.$teacherSubject);
	$pdf->Ln();
	$pdf->Write(5,'Are Overflow Students sent here? '.$isTeacherOverflow);
	$pdf->Ln();
	$pdf->Write(5,'Total # of PASS Requests Made: '.$numRequests);
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Write(5,'+ - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - +');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('','U');
	$pdf->Write(5,'Top 5 Request Categories:');
	$pdf->Ln();
	$pdf->SetFont('','');
	$pdf->Write(5,'1. '.$theTopCategoryName[0].'     '.$theTopCategoryPercent[0].'% ('.$theTopCategoryNumber[0].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'2. '.$theTopCategoryName[1].'     '.$theTopCategoryPercent[1].'% ('.$theTopCategoryNumber[1].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'3. '.$theTopCategoryName[2].'     '.$theTopCategoryPercent[2].'% ('.$theTopCategoryNumber[2].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'4. '.$theTopCategoryName[3].'     '.$theTopCategoryPercent[3].'% ('.$theTopCategoryNumber[3].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'5. '.$theTopCategoryName[4].'     '.$theTopCategoryPercent[4].'% ('.$theTopCategoryNumber[4].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('','U');
	$pdf->Write(5,'Top 5 Requested Students:');
	$pdf->Ln();
	$pdf->SetFont('','');
	$pdf->Write(5,'1. '.$theTopStudentName[0].'     '.$theTopStudentPercent[0].'% ('.$theTopStudentNumber[0].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'2. '.$theTopStudentName[1].'     '.$theTopStudentPercent[1].'% ('.$theTopStudentNumber[1].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'3. '.$theTopStudentName[2].'     '.$theTopStudentPercent[2].'% ('.$theTopStudentNumber[2].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'4. '.$theTopStudentName[3].'     '.$theTopStudentPercent[3].'% ('.$theTopStudentNumber[3].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'5. '.$theTopStudentName[4].'     '.$theTopStudentPercent[4].'% ('.$theTopStudentNumber[4].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Write(5,'+ - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - +');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('','U');
	$pdf->Write(5,'ALL REQUESTS');
	$pdf->Ln();
	$pdf->SetFont('','');
	$queryFinal = "select requestMatrix.studentID, user.firstName, user.lastName, requestMatrix.categoryID, category.categoryName, requestMatrix.isTrump, requestMatrix.dateRequested from pass.requestMatrix, catnet.user, pass.category where requestMatrix.teacherID=".$whichUser." and user.userID=requestMatrix.studentID and category.categoryID=requestMatrix.categoryID";
	$requestFinal = mysql_query($queryFinal);
	while ($rowFinal = mysql_fetch_array($requestFinal)) {
		$requestDate = date("M j, Y", strtotime($rowFinal['dateRequested']));
		$requestStudent = $rowFinal['firstName']." ".$rowFinal['lastName'];
		$requestCategory = $rowFinal['categoryName'];
		$requestTrump = ($rowFinal['isTrump'] == 1) ? " (TRUMP)" : "";

		$pdf->Write(5,'     '.$requestDate."     ".$requestStudent."     ".$requestCategory.$requestTrump);
		$pdf->Ln();
	}
	
	$pdf->Output($_SERVER['DOCUMENT_ROOT'] . '/peds/pass/reports/' . $whichUser . '.pdf','F');
	
	echo "<br><center><a href='../peds/pass/reports/" . $whichUser . ".pdf' target='_blank'>Download Report on " . $firstName . " " . $lastName . " (PDF)</a></center><br>&nbsp;";
	
} else {
	//STUDENT
	
	// Create a Teacher Array to match the Category Array
	$theTeacherCounter = 0; //this will keep track of how big our arrays are
	$query6 = "select teachers.userID, user.firstName, user.lastName from pass.teachers, catnet.user where teachers.userID=user.userID";
	$result6 = mysql_query($query6);
	while ($row6 = mysql_fetch_array($result6)) {
		$theTeacherID[$theTeacherCounter]=$row6['userID'];
		$theTeacherName[$theTeacherCounter]=$row6['firstName']." ".$row6['lastName']; //Teacher Name
		$theTeacherTotal[$theTeacherCounter]=0; //Total Requests for this Teacher
		// DEBUG: echo $theTeacherCounter." ".$theTeacherID[$theTeacherCounter]." ".$theTeacherName[$theTeacherCounter]." ".$theTeacherTotal[$theTeacherCounter]."<br>";
		$theTeacherCounter = $theTeacherCounter + 1;
	}
	$teacherFinalCount = $theTeacherCounter;
	$theTeacherCounter = 0; // reset
	
	// Request Counter
	$numRequests = 0;
	
	$query5 = "select * from pass.requestMatrix where studentID=".$whichUser;
	$result5 = mysql_query($query5);
	if (mysql_num_rows($result5) != 0) {
		while ($row5 = mysql_fetch_array($result5)) {
			$numRequests = $numRequests + 1; // Add Request
			// Add Request Category
			while ($theCategoryCounter <= $categoryFinalCount) { //cycle through our categories
				if ($theCategoryID[$theCategoryCounter] == $row5['categoryID']) {
					$theCategoryTotal[$theCategoryCounter] = $theCategoryTotal[$theCategoryCounter] + 1;
				}
				$theCategoryCounter = $theCategoryCounter + 1;
			}
			$theCategoryCounter = 0; //reset
			
			// Add Request Teacher
			while ($theTeacherCounter <= $teacherFinalCount) {
				if ($theTeacherID[$theTeacherCounter] == $row5['teacherID']) {
					$theTeacherTotal[$theTeacherCounter] = $theTeacherTotal[$theTeacherCounter] + 1;
				}
				$theTeacherCounter = $theTeacherCounter + 1;
			}
			$theTeacherCounter = 0; //reset
		} 
	} else {
		$numRequests = 0;
	}
	
	// Calculate Top 5 Request Categories and Percentages
	// Establish Variables
	
	$theTopCategoryNumber[0] = 0; //#1 - 0
	$theTopCategoryID[0] = 0;
	$theTopCategoryName[0] = '';
	$theTopCategoryPercent[0] = '';
	$theTopCategoryNumber[1] = 0; //#2 - 0
	$theTopCategoryID[1] = 0;
	$theTopCategoryName[1] = '';
	$theTopCategoryPercent[1] = '';
	$theTopCategoryNumber[2] = 0; //#3 - 0
	$theTopCategoryID[2] = 0;
	$theTopCategoryName[2] = '';
	$theTopCategoryPercent[2] = '';
	$theTopCategoryNumber[3] = 0; //#4 - 0
	$theTopCategoryID[3] = 0;
	$theTopCategoryName[3] = '';
	$theTopCategoryPercent[3] = '';
	$theTopCategoryNumber[4] = 0; //#5 - 0
	$theTopCategoryID[4] = 0;
	$theTopCategoryName[4] = '';
	$theTopCategoryPercent[4] = '';

	if ($numRequests != 0) { //Let's avoid dividing by 0, shall we?
		while ($theCategoryCounter <= $categoryFinalCount) { //cycle through our categories
			//Is it more than #1?
			if ($theCategoryTotal[$theCategoryCounter] > $theTopCategoryNumber[0]) {
				//Cycle Through, Replace
				// 4 becomes 5
				$theTopCategoryNumber[4] = $theTopCategoryNumber[3];
				$theTopCategoryID[4] = $theTopCategoryNumber[3];
				$theTopCategoryName[4] = $theTopCategoryName[3];
				$theTopCategoryPercent[4] = $theTopCategoryPercent[3];
				// 3 becomes 4
				$theTopCategoryNumber[3] = $theTopCategoryNumber[2];
				$theTopCategoryID[3] = $theTopCategoryNumber[2];
				$theTopCategoryName[3] = $theTopCategoryName[2];
				$theTopCategoryPercent[3] = $theTopCategoryPercent[2];
				// 2 becomes 3
				$theTopCategoryNumber[2] = $theTopCategoryNumber[1];
				$theTopCategoryID[2] = $theTopCategoryNumber[1];
				$theTopCategoryName[2] = $theTopCategoryName[1];
				$theTopCategoryPercent[2] = $theTopCategoryPercent[1];
				// 1 becomes 2
				$theTopCategoryNumber[1] = $theTopCategoryNumber[0];
				$theTopCategoryID[1] = $theTopCategoryNumber[0];
				$theTopCategoryName[1] = $theTopCategoryName[0];
				$theTopCategoryPercent[1] = $theTopCategoryPercent[0];
				// This new one becomes 2 = REPLACE
				$theTopCategoryNumber[0] = $theCategoryTotal[$theCategoryCounter];
				$theTopCategoryID[0] = $theCategoryID[$theCategoryCounter];
				$theTopCategoryName[0] = $theCategoryName[$theCategoryCounter];
				$theTopCategoryPercent[0] = number_format(($theTopCategoryNumber[0] / $numRequests), 2, '.', '') * 100;
			} else {
				//Is it more than #2? 
				if ($theCategoryTotal[$theCategoryCounter] > $theTopCategoryNumber[1]) {
					//Cycle Through, Replace
					// 4 becomes 5
					$theTopCategoryNumber[4] = $theTopCategoryNumber[3];
					$theTopCategoryID[4] = $theTopCategoryNumber[3];
					$theTopCategoryName[4] = $theTopCategoryName[3];
					$theTopCategoryPercent[4] = $theTopCategoryPercent[3];
					// 3 becomes 4
					$theTopCategoryNumber[3] = $theTopCategoryNumber[2];
					$theTopCategoryID[3] = $theTopCategoryNumber[2];
					$theTopCategoryName[3] = $theTopCategoryName[2];
					$theTopCategoryPercent[3] = $theTopCategoryPercent[2];
					// 2 becomes 3
					$theTopCategoryNumber[2] = $theTopCategoryNumber[1];
					$theTopCategoryID[2] = $theTopCategoryNumber[1];
					$theTopCategoryName[2] = $theTopCategoryName[1];
					$theTopCategoryPercent[2] = $theTopCategoryPercent[1];
					// This new one becomes 2 = REPLACE
					$theTopCategoryNumber[1] = $theCategoryTotal[$theCategoryCounter];
					$theTopCategoryID[1] = $theCategoryID[$theCategoryCounter];
					$theTopCategoryName[1] = $theCategoryName[$theCategoryCounter];
					$theTopCategoryPercent[1] = number_format(($theTopCategoryNumber[1] / $numRequests), 2, '.', '') * 100;
				} else {
					//Is it more than #3?
					if ($theCategoryTotal[$theCategoryCounter] > $theTopCategoryNumber[2]) {
						//Cycle Through, Replace
						// 4 becomes 5
						$theTopCategoryNumber[4] = $theTopCategoryNumber[3];
						$theTopCategoryID[4] = $theTopCategoryNumber[3];
						$theTopCategoryName[4] = $theTopCategoryName[3];
						$theTopCategoryPercent[4] = $theTopCategoryPercent[3];
						// 3 becomes 4
						$theTopCategoryNumber[3] = $theTopCategoryNumber[2];
						$theTopCategoryID[3] = $theTopCategoryNumber[2];
						$theTopCategoryName[3] = $theTopCategoryName[2];
						$theTopCategoryPercent[3] = $theTopCategoryPercent[2];
						// This new one becomes 3 = REPLACE
						$theTopCategoryNumber[2] = $theCategoryTotal[$theCategoryCounter];
						$theTopCategoryID[2] = $theCategoryID[$theCategoryCounter];
						$theTopCategoryName[2] = $theCategoryName[$theCategoryCounter];
						$theTopCategoryPercent[2] = number_format(($theTopCategoryNumber[2] / $numRequests), 2, '.', '') * 100;
					} else {
						//Is it more than #4?
						if ($theCategoryTotal[$theCategoryCounter] > $theTopCategoryNumber[3]) {
							//Cycle Through, Replace
							// 4 becomes 5
							$theTopCategoryNumber[4] = $theTopCategoryNumber[3];
							$theTopCategoryID[4] = $theTopCategoryNumber[3];
							$theTopCategoryName[4] = $theTopCategoryName[3];
							$theTopCategoryPercent[4] = $theTopCategoryPercent[3];
							// This new one becomes 4 = REPLACE
							$theTopCategoryNumber[3] = $theCategoryTotal[$theCategoryCounter];
							$theTopCategoryID[3] = $theCategoryID[$theCategoryCounter];
							$theTopCategoryName[3] = $theCategoryName[$theCategoryCounter];
							$theTopCategoryPercent[3] = number_format(($theTopCategoryNumber[3] / $numRequests), 2, '.', '') * 100;
						} else {
							//Is it more than #5?
							if ($theCategoryTotal[$theCategoryCounter] > $theTopCategoryNumber[4]) {
								// Replace
								$theTopCategoryNumber[4] = $theCategoryTotal[$theCategoryCounter];
								$theTopCategoryID[4] = $theCategoryID[$theCategoryCounter];
								$theTopCategoryName[4] = $theCategoryName[$theCategoryCounter];
								$theTopCategoryPercent[4] = number_format(($theTopCategoryNumber[4] / $numRequests), 2, '.', '') * 100;
							} else {
								//Do Nothing
							}
						}
					}
				}
			}
			$theCategoryCounter = $theCategoryCounter + 1;
		}
	}
	$theCategoryCounter = 0; //reset
	
	// Calculate Top 5 Requesting Teachers and Percentages
	// Establish Variables
	
	$theTopTeacherNumber[0] = 0; //#1 - 0
	$theTopTeacherName[0] = '';
	$theTopTeacherPercent[0] = '';
	$theTopTeacherNumber[1] = 0; //#2 - 0
	$theTopTeacherName[1] = '';
	$theTopTeacherPercent[1] = '';
	$theTopTeacherNumber[2] = 0; //#3 - 0
	$theTopTeacherName[2] = '';
	$theTopTeacherPercent[2] = '';
	$theTopTeacherNumber[3] = 0; //#4 - 0
	$theTopTeacherName[3] = '';
	$theTopTeacherPercent[3] = '';
	$theTopTeacherNumber[4] = 0; //#5 - 0
	$theTopTeacherName[4] = '';
	$theTopTeacherPercent[4] = '';

	if ($numRequests != 0) { //Let's avoid dividing by 0, shall we?
		while ($theTeacherCounter <= $teacherFinalCount) { //cycle through our categories
			//Is it more than #1?
			if ($theTeacherTotal[$theTeacherCounter] > $theTopTeacherNumber[0]) {
				//Cycle Through, Replace
				// 4 becomes 5
				$theTopTeacherNumber[4] = $theTopTeacherNumber[3];
				$theTopTeacherName[4] = $theTopTeacherName[3];
				$theTopTeacherPercent[4] = $theTopTeacherPercent[3];
				// 3 becomes 4
				$theTopTeacherNumber[3] = $theTopTeacherNumber[2];
				$theTopTeacherName[3] = $theTopTeacherName[2];
				$theTopTeacherPercent[3] = $theTopTeacherPercent[2];
				// 2 becomes 3
				$theTopTeacherNumber[2] = $theTopTeacherNumber[1];
				$theTopTeacherName[2] = $theTopTeacherName[1];
				$theTopTeacherPercent[2] = $theTopTeacherPercent[1];
				// 1 becomes 2
				$theTopTeacherNumber[1] = $theTopTeacherNumber[0];
				$theTopTeacherName[1] = $theTopTeacherName[0];
				$theTopTeacherPercent[1] = $theTopTeacherPercent[0];
				// This new one becomes 1 = REPLACE
				$theTopTeacherNumber[0] = $theTeacherTotal[$theTeacherCounter];
				$theTopTeacherName[0] = $theTeacherName[$theTeacherCounter];
				$theTopTeacherPercent[0] = number_format(($theTopTeacherNumber[0] / $numRequests), 2, '.', '') * 100;
			} else {
				//Is it more than #2? 
				if ($theTeacherTotal[$theTeacherCounter] > $theTopTeacherNumber[1]) {
					//Cycle Through, Replace
					// 4 becomes 5
					$theTopTeacherNumber[4] = $theTopTeacherNumber[3];
					$theTopTeacherName[4] = $theTopTeacherName[3];
					$theTopTeacherPercent[4] = $theTopTeacherPercent[3];
					// 3 becomes 4
					$theTopTeacherNumber[3] = $theTopTeacherNumber[2];
					$theTopTeacherName[3] = $theTopTeacherName[2];
					$theTopTeacherPercent[3] = $theTopTeacherPercent[2];
					// 2 becomes 3
					$theTopTeacherNumber[2] = $theTopTeacherNumber[1];
					$theTopTeacherName[2] = $theTopTeacherName[1];
					$theTopTeacherPercent[2] = $theTopTeacherPercent[1];
					// This new one becomes 2 = REPLACE
					$theTopTeacherNumber[1] = $theTeacherTotal[$theTeacherCounter];
					$theTopTeacherName[1] = $theTeacherName[$theTeacherCounter];
					$theTopTeacherPercent[1] = number_format(($theTopTeacherNumber[1] / $numRequests), 2, '.', '') * 100;
				} else {
					//Is it more than #3?
					if ($theTeacherTotal[$theTeacherCounter] > $theTopTeacherNumber[2]) {
						//Cycle Through, Replace
						// 4 becomes 5
						$theTopTeacherNumber[4] = $theTopTeacherNumber[3];
						$theTopTeacherName[4] = $theTopTeacherName[3];
						$theTopTeacherPercent[4] = $theTopTeacherPercent[3];
						// 3 becomes 4
						$theTopTeacherNumber[3] = $theTopTeacherNumber[2];
						$theTopTeacherName[3] = $theTopTeacherName[2];
						$theTopTeacherPercent[3] = $theTopTeacherPercent[2];
						// This new one becomes 3 = REPLACE
						$theTopTeacherNumber[2] = $theTeacherTotal[$theTeacherCounter];
						$theTopTeacherName[2] = $theTeacherName[$theTeacherCounter];
						$theTopTeacherPercent[2] = number_format(($theTopTeacherNumber[2] / $numRequests), 2, '.', '') * 100;
					} else {
						//Is it more than #4?
						if ($theTeacherTotal[$theTeacherCounter] > $theTopTeacherNumber[3]) {
							//Cycle Through, Replace
							// 4 becomes 5
							$theTopTeacherNumber[4] = $theTopTeacherNumber[3];
							$theTopTeacherName[4] = $theTopTeacherName[3];
							$theTopTeacherPercent[4] = $theTopTeacherPercent[3];
							// This new one becomes 4 = REPLACE
							$theTopTeacherNumber[3] = $theTeacherTotal[$theTeacherCounter];
							$theTopTeacherName[3] = $theTeacherName[$theTeacherCounter];
							$theTopTeacherPercent[3] = number_format(($theTopTeacherNumber[3] / $numRequests), 2, '.', '') * 100;
						} else {
							//Is it more than #5?
							if ($theTeacherTotal[$theTeacherCounter] > $theTopTeacherNumber[4]) {
								// Replace
								$theTopTeacherNumber[4] = $theTeacherTotal[$theTeacherCounter];
								$theTopTeacherName[4] = $theTeacherName[$theTeacherCounter];
								$theTopTeacherPercent[4] = number_format(($theTopTeacherNumber[4] / $numRequests), 2, '.', '') * 100;
							} else {
								//Do Nothing
							}
						}
					}
				}
			}
			$theTeacherCounter = $theTeacherCounter + 1;
		}
	}
	$theTeacherCounter = 0; //reset

	// Does a report already exist?
	if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/peds/pass/reports/' . $whichUser . '.pdf')) {
		unlink($_SERVER['DOCUMENT_ROOT'] . '/peds/pass/reports/' . $whichUser . '.pdf');
	}
	
	// Generate the PDF Report and output the link to it
	$pdf = new FPDF('P','mm','Letter');
	$pdf->AddPage();
	$pdf->SetFont('Courier','',10);
	
	$pdf->Write(5,'+ - - - - - - - - - - - - - - - - - - - PASS REPORT - - - - - - - - - - - - - - - - - - - +');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Write(5,'Student: '.$firstName.' '.$lastName);
	$pdf->Ln();
	$pdf->Write(5,'Class of: '.$classOf);
	$pdf->Ln();
	$pdf->Write(5,'Homeroom: '.$homeroomTeacher);
	$pdf->Ln();
	$pdf->Write(5,'Total # of Times Requested: '.$numRequests);
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Write(5,'+ - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - +');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('','U');
	$pdf->Write(5,'Top 5 Request Categories:');
	$pdf->Ln();
	$pdf->SetFont('','');
	$pdf->Write(5,'1. '.$theTopCategoryName[0].'     '.$theTopCategoryPercent[0].'% ('.$theTopCategoryNumber[0].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'2. '.$theTopCategoryName[1].'     '.$theTopCategoryPercent[1].'% ('.$theTopCategoryNumber[1].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'3. '.$theTopCategoryName[2].'     '.$theTopCategoryPercent[2].'% ('.$theTopCategoryNumber[2].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'4. '.$theTopCategoryName[3].'     '.$theTopCategoryPercent[3].'% ('.$theTopCategoryNumber[3].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'5. '.$theTopCategoryName[4].'     '.$theTopCategoryPercent[4].'% ('.$theTopCategoryNumber[4].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('','U');
	$pdf->Write(5,'Top 5 Requesting Teachers:');
	$pdf->Ln();
	$pdf->SetFont('','');
	$pdf->Write(5,'1. '.$theTopTeacherName[0].'     '.$theTopTeacherPercent[0].'% ('.$theTopTeacherNumber[0].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'2. '.$theTopTeacherName[1].'     '.$theTopTeacherPercent[1].'% ('.$theTopTeacherNumber[1].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'3. '.$theTopTeacherName[2].'     '.$theTopTeacherPercent[2].'% ('.$theTopTeacherNumber[2].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'4. '.$theTopTeacherName[3].'     '.$theTopTeacherPercent[3].'% ('.$theTopTeacherNumber[3].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Write(5,'5. '.$theTopTeacherName[4].'     '.$theTopTeacherPercent[4].'% ('.$theTopTeacherNumber[4].' out of '.$numRequests.')');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Write(5,'+ - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - +');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('','U');
	$pdf->Write(5,'ALL REQUESTS');
	$pdf->Ln();
	$pdf->SetFont('','');
	$queryFinal = "select requestMatrix.teacherID, user.firstName, user.lastName, requestMatrix.categoryID, category.categoryName, requestMatrix.isTrump, requestMatrix.dateRequested from pass.requestMatrix, catnet.user, pass.category where requestMatrix.studentID=".$whichUser." and user.userID=requestMatrix.teacherID and category.categoryID=requestMatrix.categoryID";
	$requestFinal = mysql_query($queryFinal);
	while ($rowFinal = mysql_fetch_array($requestFinal)) {
		$requestDate = date("M j, Y", strtotime($rowFinal['dateRequested']));
		$requestStudent = $rowFinal['firstName']." ".$rowFinal['lastName'];
		$requestCategory = $rowFinal['categoryName'];
		$requestTrump = ($rowFinal['isTrump'] == 1) ? " (TRUMP)" : "";

		$pdf->Write(5,'     '.$requestDate."     ".$requestStudent."     ".$requestCategory.$requestTrump);
		$pdf->Ln();
	}
	
	$pdf->Output($_SERVER['DOCUMENT_ROOT'] . '/peds/pass/reports/' . $whichUser . '.pdf','F');
	
	echo "<br><center><a href='../peds/pass/reports/" . $whichUser . ".pdf' target='_blank'>Download Report on " . $firstName . " " . $lastName . " (PDF)</a></center><br>&nbsp;";
	
}
?>