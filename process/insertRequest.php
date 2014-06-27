<?php

/*
	This inserts a request into the database.
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

$whichStudent = $catn->getVariable("whichStudent");
$whichCategory = $catn->getVariable("whichCategory");
$whichWeek = $catn->getVariable("whichWeek");
$requestDay[1] = $catn->getVariable("requestMonday"); // requestDay is brought in rather than calculated, because it is 0 if not requested
$requestDay[2] = $catn->getVariable("requestTuesday");
$requestDay[3] = $catn->getVariable("requestWednesday");
$requestDay[4] = $catn->getVariable("requestThursday");
$requestDay[5] = $catn->getVariable("requestFriday");
if ($whichWeek == '1') {
$dayFull[1] = date("D, M j", strtotime('Monday this week')); // dayFull is simply calculated
$dayFull[2] = date("D, M j", strtotime('Tuesday this week'));
$dayFull[3] = date("D, M j", strtotime('Wednesday this week'));
$dayFull[4] = date("D, M j", strtotime('Thursday this week'));
$dayFull[5] = date("D, M j", strtotime('Friday this week'));
$whichForm = 'viewRoster.php';
} else {
$dayFull[1] = date("D, M j", strtotime('Monday next week')); // dayFull is simply calculated
$dayFull[2] = date("D, M j", strtotime('Tuesday next week'));
$dayFull[3] = date("D, M j", strtotime('Wednesday next week'));
$dayFull[4] = date("D, M j", strtotime('Thursday next week'));
$dayFull[5] = date("D, M j", strtotime('Friday next week'));
$whichForm = 'manageRoster.php';
}

$theDay = 1; //counter for the days

	//What is our trump day?
	$query6 = "select teachers.subjectID, trumpday.whichDay from pass.teachers, pass.trumpday where teachers.userID=".$catn->userinfo[userID]." and trumpday.subjectID=teachers.subjectID";
	$result6 = mysql_query($query6);
	$row6=mysql_fetch_array($result6);
	$trumpDay= $row6['whichDay'];
	
	//Cycle through the week and only run through the steps if requestDay is NOT 0:
	while ($theDay < 6) {
		if ($requestDay[$theDay] != 0) {
			// What is the categoryLevel of our request?
			$query2 = "select categoryLevel from pass.category where categoryID =".$whichCategory;
			$result2 = mysql_query($query2);
			$row2 = mysql_fetch_array($result2);
			$curCategoryLevel = $row2['categoryLevel'];
			
			$deletePrev = 0; // will change to 1 if we should delete old request
			
			// Step 1: Is there already a request?
			$query = "select requestmatrix.categoryID, requestmatrix.requestID, requestmatrix.isTrump, category.categoryLevel from pass.requestmatrix, pass.category where requestmatrix.studentID=".$whichStudent." and requestmatrix.dateRequested='".$requestDay[$theDay]."' and category.categoryID=requestmatrix.categoryID";
			$result = mysql_query($query);
			if (mysql_num_rows($result) != 0) { // If there is already a request, proceed to step 2 of our checks.
				// Step 2: Is the request already in there a trump?
				$row = mysql_fetch_array($result);
				$isTrumped = $row['isTrump']; 
				$prevCategoryLevel = $row['categoryLevel']; // in prep for Step 3, what level is the previous request?
				$prevRequestID = $row['requestID']; // in prep for Step 3, what is the previous request's ID?
				if ($isTrumped == 1) {
					$isTaken = 1; // Trumped: the request won't be added below.
				} else { // Not a trump, so proceed to step 3
					// Step 2.5: Do we have trump?
					if ($theDay == $trumpDay) { // we have trump
						$isTaken = 0; // Ours is more important, previous request will be removed and ours will be added below.
						$deletePrev = 1;
					} else {
						// Step 3: Is the previous request a higher category level or the same category level as ours?
						if ($prevCategoryLevel >= $curCategoryLevel) {
							$isTaken = 1; // Higher or same = previous request stays; ours won't be added below.
						} else {
							$isTaken = 0; // Ours is more important, previous request will be removed and ours will be added below.
							$deletePrev = 1;
						}
						
					}
				}
			} else { // There is not already a request. isTaken = 0; request will be added below.
				$isTaken = 0;
			}
			
			// Flowchart finished: do we add the request or display a "Sorry" notice?
			if ($isTaken == 1) { // date taken
				echo "<font color='red'>Sorry, ".$dayFull[$theDay]." is already taken.</font><br>";
				$notSuccess = 1;
			} else { // date free, then if ok delete old request if it exists and add our request
				if ($deletePrev == 1) { // old request needs to be deleted
					$query3 = "delete from pass.requestmatrix where requestID=".$prevRequestID;
					mysql_query($query3); // previous request gone
				}
				// add request - query differs if it is our trump day or not
				if ($trumpDay == $theDay) { // it is our trump day
					$query5 = "insert into pass.requestmatrix value('', '".$whichStudent."', '".$whichCategory."', '1', '".$requestDay[$theDay]."', '".$catn->userinfo[userID]."')";
				} else {
					$query5 = "insert into pass.requestmatrix value('', '".$whichStudent."', '".$whichCategory."', '0', '".$requestDay[$theDay]."', '".$catn->userinfo[userID]."')";
				}
				mysql_query($query5);
				echo "<script language='JavaScript'>alert('The request  for ".$dayFull[$theDay]." was successfully submitted!');hideStudentRequest();</script>";
			}
		}
		$theDay = $theDay + 1;
	}
$catn->dbdisconnect();
?>
