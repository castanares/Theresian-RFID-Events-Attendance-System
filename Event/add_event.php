<?php require_once __DIR__ . '/../auth.php'; require_login('../'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CREATE EVENT</title>
    <link rel="stylesheet" href="../styles/dash_styles.css">
    <link rel="stylesheet" href="../styles/side_styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<script>
// Keep user department info global so submit handler can access it
let myDeptList = [];
let myDepartmentsIdCSV = '';

// Fetch semesters and departments and populate the form
async function loadFormData(){
  try{
    // load semesters
    const semRes = await fetch('../api/semesters.php');
    const semJson = await semRes.json();
    const semSel = document.getElementById('semester');
    let selectedSemester = null;
    if (semJson && semJson.success && Array.isArray(semJson.data)){
      // semJson.data expected to be array of {id,name,start_date?,end_date?}
      semSel.innerHTML = '<option value="">Select Semester</option>';
      const now = new Date();
      const curMM = String(now.getMonth() + 1).padStart(2, '0');
      let autoValue = null;
      semJson.data.forEach(s => {
        const opt = document.createElement('option');
        // prefer id if present, else use name
        opt.value = s.id ? String(s.id) : (s.name || '');
        opt.textContent = s.name || (s.id ? String(s.id) : 'Unnamed');
        // attach month info if provided (API returns start_date/end_date as month-only)
        const start = s.start_date ?? s.start ?? s.startDate ?? null;
        const end = s.end_date ?? s.end ?? s.endDate ?? null;
        if (start) opt.dataset.start = String(start).padStart(2,'0');
        if (end) opt.dataset.end = String(end).padStart(2,'0');
        // compute auto-select: exact start month OR range include (handles wrap-around)
        if (autoValue === null && start) {
          const sM = String(start).padStart(2,'0');
          if (sM === curMM) { autoValue = opt.value; selectedSemester = s; }
          else if (end) {
            const eM = String(end).padStart(2,'0');
            const sN = parseInt(sM,10), eN = parseInt(eM,10), mN = parseInt(curMM,10);
            if (sN <= eN) {
              if (mN >= sN && mN <= eN) { autoValue = opt.value; selectedSemester = s; }
            } else {
              // wrap around year end (e.g., start=10 end=03)
              if (mN >= sN || mN <= eN) { autoValue = opt.value; selectedSemester = s; }
            }
          }
        }
        semSel.appendChild(opt);
      });
      if (autoValue !== null) semSel.value = autoValue;
      document.getElementById('hiddenSemester').value = autoValue;
    } else if (semJson && semJson.success && semJson.data && typeof semJson.data === 'object'){
      // single object response
      const s = semJson.data;
      selectedSemester = s;
      semSel.innerHTML = '<option value="">Select Semester</option>';
      const opt = document.createElement('option'); opt.value = s.id ? String(s.id) : (s.name || ''); opt.textContent = s.name || (s.id ? String(s.id) : 'Unnamed');
      const now = new Date(); const curMM = String(now.getMonth() + 1).padStart(2, '0');
      const start = s.start_date ?? s.start ?? s.startDate ?? null; const end = s.end_date ?? s.end ?? s.endDate ?? null;
      if (start) opt.dataset.start = String(start).padStart(2,'0'); if (end) opt.dataset.end = String(end).padStart(2,'0');
      // auto select if matches
      if (start) {
        const sM = String(start).padStart(2,'0');
        if (sM === curMM) opt.selected = true;
        else if (end) {
          const sN = parseInt(sM,10), eN = parseInt(String(end).padStart(2,'0'),10), mN = parseInt(curMM,10);
          if (sN <= eN ? (mN >= sN && mN <= eN) : (mN >= sN || mN <= eN)) opt.selected = true;
        }
      }
      semSel.appendChild(opt);
      if (opt.selected) document.getElementById('hiddenSemester').value = opt.value;
    }

    // Set date constraints based on selected semester
    const dateInput = document.getElementById('eventDate');
    if (dateInput) {
      // Set min to today
      const today = new Date();
      const yyyy = today.getFullYear();
      const mm = String(today.getMonth() + 1).padStart(2, '0');
      const dd = String(today.getDate()).padStart(2, '0');
      dateInput.min = yyyy + '-' + mm + '-' + dd;
      
      // Set max based on semester end
      if (selectedSemester) {
        const endMonth = selectedSemester.end_date ?? selectedSemester.end ?? selectedSemester.endDate ?? null;
        if (endMonth) {
          const lastDay = new Date(yyyy, parseInt(endMonth, 10), 0).getDate();
          dateInput.max = `${yyyy}-${String(endMonth).padStart(2,'0')}-${String(lastDay).padStart(2,'0')}`;
        }
      }
    }

  // first, get current user's department(s) so we can exclude them and include departments_id in payload
  myDeptList = [];
  myDepartmentsIdCSV = '';
    try {
      const meRes = await fetch('../api/me.php', {credentials:'same-origin'});
      const meJson = await meRes.json();
      if (meJson) {
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
    } else {
      container.innerHTML = '<div class="col-12 text-muted">No departments available</div>';
    }
  }catch(err){
    console.error('Failed to load form data', err);
    const container = document.getElementById('departmentsContainer');
    container.innerHTML = '<div class="col-12 text-muted">Failed to load departments</div>';
  }
}

document.addEventListener('DOMContentLoaded', loadFormData);
</script>
<style>
  .department-check { transition: background 0.2s, box-shadow 0.2s; }
  .department-check:hover { background: #ffe5e5; box-shadow: 0 2px 8px rgba(128,0,0,0.08); }
  .dept-margin { margin-left: 10rem; }
  .dept-margin-right { margin-right: 10rem; }
  .maroon-text { color: maroon; }
  .bg-maroon { background-color: maroon; }
  .form-check-input:checked { background-color: maroon; }
  .create{ border-left: 3px solid maroon !important; }
  .create-submit {
    background-color: maroon !important;
    color: white !important;
    border: 2px solid maroon !important;
    transition: background-color 0.2s, color 0.2s, opacity 0.2s;
    min-width: 120px;
  }
  .create-submit:hover {
    background-color: #6d0000 !important;
    color: white !important;
    border-color: #6d0000 !important;
  }
  .create-submit:disabled {
    opacity: 0.65;
    cursor: not-allowed;
  }

  /* Enhanced toast animations */
  @keyframes slideInRight {
    from { transform: translateX(110%); opacity: 0; }
    to   { transform: translateX(0);   opacity: 1; }
  }
  .toast-enhanced {
    min-width: 320px;
    border-radius: 12px !important;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18) !important;
    animation: slideInRight 0.35s cubic-bezier(.4,0,.2,1) both;
    border: none !important;
    overflow: hidden;
  }
  .toast-enhanced .toast-icon {
    font-size: 1.6rem;
    line-height: 1;
  }
  .toast-enhanced .toast-title {
    font-weight: 700;
    font-size: 1rem;
    letter-spacing: 0.01em;
  }
  .toast-enhanced .toast-msg {
    font-size: 0.875rem;
    opacity: 0.92;
  }
  .toast-enhanced .btn-close { filter: brightness(0) invert(1); }
</style>
<body>
<?php include 'sidebar.php'; ?>
<?php $page_heading = 'CREATE EVENT'; include 'navbar.php'; ?>
<section style="display: flex; justify-content: center; align-items: center; min-height: 60vh;">
  <div class="container create" style="max-width: 800px; background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.10); padding: 2.5pc 2pc; margin-top:2pc;">
  <form id="createEventForm" action="" method="post" autocomplete="off">
      <div style="display: flex; flex-direction: column; gap: 1.5pc;">
        <div>
          <label for="eventName" class="event-name fw-bold">Event Name</label>
          <input type="text" name="name" id="eventName" class="event-name-fill rounded form-control mt-1" placeholder="Enter event name" required>
        </div>
        <div style="display: flex; gap: 1pc;">
          <div style="flex:1;">
            <label for="eventDate" class="event-date fw-bold">Event Date</label>
            <input type="date" name="date" id="eventDate" class="event-date-fill rounded form-control mt-1" required>
          </div>
          <div style="flex:1;">
            <label for="semester" class="max-attendance fw-bold">Semester</label>
            <select name="semester" id="semester" class="max-attendance-fill rounded form-control mt-1" required disabled>
              <option value="">Select Semester</option>
            </select>
            <input type="hidden" name="semester" id="hiddenSemester">
          </div>
        </div>
        <h5 class="mb-1 fw-bold text-center ">Include Other Departments(Optional)</h5>
        <div class="row" id="departmentsContainer">
          <!-- departments will be loaded here dynamically -->
          <div class="col-12 text-muted small">Loading departments...</div>
        </div>
        <div>
          <label class="fw-bold event-time text-decoration-underline link-underline-danger d-block text-center" style="margin-bottom: 0.5pc;">Event Time</label>
          <div style="display: flex; gap: 1pc; margin-top: 0.5pc;">
            <div style="flex:1;">
              <label for="timeInStart" class="fw-bold event-time-in">Time In Start</label>
              <input type="time" name="time-in-start" id="timeInStart" class="time-in-start rounded form-control mt-1 text-center" required>
            </div>
            <div style="flex:1;">
              <label for="timeInEnd" class="fw-bold event-time-in">Time In End</label>
              <input type="time" name="time-in-end" id="timeInEnd" class="time-in-end rounded form-control mt-1 text-center" required>
            </div>
          </div>
          <div style="display: flex; gap: 1pc; margin-top: 0.5pc;">
            <div style="flex:1;">
              <label for="timeOutStart" class="fw-bold event-time-out">Time Out Start</label>
              <input type="time" name="time-out-start" id="timeOutStart" class="time-out-start rounded form-control mt-1 text-center" required>
            </div>
            <div style="flex:1;">
              <label for="timeOutEnd" class="fw-bold event-time-out">Time Out End</label>
              <input type="time" name="time-out-end" id="timeOutEnd" class="time-out-end rounded form-control mt-1 text-center" required>
            </div>
          </div>
        </div>
      </div>
      <div class="text-center mt-4">
        <button id="createBtn" type="submit" class="create-submit btn text-light submit fw-bold fs-5 bg-maroon">Create</button>
      </div>
    </form>
    <div aria-live="polite" aria-atomic="true">
      <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1090;"></div>
    </div>
  </div>
</section>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
  <script>
    function showToast(message, type='primary', timeout=4000){
      const id = 't' + Math.random().toString(36).slice(2,8);
      // Icon and accent colour per type
      const config = {
        success: { icon: '✔', accent: '#198754', bg: 'linear-gradient(135deg,#1a7a4a 0%,#198754 100%)', title: 'Success' },
        danger:  { icon: '✖', accent: '#dc3545', bg: 'linear-gradient(135deg,#b02a37 0%,#dc3545 100%)', title: 'Error' },
        warning: { icon: '⚠', accent: '#ffc107', bg: 'linear-gradient(135deg,#cc9a06 0%,#ffc107 100%)', title: 'Warning' },
        primary: { icon: 'ℹ', accent: '#0d6efd', bg: 'linear-gradient(135deg,#0a58ca 0%,#0d6efd 100%)', title: 'Info' },
      };
      const c = config[type] || config.primary;
      const html = `
        <div id="${id}" class="toast toast-enhanced" role="alert" aria-live="assertive" aria-atomic="true"
             style="background:${c.bg}; color:#fff;">
          <div class="d-flex align-items-start gap-3 p-3">
            <span class="toast-icon">${c.icon}</span>
            <div class="flex-grow-1">
              <div class="toast-title">${c.title}</div>
              <div class="toast-msg">${message}</div>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div style="height:3px;background:rgba(255,255,255,0.35);border-radius:0 0 12px 12px;"
               id="${id}_bar"></div>
        </div>`;
      const container = document.getElementById('toastContainer');
      container.insertAdjacentHTML('beforeend', html);
      const el = document.getElementById(id);
      const t = new bootstrap.Toast(el, { delay: timeout });
      t.show();
      // Shrink progress bar over the delay duration
      const bar = document.getElementById(id + '_bar');
      if (bar) {
        bar.style.transition = `width ${timeout}ms linear`;
        bar.style.width = '100%';
        requestAnimationFrame(() => requestAnimationFrame(() => { bar.style.width = '0%'; }));
      }
      el.addEventListener('hidden.bs.toast', () => el.remove());
    }

    document.getElementById('createEventForm').addEventListener('submit', async function(e){
      e.preventDefault();
      const btn = document.getElementById('createBtn'); btn.disabled = true; btn.textContent = 'Creating...';
      try{
        const name = document.getElementById('eventName').value.trim();
        const date = document.getElementById('eventDate').value;
        const semester = document.getElementById('semester').value;
        const timeInStart = document.getElementById('timeInStart').value;
        const timeInEnd = document.getElementById('timeInEnd').value;
        const timeOutStart = document.getElementById('timeOutStart').value;
        const timeOutEnd = document.getElementById('timeOutEnd').value;
        const deptNodes = Array.from(document.querySelectorAll('input[name="departments[]"]:checked'));
  const departments = deptNodes.map(n => n.value);
  // use departments_id from session (me.php) as legacy CSV of user's department(s)
  // build numeric CSV only for selected departments as well to include additional departments selected
  const numericDeptIds = departments.filter(d => /^\d+$/.test(String(d))).map(d => String(d));
  // combine session's departments_id and any additionally-selected numeric ids (avoid duplicates)
  const combinedIds = Array.from(new Set([...(myDepartmentsIdCSV? myDepartmentsIdCSV.split(',').map(s=>s.trim()).filter(Boolean):[]), ...numericDeptIds]));
  const departments_id_csv = combinedIds.join(',');

        const payload = {
          event_name: name,
          date: date,
          semester: semester,
          TimeIN_in: timeInStart,
          TimeIN_out: timeInEnd,
          TimeOUT_in: timeOutStart,
          TimeOUT_out: timeOutEnd,
          departments: departments,
          departments_id: departments_id_csv
        };

        const res = await fetch('../api/events.php', {
          method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload), credentials: 'same-origin'
        });
        const j = await res.json();
        if (res.ok && j && j.success){
          showToast('Event <strong>' + (j.event_name || 'New Event') + '</strong> created successfully! Redirecting…', 'success', 2000);
          setTimeout(()=> window.location.href = 'index.php', 1800);
        } else {
          const msg = (j && j.error) ? j.error : 'Failed to create event';
          showToast(msg, 'danger');
        }
      }catch(err){ console.error(err); showToast('Network or server error', 'danger'); }
      finally{ btn.disabled = false; btn.textContent = 'Create'; }
    });
  </script>
</body>
</html>
