<?php
include 'dbconn.php';
include 'navbars.php';
include "functions.php";
$profile = $_SESSION['profile'];

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

if (isset($_POST['submit'])) { 
    $rfid = $_POST['rfid']; // Get the RFID value from the form

    // Step 1: Fetch the student's ID number using RFID
    $studentQuery = "SELECT ID_number, Student_name FROM students WHERE rfid = '$rfid'";
    $studentResult = mysqli_query($conn, $studentQuery);

    if (mysqli_num_rows($studentResult) > 0) {
        $student = mysqli_fetch_assoc($studentResult);
        $ID_number = $student['ID_number'];
        $student_name = $student['Student_name'];

        // Step 2: Fetch all event names from the `events` table
        $eventsQuery = "SELECT Event_name, Date FROM events";
        $eventsResult = mysqli_query($conn, $eventsQuery);

        $attendanceRecords = [];

        while ($event = mysqli_fetch_assoc($eventsResult)) {
          $eventName = $event['Event_name'];
          $eventDate = $event['Date'];
      
          // Step 3: Check attendance for the event
          $attendanceCheckQuery = "SELECT TimeIn, TimeOut FROM `$eventName` WHERE ID_number = '$ID_number'";
          $attendanceResult = mysqli_query($conn, $attendanceCheckQuery);
      
          if (mysqli_num_rows($attendanceResult) > 0) {
              $attendance = mysqli_fetch_assoc($attendanceResult);
              $timeIn = $attendance['TimeIn'] ?? "Not Recorded"; // Use default if TimeIn is NULL
              $timeOut = $attendance['TimeOut'] ?? "Not Recorded"; // Use default if TimeOut is NULL
      
              // Determine status
              if ($timeOut == "Not Recorded" || $timeOut == null) {
                  $status = "No Time Out"; // No TimeOut recorded
              } else {
                  $status = "Present"; // Attendance recorded with both TimeIn and TimeOut
              }
          } else {
              $status = "Absent"; // Student is not in the event table
              $timeIn = "Not Applicable";
              $timeOut = "Not Applicable";
          }
      
          $attendanceRecords[] = [
              'event_name' => $eventName,
              'event_date' => $eventDate,
              'status' => $status,
              'time_in' => $timeIn,
              'time_out' => $timeOut
          ];
      }
       
    }else {
      echo "<script>alert('Student Not Found'); window.location.href='dashboard.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Records</title>
    <link rel="stylesheet" href="dash_styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .student_name { margin-top: 5pc; }
        .tableList { 
            margin-top: 2pc;
            margin-right: 4pc;  
            margin-left: 10pc;
        }
        .search{

        }
    </style>
</head>
<body>
<section>
    <header class="p-3 mb-3 border-bottom bg-light">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
                <a href="#" class="d-flex align-items-center mb-2 mb-lg-0 link-body-emphasis text-decoration-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </a>
                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                    <li><a href="#" class="nav-link px-2 link-body-emphasis"><h5 class="dash">SEARCH STUDENT ATTENDANCE</h5></a></li>
                </ul>
                <div class="dropdown text-end">
                    <a href="#" class="d-block link-body-emphasis text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo $profile;?>" alt="Admin" class="rounded-circle" width="60">
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
    <br>
    <div class="search">
        <form class="d-flex d-inline-flex" role="search" method="post">
            <input class="form-control border border-danger" name="rfid" type="number" placeholder="Scan an ID" required autofocus>
            <button class="btn btn-outline-danger ms-2" name="submit" type="submit">Search</button>
        </form>
    </div>

    <?php if (isset($student_name)): ?>
        <h1 class="text-center text-decoration-underline link-underline-danger student_name"><?= htmlspecialchars($student_name) ?></h1>
        
        <section class="tableList">
            <table class="table border border-dark table-bordered">
                <thead class="border border-dark table-info">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">EVENTS</th>
                        <th scope="col">Date</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendanceRecords as $index => $record): ?>
                        <tr>
                            <th scope="row"><?= $index + 1 ?></th>
                            <td><?= htmlspecialchars($record['event_name']) ?></td>
                            <td><?= htmlspecialchars($record['event_date']) ?></td>
                            <td class=" fw-bold <?= ($record['status'] == 'Absent' || $record['status'] == 'No Time Out') ? 'text-danger' : 'text-success' ?>">

                                <?= htmlspecialchars($record['status']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger text-center" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <br><br>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
