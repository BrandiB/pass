<?php
$path = $_SERVER['DOCUMENT_ROOT'] . '/peds/pass/includes/catnetapps.inc';
require_once $path;
require('fpdf/fpdf.php');

// instantiate class
$catn= NEW CatnetApps();

$keyid = $catn->getVariable("keyid");
$catn->setUserInfo($keyid);

$catn->dbconnect("catnet",$catn->dbuser,$catn->dbpassword);

// Does a report already exist?
	if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/peds/pass/reports/general.pdf')) {
		unlink($_SERVER['DOCUMENT_ROOT'] . '/peds/pass/reports/general.pdf');
	}

// Generate the PDF Report and output the link to it
	$pdf = new FPDF('P','mm','Letter');
	$pdf->AddPage();
	$pdf->SetFont('Courier','',10);
	
	$pdf->Write(5,'+ - - - - - - - - - - - - - - - - - - - PASS REPORT - - - - - - - - - - - - - - - - - - - +');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Write(5,'GENERAL PASS REPORT');
	
	$query = "select * from pass.requestMatrix";
	$result = mysql_query($query);
	$totalNum = mysql_num_rows($result);
	
	$pdf->Ln();
	$pdf->Write(5,'     There have been '.$totalNum.' requests made in total.');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Write(5,'+ - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - +');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('','U');
	$pdf->Write(5,'Teachers:');
	$pdf->SetFont('','');
	$pdf->Ln();
	
	// Go through the teachers
	$query6 = "select teachers.userID, teachers.isOverflow, user.firstName, user.lastName from pass.teachers, catnet.user where teachers.userID=user.userID order by user.lastName, user.firstName";
	$result6 = mysql_query($query6);
	while ($row6 = mysql_fetch_array($result6)) {
		$query2 = "select requestID from pass.requestMatrix where teacherID=".$row6['userID'];
		$result2 = mysql_query($query2);
		$resultNum = mysql_num_rows($result2);
		$requestOverflow = ($row6['isOverflow'] == 1) ? " (OVERFLOW)" : "";
		$pdf->Write(5,'     '.$row6['firstName'].' '.$row6['lastName'].$requestOverflow.'     '.$resultNum.' Requests');
		$pdf->Ln();
	}

	$pdf->Ln();
	$pdf->Write(5,'+ - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - +');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('','U');
	$pdf->Write(5,'Students:');
	$pdf->SetFont('','');
	$pdf->Ln();
	
	// Go through the students
	$query6 = "select students.userID, students.classOf, user.firstName, user.lastName from pass.students, catnet.user where students.userID=user.userID and students.isEnabled=1 order by user.lastName, user.firstName";
	$result6 = mysql_query($query6);
	while ($row6 = mysql_fetch_array($result6)) {
		$query2 = "select requestID from pass.requestMatrix where studentID=".$row6['userID'];
		$result2 = mysql_query($query2);
		$resultNum = mysql_num_rows($result2);
		$pdf->Write(5,'     '.$row6['firstName'].' '.$row6['lastName'].' ('.$row6['classOf'].')     '.$resultNum.' Requests');
		$pdf->Ln();
	}
	
	$pdf->Output($_SERVER['DOCUMENT_ROOT'] . '/peds/pass/reports/general.pdf','F');
	
	echo "<br><center><a href='../peds/pass/reports/general.pdf' target='_blank'>Download General Report (PDF)</a></center><br>&nbsp;";
	
?>