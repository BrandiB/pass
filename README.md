pass
====

"Promoting Academic Student Success"

A Web-based Program for Management of Study Hall Requests

====

CONTENTS:
  Description
  Databases
  Structure

====

DESCRIPTION:
The school I worked at during the 2013-2014 school year uses block scheduling.  On each A day and B day, there are 4 large blocks (8 classes total) as well as a 45-minute block known as PASS.  This serves as a homeroom and study hall.  At the beginning of PASS, while the day's announcements are being broadcast, teachers log-in to the PASS web interface (i.e. this program) to check their rosters. They can see which students have been requested by other teachers, who they themselves have requested, etc.  A second bell rings after the announcements, at which time the requested students go to the classrooms of the teachers who requested them.  The remaining time is study hall.

Teachers can request students for a variety of reasons.  They request students through the program.  In the case of multiple requests for the same student, the program determines through a system of trumps (day of the week, category of request, time request was made) which teacher the student will be sent to.  Each subject (English, Math, Social Studies, Science, Other) has a day of the week (Monday through Friday) when it receives preferential treatment by the trump system.

If you would like to use this code to implement a similar system, please keep in mind that I did not have a lot of time to implement and refine this program before the start of school, so it is not as efficient as it should be and it lacks a proper administrative interface for managing students.  It clunkily made use of an existing user database (catnet.user below) tied in to our school's active directory, and this database was not optimized for this program.  The program was integrated into an intranet system already in place that required teachers to log in to it; the keyid being passed back and forth tells the system who is logged in and handles the session, and it is also based on the catnet.user table below.

I am only placing the files on here that I wrote for this program.  Files that relate to the log-in system for the intranet server are not here, but I am marking them in the program so that they can be replaced with other systems for handling users.

====

DATABASES:

The program uses the following MySQL tables and columns:

  Already existing:
      
      catnet.user
          userID
          firstName
          lastName
          locationID
          accessLevelID
          loginName
          shaPassword
          password
          sessid
          appkey
    
  Created for the program:
  
      pass.absentmatrix
          absentID
          studentID
          dateAbsent
      
      pass.category
          categoryID
          categoryName
          categoryLevel
      
      pass.homeroom
          homeroomID
          userIDStudent
          userIDTeacher
      
      pass.requestmatrix
          requestID
          studentID
          categoryID
          isTrump
          dateRequested
          teacherID
      
      pass.students
          studentID
          userID
          classOf
          isEnabled
      
      pass.subjects
          subjectID
          subjectName
      
      pass.teachers
          teacherID
          userID
          subjectID
          isOverflow
          maxNumberStudents
      
      pass.trumpday
          trumpID
          subjectID
          whichDay

====

STRUCTURE:

    index.php
      This set up the pass program within the existing intranet system; it displays the program in the largest available        frame.

    images
      This folder contains the following program graphics:
      ___________________
      blank-arrow.png
      delete.png
      down-arrow.png
      trash_can.png
      up-arrow.png

    process
      This folder includes php files which perform actions:
      ___________________
      addCategory.php
      addStudent.php
      addSubject.php
      cancelRequest.php
      changeAbsence.php
      changeOverflow.php
      changeSubject.php
      changeTrumpDay.php
      deleteCategory.php
      deleteStudent.php
      deleteSubject.php
      fixUserNames.php
      groupMembers.php
      homeroomQuery.php
      insertRequest.php
      maximumStudents.php
      moveCategory.php
      nameSearch.php
      overflowStudent.php
      syncDatabases.php

    reports
      This folder contains the reports generated through the reporting feature. (PDFs)
      
    templates
      This folder includes the files that determine what the program looks like for the user:
      ___________________
      FPDF - php PDF creation library - available at http://www.fpdf.org/
        changelog.htm
        doc - 47 files
        FAQ.htm
        font - 14 files
        fpdf.css
        fpdf.php
        install.txt
        license.txt
        makefont - 22 files
        tutorial - 23 files
        
      addCategory.php
      addStudent.php
      addSubject.php
      cancelRequest.php
      changeSubject.php
      changeTrumpDay.php
      checkStudents.php
      createGeneral.php
      createReport.php
      manageRoster.php
      manageTeachers.php
      overflow.php
      pass.php
      request.php
      viewAnotherRoster.php
      viewRoster.php
      
