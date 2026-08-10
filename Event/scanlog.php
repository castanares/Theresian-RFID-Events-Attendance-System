<?php require_once __DIR__ . '/../auth.php'; require_login('../'); ?>
<?php
require_once __DIR__ . '/../api/dbcon.php';

$student_name = null;
$attendanceRecords = [];
$error = null;
$alert_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        $rfid = trim($_POST['rfid'] ?? '');
        if ($rfid !== '') {
            // Find student
            $stmt = $conn->prepare("SELECT ID_Number, Student_name, status, departments_id, course FROM students WHERE RFID = ? OR ID_Number = ? LIMIT 1");
            $stmt->bind_param('ss', $rfid, $rfid);
            $stmt->execute();
            $student = $stmt->get_result()->fetch_assoc();
            if ($student) {
                if ($student['status'] !== 'active') {
                    $error = 'Student is archived and cannot view attendance records.';
                } else {
                    $student_name = $student['Student_name'];
                    $student_id = $student['ID_Number'];
                    $student_dept = $student['departments_id'];
                    $student_course = $student['course'];

                    // Determine current semester (flexible column names) and restrict events to that semester
                    $semStart = null; $semEnd = null; $wrap = false;
                    $matchedSemId = null; $matchedSemName = null;
                    $curMonth = intval(date('n'));
                    $curYear  = intval(date('Y'));

                    // Compute current school year (Aug–Jul cycle)
                    // e.g. August 2025 → SY 2025–2026; January 2026 → SY 2025–2026
                    if ($curMonth >= 8) {
                        $syStart = $curYear;          // e.g. 2025
                        $syEnd   = $curYear + 1;      // e.g. 2026
                    } else {
                        $syStart = $curYear - 1;      // e.g. 2025
                        $syEnd   = $curYear;          // e.g. 2026
                    }
                    $currentSchoolYear = $syStart . '-' . $syEnd; // "2025-2026"

                    $sres = $conn->query("SELECT * FROM semester");
                    if ($sres) {
                        while ($sr = $sres->fetch_assoc()) {
                            // try a few common column names for start/end
                            $startRaw = $sr['start_date'] ?? $sr['start'] ?? $sr['startDate'] ?? $sr['start_month'] ?? null;
                            $endRaw = $sr['end_date'] ?? $sr['end'] ?? $sr['endDate'] ?? $sr['end_month'] ?? null;
                            $startM = null; $endM = null;
                            if ($startRaw !== null && $startRaw !== '') {
                                if (preg_match('/^\d{1,2}$/', trim($startRaw))) { $startM = intval($startRaw); }
                                else { $ts = strtotime($startRaw); if ($ts) $startM = intval(date('n',$ts)); }
                            }
                            if ($endRaw !== null && $endRaw !== '') {
                                if (preg_match('/^\d{1,2}$/', trim($endRaw))) { $endM = intval($endRaw); }
                                else { $ts = strtotime($endRaw); if ($ts) $endM = intval(date('n',$ts)); }
                            }
                                if ($startM && $endM) {
                                    if ($startM <= $endM) {
                                        if ($curMonth >= $startM && $curMonth <= $endM) { $semStart = $startM; $semEnd = $endM; $wrap = false; $matchedSemId = $sr['id'] ?? $sr['ID'] ?? $sr['semester_id'] ?? null; $matchedSemName = $sr['name'] ?? $sr['semester'] ?? $sr['semester_name'] ?? null; break; }
                                    } else {
                                        // wrap-around semester (e.g., start=10 end=3)
                                        if ($curMonth >= $startM || $curMonth <= $endM) { $semStart = $startM; $semEnd = $endM; $wrap = true; $matchedSemId = $sr['id'] ?? $sr['ID'] ?? $sr['semester_id'] ?? null; $matchedSemName = $sr['name'] ?? $sr['semester'] ?? $sr['semester_name'] ?? null; break; }
                                    }
                            }
                        }
                    }
                    // Build a human-readable label for the matched semester
                    $currentSemesterLabel = $matchedSemName ?? null;
                    if ($currentSemesterLabel) {
                        // Normalise: 'First' -> '1st Semester', etc.
                        $semMap = ['first' => '1st Semester', 'second' => '2nd Semester', 'summer' => 'Summer'];
                        $key = strtolower(trim($currentSemesterLabel));
                        $currentSemesterLabel = $semMap[$key] ?? $currentSemesterLabel;
                    }

                    // Build events query with semester AND school-year restriction
                    $sql = "SELECT DISTINCT e.id, e.event_name, e.date, e.TimeIn_end, e.TimeOUT_out FROM events e LEFT JOIN event_other_departments eod ON e.id = eod.event_id WHERE e.status != 'archived' AND (e.date < CURDATE() OR (e.date = CURDATE() AND e.TimeOUT_out IS NOT NULL AND CURTIME() > e.TimeOUT_out)) AND (e.departments_id = ? OR eod.department_id = ?)";
                    $params = [$student_dept, $student_dept]; $types = 'ii';

                    // --- School-year filter ---
                    // For a wrap-around school year (Aug–Jul), events can span two calendar years:
                    //   months Aug–Dec belong to $syStart, months Jan–Jul belong to $syEnd
                    // So we allow: (YEAR = syStart AND MONTH >= 8) OR (YEAR = syEnd AND MONTH <= 7)
                    $sql .= " AND ((YEAR(e.date) = ? AND MONTH(e.date) >= 8) OR (YEAR(e.date) = ? AND MONTH(e.date) <= 7))";
                    $types .= 'ii'; $params[] = $syStart; $params[] = $syEnd;

                    // --- Semester filter (month range within the school year) ---
                    if ($semStart !== null && $semEnd !== null) {
                        if (!$wrap) {
                            $sql .= " AND (MONTH(e.date) BETWEEN ? AND ?)";
                            $types .= 'ii'; $params[] = $semStart; $params[] = $semEnd;
                        } else {
                            // wrap-around: month >= start OR month <= end
                            $sql .= " AND (MONTH(e.date) >= ? OR MONTH(e.date) <= ?)";
                            $types .= 'ii'; $params[] = $semStart; $params[] = $semEnd;
                        }
                        // also restrict by semester identifier if available (use numeric semester_id)
                        if ($matchedSemId !== null) {
                            $sql .= " AND (e.semester_id = ?)";
                            $types .= 'i'; $params[] = intval($matchedSemId);
                        }
                    }
                    $sql .= " ORDER BY e.date DESC";
                    $stmt3 = $conn->prepare($sql);
                    if ($stmt3) {
                        // bind params dynamically
                        $bindNames = array_merge([$types], $params);
                        $refs = [];
                        foreach ($bindNames as $k => $v) $refs[$k] = &$bindNames[$k];
                        call_user_func_array([$stmt3, 'bind_param'], $refs);
                        $stmt3->execute();
                        $events = $stmt3->get_result();
                    } else {
                        $events = false;
                    }

                    while ($event = $events->fetch_assoc()) {
                        // Check attendance
                        $stmt4 = $conn->prepare("SELECT Time_in, Time_out FROM attendance_tbl WHERE Event_ID = ? AND Student_ID = ? LIMIT 1");
                        $stmt4->bind_param('is', $event['id'], $student_id);
                        $stmt4->execute();
                        $att = $stmt4->get_result()->fetch_assoc();

                        $status = 'Absent';
                        $late_stamp = 'absent';
                        if ($att) {
                            if ($att['Time_in'] && $att['Time_out']) {
                                $status = 'Present';
                                $late_stamp = 'None';
                            } elseif ($att['Time_in']) {
                                $status = 'No Time Out';
                                $late_stamp = 'none';
                            }
                            // Calculate late if Time_in > TimeIn_end
                            if ($att['Time_in'] && $event['TimeIn_end']) {
                                $timeInParts = explode(' ', $att['Time_in']);
                                $timeInTime = end($timeInParts); // HH:MM:SS
                                $timeInHM = substr($timeInTime, 0, 5); // HH:MM
                                if ($timeInHM > $event['TimeIn_end']) {
                                    $endTS = strtotime($event['TimeIn_end']);
                                    $inTS = strtotime($timeInHM);
                                    $late_seconds = $inTS - $endTS;
                                    $late_minutes = floor($late_seconds / 60);
                                    
                                    // Convert minutes to hours and minutes format
                                    if ($late_minutes >= 60) {
                                        $hours = floor($late_minutes / 60);
                                        $remaining_minutes = $late_minutes % 60;
                                        $late_stamp = $hours . ' hour' . ($hours > 1 ? 's' : '');
                                        if ($remaining_minutes > 0) {
                                            $late_stamp .= ' and ' . $remaining_minutes . ' minute' . ($remaining_minutes > 1 ? 's' : '');
                                        }
                                        $late_stamp .= ' late';
                                    } else {
                                        $late_stamp = $late_minutes . ' minute' . ($late_minutes > 1 ? 's' : '') . ' late';
                                    }
                                    
                                    if ($status === 'Present') {
                                        $status = 'late';
                                    }
                                }
                            }
                        }

                        $attendanceRecords[] = [
                            'event_name' => $event['event_name'],
                            'course' => $student_course,
                            'date' => date('F j, Y', strtotime($event['date'])),
                            'status' => $status,
                            'late_stamp' => $late_stamp
                        ];
                    }
                }
            }
        
            if (!$student) {
                $alert_error = 'Student not found.';
            }
        }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Records</title>
    <link rel="stylesheet" href="../styles/dash_styles.css">
    <link rel="stylesheet" href="../styles/side_styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001" />
   <style>
      .student_name { margin-top: 1rem; }
      .tableList { margin-top: 2pc; margin-right: 4pc; margin-left: 10pc; }
      .search{ margin-left: 10rem; }
      .table-maroon th { background-color: maroon !important; color: white !important; }
      .no-events-card {
        border: 2px dashed #d9b0b0;
        border-radius: 16px;
        padding: 3rem 2rem;
        background: linear-gradient(135deg, #fff5f5 0%, #fff 100%);
        text-align: center;
        margin: 2rem 10pc 1rem;
        animation: fadeInUp 0.4s ease;
      }
      .no-events-card .no-events-icon { margin-bottom: 1rem; }
      .no-events-card h5 { color: maroon; font-weight: 700; margin-bottom: 0.5rem; }
      .no-events-card p  { color: #888; font-size: 0.95rem; margin-bottom: 0; }
      @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
      }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<?php $page_heading = 'Attendance Records'; include 'navbar.php'; ?>
    <br>
    <div class="search">
        <form class="d-flex d-inline-flex" role="search" method="post">
            <input id="rfid" class="form-control border border-danger" name="rfid" type="text" placeholder="Scan an ID" required autofocus autocomplete="off">
            <button class="btn btn-outline-danger ms-2" name="submit" type="submit">Scan</button>
        </form>
    </div>

    <?php if ($error): ?>
    <?php elseif ($student_name): ?>
        <h1 class="text-center text-decoration-underline link-underline-danger student_name"><?= htmlspecialchars($student_name) ?></h1>
        <?php if (!empty($currentSemesterLabel) || !empty($currentSchoolYear)): ?>
        <p class="text-center mt-1 mb-0">
            <span class="badge rounded-pill" style="background-color: maroon; font-size: 0.9rem; padding: 0.45em 1em;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-book me-1" viewBox="0 0 16 16" style="vertical-align:-2px">
                    <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
                </svg>
                SY <?= htmlspecialchars($currentSchoolYear ?? '') ?>
                <?php if (!empty($currentSemesterLabel)): ?>
                &mdash; <?= htmlspecialchars($currentSemesterLabel) ?>
                <?php endif; ?>
            </span>
        </p>
        <?php endif; ?>

        <?php if (count($attendanceRecords) === 0): ?>
        <div class="no-events-card">
            <div class="no-events-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#d9b0b0" class="bi bi-calendar-x" viewBox="0 0 16 16">
                    <path d="M6.146 7.146a.5.5 0 0 1 .708 0L8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 0 1 0-.708"/>
                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                </svg>
            </div>
            <h5>No Events Yet This Semester</h5>
            <p>
                There are no completed events for
                <?php if (!empty($currentSemesterLabel) || !empty($currentSchoolYear)): ?>
                    <strong>SY <?= htmlspecialchars($currentSchoolYear ?? '') ?><?= !empty($currentSemesterLabel) ? ' &mdash; ' . htmlspecialchars($currentSemesterLabel) : '' ?></strong>
                <?php else: ?>
                    the current semester
                <?php endif; ?>
                that include your department.<br>
                Check back after an event has taken place.
            </p>
        </div>
        <?php else: ?>
        <section class="tableList">
            <table class="table border border-dark table-bordered table-maroon">
                <thead class="border border-dark table-info">
                    <tr>
                        <th>#</th>
                        <th>Event</th>
                        <th>Course</th>
                        <th>Date</th>
                        <th>Attendance Logs</th>
                        <th>Late stamps</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendanceRecords as $index => $record): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($record['event_name']) ?></td>
                            <td><?= htmlspecialchars($record['course']) ?></td>
                            <td><?= htmlspecialchars($record['date']) ?></td>
                            <td class="fw-bold <?= ($record['status'] == 'Absent' || $record['status'] == 'late' || $record['status'] == 'No Time Out') ? 'text-danger' : 'text-success' ?>">
                                <?= htmlspecialchars($record['status']) ?>
                            </td>
                            <td class="fw-bold <?= ($record['late_stamp'] == 'absent' || strpos($record['late_stamp'], 'late') !== false) ? 'text-danger' : '' ?>">
                                <?= htmlspecialchars($record['late_stamp']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php
                        // compute totals: treat 'Absent' and 'No Time Out' as absent
                        $total_present = 0;
                        $total_absent = 0;
                        foreach ($attendanceRecords as $r) {
                            $s = isset($r['status']) ? strtolower(trim($r['status'])) : '';
                            if ($s === 'absent' || $s === 'no time out') {
                                $total_absent++;
                            } else {
                                $total_present++;
                            }
                        }
                    ?>
                    <tr class="fw-bold">
                        <td colspan="4" class="text-end">Totals</td>
                        <td class="text-success">Present: <?= $total_present ?></td>
                        <td class="text-danger">Absent: <?= $total_absent ?></td>
                    </tr>
                </tbody>
            </table>
        </section>
        <?php endif; ?>
    <?php else: ?>
        <h1 class="text-center text-decoration-underline link-underline-danger student_name">Scan RFID to Show Attendance Records</h1>
    <?php endif; ?>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
  <script>
    function showToast(message, type = 'info') {
      const toast = document.createElement('div');
      toast.className = `toast align-items-center text-bg-${type} border-0 position-fixed top-0 end-0 m-3`;
      toast.style.top = '1rem';
      toast.style.right = '1rem';
      toast.style.zIndex = '2000';
      toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
      document.body.appendChild(toast);
      const bs = new bootstrap.Toast(toast, { delay: 2500 });
      bs.show();
      toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }
    // Clear the input on page load to allow new scans
    const rfidInput = document.getElementById('rfid');
    rfidInput.value = '';
    rfidInput.focus();

    // Keep the scan input always focused
    rfidInput.addEventListener('blur', () => {
      setTimeout(() => {
        const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        if (activeTag !== 'input' && activeTag !== 'textarea' && activeTag !== 'select') {
          rfidInput.focus();
        }
      }, 100);
    });
    document.addEventListener('click', () => {
      setTimeout(() => {
        const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        if (activeTag !== 'input' && activeTag !== 'textarea' && activeTag !== 'select') {
          rfidInput.focus();
        }
      }, 150);
    });
  </script>
  <?php if ($alert_error): ?>
  <script>
    showToast('<?= addslashes($alert_error) ?>', 'danger');
  </script>
  <?php endif; ?>
  <?php if ($error): ?>
  <script>
    showToast('<?= addslashes($error) ?>', 'danger');
  </script>
  <?php endif; ?>
</body>
</html>
