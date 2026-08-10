<?php
require_once __DIR__ . '/../auth.php';
// require_login('/new/');
require_login('../');
// Fetch stats from database
require_once __DIR__ . '/../api/dbcon.php';

// Determine current semester
$current_month = date('m');
$current_semester_query = $conn->query("SELECT id FROM semester WHERE (MONTH(start) <= $current_month AND MONTH(end) >= $current_month) OR (MONTH(start) > MONTH(end) AND ($current_month >= MONTH(start) OR $current_month <= MONTH(end))) ORDER BY id LIMIT 1");
$current_semester = $current_semester_query->fetch_assoc()['id'] ?? 1;

// Function to get school year from date
function getSchoolYear($date) {
    $timestamp = strtotime($date);
    $year = date('Y', $timestamp);
    $month = date('n', $timestamp);
    if ($month >= 6) {
        return $year . '-' . ($year + 1);
    } else {
        return ($year - 1) . '-' . $year;
    }
}

$current_school_year = getSchoolYear(date('Y-m-d'));

// Total active students
$totalStudents = $conn->query("SELECT COUNT(*) as count FROM students WHERE status = 'active'")->fetch_assoc()['count'];

// Total events (for current semester and school year, active only to match events.php)
$totalEvents = $conn->query("SELECT COUNT(*) as count FROM events WHERE semester_id = $current_semester AND status = 'active' AND CASE WHEN MONTH(date) >= 6 THEN CONCAT(YEAR(date), '-', YEAR(date)+1) ELSE CONCAT(YEAR(date)-1, '-', YEAR(date)) END = '$current_school_year'")->fetch_assoc()['count'];

// Today's scans (for events in current semester and school year)
$todaysScans = $conn->query("SELECT COUNT(a.Attendance_ID) as count FROM attendance_tbl a JOIN events e ON a.Event_ID = e.id WHERE DATE(e.date) = CURDATE() AND e.semester_id = $current_semester AND CASE WHEN MONTH(e.date) >= 6 THEN CONCAT(YEAR(e.date), '-', YEAR(e.date)+1) ELSE CONCAT(YEAR(e.date)-1, '-', YEAR(e.date)) END = '$current_school_year'")->fetch_assoc()['count'];

// Today's events (for current semester and school year)
$todaysEvents = $conn->query("SELECT COUNT(*) as count FROM events WHERE DATE(date) = CURDATE() AND semester_id = $current_semester AND CASE WHEN MONTH(date) >= 6 THEN CONCAT(YEAR(date), '-', YEAR(date)+1) ELSE CONCAT(YEAR(date)-1, '-', YEAR(date)) END = '$current_school_year'")->fetch_assoc()['count'];

// Scans last 7 days
$scansLast7Days = [];
$labelsLast7Days = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $label = date('D', strtotime("-$i days"));
    $count = $conn->query("SELECT COUNT(a.Attendance_ID) as count FROM attendance_tbl a JOIN events e ON a.Event_ID = e.id WHERE DATE(a.Time_in) = '$date' AND e.semester_id = $current_semester AND CASE WHEN MONTH(e.date) >= 6 THEN CONCAT(YEAR(e.date), '-', YEAR(e.date)+1) ELSE CONCAT(YEAR(e.date)-1, '-', YEAR(e.date)) END = '$current_school_year'")->fetch_assoc()['count'];
    $scansLast7Days[] = $count;
    $labelsLast7Days[] = $label;
}

// Event status counts (using dynamic status calculation for active events only)
$eventStatuses = $conn->query("SELECT * FROM events WHERE status = 'active' AND semester_id = $current_semester AND CASE WHEN MONTH(date) >= 6 THEN CONCAT(YEAR(date), '-', YEAR(date)+1) ELSE CONCAT(YEAR(date)-1, '-', YEAR(date)) END = '$current_school_year'");
$activeCount = 0;
$scheduledCount = 0;
$completedCount = 0;

$current = $conn->query("SELECT CURDATE() as cdate, CURTIME() as ctime")->fetch_assoc();
$now = new DateTime($current['cdate'] . ' ' . $current['ctime']);

while ($event = $eventStatuses->fetch_assoc()) {
    $event_date = new DateTime($event['date']);
    $now_date = new DateTime($current['cdate']);

    if ($event_date < $now_date) {
        // Completed - past events
        $completedCount++;
    } elseif ($event_date->format('Y-m-d') == $now->format('Y-m-d')) {
        $start_time = new DateTime($event['date'] . ' ' . $event['TimeIn_start']);
        $end_time = new DateTime($event['date'] . ' ' . $event['Timeout_out']);
        if ($now < $start_time) {
            $scheduledCount++;
        } elseif ($now >= $start_time && $now <= $end_time) {
            $activeCount++;
        } else {
            $completedCount++; // Completed
        }
    } else {
        $scheduledCount++;
    }
}

// Find ongoing event (today, active, within time window)
$ongoingEvent = null;
$todayEventsMetrics = $conn->query("SELECT id, event_name, date, TimeIn_start, Timeout_out FROM events WHERE DATE(date) = CURDATE() AND status = 'active'");
if ($todayEventsMetrics) {
    while ($ev = $todayEventsMetrics->fetch_assoc()) {
        $ev_start = new DateTime($ev['date'] . ' ' . $ev['TimeIn_start']);
        $ev_end   = new DateTime($ev['date'] . ' ' . $ev['Timeout_out']);
        if ($now >= $ev_start && $now <= $ev_end) {
            $ongoingEvent = $ev;
            break;
        }
    }
}

// Attendance by department — for ongoing event, or today if none
$deptAttendanceLabels = [];
$deptAttendanceCounts = [];
$deptColors = ['#7f1d1d','#d97706','#17a2b8','#28a745','#6f42c1','#fd7e14','#6b7280','#e83e8c'];

if ($ongoingEvent) {
    $metricsEventName = htmlspecialchars($ongoingEvent['event_name']);
    $eid = (int)$ongoingEvent['id'];
    $deptQ = $conn->query("
        SELECT d.dept_Acronym AS dept, COUNT(a.Attendance_ID) AS cnt
        FROM attendance_tbl a
        JOIN students s ON a.Student_ID = s.ID_Number
        JOIN departments d ON s.departments_id = d.id
        WHERE a.Event_ID = $eid
        GROUP BY d.id, d.dept_Acronym
        ORDER BY cnt DESC
    ");
} else {
    $metricsEventName = "Today&#39;s Attendance by Dept";
    $deptQ = $conn->query("
        SELECT d.dept_Acronym AS dept, COUNT(a.Attendance_ID) AS cnt
        FROM attendance_tbl a
        JOIN students s ON a.Student_ID = s.ID_Number
        JOIN departments d ON s.departments_id = d.id
        JOIN events e ON a.Event_ID = e.id
        WHERE DATE(e.date) = CURDATE()
        GROUP BY d.id, d.dept_Acronym
        ORDER BY cnt DESC
    ");
}

if ($deptQ && $deptQ->num_rows > 0) {
    while ($row = $deptQ->fetch_assoc()) {
        $deptAttendanceLabels[] = $row['dept'];
        $deptAttendanceCounts[] = (int)$row['cnt'];
    }
} else {
    // Show all active departments with 0
    $metricsEventName = $ongoingEvent ? htmlspecialchars($ongoingEvent['event_name']) . ' (no scans yet)' : 'No Attendance Today';
    $allDeptsQ = $conn->query("SELECT dept_Acronym FROM departments WHERE status = 'active' AND archived = 0 ORDER BY id");
    if ($allDeptsQ) {
        while ($row = $allDeptsQ->fetch_assoc()) {
            $deptAttendanceLabels[] = $row['dept_Acronym'];
            $deptAttendanceCounts[] = 0;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../styles/dash_styles.css">
  <link rel="stylesheet" href="../styles/side_styles.css">
  <link rel="stylesheet" href="admin_styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001" />
  <style>
    .admin-wrap{ max-width:1700px; margin-left:10rem; margin-right:3rem; }
    .stats { display:flex; gap:1rem; }
    .stat { background:#fff; padding:1rem; border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.06); flex:1; }
    .card-analytics { display:flex; gap:1rem; flex-wrap:wrap; }
    .analytic { flex:1; min-width:220px; background:#fff; padding:1rem; border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.06); }
    .chart-wrap { display:flex; gap:1rem; flex-wrap:wrap; }
    .chart-card { flex:1; min-width:300px; background:#fff; padding:1rem; border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.06); }
  </style>
</head>
<body>
  <?php $page_heading = 'Admin Dashboard'; include 'navbar.php'; ?>
<?php 
include  'sidebar.php';


?>


<main class="admin-wrap">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Overview</h3>
    <a href="students.php" class="btn btn-maroon" onclick="setActiveSidebar('students')">Manage Students</a>
  </div>
  <div class="card-analytics mb-4">
    <div class="analytic">
      <h6 class="mb-1">Total Students</h6>
      <p class="h3 mb-0"><?php echo number_format($totalStudents); ?></p>
      <small class="text-muted">Registered in system</small>
    </div>
    <div class="analytic">
      <h6 class="mb-1">Total Events</h6>
      <p class="h3 mb-0"><?php echo $totalEvents; ?></p>
      <small class="text-muted">This semester</small>
    </div>
    <div class="analytic">
      <h6 class="mb-1">Today's Scans</h6>
      <p class="h3 mb-0"><?php echo $todaysScans; ?></p>
      <small class="text-muted">Scans so far today</small>
    </div>
    <div class="analytic">
      <h6 class="mb-1">Today's Events</h6>
      <p class="h4 mb-0"><?php echo $todaysEvents; ?></p>
      <small class="text-muted">Events scheduled today</small>
    </div>
  </div>

  <div class="chart-wrap mb-4">
    <div class="chart-card">
      <h6>Scans - Last 7 Days</h6>
      <canvas id="scansChart" height="140"></canvas>
    </div>
    <div class="chart-card">
      <h6>Event Status</h6>
      <canvas id="eventsDonut" height="140"></canvas>
    </div>
    <div class="chart-card">
      <h6 class="mb-0">Attendance by Department</h6>
      <p class="text-muted small mb-2" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?php echo $metricsEventName; ?>"><?php echo $metricsEventName; ?></p>
      <canvas id="metricsBar" height="140"></canvas>
    </div>
  </div>

 
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Function to set active sidebar state
function setActiveSidebar(key) {
  try {
    localStorage.setItem('activeSidebar', key);
  } catch(e) {
    // Handle localStorage errors silently
  }
}
// Real data from database
const scansData = {
  labels: <?php echo json_encode($labelsLast7Days); ?>,
  datasets: [{
    label: 'Scans',
    data: <?php echo json_encode($scansLast7Days); ?>,
    borderColor: '#7f1d1d',
    backgroundColor: 'rgba(127,29,29,0.08)',
    tension: 0.3,
    fill: true,
    pointRadius: 4,
  }]
};

const scansCtx = document.getElementById('scansChart');
if (scansCtx) {
  new Chart(scansCtx, {
    type: 'line',
    data: scansData,
    options: {
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
}

const eventsData = {
  labels: ['Active','Scheduled','Completed'],
  datasets: [{
    data: [<?php echo $activeCount; ?>, <?php echo $scheduledCount; ?>, <?php echo $completedCount; ?>],
    backgroundColor: ['#7f1d1d','#d97706','#28a745']
  }]
};
const eventsCtx = document.getElementById('eventsDonut');
if (eventsCtx) {
  // Plugin to display total in center
  const centerTextPlugin = {
    id: 'centerText',
    beforeDraw: (chart) => {
      const { ctx, chartArea: { left, right, top, bottom, width, height } } = chart;
      ctx.save();
      const centerX = (left + right) / 2;
      const centerY = (top + bottom) / 2;
      ctx.font = 'bold 16px Arial';
      ctx.fillStyle = '#7f1d1d';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText('Total: <?php echo $activeCount + $scheduledCount + $completedCount; ?>', centerX, centerY);
    }
  };

  new Chart(eventsCtx, { 
    type: 'doughnut', 
    data: eventsData, 
    options: { 
      plugins: { 
        legend: { position: 'bottom' },
        centerText: centerTextPlugin
      } 
    },
    plugins: [centerTextPlugin]
  });
}

// Attendance by department chart
const allDeptColors = ['#7f1d1d','#d97706','#17a2b8','#28a745','#6f42c1','#fd7e14','#6b7280','#e83e8c'];
const deptLabels = <?php echo json_encode($deptAttendanceLabels); ?>;
const deptCounts = <?php echo json_encode($deptAttendanceCounts); ?>;
const deptBgColors = deptLabels.map((_, i) => allDeptColors[i % allDeptColors.length]);
const metricsData = {
  labels: deptLabels,
  datasets: [{
    label: 'Attendance',
    data: deptCounts,
    backgroundColor: deptBgColors,
    borderColor: deptBgColors,
    borderWidth: 1
  }]
};

const metricsCtx = document.getElementById('metricsBar');
if (metricsCtx) {
  new Chart(metricsCtx, {
    type: 'bar',
    data: metricsData,
    options: {
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
}
</script>
</body>
</html>