<?php
require_once __DIR__ . '/../auth.php';
// require_login('/new/');
require_login('../');

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Events</title>
  <link rel="stylesheet" href="../styles/dash_styles.css">
  <link rel="stylesheet" href="../styles/side_styles.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>.table-maroon th { background-color: maroon !important; color: white !important; }
  .department-check { transition: background 0.2s, box-shadow 0.2s; }
  .department-check:hover { background: #ffe5e5; box-shadow: 0 2px 8px rgba(128,0,0,0.08); }
  .dept-margin { margin-left: 10rem; }
  .dept-margin-right { margin-right: 10rem; }
  .maroon-text { color: maroon; }
  .bg-maroon { background-color: maroon; }
  .form-check-input:checked { background-color: maroon; }
  .events-table-hidden { opacity: 0; transition: opacity 0.3s ease; }
  .events-table-visible { opacity: 1; }
</style>
<body>
   <?php $page_heading = 'Manage Events'; include 'navbar.php'; ?>
   <?php include 'sidebar.php'; ?>
<?php
require_once __DIR__ . '/../api/dbcon.php';

// Fetch all events
$events = $conn->query("SELECT * FROM events WHERE status = 'active' ORDER BY date DESC, TimeIn_start DESC");

// Function to get department name
function get_dept_name($dept_id) {
    global $conn;
    // Find correct column names
    $res = $conn->query("SHOW COLUMNS FROM `departments`");
    $cols = [];
    while ($r = $res->fetch_assoc()) $cols[] = $r['Field'];
    $idCol = null;
    $nameCol = null;
    foreach (['Dept_ID','id','ID'] as $c) {
        if (in_array($c, $cols)) { $idCol = $c; break; }
    }
    foreach (['Dept_name','dept_name','name'] as $c) {
        if (in_array($c, $cols)) { $nameCol = $c; break; }
    }
    if (!$idCol || !$nameCol) return 'Unknown';
    $stmt = $conn->prepare("SELECT `$nameCol` FROM departments WHERE `$idCol` = ?");
    $stmt->bind_param('i', $dept_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result[$nameCol] : 'Unknown';
}

// Function to get department acronym
function get_dept_acronym($dept_id) {
    global $conn;
    // Find correct column names
    $res = $conn->query("SHOW COLUMNS FROM `departments`");
    $cols = [];
    while ($r = $res->fetch_assoc()) $cols[] = $r['Field'];
    $idCol = null;
    $acronymCol = null;
    foreach (['Dept_ID','id','ID'] as $c) {
        if (in_array($c, $cols)) { $idCol = $c; break; }
    }
    foreach (['dept_Acronym','acronym','Acronym'] as $c) {
        if (in_array($c, $cols)) { $acronymCol = $c; break; }
    }
    if (!$idCol || !$acronymCol) return 'UNK';
    $stmt = $conn->prepare("SELECT `$acronymCol` FROM departments WHERE `$idCol` = ?");
    $stmt->bind_param('i', $dept_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result[$acronymCol] : 'UNK';
}

// Function to get all department acronyms for an event
function get_event_departments($event) {
    $depts = [];
    
    // Primary department
    if (!empty($event['departments_id'])) {
        $primary_acronym = get_dept_acronym($event['departments_id']);
        if ($primary_acronym) $depts[] = $primary_acronym;
    }
    
    // Other departments - try to find the mapping table
    global $conn;
    $other_depts = [];
    if (!empty($event['id'])) {
        // Try different possible table names
        $possible_tables = ['event_other_departments', 'event_other_depart', 'event_departments'];
        $mapping_table = null;
        foreach ($possible_tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                $mapping_table = $table;
                break;
            }
        }
        
        if ($mapping_table) {
            // Find column names
            $cols = [];
            $res = $conn->query("SHOW COLUMNS FROM `$mapping_table`");
            while ($r = $res->fetch_assoc()) $cols[] = $r['Field'];
            
            $event_col = null;
            $dept_col = null;
            foreach (['event_id', 'eventId', 'evt_id'] as $c) {
                if (in_array($c, $cols)) { $event_col = $c; break; }
            }
            foreach (['department_id', 'dept_id', 'departmentid'] as $c) {
                if (in_array($c, $cols)) { $dept_col = $c; break; }
            }
            
            if ($event_col && $dept_col) {
                $stmt = $conn->prepare("SELECT `$dept_col` FROM `$mapping_table` WHERE `$event_col` = ?");
                $stmt->bind_param('i', $event['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $other_depts[] = $row[$dept_col];
                }
            }
        }
    }
    
    foreach ($other_depts as $dept_id) {
        $acronym = get_dept_acronym($dept_id);
        if ($acronym && !in_array($acronym, $depts)) $depts[] = $acronym;
    }
    
    return implode(', ', $depts);
}

// Function to count participants
function count_participants($event_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM attendance_tbl WHERE Event_ID = ?");
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'];
}

// Function to determine status
function get_event_status($event) {
    global $conn;
    // Get current date and time from DB to match ongoing.php logic
    $current = $conn->query("SELECT CURDATE() as cdate, CURTIME() as ctime")->fetch_assoc();
    $now = new DateTime($current['cdate'] . ' ' . $current['ctime']);
    $event_date = new DateTime($event['date']);
    $now_date = new DateTime($current['cdate']);

    if ($event_date < $now_date) {
        return 'Completed';
    } elseif ($event_date->format('Y-m-d') == $now->format('Y-m-d')) {
        $start_time = new DateTime($event['date'] . ' ' . $event['TimeIn_start']);
        $end_time = new DateTime($event['date'] . ' ' . $event['Timeout_out']);
        if ($now < $start_time) {
            return 'Scheduled';
        } elseif ($now >= $start_time && $now <= $end_time) {
            return 'Active';
        } else {
            return 'Completed';
        }
    } else {
        return 'Scheduled';
    }
}

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
?>

<!-- Enhanced Events Page Content -->
<style>
  .btn-maroon { background-color: white; color: maroon; border: 1px solid maroon; }
  .btn-maroon:hover { background-color: maroon; color: white; }
  .table-maroon th { background-color: maroon !important; color: white !important; }
  .badge-active { background-color: #28a745 !important; }
  .badge-scheduled { background-color: #ffc107 !important; color: #000 !important; }
  .badge-archived { background-color: #6c757d !important; }
  /* Completed: use Bootstrap primary color for consistency */
  .badge-complete { background-color: #0d6efd !important; color: #fff !important; }
</style>

<main class="container-fluid" style="margin-top:1rem;">
  <h1 class="text-center text-decoration-underline link-underline-danger mb-4">ADMIN EVENT MANAGEMENT</h1>
  
  <div class="text-year-sem justify-content-md-end d-md-flex">
    <h6 class="h6-year">School Year</h6>
    <h6 class="h6-semester">Semester</h6>
  </div>

  <section class="d-md-flex justify-content-md-between year-sem mb-3">
    <!-- Left side - Department filter -->
    <div class="d-flex align-items-center" style="margin-left: 10rem;">
      <label class="me-2 fw-bold">Department:</label>
      <div class="btn-group border border-dark">
        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          All Departments
        </button>
        <ul class="dropdown-menu" id="departmentDropdown">
          <li><a class="dropdown-item active" href="#">All Departments</a></li>
        </ul>
      </div>
    </div>

    <!-- Right side - Year, Semester, Filter -->
    <div class="d-flex align-items-center">
      <div class="btn-group border border-dark">
        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          2025-2026
        </button>
        <ul class="dropdown-menu" id="yearDropdown">
          <li><a class="dropdown-item" href="#">2024-2025</a></li>
          <li><a class="dropdown-item active" href="#">2025-2026</a></li>
          <li><a class="dropdown-item" href="#">2026-2027</a></li>
        </ul>
      </div>
      <div class="btn-group border border-dark ms-1">
        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          FIRST
        </button>
        <ul class="dropdown-menu" id="semesterDropdown">
          <li><a class="dropdown-item active" href="#">First</a></li>
          <li><a class="dropdown-item" href="#">Second</a></li>
          <li><a class="dropdown-item" href="#">Summer</a></li>
        </ul>
      </div>
      <button class="btn btn-maroon border-dark filter ms-2" type="button">
        Filter
      </button>
      <!-- <a href="students.php" class="btn btn-outline-secondary ms-2">Manage Students</a> -->
    </div>
  </section>

  <section class="event-table mt-3">
    <table class="table border border-dark table-bordered events-table-hidden" id="eventsTable">
      <thead class="table-maroon border border-dark">
        <tr>
          <th scope="col" class="text-center">#</th>
          <th scope="col" class="text-center">EVENT NAME</th>
          <th scope="col" class="text-center">DATE</th>
          <th scope="col" class="text-center">Department</th>
          <th scope="col" class="text-center">STATUS</th>
          <th scope="col" class="text-center">PARTICIPANTS</th>
          <th scope="col" class="text-center">ACTIONS</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $counter = 1;
        while ($event = $events->fetch_assoc()) {
            $status = get_event_status($event);
            $participants = count_participants($event['id']);
            $dept_name = get_event_departments($event);
            $badge_class = $status == 'Active' ? 'badge-active' : ($status == 'Scheduled' ? 'badge-scheduled' : ($status == 'Completed' ? 'badge-complete' : 'badge-archived'));
            $badge_text = $status;
        ?>
        <tr data-year="<?php echo getSchoolYear($event['date']); ?>" data-semester="<?php echo $event['semester_id']; ?>">
          <th scope="row" class="text-center"><?php echo $counter++; ?></th>
          <td class="fw-bold"><a href="ongoing.php?id=<?php echo $event['id']; ?>" style="text-decoration: none; color: inherit;"><?php echo htmlspecialchars($event['event_name']); ?></a></td>
          <td class="text-center"><?php echo date('M j, Y', strtotime($event['date'])); ?></td>
          <td class="text-center"><?php echo htmlspecialchars($dept_name); ?></td>
          <td class="text-center"><span class="badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span></td>
          <td class="text-center"><?php echo $participants; ?></td>
          <td class="text-center">
            <button class="btn btn-sm btn-outline-primary me-1" title="Edit Event" data-id="<?php echo $event['id']; ?>">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-success me-1" title="View Details" data-id="<?php echo $event['id']; ?>">
              <i class="bi bi-eye"></i>
            </button>
            <?php if ($event['status'] == 'active') { ?>
            <button class="btn btn-sm btn-outline-danger" title="Archive Event" data-id="<?php echo $event['id']; ?>">
              <i class="bi bi-archive"></i>
            </button>
            <?php } else { ?>
            <button class="btn btn-sm btn-outline-danger" title="Archive Event" data-id="<?php echo $event['id']; ?>">
              <i class="bi bi-archive"></i>
            </button>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </section>
</main>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Event Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewModalBody">
        <!-- details here -->
      </div>
    </div>
  </div>
</div>

<!-- Edit Event Modal -->
<div class="modal fade" tabindex="-1" id="editEventModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background-color: maroon; color: white;">
        <h5 class="modal-title">Edit Events</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editEventForm">
        <input type="hidden" name="id" id="editEventId">
        <div class="modal-body">
          <div class="mb-3">
            <label class="fw-bold">Event Name:</label>
            <input type="text" name="event_name" class="form-control" id="editEventName" required>
          </div>
          <div class="mb-3 row">
            <div class="col">
              <label class="fw-bold">Event Date:</label>
              <input type="date" name="date" class="form-control" id="editEventDate" required>
            </div>
            <div class="col">
              <label class="fw-bold">Semester:</label>
              <input type="text" name="semester" class="form-control" id="editEventSemester" readonly>
            </div>
          </div>
          <div class="mb-3 text-center">
            <label class="fw-bold text-center text-decoration-underline link-underline-danger fs-4">Include Other Departments:</label>
            <div id="departmentsContainer" class="row"></div>
          </div>
          <div class="mb-3">
            <div class="text-center"> <label class="fw-bold text-decoration-underline link-underline-danger fs-4">Event Time</label> </div>
            <div class="row">
              <div class="col">
                <label class="fw-bold">Time In:</label>
                <input type="time" name="TimeIN_in" id="editTimeIN_in" class="form-control text-center" required>
              </div>
              <div class="col">
                <label class="fw-bold">Time Out:</label>
                <input type="time" name="TimeIN_out" id="editTimeIN_out" class="form-control text-center" required>
              </div>
              <div class="col">
                <label class="fw-bold">Time In:</label>
                <input type="time" name="TimeOUT_in" id="editTimeOUT_in" class="form-control text-center" required>
              </div>
              <div class="col">
                <label class="fw-bold">Time Out:</label>
                <input type="time" name="TimeOUT_out" id="editTimeOUT_out" class="form-control text-center" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn" style="background-color: maroon; color: white;" onclick="saveChanges()">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Archive Confirmation Modal -->
<div class="modal fade" id="archiveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Archive Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="archiveModalBody"></p>
        <p class="small text-muted">This will set the event status to archived.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="confirmArchiveBtn" type="button" class="btn btn-danger">Archive</button>
      </div>
    </div>
  </div>
</div>

<script>
// Filter variables
let selectedDepartment = '';
let selectedYear = '';
let selectedSemester = '';

// Helper functions
function getSchoolYear(dateStr) {
  const date = new Date(dateStr);
  const year = date.getFullYear();
  const month = date.getMonth() + 1;
  if (month >= 6) {
    return `${year}-${year + 1}`;
  } else {
    return `${year - 1}-${year}`;
  }
}

function formatSemester(rawName) {
  const map = {
    '1st Semester': 'First',
    '2nd Semester': 'Second',
    'Summer': 'Summer'
  };
  return map[rawName] || rawName;
}

function getRawSemester(displayName) {
  const map = {
    'First': '1st Semester',
    'Second': '2nd Semester',
    'Summer': 'Summer'
  };
  return map[displayName] || displayName;
}

document.addEventListener('DOMContentLoaded', async function() {
  await loadYearFilter();
  await loadSemesterFilter();
  loadDepartmentFilter();
  filterEvents();
  
  // Show the table after filters are applied
  const eventsTable = document.getElementById('eventsTable');
  eventsTable.classList.remove('events-table-hidden');
  eventsTable.classList.add('events-table-visible');

  // View (eye) - go to ongoing
  document.querySelectorAll('.btn-outline-success').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      window.location.href = 'ongoing.php?id=' + id;
    });
  });

  // Edit (pencil) - open edit modal
  document.querySelectorAll('.btn-outline-primary').forEach(btn => {
    btn.addEventListener('click', function() {
      const eventId = this.getAttribute('data-id');
      fetch('../api/events.php?id=' + eventId)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const event = data.data;
            document.getElementById('editEventId').value = event.id;
            document.getElementById('editEventName').value = event.event_name;
            document.getElementById('editEventDate').value = event.date;
            document.getElementById('editEventSemester').value = event.semester;
            document.getElementById('editTimeIN_in').value = event.TimeIN_in;
            document.getElementById('editTimeIN_out').value = event.TimeIN_out;
            document.getElementById('editTimeOUT_in').value = event.TimeOUT_in;
            document.getElementById('editTimeOUT_out').value = event.TimeOUT_out;
            loadDepartments(event);
            const modal = new bootstrap.Modal(document.getElementById('editEventModal'));
            modal.show();
          } else {
            alert('Failed to load event details');
          }
        })
        .catch(error => console.error('Error:', error));
    });
  });

  // Archive
  document.querySelectorAll('.btn-outline-danger').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      // Find the event name from the row
      const row = btn.closest('tr');
      const eventName = row ? row.cells[1].textContent.trim() : 'this event';
      const modalEl = document.getElementById('archiveModal');
      document.getElementById('archiveModalBody').textContent = `Archive event: ${eventName}?`;
      modalEl.dataset.eventId = id;
      const bsModal = new bootstrap.Modal(modalEl);
      bsModal.show();
    });
  });

  // Handle archive confirmation
  document.getElementById('confirmArchiveBtn').addEventListener('click', async () => {
    const modalEl = document.getElementById('archiveModal');
    const id = modalEl.dataset.eventId;
    if (!id) return;
    
    document.getElementById('confirmArchiveBtn').disabled = true;
    document.getElementById('confirmArchiveBtn').textContent = 'Archiving...';
    
    try {
      const res = await fetch('../api/events.php', {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: id, status: 'archived'})
      });
      const json = await res.json();
      if (json.success) {
        const bsModal = bootstrap.Modal.getInstance(modalEl);
        if (bsModal) bsModal.hide();
        showToast('Event archived successfully', 'success');
        setTimeout(() => location.reload(), 1500);
      } else {
        showToast('Error: ' + (json.error || 'Unknown error'), 'danger');
      }
    } catch (err) {
      console.error(err);
      showToast('Error updating event', 'danger');
    } finally {
      document.getElementById('confirmArchiveBtn').disabled = false;
      document.getElementById('confirmArchiveBtn').textContent = 'Archive';
    }
  });

  // Filter button
  document.querySelector('.filter').addEventListener('click', function() {
    filterEvents();
  });
});

function loadDepartments(event) {
  fetch('../api/departments.php?archived=0')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const container = document.getElementById('departmentsContainer');
        container.innerHTML = '';
        const cols = [document.createElement('div'), document.createElement('div')];
        cols[0].className = 'col-md-6';
        cols[1].className = 'col-md-6';
        const selected = [event.departments_id, ...(event.other_departments || [])].filter(id => id).map(String);
        data.data.forEach((dept, index) => {
          const wrapper = document.createElement('div');
          wrapper.className = 'form-check d-flex align-items-center gap-2 p-2 rounded department-check ' + (index % 2 === 0 ? 'dept-margin' : 'dept-margin-right');
          const input = document.createElement('input');
          input.type = 'checkbox';
          input.className = 'form-check-input';
          input.value = dept.id;
          input.name = 'departments[]';
          input.id = 'dept_' + dept.id;
          if (selected.includes(String(dept.id))) {
            input.checked = true;
          }
          const label = document.createElement('label');
          label.className = 'form-check-label fw-semibold';
          label.htmlFor = input.id;
          label.innerHTML = `<i class="bi bi-building me-2 maroon-text"></i>${dept.acronym}`;
          wrapper.appendChild(input);
          wrapper.appendChild(label);
          cols[index % 2].appendChild(wrapper);
        });
        container.appendChild(cols[0]);
        container.appendChild(cols[1]);
      }
    })
    .catch(error => console.error('Error loading departments:', error));
}

function saveChanges() {
  const form = document.getElementById('editEventForm');
  const formData = new FormData(form);
  const eventId = formData.get('id');
  fetch('../api/events.php?id=' + eventId, {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Close the modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('editEventModal'));
      modal.hide();
      // Show success toast
      const toast = new bootstrap.Toast(document.getElementById('successToast'));
      toast.show();
      // Refresh page after toast
      setTimeout(() => location.reload(), 1500);
    } else {
      alert('Failed to update event');
    }
  })
  .catch(error => console.error('Error:', error));
}

// small toast helper
function showToast(message, type = 'info') {
  const toast = document.createElement('div');
  toast.className = `alert alert-${type} position-fixed`;
  toast.style.top = '1rem';
  toast.style.right = '1rem';
  toast.style.zIndex = '2000';
  toast.textContent = message;
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

async function loadYearFilter() {
  try {
    const response = await fetch('../api/events.php');
    const data = await response.json();
    if (data.success) {
      const events = data.data;
      const schoolYears = [...new Set(events.map(event => getSchoolYear(event.date)))];
      schoolYears.sort();
      const dropdown = document.getElementById('yearDropdown');
      const button = dropdown.previousElementSibling;
      dropdown.innerHTML = '';
      const currentSchoolYear = getSchoolYear(new Date().toISOString().split('T')[0]);
      schoolYears.forEach(year => {
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.className = 'dropdown-item' + (year === currentSchoolYear ? ' active' : '');
        a.href = '#';
        a.textContent = year;
        a.setAttribute('data-value', year);
        a.addEventListener('click', function(e) {
          e.preventDefault();
          button.textContent = this.textContent;
          dropdown.querySelectorAll('.dropdown-item').forEach(item => item.classList.remove('active'));
          this.classList.add('active');
          selectedYear = this.getAttribute('data-value');
          filterEvents();
        });
        li.appendChild(a);
        dropdown.appendChild(li);
      });
      button.textContent = currentSchoolYear;
      selectedYear = currentSchoolYear;
    }
  } catch (error) {
    console.error('Error loading year filter:', error);
  }
}

async function loadSemesterFilter() {
  try {
    const response = await fetch('../api/semesters.php?archived=0');
    const data = await response.json();
    if (data.success) {
      const dropdown = document.getElementById('semesterDropdown');
      const button = dropdown.previousElementSibling;
      dropdown.innerHTML = '';
      const now = new Date();
      const curMM = String(now.getMonth() + 1).padStart(2, '0');
      let currentSemester = null;
      data.data.forEach(sem => {
        const start = sem.start_date;
        const end = sem.end_date;
        if (start) {
          const sM = String(start).padStart(2, '0');
          if (sM === curMM) {
            currentSemester = sem;
          } else if (end) {
            const eM = String(end).padStart(2, '0');
            const sN = parseInt(sM, 10);
            const eN = parseInt(eM, 10);
            const mN = parseInt(curMM, 10);
            if (sN <= eN) {
              if (mN >= sN && mN <= eN) currentSemester = sem;
            } else {
              // wrap around year end (e.g., start=10 end=03)
              if (mN >= sN || mN <= eN) currentSemester = sem;
            }
          }
        }
      });
      if (!currentSemester) {
        // If no current, find the first one
        currentSemester = data.data[0];
      }
      data.data.forEach(sem => {
        const displayName = formatSemester(sem.name);
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.className = 'dropdown-item' + (sem.id == currentSemester.id ? ' active' : '');
        a.href = '#';
        a.textContent = displayName;
        a.setAttribute('data-value', sem.id);
        a.addEventListener('click', function(e) {
          e.preventDefault();
          button.textContent = this.textContent.toUpperCase();
          dropdown.querySelectorAll('.dropdown-item').forEach(item => item.classList.remove('active'));
          this.classList.add('active');
          selectedSemester = this.getAttribute('data-value');
          filterEvents();
        });
        li.appendChild(a);
        dropdown.appendChild(li);
      });
      if (currentSemester) {
        button.textContent = formatSemester(currentSemester.name).toUpperCase();
        selectedSemester = currentSemester.id;
      }
    }
  } catch (error) {
    console.error('Error loading semester filter:', error);
  }
}

function loadDepartmentFilter() {
  fetch('../api/departments.php?archived=0')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const dropdown = document.getElementById('departmentDropdown');
        const button = dropdown.previousElementSibling; // The button
        // Clear existing items except "All Departments"
        dropdown.innerHTML = '<li><a class="dropdown-item active" href="#" data-value="">All Departments</a></li>';
        // Add click handler to "All Departments"
        const allDeptsItem = dropdown.querySelector('.dropdown-item');
        allDeptsItem.addEventListener('click', function(e) {
          e.preventDefault();
          button.textContent = this.textContent;
          dropdown.querySelectorAll('.dropdown-item').forEach(item => item.classList.remove('active'));
          this.classList.add('active');
          selectedDepartment = '';
          filterEvents();
        });
        data.data.forEach(dept => {
          const li = document.createElement('li');
          const a = document.createElement('a');
          a.className = 'dropdown-item';
          a.href = '#';
          a.textContent = dept.acronym || dept.name;
          a.setAttribute('data-value', dept.id);
          a.addEventListener('click', function(e) {
            e.preventDefault();
            // Update button text
            button.textContent = this.textContent;
            // Update active class
            dropdown.querySelectorAll('.dropdown-item').forEach(item => item.classList.remove('active'));
            this.classList.add('active');
            // Update selected
            selectedDepartment = this.getAttribute('data-value') === '' ? '' : this.textContent;
            // Filter immediately
            filterEvents();
          });
          li.appendChild(a);
          dropdown.appendChild(li);
        });
        // Set initial selected
        selectedDepartment = '';
      }
    })
    .catch(error => console.error('Error loading departments:', error));
}

function filterEvents() {
  const rows = document.querySelectorAll('tbody tr');
  let visibleCount = 0;
  rows.forEach(row => {
    const deptCell = row.cells[3].textContent.trim();
    const semesterId = row.getAttribute('data-semester');
    const eventYear = row.getAttribute('data-year');

    let show = true;

    // Filter by department
    if (selectedDepartment && !deptCell.includes(selectedDepartment)) {
      show = false;
    }

    // Filter by year
    if (selectedYear && eventYear !== selectedYear) {
      show = false;
    }

    // Filter by semester
    if (selectedSemester && semesterId != selectedSemester) {
      show = false;
    }

    row.style.display = show ? '' : 'none';
    if (show) {
      visibleCount++;
      row.cells[0].textContent = visibleCount;
    }
  });
}
</script>

<script>
function replaceSpaces(input) {
  input.value = input.value.replace(/\s+/g, '_');
}

function showTimePicker(input) {
  // placeholder
}

function convertTo12HourFormat(input) {
  // placeholder
}
</script>
<br>
<br>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Toast Notification -->
<div class="toast-container position-fixed top-0 end-0 p-3">
  <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        <i class="bi bi-check-circle-fill me-2"></i>Event updated successfully!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

</body>
</html>