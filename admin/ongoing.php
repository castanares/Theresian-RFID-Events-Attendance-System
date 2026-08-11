<?php require_once __DIR__ . '/../auth.php'; require_login('../'); ?>
<?php
$eventId = isset($_GET['id']) ? (int)$_GET['id'] : null;
require_once __DIR__ . '/../api/dbcon.php';
if ($eventId === null) {
    // Find current ongoing event based on date and time
    $stmt = $conn->prepare("SELECT id FROM events WHERE date = CURDATE() AND CURTIME() BETWEEN TimeIn_Start AND Timeout_out LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result) {
        $eventId = (int)$result['id'];
    } else {
        $eventId = null; // no event scheduled
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $rfid = trim($_POST['rfid'] ?? '');
    if ($rfid !== '') {
        // Find student by RFID or ID_Number
        $stmt = $conn->prepare("SELECT ID_Number, Student_name, status FROM students WHERE RFID = ? OR ID_Number = ? LIMIT 1");
        $stmt->bind_param('ss', $rfid, $rfid);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        if ($student) {
            if ($student['status'] !== 'active') {
                // Student is archived, cannot attend
                // Redirect with error, but since no session, perhaps just skip
                header('Location: ongoing.php?id=' . $eventId);
                exit;
            }
            // Check if already attended today or for this event
            $stmt2 = $conn->prepare("SELECT id FROM attendance WHERE event_id = ? AND student_id = ? LIMIT 1");
            $stmt2->bind_param('ss', $eventId, $student['ID_Number']);
            $stmt2->execute();
            $existing = $stmt2->get_result()->fetch_assoc();
            if (!$existing) {
                // Record attendance
                $stmt3 = $conn->prepare("INSERT INTO attendance (event_id, student_id, time_in, timestamp) VALUES (?, ?, NOW(), NOW())");
                $stmt3->bind_param('ss', $eventId, $student['ID_Number']);
                $stmt3->execute();
            }
        }
    }
    // Redirect to avoid resubmit
    header('Location: ongoing.php?id=' . $eventId);
    exit;
}

// Handle edit save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $event_id = $_POST['event_id'];
    $event_name = $_POST['event_name'];
    $date = $_POST['date'];
    $TimeIN_in = $_POST['TimeIN_in'];
    $TimeIN_out = $_POST['TimeIN_out'];
    $TimeOUT_in = $_POST['TimeOUT_in'];
    $TimeOUT_out = $_POST['TimeOUT_out'];
    // Update event
    $stmt = $conn->prepare("UPDATE events SET event_name = ?, date = ?, TimeIn_start = ?, TimeIn_end = ?, Time_out = ?, Timeout_out = ? WHERE id = ?");
    $stmt->bind_param('ssssssi', $event_name, $date, $TimeIN_in, $TimeIN_out, $TimeOUT_in, $TimeOUT_out, $event_id);
    $stmt->execute();
    // Redirect
    header('Location: ongoing.php?id=' . $event_id);
    exit;
}

// Handle archive
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive'])) {
    $event_id = $_POST['event_id'];
    $stmt = $conn->prepare("UPDATE events SET status = 'archived' WHERE id = ?");
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    // Redirect to events list
    header('Location: events.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EVENT Attendance</title>
  <link rel="stylesheet" href="../styles/dash_styles.css">
  <link rel="stylesheet" href="../styles/side_styles.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="../Event/js/html2pdf.bundle.min.js"></script>
  <script src="../Event/js/docx.min.js"></script>
  <script src="../Event/js/xlsx.min.js"></script>
</head>
<style>.table-maroon th { background-color: maroon !important; color: white !important; }
  .department-check { transition: background 0.2s, box-shadow 0.2s; }
  .department-check:hover { background: #ffe5e5; box-shadow: 0 2px 8px rgba(128,0,0,0.08); }
  .dept-margin { margin-left: 10rem; }
  .dept-margin-right { margin-right: 10rem; }
  .maroon-text { color: maroon; }
  .bg-maroon { background-color: maroon; }
  .form-check-input:checked { background-color: maroon; }
  .create{ border-left: 3px solid maroon !important; }
  .create-submit:hover{ background-color:white; border-color: solid maroon !important; }
  .btn-maroon {
    background-color: maroon;
    color: white;
    border: 1px solid maroon;
  }
  .btn-maroon:hover {
    background-color: #800000;
    border-color: #800000;
    color: white;
  }
  .modal-header {
    background-color: maroon;
    color: white;
  }
  .modal-header .btn-close {
    filter: invert(1);
  }
  .modal-body {
    padding: 2rem;
  }
  .form-check-label {
    font-weight: 500;
  }
</style>
<body>
<?php include 'sidebar.php'; ?>
<?php $page_heading = 'ONGOING EVENTS'; include 'navbar.php'; ?>
  <section>
    <br>
    <h1 class="text-center text-decoration-underline link-underline-danger text-uppercase"><span id="eventTitle">PANAGHIUSA 2024</span>
      <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-pencil-square fw-bold" viewBox="0 0 16 16" data-bs-toggle="modal" data-bs-target="#Edit-modal" style="cursor: pointer;">
        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
      </svg>
    </h1>
    <!-- Edit Event Modal -->
    <div class="modal fade" tabindex="-1" id="Edit-modal">
      <div class="modal-dialog modal-lg ">
        <div class="modal-content ">
          <div class="modal-header" style="background-color: maroon; color: white;">
            <h5 class="modal-title">Edit Events</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="ongoing.php?id=<?php echo $eventId; ?>" method="post" id="editEventForm">
            <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
            <div class="modal-body">
                <div class="mb-3">
                  <label class="fw-bold">Event Name:</label>
                  <input type="text" name="event_name" class="form-control" value="PANAGHIUSA_2024" required oninput="replaceSpaces(this)">
                </div>
                <div class="mb-3 row">
                  <div class="col">
                    <label class="fw-bold">Event Date:</label>
                    <input type="date" name="date" class="form-control" value="2025-09-23" required>
                  </div>
                  <div class="col">
                    <label class="fw-bold">Semester:</label>
                    <input type="text" name="semester" class="form-control " value="FIRST" readonly>
                  </div>
                </div>
                <div class="mb-3 text-center">
                  <label class="fw-bold text-center text-decoration-underline link-underline-danger  fs-4">Include Other Departments:</label>
                  <div id="departmentsContainer" class="row"></div>
                </div>
                <div class="mb-3">
                  <div class="text-center"> <label class="fw-bold text-decoration-underline link-underline-danger fs-4">Event Time</label> </div>
                  <div class="row">
                    <div class="col">
                      <label class="fw-bold">Time In:</label>
                      <input type="time" name="TimeIN_in" placeholder="START" onfocus="showTimePicker(this)" onblur="convertTo12HourFormat(this)" class="form-control text-center " value="7:30 am" required>
                    </div>
                    <div class="col">
                      <label class="fw-bold">Time Out:</label>
                      <input type="time" name="TimeIN_out" placeholder="END" onfocus="showTimePicker(this)" onblur="convertTo12HourFormat(this)" class="form-control text-center" value="11:45 am" required>
                    </div>
                    <div class="col">
                      <label class="fw-bold">Time Out In:</label>
                      <input type="time" name="TimeOUT_in" placeholder="START" onfocus="showTimePicker(this)" onblur="convertTo12HourFormat(this)" class="form-control text-center " value="12:00 pm" required>
                    </div>
                    <div class="col">
                      <label class="fw-bold">Time Out:</label>
                      <input type="time" name="TimeOUT_out" placeholder="END" onfocus="showTimePicker(this)" onblur="convertTo12HourFormat(this)" class="form-control text-center " value="12:30 pm" required>
                    </div>
                  </div>
                </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-danger" name="archive">Archive Event</button>
              <button type="submit" class="btn" style="background-color: maroon; color: white;" name="save">Save changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
  <br>

  <section class="d-md-flex justify-content-md-center mb-3">
    <!-- scan form moved to the start of the attendance table for inline scanning -->
  </section>

  <section class="event-table">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <form id="scanForm" class="m-0">
        <div class="d-flex gap-2 align-items-center">
          <input type="number" name="rfid" autofocus placeholder="Scan Here" class="rounded form-control" style="width:230px;" required>
          <button type="submit" class="btn btn-maroon rounded">Submit</button>
        </div>
      </form>
      <div>
        <button id="exportBtn" class="btn btn-maroon" data-bs-toggle="modal" data-bs-target="#exportModal">Export Attendance</button>
      </div>
    </div>
    <table class="table border border-dark table-bordered">
      <thead class="table-maroon border border-dark">
        <tr>
          <th scope="col" rowspan="2" class="text-center">#</th>
          <th scope="col" rowspan="2">ID NUMBER</th>
          <th scope="col" rowspan="2">STUDENT NAME</th>
           <th scope="col" rowspan="2">Course</th>
          <th scope="col" class="text-center">TIME IN</th>
          <th scope="col" class="text-center">TIME OUT</th>
        </tr>
      </thead>
      <tbody id="attendanceTableBody">
        <!-- Attendance data will be loaded here -->
      </tbody>
    </table>
  </section>
  
  <!-- Export Modal -->
  <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exportModalLabel">Export Attendance</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted mb-3">Choose the format for exporting the attendance data:</p>
          <form>
            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="format" id="docx" value="docx" checked>
              <label class="form-check-label" for="docx">
                <i class="bi bi-file-earmark-word me-2"></i>DOCX - Microsoft Word Document
              </label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="format" id="pdf" value="pdf">
              <label class="form-check-label" for="pdf">
                <i class="bi bi-file-earmark-pdf me-2"></i>PDF - Portable Document Format
              </label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="format" id="xlsx" value="xlsx">
              <label class="form-check-label" for="xlsx">
                <i class="bi bi-file-earmark-excel me-2"></i>XLSX - Microsoft Excel Spreadsheet
              </label>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" id="confirmExport" class="btn btn-maroon">Export</button>
        </div>
      </div>
    </div>
  </div>
  <br><br><br><br>
  
  <section>
    <footer class="footer d-flex justify-content-center fixed-bottom bg-light z-0">
    <div class="fixed text-center ">
          <h5>Event Start 7:30 am</h5>
          <h5>Event End 12:30 pm</h5>
    </div>
  </footer>
  </section>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script>
const eventId = <?php echo json_encode($eventId); ?>;
let myDeptList = [];
let myDepartmentsIdCSV = '';
let eventTimes = {};
let eventDate = '';
let userDepartment = '';
let allowedToViewEvent = true;
let isAdmin = false;

function to12Hour(time) {
  if (!time) return '';
  const [h, m] = time.split(':');
  let hour = parseInt(h, 10);
  const ampm = hour >= 12 ? 'pm' : 'am';
  hour = hour % 12 || 12;
  return `${hour}:${m} ${ampm}`;
}

async function loadFormData() {
  try {
    // first, get current user's department(s) so we can exclude them and include departments_id in payload
    myDeptList = [];
    myDepartmentsIdCSV = '';
    let myUserId = null;
    try {
      const meRes = await fetch('../api/me.php', {credentials:'same-origin'});
      const meJson = await meRes.json();
      if (meJson) {
        myUserId = meJson.user_id ?? null;
        // prefer departments_id (CSV), then department_id, then raw department
        if (meJson.departments_id !== undefined && meJson.departments_id !== null && String(meJson.departments_id).trim() !== '') {
          myDepartmentsIdCSV = String(meJson.departments_id);
        } else if (meJson.department_id !== undefined && meJson.department_id !== null) {
          if (Array.isArray(meJson.department_id)) myDepartmentsIdCSV = meJson.department_id.map(String).join(',');
          else myDepartmentsIdCSV = String(meJson.department_id);
        } else if (meJson.department !== undefined && meJson.department !== null) {
          myDepartmentsIdCSV = String(meJson.department);
        }

        if (myDepartmentsIdCSV !== '') myDeptList = myDepartmentsIdCSV.split(',').map(s=>s.trim()).filter(Boolean);
        // detect admin role from common properties returned by ../api/me.php
        if (meJson.account_type === 'admin' || meJson.role === 'admin' || meJson.type === 'admin' || meJson.is_admin == 1) {
          isAdmin = true;
        }
      }
    } catch(e) { console.warn('Could not fetch user dept', e); }

    // fetch event details
    const eventRes = await fetch(`../api/events.php?id=${eventId}`, {credentials:'same-origin'});
    const eventJson = await eventRes.json();
    if (eventJson && eventJson.success && eventJson.data) {
      const event = eventJson.data;
      // populate form
      document.querySelector('[name="event_name"]').value = event.event_name || '';
      document.querySelector('[name="date"]').value = event.date || '';
      document.querySelector('[name="semester"]').value = event.semester || '';
      
      // store event date for exports
      eventDate = event.date || '';
      document.querySelector('[name="TimeIN_in"]').value = event.TimeIN_in || '';
      document.querySelector('[name="TimeIN_out"]').value = event.TimeIN_out || '';
      document.querySelector('[name="TimeOUT_in"]').value = event.TimeOUT_in || '';
      document.querySelector('[name="TimeOUT_out"]').value = event.TimeOUT_out || '';
      // update page title
      document.getElementById('eventTitle').textContent = event.event_name || 'EVENT';
      // update footer times
      const footer = document.querySelector('.footer .fixed');
      if (footer) {
        footer.innerHTML = `
          <h5>Event Start ${to12Hour(event.TimeIN_in) || ''}</h5>
          <h5>Event End ${to12Hour(event.TimeOUT_out) || ''}</h5>
        `;
      }
      // store event times for coloring
      eventTimes = {
        TimeIN_in: event.TimeIN_in,
        TimeIN_out: event.TimeIN_out,
        TimeOUT_in: event.TimeOUT_in,
        TimeOUT_out: event.TimeOUT_out
      };

      // check if user can edit
      // Admin can edit any event
      // Removed permission check
    }

    // load departments
    const depRes = await fetch('../api/departments.php?archived=0');
    const depJson = await depRes.json();
    const container = document.getElementById('departmentsContainer');
    container.innerHTML = '';
    if (depJson && depJson.success && Array.isArray(depJson.data) && depJson.data.length){
      // render into two columns roughly equal
      const cols = [document.createElement('div'), document.createElement('div')];
      cols[0].className = 'col-md-6'; cols[1].className = 'col-md-6';
      let shownIndex = 0;
      depJson.data.forEach((d, i) => {
        const id = d.id ?? d.dept_id ?? d.Dept_ID ?? d.ID ?? null;
        // skip if user's dept list contains this id
        if (id !== null && myDeptList.length && myDeptList.map(String).includes(String(id))) return;
        const label = d.acronym ?? d.acro ?? d.name ?? d.Dept_name ?? d.name ?? '';
        const display = label || id || 'Dept';
        const wrapper = document.createElement('div'); wrapper.className = 'form-check d-flex align-items-center gap-2 p-2 rounded department-check ' + (shownIndex%2===0 ? 'dept-margin' : 'dept-margin-right');
        const input = document.createElement('input'); input.type = 'checkbox'; input.className = 'form-check-input';
        // set value to numeric id when available, otherwise send acronym/name
        input.value = id ? String(id) : (d.acronym ?? d.name ?? display);
        input.name = 'departments[]';
        input.id = 'dept_' + (id ? String(id) : shownIndex);
        const lab = document.createElement('label'); lab.className = 'form-check-label fw-semibold'; lab.htmlFor = input.id; lab.innerHTML = `<i class="bi bi-building me-2 maroon-text"></i>${display}`;
        wrapper.appendChild(input); wrapper.appendChild(lab);
        cols[shownIndex % 2].appendChild(wrapper);
        shownIndex++;
      });
      container.appendChild(cols[0]); container.appendChild(cols[1]);

      // pre-select based on event's other_departments and primary department
      let preSelected = [];
      if (eventJson.data.other_departments) {
        preSelected = preSelected.concat((Array.isArray(eventJson.data.other_departments) ? eventJson.data.other_departments : String(eventJson.data.other_departments).split(',').map(s=>s.trim())).map(String));
      }
      if (eventJson.data.departments_id) {
        preSelected = preSelected.concat(String(eventJson.data.departments_id).split(',').map(s=>s.trim()).filter(Boolean));
      }
      document.querySelectorAll('#departmentsContainer input[type="checkbox"]').forEach(cb => {
        if (preSelected.includes(cb.value)) cb.checked = true;
      });
    } else {
      container.innerHTML = '<div class="col-12 text-muted">No departments available</div>';
    }

    // Set user department for export
    if (myDeptList.length > 0 && depJson && depJson.success && Array.isArray(depJson.data)) {
      const dept = depJson.data.find(d => String(d.id) === myDeptList[0]);
      if (dept) {
        userDepartment = dept.name || dept.Dept_name || dept.acronym || 'Unknown Department';
      }
    }
  }catch(err){
    console.error('Failed to load form data', err);
    const container = document.getElementById('departmentsContainer');
    container.innerHTML = '<div class="col-12 text-muted">Failed to load departments</div>';
  }
}

async function loadAttendance() {
  try {
    const res = await fetch(`../api/attendance.php?event_id=${eventId}`, {credentials:'same-origin'});
    const json = await res.json();
    if (json.success && Array.isArray(json.data)) {
      const tbody = document.getElementById('attendanceTableBody');
      tbody.innerHTML = '';
      json.data.forEach((att, index) => {
        const tr = document.createElement('tr');
        let timeInClass = '';
        if (att.Time_in) {
          const timeInDate = new Date(att.Time_in.replace(' ', 'T'));
          const timeStr = timeInDate.getHours().toString().padStart(2,'0') + ':' + timeInDate.getMinutes().toString().padStart(2,'0');
          if (timeStr >= eventTimes.TimeIN_in && timeStr <= eventTimes.TimeIN_out) {
            timeInClass = 'text-success fw-bold';
          } else if (timeStr > eventTimes.TimeIN_out && timeStr <= eventTimes.TimeOUT_in) {
            timeInClass = 'text-danger fw-bold';
          }
        }
        let timeOutClass = '';
        if (att.Time_out) {
          const timeOutDate = new Date(att.Time_out.replace(' ', 'T'));
          const timeStr = timeOutDate.getHours().toString().padStart(2,'0') + ':' + timeOutDate.getMinutes().toString().padStart(2,'0');
          if (timeStr >= eventTimes.TimeOUT_in && timeStr <= eventTimes.TimeOUT_out) {
            timeOutClass = 'text-success fw-bold';
          } else {
            timeOutClass = 'text-danger fw-bold';
          }
        }
        tr.innerHTML = `
          <th scope="row">${index + 1}</th>
          <td>${att.ID_Number || att.student_id}</td>
          <td>${att.student_name || 'Unknown'}</td>
          <td>${att.Course || ''}</td>
          <td class="text-center ${timeInClass}">${att.Time_in ? new Date(att.Time_in.replace(' ', 'T')).toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true}) : ''}</td>
          <td class="text-center ${timeOutClass}">${att.Time_out ? new Date(att.Time_out.replace(' ', 'T')).toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true}) : ''}</td>
        `;
        tbody.appendChild(tr);
      });
    }
  } catch (err) {
    console.error('Failed to load attendance', err);
  }
}

document.addEventListener('DOMContentLoaded', async () => {
  if (!eventId) {
    document.getElementById('eventTitle').textContent = 'No Event is Scheduled';
    // remove elements
    document.querySelector('.bi-pencil-square').remove();
    document.getElementById('Edit-modal').remove();
    document.querySelector('section.d-md-flex').remove();
    document.querySelector('.event-table').remove();
    document.querySelector('section footer').remove();
    return;
  }
  await loadFormData();
  loadAttendance();

  // Handle attendance forms
  document.getElementById('scanForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const rfid = e.target.rfid.value.trim();
    if (rfid) {
      await recordAttendance(rfid);
      e.target.reset();
      loadAttendance(); // refresh table
    }
  });

  document.getElementById('idForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const rfid = e.target.rfid.value.trim();
    if (rfid) {
      await recordAttendance(rfid);
      e.target.reset();
      loadAttendance(); // refresh table
    }
  });
});

async function recordAttendance(rfid) {
  try {
    // First, check student status
    const studentRes = await fetch('../api/students.php?rfid=' + encodeURIComponent(rfid), {credentials:'same-origin'});
    const studentJson = await studentRes.json();
    if (!studentJson.success || !studentJson.data || studentJson.data.length === 0) {
      const toastEl = document.createElement('div');
      toastEl.className = 'toast align-items-center text-white bg-danger border-0 position-fixed top-0 end-0 m-3';
      toastEl.setAttribute('role', 'alert');
      toastEl.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">Student not found</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      `;
      document.body.appendChild(toastEl);
      const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
      toast.show();
      return;
    }
    const student = studentJson.data[0];
    if (student.status !== 'active') {
      const toastEl = document.createElement('div');
      toastEl.className = 'toast align-items-center text-white bg-warning border-0 position-fixed top-0 end-0 m-3';
      toastEl.setAttribute('role', 'alert');
      toastEl.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">Student is archived and cannot attend</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      `;
      document.body.appendChild(toastEl);
      const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
      toast.show();
      return;
    }
    // Proceed to record attendance
    const attRes = await fetch('../api/attendance.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        event_id: eventId.toString(),
        rfid: rfid
      }),
      credentials: 'same-origin'
    });
    const attJson = await attRes.json();
    if (attJson.success) {
      // show success toast
      const toastType = attJson.type === 'success' ? 'success' : 'danger';
      const toastEl = document.createElement('div');
      toastEl.className = `toast align-items-center text-white bg-${toastType} border-0 position-fixed top-0 end-0 m-3`;
      toastEl.setAttribute('role', 'alert');
      toastEl.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">${attJson.message}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      `;
      document.body.appendChild(toastEl);
      const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
      toast.show();
    } else {
      // show error toast
      const toastEl = document.createElement('div');
      toastEl.className = 'toast align-items-center text-white bg-danger border-0 position-fixed top-0 end-0 m-3';
      toastEl.setAttribute('role', 'alert');
      toastEl.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">${attJson.message || 'Failed to record attendance'}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      `;
      document.body.appendChild(toastEl);
      const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 5000 });
      toast.show();
    }
  } catch (err) {
    console.error('Error recording attendance', err);
    // show error toast
    const toastEl = document.createElement('div');
    toastEl.className = 'toast align-items-center text-white bg-danger border-0 position-fixed top-0 end-0 m-3';
    toastEl.setAttribute('role', 'alert');
    toastEl.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">Error recording attendance</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    `;
    document.body.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 5000 });
    toast.show();
  }
}

// handle form submission
document.getElementById('editEventForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData(e.target);
  const payload = {
    id: formData.get('event_id'),
    event_name: formData.get('event_name'),
    date: formData.get('date'),
    semester: formData.get('semester'),
    TimeIN_in: formData.get('TimeIN_in'),
    TimeIN_out: formData.get('TimeIN_out'),
    TimeOUT_in: formData.get('TimeOUT_in'),
    TimeOUT_out: formData.get('TimeOUT_out'),
    departments_id: myDepartmentsIdCSV,
    departments: formData.getAll('departments[]')
  };
  try {
    const res = await fetch('../api/events.php', {
      method: 'PUT',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(payload),
      credentials: 'same-origin'
    });
    const json = await res.json();
    if (json.success) {
      // show success toast
      const toastEl = document.createElement('div');
      toastEl.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
      toastEl.setAttribute('role', 'alert');
      toastEl.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">Event updated successfully!</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      `;
      document.body.appendChild(toastEl);
      const toast = new bootstrap.Toast(toastEl);
      toast.show();
      // close modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('Edit-modal'));
      modal.hide();
      // reload page or update display
      setTimeout(() => location.reload(), 2000);
    } else {
      alert('Failed to update event: ' + (json.error || 'Unknown error'));
    }
  } catch (err) {
    console.error('Submit error', err);
    alert('Error updating event');
  }
});

// Export functions
function download(content, filename, mime) {
  const blob = new Blob([content], {type: mime});
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
}

function exportPDF() {
  const eventTitle = document.getElementById('eventTitle').textContent;
  const startTime = document.querySelector('.footer .fixed h5:first-child').textContent;
  const endTime = document.querySelector('.footer .fixed h5:last-child').textContent;
  const rows = Array.from(document.querySelectorAll('#attendanceTableBody tr'));

  // Helper function to convert time string to minutes
  function timeToMinutes(timeStr) {
    if (!timeStr) return null;
    const match = timeStr.match(/(\d+):(\d+)(?:\s*(AM|PM))?/i);
    if (!match) return null;
    let hour = parseInt(match[1], 10);
    const min = parseInt(match[2], 10);
    const ampm = match[3]?.toUpperCase();
    if (ampm) {
      // 12-hour format
      if (ampm === 'PM' && hour !== 12) hour += 12;
      if (ampm === 'AM' && hour === 12) hour = 0;
    } else {
      // 24-hour format, hour is already 0-23
    }
    return hour * 60 + min;
  }

  // Build a clean, print-friendly table (avoid Bootstrap wrappers)
  let bodyRowsHtml = '';
  rows.forEach(r => {
    const cells = r.querySelectorAll('th,td');
    const num = (cells[0]?.textContent || '').trim();
    const id = (cells[1]?.textContent || '').trim();
    const name = (cells[2]?.textContent || '').trim();
    const course = (cells[3]?.textContent || '').trim();
    const timeIn = (cells[4]?.textContent || '').trim();
    const timeOut = (cells[5]?.textContent || '').trim();

    // Determine styles for TIME IN
    let timeInClass = '';
    const timeInMin = timeToMinutes(timeIn);
    const timeInStart = timeToMinutes(eventTimes.TimeIN_in);
    const timeInEnd = timeToMinutes(eventTimes.TimeIN_out);
    const timeOutStart = timeToMinutes(eventTimes.TimeOUT_in);
    if (timeInMin !== null) {
      if (timeInMin >= timeInStart && timeInMin <= timeInEnd) {
        timeInClass = 'time-on-time';
      } else if (timeInMin > timeInEnd && timeInMin <= timeOutStart) {
        timeInClass = 'time-late';
      }
    }

    // Determine styles for TIME OUT
    let timeOutClass = '';
    const timeOutMin = timeToMinutes(timeOut);
    const timeOutEnd = timeToMinutes(eventTimes.TimeOUT_out);
    if (timeOutMin !== null) {
      if (timeOutMin >= timeOutStart && timeOutMin <= timeOutEnd) {
        timeOutClass = 'time-on-time';
      } else {
        timeOutClass = 'time-late';
      }
    }

    bodyRowsHtml += `<tr>
      <td class="c-num">${num}</td>
      <td>${id}</td>
      <td>${name}</td>
      <td>${course}</td>
      <td class="c-time ${timeInClass}">${timeIn}</td>
      <td class="c-time ${timeOutClass}">${timeOut}</td>
    </tr>`;
  });

  const exportDiv = document.createElement('div');
  exportDiv.innerHTML = `
    <style>
      @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap');
      * { box-sizing: border-box; }
      html, body { margin: 0; padding: 0; }
      body { font-family: Arial, sans-serif; font-size: 11px; color: #000; }
      .doc { padding: 0; }
      /* Fixed header that repeats per page */
      .pdf-header { position: fixed; top: 0; left: 0; right: 0; height: 110px; }
      .hdr { text-align: center; color: #800000; }
      .hdr .logo { width: 70px; height: auto; display:block; margin: 2px auto 4px; }
      .hdr .line1 { font-size: 12px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; }
      .hdr .line2 { font-size: 10px; margin-top: 2px; color: #333; }
      .hdr .motto { font-family: Vivaldi, 'Great Vibes', 'Brush Script MT', cursive; font-size: 16px; font-style: italic; color: #800000; margin-top: 2px; }
      .content { margin-top: 120px; }
      .title { text-align:center; font-size: 13px; font-weight: 700; margin: 6px 0 8px; text-transform: uppercase; color:#000; }
      .meta { text-align:center; font-size: 10px; margin-bottom: 8px; color:#000; }
      table { width: 100%; border-collapse: collapse; table-layout: fixed; }
      thead th { background:#800000; color:#fff; font-weight:700; }
      th, td { border: 1px solid #000; padding: 3px 4px; word-wrap: break-word; }
      thead { display: table-header-group; }
      tr, td, th { page-break-inside: avoid; break-inside: avoid; }
      .c-num { width: 6%; text-align:center; }
      .c-id { width: 18%; }
      .c-name { width: 34%; }
      .c-course { width: 20%; }
      .c-time { width: 11%; text-align:center; }
      .time-on-time { color: #198754; font-weight: bold; }
      .time-late { color: #dc3545; font-weight: bold; }
    </style>
    <div class="doc">
      <div class="pdf-header">
        <div class="hdr">
          <img class="logo" src="../images/stctandag-logo.png" alt="SAINT THERESA COLLEGE OF TANDAG, INC. Logo" />
          <div class="motto">"Ad Ministerium, Veritatem ot Communionom"</div>
          <div class="line1">SAINT THERESA COLLEGE OF TANDAG, INC.</div>
          <div class="department" style="text-align:center; font-size: 12px; margin-bottom: 2px; color:#000;">${userDepartment}</div>
          <div class="line2">Tandag City, Surigao del Sur</div>
          <div style="border-bottom: 1px solid #000; margin: 4px auto; width: 95%;"></div>
        </div>
      </div>
      <div class="content">
  <div class="title"><br><br><strong style="font-size: 20px; font-weight: bold;">${eventTitle}</strong></div>
        <div class="meta">Event Date: ${eventDate ? new Date(eventDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A'} | ${startTime} | ${endTime}</div>
        <table>
        <colgroup>
          <col style="width:6%"><col style="width:18%"><col style="width:34%"><col style="width:20%"><col style="width:11%"><col style="width:11%">
        </colgroup>
        <thead>
          <tr>
            <th class="c-num">#</th>
            <th class="c-id">ID NUMBER</th>
            <th class="c-name">STUDENT NAME</th>
            <th class="c-course">COURSE</th>
            <th class="c-time">TIME IN</th>
            <th class="c-time">TIME OUT</th>
          </tr>
        </thead>
        <tbody>
          ${bodyRowsHtml}
        </tbody>
        </table>
      </div>
    </div>
  `;

  html2pdf().from(exportDiv).set({
    // Top, left, bottom, right margins in inches (tighter side margins)
    margin: [0.4, 0.25, 0.4, 0.25],
    filename: 'attendance.pdf',
    html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
    pagebreak: { mode: ['css', 'legacy'] },
    jsPDF: { orientation: 'portrait', unit: 'in', format: 'a4' }
  }).save();
}

async function exportDOCX() {
  try {
    const {
      Document,
      Packer,
      Paragraph,
      Table,
      TableCell,
      TableRow,
      WidthType,
      AlignmentType,
      TextRun,
      ImageRun,
      HeadingLevel,
      BorderStyle
    } = window.docx || {};

    if (!Document || !Packer) {
      alert('DOCX library not loaded. Please refresh the page.');
      return;
    }

    const eventTitle = document.getElementById('eventTitle').textContent;
    const startTime = document.querySelector('.footer .fixed h5:first-child').textContent;
    const endTime = document.querySelector('.footer .fixed h5:last-child').textContent;
    const rows = Array.from(document.querySelectorAll('#attendanceTableBody tr'));

    // Helper: time parsing same as PDF
    const timeToMinutes = (timeStr) => {
      if (!timeStr) return null;
      const match = timeStr.match(/(\d+):(\d+)(?:\s*(AM|PM))?/i);
      if (!match) return null;
      let hour = parseInt(match[1], 10);
      const min = parseInt(match[2], 10);
      const ampm = match[3]?.toUpperCase();
      if (ampm) {
        if (ampm === 'PM' && hour !== 12) hour += 12;
        if (ampm === 'AM' && hour === 12) hour = 0;
      }
      return hour * 60 + min;
    };

    // Load logo as ImageRun (optional)
    let logoRun = null;
    try {
      const resp = await fetch('../images/stctandag-logo.png');
      if (resp.ok) {
        const buf = await resp.arrayBuffer();
        logoRun = new ImageRun({ data: buf, transformation: { width: 70, height: 70 } });
      }
    } catch (_) { /* ignore image load errors */ }

    // Header content to be placed in the Word header
    const headerParas = [];
    if (logoRun) {
      headerParas.push(new Paragraph({ alignment: AlignmentType.CENTER, children: [logoRun] }));
    }
    headerParas.push(
       new Paragraph({ alignment: AlignmentType.CENTER, children: [
        new TextRun({ text: '\"Ad Ministerium, Veritatem ot Communionom\"', italics: true, color: '800000', font: 'Vivaldi' })
      ]}),
      new Paragraph({ alignment: AlignmentType.CENTER, children: [
        new TextRun({ text: 'SAINT THERESA COLLEGE OF TANDAG, INC.', bold: true, color: '800000' })
      ]}),
     
      new Paragraph({ alignment: AlignmentType.CENTER, children: [
        new TextRun({ text: userDepartment || '', color: '000000' })
      ]}),
      new Paragraph({ alignment: AlignmentType.CENTER, children: [
        new TextRun({ text: 'Tandag City, Surigao del Sur', color: '333333' })
      ]}),
      // Horizontal divider line similar to <div style="border-bottom:1px solid #000; width:95%">
      new Paragraph({
        border: {
          bottom: { color: '000000', space: 1, size: 6, style: BorderStyle ? BorderStyle.SINGLE : 'single' }
        }
      }),
      new Paragraph({ children: [new TextRun({ text: '' })] })
      
    );

    // Title and meta
    const titlePara = new Paragraph({ alignment: AlignmentType.CENTER, children: [
      new TextRun({ text: eventTitle, bold: true, size: 40 }), // 20pt
      new TextRun({ text: '', break: 1 })
    ]});
    const metaPara = new Paragraph({ alignment: AlignmentType.CENTER, children: [
      new TextRun({ text: `Event Date: ${eventDate ? new Date(eventDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A'} | ${startTime} | ${endTime}`, size: 22 }) // 11pt
    ]});

    // Table header styling
    const makeHeaderCell = (text, widthTwips) => new TableCell({
      width: { size: widthTwips, type: WidthType.DXA },
      children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [
        new TextRun({ text, bold: true, color: 'FFFFFF' })
      ]})],
      shading: { fill: '800000' }
    });

    // Compute DXA widths based on ~10.77k twips usable width
    const contentWidth = 10772; // approx A4 content width with 10mm margins
    const w = {
      num: Math.round(contentWidth * 0.06),
      id: Math.round(contentWidth * 0.18),
      name: Math.round(contentWidth * 0.34),
      course: Math.round(contentWidth * 0.20),
      time: Math.round(contentWidth * 0.11),
    };

    const headerRow = new TableRow({ tableHeader: true, children: [
      makeHeaderCell('#', w.num),
      makeHeaderCell('ID NUMBER', w.id),
      makeHeaderCell('STUDENT NAME', w.name),
      makeHeaderCell('COURSE', w.course),
      makeHeaderCell('TIME IN', w.time),
      makeHeaderCell('TIME OUT', w.time),
    ]});

    // Build body rows with on-time/late coloring
    const bodyRows = rows.map((row) => {
      const cells = row.querySelectorAll('th,td');
      const num = (cells[0]?.textContent || '').trim();
      const id = (cells[1]?.textContent || '').trim();
      const name = (cells[2]?.textContent || '').trim();
      const course = (cells[3]?.textContent || '').trim();
      const timeIn = (cells[4]?.textContent || '').trim();
      const timeOut = (cells[5]?.textContent || '').trim();

      // Determine color for times using same logic as PDF
      let timeInColor = '000000';
      let timeOutColor = '000000';
      try {
        const timeInMin = timeToMinutes(timeIn);
        const timeInStart = timeToMinutes(eventTimes.TimeIN_in);
        const timeInEnd = timeToMinutes(eventTimes.TimeIN_out);
        const timeOutStart = timeToMinutes(eventTimes.TimeOUT_in);
        if (timeInMin !== null) {
          if (timeInMin >= timeInStart && timeInMin <= timeInEnd) timeInColor = '198754';
          else if (timeInMin > timeInEnd && timeInMin <= timeOutStart) timeInColor = 'DC3545';
        }
        const timeOutMin = timeToMinutes(timeOut);
        const timeOutEnd = timeToMinutes(eventTimes.TimeOUT_out);
        if (timeOutMin !== null) {
          if (timeOutMin >= timeOutStart && timeOutMin <= timeOutEnd) timeOutColor = '198754'; else timeOutColor = 'DC3545';
        }
      } catch(_) { /* if eventTimes missing, keep default color */ }

      const makeCell = (text, widthTwips, alignCenter = false, color) => new TableCell({
        width: { size: widthTwips, type: WidthType.DXA },
        children: [new Paragraph({ alignment: alignCenter ? AlignmentType.CENTER : undefined, children: [
          new TextRun({ text: text || '', color: color || '000000' })
        ]})]
      });

      return new TableRow({ cantSplit: true, children: [
        makeCell(num, w.num, true),
        makeCell(id, w.id),
        makeCell(name, w.name),
        makeCell(course, w.course),
        makeCell(timeIn, w.time, true, timeInColor),
        makeCell(timeOut, w.time, true, timeOutColor),
      ]});
    });

    const table = new Table({
      width: { size: contentWidth, type: WidthType.DXA },
      rows: [headerRow, ...bodyRows],
    });

    const doc = new Document({
      sections: [{
        properties: {
          page: {
            margin: { top: 567, right: 567, bottom: 567, left: 567 }, // ~10mm sides
          }
        },
        headers: {
          default: new docx.Header({ children: headerParas })
        },
        children: [
          titlePara,
          metaPara,
          // new Paragraph({ children: [new TextRun({ text: '' })] }),
          table,
        ]
      }]
    });

    const blob = await Packer.toBlob(doc);
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'attendance.docx';
    a.click();
    URL.revokeObjectURL(url);
  } catch (err) {
    console.error('DOCX export error:', err);
    alert('Failed to export DOCX. Please try again.');
  }
}

function exportXLSX() {
  const XLSX = window.XLSX;
  if (!XLSX) {
    alert('XLSX library not loaded. Please refresh the page.');
    return;
  }
  const eventTitle = document.getElementById('eventTitle').textContent;
  const startTime = document.querySelector('.footer .fixed h5:first-child').textContent;
  const endTime = document.querySelector('.footer .fixed h5:last-child').textContent;
  const rows = Array.from(document.querySelectorAll('#attendanceTableBody tr'));

  // Helper function: parse 12h/24h time to minutes since midnight
  function timeToMinutes(timeStr) {
    if (!timeStr) return null;
    const match = timeStr.match(/(\d+):(\d+)(?:\s*(AM|PM))?/i);
    if (!match) return null;
    let hour = parseInt(match[1], 10);
    const min = parseInt(match[2], 10);
    const ampm = match[3]?.toUpperCase();
    if (ampm) {
      if (ampm === 'PM' && hour !== 12) hour += 12;
      if (ampm === 'AM' && hour === 12) hour = 0;
    }
    return hour * 60 + min;
  }

  // Build data array
  const data = [
    ['Event Title:', eventTitle],
    ['Event Date:', eventDate ? new Date(eventDate).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A'],
    ['Start Time', startTime],
    ['End Time', endTime],
    [], // empty row
    ['#', 'ID NUMBER', 'STUDENT NAME', 'COURSE', 'TIME IN', 'TIME OUT']
  ];

  // Helper to convert a time string to a Date object (date part arbitrary)
  function parseTimeToDate(timeStr) {
    const mins = timeToMinutes(timeStr);
    if (mins === null) return null;
    const h = Math.floor(mins / 60);
    const m = mins % 60;
    return new Date(1970, 0, 1, h, m, 0);
  }

  // Track per-row formatting for time cells (green/red)
  const rowFlags = [];

  rows.forEach(row => {
    const cells = row.querySelectorAll('th,td');
    const num = (cells[0]?.textContent || '').trim();
    const id = (cells[1]?.textContent || '').trim();
    const name = (cells[2]?.textContent || '').trim();
    const course = (cells[3]?.textContent || '').trim();
    const timeIn = (cells[4]?.textContent || '').trim();
    const timeOut = (cells[5]?.textContent || '').trim();

    // Determine on-time/late for TIME IN and TIME OUT
    let inFmt = null;  // 'green' | 'red' | null
    let outFmt = null; // 'green' | 'red' | null

    try {
      const timeInMin = timeToMinutes(timeIn);
      const timeInStart = timeToMinutes(eventTimes.TimeIN_in);
      const timeInEnd = timeToMinutes(eventTimes.TimeIN_out);
      const timeOutStart = timeToMinutes(eventTimes.TimeOUT_in);
      if (timeInMin !== null && timeInStart !== null && timeInEnd !== null && timeOutStart !== null) {
        if (timeInMin >= timeInStart && timeInMin <= timeInEnd) inFmt = 'green';
        else if (timeInMin > timeInEnd && timeInMin <= timeOutStart) inFmt = 'red';
      }

      const timeOutMin = timeToMinutes(timeOut);
      const timeOutEnd = timeToMinutes(eventTimes.TimeOUT_out);
      if (timeOutMin !== null && timeOutStart !== null && timeOutEnd !== null) {
        if (timeOutMin >= timeOutStart && timeOutMin <= timeOutEnd) outFmt = 'green'; else outFmt = 'red';
      }
    } catch (_) { /* leave status blank on parse issues */ }

    const timeInDate = parseTimeToDate(timeIn);
    const timeOutDate = parseTimeToDate(timeOut);
    data.push([num, id, name, course, timeInDate || '', timeOutDate || '']);
    rowFlags.push({ inFmt, outFmt });
  });

  const ws = XLSX.utils.aoa_to_sheet(data);
  // Apply time formats and colors directly to TIME IN (E) and TIME OUT (F) cells
  const firstDataRow = 6; // header at row 5, data starts at row 6
  for (let i = 0; i < rowFlags.length; i++) {
    const r = firstDataRow + i;
    const inCellAddr = 'E' + r;
    const outCellAddr = 'F' + r;
    const inCell = ws[inCellAddr];
    const outCell = ws[outCellAddr];
    if (inCell && inCell.v !== '' && inCell.v != null) {
      inCell.z = rowFlags[i].inFmt === 'green' ? '[Green]h:mm AM/PM' : rowFlags[i].inFmt === 'red' ? '[Red]h:mm AM/PM' : 'h:mm AM/PM';
    }
    if (outCell && outCell.v !== '' && outCell.v != null) {
      outCell.z = rowFlags[i].outFmt === 'green' ? '[Green]h:mm AM/PM' : rowFlags[i].outFmt === 'red' ? '[Red]h:mm AM/PM' : 'h:mm AM/PM';
    }
  }
  // Optional: set basic column widths for readability
  ws['!cols'] = [
    { wch: 4 },   // #
    { wch: 14 },  // ID NUMBER
    { wch: 28 },  // STUDENT NAME
    { wch: 12 },  // COURSE
    { wch: 10 },  // TIME IN
    { wch: 10 },  // TIME OUT
  ];
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Attendance');
  XLSX.writeFile(wb, 'attendance.xlsx');
}

// Export button event
document.getElementById('confirmExport').addEventListener('click', () => {
  const format = document.querySelector('input[name="format"]:checked').value;
  if (format === 'pdf') {
    exportPDF();
  } else if (format === 'docx') {
    exportDOCX();
  } else if (format === 'xlsx') {
    exportXLSX();
  }
  const modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
  modal.hide();
});

    </script>
</body>
</html>
