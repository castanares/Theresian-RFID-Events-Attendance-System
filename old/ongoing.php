<?php
include "navbars.php";
include "functions.php";
include "dbconn.php";

if (!isset($_SESSION['username'])) {
  echo "<script>alert('Please login first'); window.location.href='login.php';</script>";
  exit();
}


if (isset($_POST['logout'])) {
  // session_start();
  session_unset();
  session_destroy();
  echo "<script>alert('Logged out successfully'); window.location.href='login.php';</script>";
}

// Check if the profile session variable is set before accessing it
if (isset($_SESSION['profile'])) {
    $profile = $_SESSION['profile'];
} else {
    $profile = null; // or handle the case where the profile is not set
}



$attendanceResult = fetchAttendanceData($conn);
$Data = fetchData($conn);



if (isset($_POST["submit"])) {
  $rfid = $_POST['rfid'];
  $response = processRFID($conn, $rfid);

  // Check if $response is valid and contains the expected keys
  if (is_array($response) && isset($response['message']) && isset($response['type'])) {
      $alertMessage = $response['message'];
      $alertType = $response['type'];
  }
   else {
      // Provide fallback defaults if $response is invalid
      $alertMessage = "An error occurred while processing RFID. Please Check The Event Schedule.";
      $alertType = "danger";
  }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="dash_styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
</head>
<style>
  /* Chrome, Safari, Edge, Opera */
      input::-webkit-outer-spin-button,
      input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
      }

  /* Firefox */
      input[type=number] {
        -moz-appearance: textfield;
      }

      .scan{
        margin-left:3pc !important;
      }
      .edit-back{
        background-color: maroon;
      }
      <style>
  .btn-primary-fade {
    background-color: #28a745;
    transition: background-color 0.3s ease;
  }

  .btn-primary-fade:hover {
    background-color: #218838;
  }
</style>




<body>


<section>
    <header class="p-3 mb-3 border-bottom bg-light">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                <a href="#" class="d-flex align-items-center mb-2 mb-lg-0 link-body-emphasis text-decoration-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-calendar-check" viewBox="0 0 16 16">
                  <path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                  <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                </svg>
                </a>
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                    <li><a href="#" class="nav-link px-2 link-body-emphasis"><h5 class="dash">ONGOING EVENT</h5></a></li>
                </ul>
                <div class="dropdown text-end">
                    <a href="#" class="d-block link-body-emphasis text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo $profile;?>" alt="Profile" class="rounded-circle" width="60">
                        <!-- <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                            <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                        </svg> -->
                    </a>
                    <form action="" method="post">
                        <ul class="dropdown-menu text-small">
                            <!-- <li><a class="dropdown-item active" aria-current="true" href="#">New project...</a></li>
                            <li><a class="dropdown-item" href="#">Settings</a></li> -->
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button type="submit" name="logout" class="dropdown-item">Sign out</button>
                            </li>
                        </ul>
                    </form>
                </div>
                <div class="account_name">
                    <h6><?php echo $_SESSION['username']?></h6>
                </div>
            </div>
        </div>
    </header>
</section>

      

  </section>
<section>
<section>
<?php
      $data = fetchData($conn);
      if ($data) {
          // Access event data directly
          $timeIn_in = $data['TimeIN_in'] ?? 'Not Available';
          $timeIn_out = $data['TimeIN_out'] ?? 'Not Available';
          $timeOut_in = $data['TimeOUT_in'] ?? 'Not Available';
          $timeOut_out = $data['TimeOUT_out'] ?? 'Not Available';
          $date = $data['Date'] ?? 'Not Available';

         
      
    ?>
    <form action="ongoing.php" method="post">
  <div class="modal fade" tabindex="-1" id="Edit-modal">
    <div class="modal-dialog modal-lg ">
      <div class="modal-content ">
        
        <div class="modal-header edit-back text-white">
          <h5 class="modal-title">Edit Events</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="mb-3">
              <label class="fw-bold">Event Name:</label>
              <input type="text" name="event_name" class="form-control" value="<?php echo $event_name;?>" required oninput="replaceSpaces(this)">
            </div>
            <div class="mb-3 row">
              <div class="col">
                <label class="fw-bold">Event Date:</label>
                <input type="text" name="date" class="form-control" value="<?php echo $date; ?>" required>
              </div>
              <div class="col">
                <label class="fw-bold">Semester:</label>
                <input type="text" name="semester" class="form-control " value="FIRST" readonly>
              </div>
            </div>
            <div class="mb-3">
            <div class="text-center"> <label class="fw-bold text-decoration-underline link-underline-danger fs-4">Event Time</label> </div>
              <div class="row">
                <div class="col">
                  <label class="fw-bold">Time In:</label>
                  <input type="text" name="TimeIN_in" placeholder="START" onfocus="showTimePicker(this)" onblur="convertTo12HourFormat(this)" class="form-control text-center " value="<?php echo $timeIn_in; ?>" required>
                </div>
                <div class="col">
                  <label class="fw-bold">Time Out:</label>
                  <input type="text" name="TimeIN_out" placeholder="END" onfocus="showTimePicker(this)" onblur="convertTo12HourFormat(this)" class="form-control text-center" value="<?php echo $timeIn_out; ?>" required>
                </div>
                <div class="col">
                  <label class="fw-bold">Time In:</label>
                  <input type="text" name="TimeOUT_in" placeholder="START" onfocus="showTimePicker(this)" onblur="convertTo12HourFormat(this)" class="form-control text-center " value="<?php echo $timeOut_in; ?>" required>
                </div>
                <div class="col">
                  <label class="fw-bold">Time Out:</label>
                  <input type="text" name="TimeOUT_out" placeholder="END" onfocus="showTimePicker(this)" onblur="convertTo12HourFormat(this)" class="form-control text-center " value="<?php echo $timeOut_out; ?>" required>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn edit-back btn-primary-fade text-light" name="save">Save changes</button>
        </div>
      </div>
    </div>
  </div>
  <?php }  ?>
  </form>
</section>

 
 <?php
  if(empty($event_name)){
    $event_name = 'No Event Selected';
    return false;
  }
  ?>
  <h1 class="text-center text-decoration-underline link-underline-danger text-uppercase"><?php echo $event_name;?>
    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-pencil-square fw-bold" viewBox="0 0 16 16" data-bs-toggle="modal" data-bs-target="#Edit-modal" style="cursor: pointer;">
    <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
    <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
  </svg></h1>
  
</section>


<br>
<section class="d-md-flex justify-content-md-center scan">
<form action="ongoing.php" method="post">
    <div class="d-grid gap-2 d-md-flex justify-content-md-end start-end me-5">
        <input type="number" name="rfid" id="" autofocus placeholder="Scan Here" class="rounded" required>
        <button type="submit" name="submit" class="btn btn-primary rounded">Submit</button>
    </div>
</form>
</section>
<div class="d-flex flex-row-reverse me-5">
  <a href="pdf.php" target="_blank">
    <button type="button" class="btn btn-outline-danger text-dark border-3 download me-5">Download PDF</button>
  </a>
</div>



<section class="event-table">
  <table class="table border border-dark table-bordered">
    <thead class="table-info border border-dark ">
      <tr>
      
        <th scope="col" rowspan="2" class="text-center">#</th>
        <th scope="col" rowspan="2">ID NUMBER</th>
        <th scope="col" rowspan="2">STUDENT NAME</th>
        <th scope="col" class="text-center">COURSE</th>
        <th scope="col" class="text-center">TIME IN</th>
        <th scope="col" class="text-center">TIME OUT</th>
      </tr>

    </thead>
    <tbody id="attendanceTableBody">
    <?php 
    $eventData = fetchData($conn); // Fetch event details

    // Parse event times
    $eventTimeIN = $eventData ? strtotime($eventData['TimeIN_in']) : null;
    $eventTimeOUT = $eventData ? strtotime($eventData['TimeIN_out']) : null;

    if ($attendanceResult && $attendanceResult->num_rows > 0): 
        while ($row = $attendanceResult->fetch_assoc()): 
            $studentTimeIN = $row['TimeIN'] ? strtotime($row['TimeIN']) : null;

            // Determine if the student is late
            $isLate = $eventTimeOUT && $studentTimeIN && $studentTimeIN > $eventTimeOUT;

            // Assign class for late attendance
            $timeInClass = $isLate ? 'text-danger fw-bold' : '';
    ?>
        <tr>
            <td class="text-center"><?= htmlspecialchars($row['id']); ?></td>
            <td><?= htmlspecialchars($row['ID_number']); ?></td>
            <td><?= htmlspecialchars($row['Student_name']); ?></td>
            <td class="text-center"><?= htmlspecialchars($row['Course']); ?></td>
            <td class="text-center <?= $timeInClass; ?>">
                <?= htmlspecialchars($row['TimeIN'] ?? ''); ?>
            </td>
            <td class="text-center"><?= htmlspecialchars($row['TimeOUT'] ?? ''); ?></td>
        </tr>
    <?php 
            // Reset $timeInClass to avoid carryover
            $timeInClass = '';
        endwhile; 
    else: 
    ?>
        <tr>
            <td colspan="6" class="text-center">No attendance records found.</td>
        </tr>
    <?php endif; ?>
</tbody>



  </table>

</section>
<br><br><br>


<section>
<footer class="footer d-flex justify-content-center fixed-bottom bg-light z-0">
    <div class="fixed text-center">
    <?php
      $data = fetchData($conn);
      if ($data) {
          // Access event data directly
          $timeIn_in = $data['TimeIN_in'] ?? 'Not Available';
          $timeIn_out = $data['TimeIN_out'] ?? 'Not Available';
          $timeOut_in = $data['TimeOUT_in'] ?? 'Not Available';
          $timeOut_out = $data['TimeOUT_out'] ?? 'Not Available';
          $date = $data['Date'] ?? 'Not Available';

          echo "<h5>Event Start: $timeIn_in - $timeIn_out</h5>";
          echo "<h5>Event End: $timeOut_in - $timeOut_out</h5>";
          // echo $event_name;
      } else {
          // Handle the case when no event data is available
          echo "<h5>No event data available</h5>";
      }
    ?>


    </div>
</footer>

</section>
<div id="floating-alert" class="alert alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x shadow-lg mt-2 d-none" role="alert" style="z-index: 1050; max-width: 90%;"></div>
<script>
function showFloatingAlert(message, type) {
    const alertDiv = document.getElementById("floating-alert");
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x shadow-lg mt-2`;
    alertDiv.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    alertDiv.classList.remove("d-none");
    setTimeout(() => alertDiv.classList.add("d-none"), 3000);
}


<?php 
if (!empty($alertMessage) && !empty($alertType)): ?>
    document.addEventListener('DOMContentLoaded', function () {
        showFloatingAlert(<?= json_encode($alertMessage); ?>, <?= json_encode($alertType); ?>);
    });
<?php endif; ?>
</script>

<script>
function showTimePicker(input) {
    input.type = "time"; // Change to time picker
}

function convertTo12HourFormat(input) {
    const timeValue = input.value; // Capture the value before switching back
    if (!timeValue) return; // Skip if no time selected

    // Parse the time
    const [hours, minutes] = timeValue.split(":").map(Number);

    // Convert to 12-hour format
    const period = hours >= 12 ? "PM" : "AM";
    const adjustedHours = hours % 12 || 12; // Convert 0 to 12 for AM times

    // Format time with leading zero for minutes if needed
    const formattedTime = `${adjustedHours}:${String(minutes).padStart(2, '0')} ${period}`;

    // Display the formatted time and switch type back to text
    input.type = "text";
    input.value = formattedTime;
}
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const dateInput = document.querySelector('input[type="date"]');

        if (dateInput) {
            // Get today's date
            const today = new Date();

            // Format today's date in YYYY-MM-DD format
            const todayFormatted = today.toISOString().split('T')[0];

            // Set the maximum date to the last day of the current year
            const maxDate = new Date(today.getFullYear(), 11, 31).toISOString().split('T')[0];

            // Disable past dates and dates beyond the current year
            dateInput.min = todayFormatted;
            dateInput.max = maxDate;
        }
    });

    function replaceSpaces(input) {
  input.value = input.value.replace(/ /g, '_');
}

</script>
<script>
function refreshTable() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_attendance_data.php', true);
    xhr.onload = function() {
        if (this.status === 200) {
            document.getElementById('attendanceTableBody').innerHTML = this.responseText;
        }
    };
    xhr.send();
}

// Refresh the table every 5 seconds (5000 milliseconds)
setInterval(refreshTable, 1000);
</script>



<script src="pdf.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-OFhNyGEOf1BIpDmOzjtCShUJkFvFkcRUDRWYuOfFZVtHLzD9XjyAcHIF1C70aDfx" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
 
  </body>
</html>
<?php

$Event_name = $event_name;

if (isset($_POST['save'])) {
  // Get the form data
  $current_event_name = $Event_name;
  $new_event_name = $_POST['event_name']; // The updated event name from the form
  $date = $_POST['date'];
  $semester = $_POST['semester'];
  $TimeIN_in = $_POST['TimeIN_in'];
  $TimeIN_out = $_POST['TimeIN_out'];
  $TimeOUT_in = $_POST['TimeOUT_in'];
  $TimeOUT_out = $_POST['TimeOUT_out'];

  // Update query for the `events` table
  $update_event_sql = "UPDATE events 
          SET 
              Event_Name = '$new_event_name', 
              Date = '$date', 
              Semester = '$semester', 
              TimeIN_in = '$TimeIN_in', 
              TimeIN_out = '$TimeIN_out', 
              TimeOUT_in = '$TimeOUT_in', 
              TimeOUT_out = '$TimeOUT_out' 
          WHERE Event_Name = '$current_event_name'";

  // Execute the update query
  if ($conn->query($update_event_sql) === TRUE) {
      // echo "Event details updated successfully.";
      echo "<script>alert('Event Updated Successfully'); window.location.href='dashboard.php';</script>";

      // Rename the existing table to the updated event name
      $rename_table_sql = "ALTER TABLE `$current_event_name` RENAME TO `$new_event_name`";

      if ($conn->query($rename_table_sql) === TRUE) {
          // echo "Table renamed successfully to $new_event_name.";
          echo "<script>alert('Table renamed successfully to $new_event_name'); window.location.href='dashboard.php';</script>";

      } else {
        echo "<script>alert('Error renaming table: '  $conn->error');</script>";

          // echo "Error renaming table: " . $conn->error;
      }
  } else {
      echo "Error updating event details: " . $conn->error;
  }

  $conn->close();
}



// $Event_name = $event_name;

// if (isset($_POST['save'])) {
//   // Get the form data
//   $event_name = $_POST['event_name'];
//   $date = $_POST['date'];
//   $semester = $_POST['semester']; 
//   $TimeIN_in = $_POST['TimeIN_in'];
//   $TimeIN_out = $_POST['TimeIN_out'];
//   $TimeOUT_in = $_POST['TimeOUT_in'];
//   $TimeOUT_out = $_POST['TimeOUT_out'];

//   // Update query
//   $sql = "UPDATE events 
//           SET 
//               Event_Name = '$event_name', 
//               Date = '$date', 
//               Semester = '$semester', 
//               TimeIN_in = '$TimeIN_in', 
//               TimeIN_out = '$TimeIN_out', 
//               TimeOUT_in = '$TimeOUT_in', 
//               TimeOUT_out = '$TimeOUT_out' 
//           WHERE Event_Name = '$Event_name'";

//   // Execute the query
//   if ($conn->query($sql) === TRUE) {
//       echo "Event updated successfully.";
//   } else {
//       echo "Error updating event: " . $conn->error;
//   }

 


//   $conn->close();
// }

?>



