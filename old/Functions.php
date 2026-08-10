<?php
session_start();
include 'dbconn.php';
class ADD {
    private $event_name;
    private $date;
    private $semester;
    private $TimeIN_in;
    private $TimeIN_out;
    private $TimeOUT_in;
    private $TimeOUT_out;

    public function __construct($event_name, $date, $semester, $TimeIN_in, $TimeIN_out, $TimeOUT_in, $TimeOUT_out, $conn) {
        $this->event_name = $event_name;
        $this->date = $this->formatDate($date); // Format the date as "December 21, 2024"
        $this->semester = $semester;
        $this->TimeIN_in = $TimeIN_in;
        $this->TimeIN_out = $TimeIN_out;
        $this->TimeOUT_in = $TimeOUT_in;
        $this->TimeOUT_out = $TimeOUT_out;

        $this->insertEvent($conn);
        $this->createEventTable($conn);
    }

    // Format the date to "Month Day, Year"
    private function formatDate($date) {
        $dateObject = DateTime::createFromFormat('Y-m-d', $date); // Assuming input is in "Y-m-d" format
        return $dateObject->format('F j, Y'); // Example: "December 21, 2024"
    }

    private function insertEvent($conn) {
        $sql_insert = "INSERT INTO `events` (Event_name, date, Semester, TimeIN_in, TimeIN_out, TimeOUT_in, TimeOUT_out) 
                       VALUES ('$this->event_name', '$this->date', '$this->semester', '$this->TimeIN_in', '$this->TimeIN_out', '$this->TimeOUT_in', '$this->TimeOUT_out')";

        if ($conn->query($sql_insert) === TRUE) {
            // echo "New record created successfully for '$this->event_name'.";

            echo "<script>alert('New record created successfully for $this->event_name'); window.location.href='dashboard.php';</script>";
        } else {
            echo "Error inserting data: " . $conn->error;
        }
    }

    private function createEventTable($conn) {
        $sql_create = "CREATE TABLE IF NOT EXISTS `$this->event_name` (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ID_number VARCHAR(50) NOT NULL,
            Student_name VARCHAR(50) NOT NULL,
            Course VARCHAR(50) NOT NULL,
            TimeIN  VARCHAR(50) NOT NULL,
            TimeOUT  VARCHAR(50) NOT NULL
        )";

        if ($conn->query($sql_create) === TRUE) {
            echo ".";
        } else {
            echo "Error creating table: " . $conn->error;
        }
    }
}


// class ADD {
    //     private $event_name;
    //     private $date;
    //     private $semester;
    //     private $TimeIN_in;
    //     private $TimeIN_out;
    //     private $TimeOUT_in;
    //     private $TimeOUT_out;

    //     public function __construct($event_name, $date, $semester, $TimeIN_in, $TimeIN_out, $TimeOUT_in, $TimeOUT_out, $conn) {
    //         $this->event_name = $event_name;
    //         $this->date = $date;
    //         $this->semester = $semester;
    //         $this->TimeIN_in = $TimeIN_in;
    //         $this->TimeIN_out = $TimeIN_out;
    //         $this->TimeOUT_in = $TimeOUT_in;
    //         $this->TimeOUT_out = $TimeOUT_out;

    //         $this->insertEvent($conn);
    //         $this->createEventTable($conn);
    //     }

    //     private function insertEvent($conn) {
    //         $sql_insert = "INSERT INTO `events` (Event_name, date, Semester, TimeIN_in, TimeIN_out, TimeOUT_in, TimeOUT_out) 
    //                        VALUES ('$this->event_name', '$this->date', '$this->semester', '$this->TimeIN_in', '$this->TimeIN_out', '$this->TimeOUT_in', '$this->TimeOUT_out')";

    //         if ($conn->query($sql_insert) === TRUE) {
    //             echo "New record created successfully for '$this->event_name'.";
    //         } else {
    //             echo "Error inserting data: " . $conn->error;
    //         }
    //     }

    //     private function createEventTable($conn) {
    //         $sql_create = "CREATE TABLE IF NOT EXISTS `$this->event_name` (
    //             id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    //             ID_number VARCHAR(50) NOT NULL,
    //             Student_name VARCHAR(50) NOT NULL,
    //             Course VARCHAR(50) NOT NULL,
    //             TimeIN  VARCHAR(50) NOT NULL,
    //             TimeOUT  VARCHAR(50) NOT NULL
    //         )";

    //         if ($conn->query($sql_create) === TRUE) {
    //             echo "Table '$this->event_name' created successfully.";
    //         } else {
    //             echo "Error creating table: " . $conn->error;
    //         }
    //     }
    // }
///////////RFID submit////////////////////////////////////////////////////////////////////////////////

// Function to handle RFID submissions for Time In and Time Out
    // function processRFID($conn, $rfid) {
    //     date_default_timezone_set('Asia/Manila'); // Ensure timezone is set
    //     $currentDate = date('Y-m-d');
    //     $currentTime = strtotime('+1 hour', strtotime(date('H:i'))); // Adjust for time offset and remove seconds
    //     $EventName = 'new'; // Ensure this matches the event name in your database

    //     // Fetch event details
    //     $eventQuery = "SELECT Date, TimeIN_in, TimeIN_out, TimeOUT_in, TimeOUT_out 
    //                    FROM events 
    //                    WHERE Event_Name = '$EventName'";
    //     $eventResult = $conn->query($eventQuery);

    //     if ($eventResult && $eventResult->num_rows > 0) {
    //         $event = $eventResult->fetch_assoc();

    //         // Convert event times to timestamps
    //         $event['TimeIN_in'] = strtotime($event['TimeIN_in']);
    //         $event['TimeIN_out'] = strtotime($event['TimeIN_out']);
    //         $event['TimeOUT_in'] = strtotime($event['TimeOUT_in']);
    //         $event['TimeOUT_out'] = strtotime($event['TimeOUT_out']);

    //         // Validate event date
    //         if ($event['Date'] !== $currentDate) {
    //             echo "<script>alert('Invalid date. Please check the event schedule.');</script>";
    //             return;
    //         }

    //         // Fetch student details
    //         $studentQuery = "SELECT ID_number, Student_name, Course 
    //                          FROM students 
    //                          WHERE rfid = '$rfid'";
    //         $studentResult = $conn->query($studentQuery);

    //         if ($studentResult && $studentResult->num_rows > 0) {
    //             $student = $studentResult->fetch_assoc();
    //             $status = "";
    //             $timeIN = null;
    //             $timeOUT = null;

    //             // Check if the student is already clocked in or out
    //             $attendanceQuery = "SELECT TimeIN, TimeOUT 
    //                                 FROM $EventName 
    //                                 WHERE ID_number = '{$student['ID_number']}' 
    //                                 ORDER BY ID DESC LIMIT 1";
    //             $attendanceResult = $conn->query($attendanceQuery);

    //             if ($attendanceResult && $attendanceResult->num_rows > 0) {
    //                 $attendance = $attendanceResult->fetch_assoc();
                    
    //                 // Check if student has already clocked in
    //                 if ($attendance['TimeIN']) {
    //                     echo "<script>alert('Student already Time In');</script>";
    //                     return;
    //                 }

    //                 // Check if student has already clocked out
    //                 if ($attendance['TimeOUT']) {
    //                     echo "<script>alert('Student already Time Out');</script>";
    //                     return;
    //                 }
    //             }

    //             // Check time conditions
    //             if ($currentTime >= $event['TimeIN_in'] && $currentTime <= $event['TimeIN_out']) {
    //                 $status = "on time";
    //                 $timeIN = date('h:i A', $currentTime);
    //             } elseif ($currentTime > $event['TimeIN_out'] && $currentTime <= $event['TimeOUT_in']) {
    //                 $status = "late";
    //                 $timeIN = date('h:i A', $currentTime);
    //             } elseif ($currentTime > $event['TimeOUT_in'] && $currentTime <= $event['TimeOUT_out']) {
    //                 $status = "updating timeout";
    //                 $timeOUT = date('h:i A', $currentTime);
    //             } else {
    //                 echo "<script>alert('Event has Ended. Event ended at " . date('h:i A', $event['TimeOUT_out']) . "');</script>";
    //                 return;
    //             }
                
                
    //             if ($timeIN) {
    //                 $insertQuery = "INSERT INTO $EventName (ID_number, Student_name, Course, TimeIN) 
    //                                 VALUES ('{$student['ID_number']}', '{$student['Student_name']}', '{$student['Course']}', '$timeIN')";
    //                 if (!$conn->query($insertQuery)) {
    //                     echo "<script>alert('Error: {$conn->error}');</script>";
    //                     return;
    //                 }
    //             }

    //             if ($timeOUT) {
    //                 $updateQuery = "UPDATE $EventName 
    //                                 SET TimeOUT = '$timeOUT' 
    //                                 WHERE ID_number = '{$student['ID_number']}' AND (TimeOUT IS NULL OR TimeOUT = '')";
                    
    //                 if (!$conn->query($updateQuery)) {
    //                     echo "<script>alert('Error updating TimeOUT: {$conn->error}');</script>";
    //                     return;
    //                 }
    //             }

    //             echo "<script>alert('Attendance Recorded: $status');</script>";
    //         } else {
    //             echo "<script>alert('Student Not Found');</script>";
    //         }
    //     } else {
    //         echo "<script>alert('Event not found or mismatched');</script>";
    //     }
    // }

///////////////STABLE CODE//////////
    // function processRFID($conn, $rfid) {
    //     date_default_timezone_set('Asia/Manila'); // Ensure timezone is set
    //     $currentDate = date('Y-m-d');
    //     $currentTime = strtotime('+1 hour', strtotime(date('H:i'))); // Adjust for time offset and remove seconds
    //     $EventName = 'Jhomong_day'; // Ensure this matches the event name in your database

    //     // Fetch event details
    //     $eventQuery = "SELECT Date, TimeIN_in, TimeIN_out, TimeOUT_in, TimeOUT_out 
    //                    FROM events 
    //                    WHERE Event_Name = '$EventName'";
    //     $eventResult = $conn->query($eventQuery);

    //     if ($eventResult && $eventResult->num_rows > 0) {
    //         $event = $eventResult->fetch_assoc();

    //         // Convert event times to timestamps
    //         $event['TimeIN_in'] = strtotime($event['TimeIN_in']);
    //         $event['TimeIN_out'] = strtotime($event['TimeIN_out']);
    //         $event['TimeOUT_in'] = strtotime($event['TimeOUT_in']);
    //         $event['TimeOUT_out'] = strtotime($event['TimeOUT_out']);

    //         // Validate event date
    //         if ($event['Date'] !== $currentDate) {
    //             echo "<script>alert('Invalid date. Please check the event schedule.');</script>";
    //             return;
    //         }

    //         // Fetch student details
    //         $studentQuery = "SELECT ID_number, Student_name, Course 
    //                          FROM students 
    //                          WHERE rfid = '$rfid'";
    //         $studentResult = $conn->query($studentQuery);

    //         if ($studentResult && $studentResult->num_rows > 0) {
    //             $student = $studentResult->fetch_assoc();
    //             // $status = "";
    //             $timeIN = null;
    //             $timeOUT = null;

    //             // Check the student's last attendance record
    //             $attendanceQuery = "SELECT TimeIN, TimeOUT 
    //                                 FROM $EventName 
    //                                 WHERE ID_number = '{$student['ID_number']}' 
    //                                 ORDER BY ID DESC LIMIT 1";
    //             $attendanceResult = $conn->query($attendanceQuery);

    //             $lastAttendance = $attendanceResult->fetch_assoc();
    //             $hasTimeIN = !empty($lastAttendance['TimeIN']);
    //             $hasTimeOUT = !empty($lastAttendance['TimeOUT']);

    //             // Determine attendance action
    //             if ($currentTime >= $event['TimeIN_in'] && $currentTime <= $event['TimeIN_out'] && !$hasTimeIN) {
    //                 // $status = "on time";
    //                 echo "<script>alert('On Time');</script>";
    //                 $timeIN = date('h:i A', $currentTime);
    //             } elseif ($currentTime > $event['TimeIN_out'] && $currentTime <= $event['TimeOUT_in'] && !$hasTimeIN) {
    //                 // $status = "late";
    //                 echo "<script>alert('Late');</script>";
    //                 $timeIN = date('h:i A', $currentTime);
    //             } elseif ($currentTime > $event['TimeOUT_in'] && $currentTime <= $event['TimeOUT_out'] && $hasTimeIN && !$hasTimeOUT) {
    //                 // $status = "updating timeout";
    //                 echo "<script>alert('updating timeout');</script>";
    //                 $timeOUT = date('h:i A', $currentTime);
    //             }
    //             elseif($hasTimeIN && $currentTime < $event['TimeIN_out']){
    //                 echo "<script>alert('Student Already Time In');</script>";
    //             }elseif($hasTimeOUT){
    //                 echo "<script>alert('Student Already Time Out');</script>";
    //             }elseif ($currentTime > $event['TimeOUT_in'] && $currentTime <= $event['TimeOUT_out'] && !$hasTimeIN && !$hasTimeOUT) {
    //                 // $status = "updating timeout";
    //                 echo "<script>alert('Student Did Not Time IN!');</script>";
    //                 $NoTimeIn = date('h:i A', $currentTime);
    //             }
                
    //             elseif($currentTime < $event['TimeOUT_out']){
    //                 echo "<script>alert('Event Attendance Has Ended');</script>";
    //             }else {
    //                 echo "<script>alert('Invalid operation. Check the event schedule or attendance status.');</script>";
    //                 return;
    //             }

    //             // Insert or update attendance records
    //             if ($timeIN) {
    //                 $insertQuery = "INSERT INTO $EventName (ID_number, Student_name, Course, TimeIN) 
    //                                 VALUES ('{$student['ID_number']}', '{$student['Student_name']}', '{$student['Course']}', '$timeIN')";
    //                 if (!$conn->query($insertQuery)) {
    //                     echo "<script>alert('Error: {$conn->error}');</script>";
    //                     return;
    //                 }
    //             }

    //             if ($timeOUT) {
    //                 $updateQuery = "UPDATE $EventName 
    //                                 SET TimeOUT = '$timeOUT' 
    //                                 WHERE ID_number = '{$student['ID_number']}' AND (TimeOUT IS NULL OR TimeOUT = '')";
    //                 if (!$conn->query($updateQuery)) {
    //                     echo "<script>alert('Error updating TimeOUT: {$conn->error}');</script>";
    //                     return;
    //                 }
    //             }

    //             // echo "<script>alert('Attendance Recorded: $status');</script>";
    //         } else {
    //             echo "<script>alert('Student Not Found');</script>";
    //         }
    //     } else {
    //         echo "<script>alert('Event not found or mismatched');</script>";
    //     }
    // }


$event_name = isset($_SESSION['eventName']) ? $_SESSION['eventName'] : 'no_event_selected';

    // Fetch event name from database if 'id' is passed in the URL
    if (isset($_GET['id'])) {
        $eventId = $_GET['id'];
    
        // Fetch the event name from the database using the event ID
        $sql = "SELECT Event_name FROM events WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $eventName = $row['Event_name'];
    
            // Store the event name in session
            $_SESSION['eventName'] = $eventName;
            $event_name = $eventName; // Update the local variable
        }
    }





function processRFID($conn, $rfid) {
    date_default_timezone_set('Asia/Manila'); // Ensure timezone is set
    // $currentDate = date('Y-m-d');
    $currentDate = date('F j, Y');
    $currentTime = strtotime('+1 hour', strtotime(date('H:i'))); // Adjust for time offset and remove seconds
    global $event_name;
    $EventName = $event_name;
   
    $alertMessage = ""; // Placeholder for alert message
    $alertType = "";    // Placeholder for alert type

    // Fetch event details
    $eventQuery = "SELECT Date, TimeIN_in, TimeIN_out, TimeOUT_in, TimeOUT_out 
                   FROM events 
                   WHERE Event_Name = '$EventName'";
    $eventResult = $conn->query($eventQuery);

    if ($eventResult && $eventResult->num_rows > 0) {
        $event = $eventResult->fetch_assoc();

        // Convert event times to timestamps
       
        $event['TimeIN_in'] = strtotime($event['TimeIN_in']);
        $event['TimeIN_out'] = strtotime($event['TimeIN_out']);
        $event['TimeOUT_in'] = strtotime($event['TimeOUT_in']);
        $event['TimeOUT_out'] = strtotime($event['TimeOUT_out']);

        // Validate event date
        if (trim($event['Date']) !== $currentDate) {
            $alertMessage = "Invalid date. Please check the event schedule.";
            $alertType = "danger";
            return ["message" => $alertMessage, "type" => $alertType];
        }

        // Fetch student details
        $studentQuery = "SELECT ID_number, Student_name, Course 
                         FROM students 
                         WHERE rfid = '$rfid'";
        $studentResult = $conn->query($studentQuery);

        if ($studentResult && $studentResult->num_rows > 0) {
            $student = $studentResult->fetch_assoc();
            $timeIN = null;
            $timeOUT = null;

            // Check the student's last attendance record
            $attendanceQuery = "SELECT TimeIN, TimeOUT 
                                FROM $EventName 
                                WHERE ID_number = '{$student['ID_number']}' 
                                ORDER BY ID DESC LIMIT 1";
            $attendanceResult = $conn->query($attendanceQuery);

            $lastAttendance = $attendanceResult->fetch_assoc();
            $hasTimeIN = !empty($lastAttendance['TimeIN']);
            $hasTimeOUT = !empty($lastAttendance['TimeOUT']);
          
            if ($currentTime >= $event['TimeIN_in'] && $currentTime <= $event['TimeIN_out'] && !$hasTimeIN) {
                $alertMessage = "On Time";
                $alertType = "success";
                $timeIN = date('h:i A', $currentTime);
            } elseif ($currentTime > $event['TimeIN_out'] && $currentTime <= $event['TimeOUT_in'] && !$hasTimeIN) {
                $alertMessage = "Late";
                $alertType = "danger";
                $timeIN = date('h:i A', $currentTime);
            } elseif ($currentTime > $event['TimeOUT_in'] && $currentTime <= $event['TimeOUT_out'] && $hasTimeIN && !$hasTimeOUT) {
                $alertMessage = "Timeout Updated";
                $alertType = "success";
                $timeOUT = date('h:i A', $currentTime);
            } elseif ($hasTimeIN && $currentTime <= $event['TimeOUT_in']) {
                $alertMessage = "Student Already Time In";
                $alertType = "danger";
            } elseif ($hasTimeOUT) {
                $alertMessage = "Student Already Time Out";
                $alertType = "danger";
            } elseif ($currentTime > $event['TimeOUT_in'] && $currentTime < $event['TimeOUT_out'] && !$hasTimeIN) {
                $alertMessage = "Student Did Not Time IN!";
                $alertType = "danger";
            } elseif ($currentTime > $event['TimeOUT_out']) {
                $alertMessage = "Event Attendance Has Ended";
                $alertType = "danger";
            } else {
                $alertMessage = "Invalid operation. Check the event schedule";
                $alertType = "danger";
                return;
            }

         
            if ($timeIN) {
                $insertQuery = "INSERT INTO $EventName (ID_number, Student_name, Course, TimeIN) 
                                VALUES ('{$student['ID_number']}', '{$student['Student_name']}', '{$student['Course']}', '$timeIN')";
                if (!$conn->query($insertQuery)) {
                    $alertMessage = "Error: {$conn->error}";
                    $alertType = "danger";
                  
                    return;
                }
            }

            if ($timeOUT) {
                $updateQuery = "UPDATE $EventName 
                                SET TimeOUT = '$timeOUT' 
                                WHERE ID_number = '{$student['ID_number']}' AND (TimeOUT IS NULL OR TimeOUT = '')";
                if (!$conn->query($updateQuery)) {
                    $alertMessage = "Error updating TimeOUT: {$conn->error}";
                    $alertType = "danger";
                   
                    return;
                }
            }
        } else {
            $alertMessage = "Student Not Found";
            $alertType = "danger";
            
        }
    } else {
        $alertMessage = "Event not found or mismatched";
        $alertType = "danger";
        
    }

    return ["message" => $alertMessage, "type" => $alertType];
}



// Function to fetch attendance data
// function fetchAttendanceData($conn) {
//     global $event_name;
//     $EventName =  $event_name;

// $attendanceQuery = "SELECT * FROM `$EventName`";
// return $conn->query($attendanceQuery);
// }

function fetchAttendanceData($conn) {
    global $event_name;
    $EventName = $event_name;

    // Check if the table exists
    $checkTableQuery = "SHOW TABLES LIKE '$EventName'";
    $checkTableResult = $conn->query($checkTableQuery);

    if ($checkTableResult && $checkTableResult->num_rows > 0) {
        // Table exists, fetch attendance data from the event table
        $attendanceQuery = "SELECT * FROM `$EventName`";
    } else {
        // Table does not exist, fetch attendance data from the no_event_selected table
        $attendanceQuery = "SELECT * FROM `no_event_selected`";
    }

    return $conn->query($attendanceQuery);
}




function fetchData($conn) {
    global $event_name;
$EventName =  $event_name;
$query = "SELECT `Date`, `TimeIN_in`, `TimeIN_out`, `TimeOUT_in`, `TimeOUT_out` 
          FROM `events` 
          WHERE `event_name` = '$EventName'";
$result = $conn->query($query);
return $result && $result->num_rows > 0 ? $result->fetch_assoc() : null; // Return the first row or null
}



?>