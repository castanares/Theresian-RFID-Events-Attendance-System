<?php require_once __DIR__ . '/../auth.php'; require_login('/new/'); ?>
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
</style>
<body>
<?php include 'sidebar.php'; ?>
<?php $page_heading = 'ONGOING EVENTS'; include 'navbar.php'; ?>
  <section>
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
              <button type="submit" class="btn" style="background-color: maroon; color: white;" name="save">Save changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
  <br>
  <section class="d-md-flex justify-content-md-center">
        <form id="scanForm">
      <div class="d-grid gap-2 d-md-flex justify-content-md-end start-end me-5">
        <input type="number" name="rfid" autofocus placeholder="Scan Here" class="rounded" required>
        <button type="submit" class="btn btn-maroon rounded">Submit</button>
      </div>
    </form>
        <form id="idForm">
      <div class="d-grid gap-2 d-md-flex justify-content-md-end start-end me-5">
        <input type="number" name="rfid" placeholder="ID Here (Optional)" class="rounded">
        <button type="submit" class="btn btn-maroon rounded">Submit</button>
      </div>
    </form>


    

  </section>

  <section class="event-table">
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
      if (myUserId !== null && String(event.user_id) !== String(myUserId)) {
        const form = document.getElementById('editEventForm');
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => input.disabled = true);
        const saveBtn = form.querySelector('button[name="save"]');
        if (saveBtn) saveBtn.disabled = true;
        const modalBody = form.querySelector('.modal-body');
        if (modalBody) {
          const note = document.createElement('div');
          note.className = 'alert alert-warning';
          note.textContent = 'You can view but not edit this event.';
          modalBody.insertBefore(note, modalBody.firstChild);
        }
      }
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
    </script>
</body>
</html>
