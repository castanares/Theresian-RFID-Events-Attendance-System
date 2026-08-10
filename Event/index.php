<?php 
require_once __DIR__ . '/../auth.php'; 
require_login('../'); 

$currentMonth = date('n');
$currentYear = date('Y');
$currentSchoolYear = ($currentMonth >= 8) ? $currentYear . '-' . ($currentYear + 1) : ($currentYear - 1) . '-' . $currentYear;
$currentSemester = 'Loading...';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVENT Attendance</title>
    <link rel="stylesheet" href="../styles/dash_styles.css">
    <link rel="stylesheet" href="../styles/side_styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001" />
</head>
<style>
  .btn-maroon { background-color: white; color: maroon; }
  .btn-maroon:hover { background-color: maroon; color: white; }
  .table-maroon th { background-color: maroon !important; color: white !important; }
  .department-check { transition: background 0.2s, box-shadow 0.2s; }
  .department-check:hover { background: #ffe5e5; box-shadow: 0 2px 8px rgba(128,0,0,0.08); }
  .dept-margin { margin-left: 10rem; }
  .dept-margin-right { margin-right: 10rem; }
  .maroon-text { color: maroon; }
  .form-check-input:checked { background-color: maroon; }
  .events-table-hidden { opacity: 0; transition: opacity 0.3s ease; }
  .events-table-visible { opacity: 1; }
  .no-events-card {
    border: 2px dashed #d9b0b0;
    border-radius: 16px;
    padding: 3rem 2rem;
    background: linear-gradient(135deg, #fff5f5 0%, #fff 100%);
    text-align: center;
    animation: fadeInUp 0.4s ease;
  }
  .no-events-card .no-events-icon {
    font-size: 4rem;
    color: #c0c0c0;
    margin-bottom: 1rem;
  }
  .no-events-card h5 {
    color: maroon;
    font-weight: 700;
    margin-bottom: 0.5rem;
  }
  .no-events-card p {
    color: #888;
    font-size: 0.95rem;
    margin-bottom: 0;
  }
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
  }
</style>
<body>
<?php include 'sidebar.php'; ?>
<?php $page_heading = 'Dashboard'; include 'navbar.php'; ?>
  <h1 class="text-center text-decoration-underline link-underline-danger">RECENT EVENTS</h1>
  <br>



  <section class="d-md-flex justify-content-md-end align-items-end year-sem">
    <div class="me-3">
      <h6 class="h6-year mb-1">School Year</h6>
      <div class="btn-group border border-dark">
        <button class="btn btn-light dropdown-toggle " type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar me-1" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
          <?php echo $currentSchoolYear; ?>
        </button>
        <ul class="dropdown-menu" id="yearDropdown">
          <!-- dynamically populated by loadYearFilter() -->
        </ul>
      </div>
    </div>
    <div class="me-3">
      <h6 class="h6-semester mb-1">Semester</h6>
      <div class="btn-group border border-dark">
        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book me-1" viewBox="0 0 16 16"><path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/></svg>
          <?php echo $currentSemester; ?>
        </button>
        <ul class="dropdown-menu" id="semesterDropdown">
          <li><a class="dropdown-item active" href="#">First</a></li>
          <li><a class="dropdown-item" href="#">Second</a></li>
          <li><a class="dropdown-item" href="#">Summer</a></li>
        </ul>
      </div>
    </div>
    <button class="btn btn-maroon border-dark filter" type="button">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel me-1" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/></svg>
      Filter
    </button>
  </section>


  <section class="event-table mt-3">
    <table class="table table-hover align-middle table-bordered border border-dark events-table-hidden">
      <thead class="table-maroon">
        <tr>
          <th scope="col" class="text-center">#</th>
          <th scope="col" class="">EVENTS</th>
          <th scope="col" class="text-center">DATE</th>
          <th scope="col" class="text-center">STATUS</th>
          <th scope="col" class="text-center">PARTICIPANTS</th>
          <th scope="col" class="text-center">ACTIONS</th>
        </tr>
      </thead>
      <tbody id="eventsTbody">

        <tr><td class="text-center" colspan="4">Loading events...</td></tr>
      </tbody>
      
    </table>
    
  </section>
  <!-- Empty state: no events exist for this account/department -->
  <div id="noEventsAlert" style="display:none; margin: 0 5pc 1rem 10pc;">
    <div class="no-events-card">
      <div class="no-events-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#d9b0b0" class="bi bi-calendar-x" viewBox="0 0 16 16">
          <path d="M6.146 7.146a.5.5 0 0 1 .708 0L8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 0 1 0-.708"/>
          <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
        </svg>
      </div>
      <h5>No Events Yet</h5>
      <p>There are no events available for your account or department at the moment.<br>Events you create or are included in will appear here.</p>
    </div>
  </div>
  <!-- Empty state: filter returned no results -->
  <div id="noFilterResultsAlert" style="display:none; margin: 0 5pc 1rem 10pc;">
    <div class="no-events-card">
      <div class="no-events-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#d9b0b0" class="bi bi-funnel" viewBox="0 0 16 16">
          <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/>
        </svg>
      </div>
      <h5>No Events Found</h5>
      <p>No events match the selected school year and semester.<br>Try adjusting your filter criteria.</p>
    </div>
  </div>

  <script>
    let myDeptList = [];
    let myDepartmentsIdCSV = '';
    let myDeptIds = [];
    let myDeptNames = [];
    let selectedYear = '';
    let selectedSemester = '';
    let _archiveEventId = null;
    function formatSemester(sem) {
      if (sem === 'First') return '1st Semester';
      if (sem === 'Second') return '2nd Semester';
      if (sem === 'Summer') return 'Summer';
      return sem;
    }
    function getRawSemester(formatted) {
      if (formatted === '1st Semester') return 'First';
      if (formatted === '2nd Semester') return 'Second';
      if (formatted === 'Summer') return 'Summer';
      return formatted;
    }
    async function loadAndRenderEvents(){
      const tbody = document.getElementById('eventsTbody');
      try{
        // fetch semester map
        const semRes = await fetch('../api/semesters.php', {credentials:'same-origin'});
        const semJson = await semRes.json();
        const semesterMap = {};
        if (semJson.success && Array.isArray(semJson.data)) {
          semJson.data.forEach(s => {
            const raw = getRawSemester(s.name || s.semester);
            semesterMap[s.id] = raw;
          });
        }

        // fetch current user info to determine their department ids
        const meRes = await fetch('../api/me.php', {credentials:'same-origin'});
        const meJson = await meRes.json().catch(()=>null);
        let myUserId = null;
        let myDeptList = [];
        if (meJson) {
          myUserId = meJson.user_id ?? null;
          if (meJson.departments_id !== undefined && meJson.departments_id !== null && String(meJson.departments_id).trim() !== '') {
            myDeptList = String(meJson.departments_id).split(',').map(s=>s.trim()).filter(Boolean);
          } else if (meJson.department_id !== undefined && meJson.department_id !== null) {
            if (Array.isArray(meJson.department_id)) myDeptList = meJson.department_id.map(String);
            else myDeptList = [String(meJson.department_id)];
          } else if (meJson.department !== undefined && meJson.department !== null) {
            myDeptList = String(meJson.department).split(',').map(s=>s.trim()).filter(Boolean);
          }
        }
        myDepartmentsIdCSV = myDeptList.join(',');
        myDeptIds = [];
        myDeptNames = [];
        if (meJson.departments_id) {
          myDeptIds = String(meJson.departments_id).split(',').map(s=>s.trim()).filter(Boolean);
        }
        if (meJson.department_info) {
          const infos = Array.isArray(meJson.department_info) ? meJson.department_info : [meJson.department_info];
          infos.forEach(info => {
            if (info.id) myDeptIds.push(String(info.id));
            if (info.name) myDeptNames.push(info.name.toLowerCase());
            if (info.acronym) myDeptNames.push(info.acronym.toLowerCase());
          });
        }
        myDeptIds = [...new Set(myDeptIds)];
        myDeptNames = [...new Set(myDeptNames)];

        // fetch all events and filter: show events owned by user OR events where primary departments_id or other_departments include the user's department
        const res = await fetch('../api/events.php', {credentials:'same-origin'});
        let j = null;
        try { j = await res.json(); } catch(e) {
          const text = await res.text().catch(()=>null);
          tbody.innerHTML = '<tr><td class="text-center" colspan="4">Server error: ' + (res.status || '') + ' ' + (text ? text.replace(/</g,'&lt;') : 'No response body') + '</td></tr>';
          return;
        }
        if (!j || !j.success || !Array.isArray(j.data)) { tbody.innerHTML = '<tr><td class="text-center" colspan="4">No events found</td></tr>'; return; }

        // build a set of department ids (strings) for quick lookup
        const myDeptSet = new Set(myDeptList.map(String));

        // filter events
        const filtered = j.data.filter(ev => {
          // show if owner
          if (myUserId !== null && String(ev.user_id) === String(myUserId)) return true;
          // primary departments_id may be CSV
          if (ev.departments_id) {
            const prim = String(ev.departments_id).split(',').map(s=>s.trim()).filter(Boolean);
            for (const p of prim) if (myDeptSet.has(p)) return true;
          }
          // other_departments may be array of ints
          if (Array.isArray(ev.other_departments) && ev.other_departments.length) {
            for (const d of ev.other_departments) if (myDeptSet.has(String(d))) return true;
          }
          return false;
        }).filter(ev => ev.status !== 'archived');

        const noEventsAlert = document.getElementById('noEventsAlert');
        if (!filtered.length) {
          // show banner and clear the table body
          if (noEventsAlert) noEventsAlert.style.display = '';
          tbody.innerHTML = '';
          return;
        } else {
          if (noEventsAlert) noEventsAlert.style.display = 'none';
        }

        // sort filtered by date (newest first); tiebreak by start time if available
        filtered.sort((a, b) => {
          const da = a.date ? new Date(a.date) : new Date(0);
          const db = b.date ? new Date(b.date) : new Date(0);
          if (db - da !== 0) return db - da; // newest first
          // tiebreaker: compare TimeIN fields if present
          const ta = (a.TimeIN_in || a.TimeIn || a.TimeIn_start || a.TimeIN_start || '').toString();
          const tb = (b.TimeIN_in || b.TimeIn || b.TimeIn_start || b.TimeIN_start || '').toString();
          if (ta && tb) {
            const pa = Date.parse((a.date || '') + ' ' + ta) || 0;
            const pb = Date.parse((b.date || '') + ' ' + tb) || 0;
            return pb - pa;
          }
          return 0;
        });

        // render filtered
        tbody.innerHTML = '';
        filtered.forEach((ev, idx) => {
          const tr = document.createElement('tr');
          const eventDate = ev.date ? new Date(ev.date) : null;
          if (eventDate) {
            const month = eventDate.getMonth() + 1;
            const year = eventDate.getFullYear();
            const schoolYear = month >= 8 ? `${year}-${year+1}` : `${year-1}-${year}`;
            tr.setAttribute('data-year', schoolYear);
          }
          tr.setAttribute('data-semester', semesterMap[ev.semester_id] || ev.semester || '');
          const idCell = document.createElement('th'); idCell.scope='row'; idCell.className='text-center'; idCell.textContent = idx+1;
          const nameCell = document.createElement('td'); nameCell.className='fw-bold';
          const link = document.createElement('a'); link.className='text-decoration-none text-dark'; link.href = 'ongoing.php?id=' + encodeURIComponent(ev.id || ev[Object.keys(ev)[0]] || '');
          link.textContent = ev.event_name || ev.name || ev.title || 'Untitled';
          nameCell.appendChild(link);
          const dateCell = document.createElement('td'); dateCell.className='text-center'; dateCell.textContent = ev.date ? new Date(ev.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '';
          // compute status client-side
          const statusCell = document.createElement('td'); statusCell.className = 'text-center';
          (function computeStatus(){
            try{
              const now = new Date();
              const eventDate = ev.date ? new Date(ev.date) : null;
              if (!eventDate) { statusCell.textContent = 'Unknown'; return; }
              const startTime = ev.TimeIN_in || ev.TimeIn || ev.TimeIn_start || ev.TimeIN_start || ev.TimeIN || '';
              const endTime = ev.TimeOUT_out || ev.TimeOut || ev.Timeout_out || ev.Timeout || ev.TimeOUT || '';
              // build Date objects for start/end if times available
              let startDT = null, endDT = null;
              if (startTime) startDT = new Date(eventDate.toDateString() + ' ' + startTime);
              if (endTime) endDT = new Date(eventDate.toDateString() + ' ' + endTime);
              let s = 'Scheduled';
              if (eventDate < new Date(now.toDateString())) s = 'Completed';
              else if (eventDate.toDateString() === now.toDateString() && startDT && endDT) {
                if (now < startDT) s = 'Scheduled';
                else if (now >= startDT && now <= endDT) s = 'Active';
                else s = 'Completed';
              }
              // map status to Bootstrap badge color classes
              let cls = 'badge bg-secondary';
              if (s === 'Active') cls = 'badge bg-success';
              else if (s === 'Scheduled') cls = 'badge bg-warning text-dark';
              else if (s === 'Completed') cls = 'badge bg-primary';
              statusCell.innerHTML = `<span class="${cls}">${s}</span>`;
            }catch(e){ statusCell.textContent = 'Unknown'; }
          })();

          const participantsCell = document.createElement('td'); participantsCell.className='text-center'; participantsCell.textContent = '...';
          const actionCell = document.createElement('td'); actionCell.className='text-center';
          const edit = document.createElement('button'); edit.className='btn btn-sm btn-outline-primary'; edit.onclick = async () => {
            const eventId = ev.id;
            
            // Check if event is completed before allowing edit
            try {
              const now = new Date();
              const eventDate = ev.date ? new Date(ev.date) : null;
              if (eventDate) {
                const startTime = ev.TimeIN_in || ev.TimeIn || ev.TimeIn_start || ev.TimeIN_start || ev.TimeIN || '';
                const endTime = ev.TimeOUT_out || ev.TimeOut || ev.Timeout_out || ev.Timeout || ev.TimeOUT || '';
                
                let isCompleted = false;
                
                // Event is completed if date is in the past
                if (eventDate < new Date(now.toDateString())) {
                  isCompleted = true;
                } 
                // Or if it's today but past the end time
                else if (eventDate.toDateString() === now.toDateString() && endTime) {
                  const endDT = new Date(eventDate.toDateString() + ' ' + endTime);
                  if (now > endDT) {
                    isCompleted = true;
                  }
                }
                
                if (isCompleted) {
                  // Open in view-only mode instead of blocking
                  viewOnlyReason = 'This event has completed and cannot be edited.';
                  openEditModal(eventId, ev, true);
                  return;
                }
              }
            } catch (err) {
              console.error('Error checking event status:', err);
            }
            
            try {
              const eventRes = await fetch(`../api/events.php?id=${eventId}`, {credentials:'same-origin'});
              const eventJson = await eventRes.json();
              if (eventJson.success && eventJson.data) {
                const event = eventJson.data;
                if (myUserId !== null && String(event.user_id) !== String(myUserId)) {
                  // Open in view-only mode instead of blocking
                  viewOnlyReason = 'You are not the creator of this event.';
                  openEditModal(eventId, ev, true, event);
                  return;
                }
                document.querySelector('[name="event_id"]').value = event.id;
                document.querySelector('[name="event_name"]').value = event.event_name || '';
                document.querySelector('[name="date"]').value = event.date || '';
                document.querySelector('[name="semester"]').value = event.semester || '';
                document.querySelector('[name="TimeIN_in"]').value = event.TimeIN_in || '';
                document.querySelector('[name="TimeIN_out"]').value = event.TimeIN_out || '';
                document.querySelector('[name="TimeOUT_in"]').value = event.TimeOUT_in || '';
                document.querySelector('[name="TimeOUT_out"]').value = event.TimeOUT_out || '';
                // reset and pre-select departments
                document.querySelectorAll('#departmentsContainer input[type="checkbox"]').forEach(cb => cb.checked = false);
                if (event.other_departments) {
                  const otherDepts = (Array.isArray(event.other_departments) ? event.other_departments : String(event.other_departments).split(',').map(s=>s.trim())).map(String);
                  document.querySelectorAll('#departmentsContainer input[type="checkbox"]').forEach(cb => {
                    if (otherDepts.includes(cb.value)) cb.checked = true;
                  });
                }
                // show modal
              openEditModal(eventId, ev, false, event);
              }
            } catch (err) {
              console.error('Failed to load event', err);
            }
          }; edit.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>';
          const archive = document.createElement('button'); archive.className='btn btn-sm btn-outline-danger ms-1'; archive.style.color = 'red'; archive.onclick = () => {
            if (myUserId !== null && String(ev.user_id) !== String(myUserId)) {
              showToast('You are not the creator of this event', 'warning');
              return;
            }
            document.getElementById('archive_event_name').textContent = ev.event_name || ev.name || '';
            _archiveEventId = ev.id;
            const modal = new bootstrap.Modal(document.getElementById('archiveEventModal'));
            modal.show();
          }; archive.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg>';
          actionCell.appendChild(edit);
          actionCell.appendChild(archive);
          tr.appendChild(idCell); tr.appendChild(nameCell); tr.appendChild(dateCell); tr.appendChild(statusCell); tr.appendChild(participantsCell); tr.appendChild(actionCell);
          // asynchronously fetch participant count
          (async () => {
            try {
              const res = await fetch(`../api/attendance.php?event_id=${encodeURIComponent(ev.id)}`, {credentials:'same-origin'});
              const j = await res.json();
              if (j && j.success && Array.isArray(j.data)) participantsCell.textContent = j.data.length;
              else if (j && typeof j.count === 'number') participantsCell.textContent = j.count;
              else participantsCell.textContent = '0';
            } catch (err) { participantsCell.textContent = '0'; }
          })();
          tbody.appendChild(tr);
        });
      }catch(err){ console.error(err); document.getElementById('eventsTbody').innerHTML = '<tr><td class="text-center" colspan="4">Failed to load events</td></tr>'; }
    }
    async function loadDepartments() {
      try {
        const depRes = await fetch('../api/departments.php?archived=0');
        const depJson = await depRes.json();
        const container = document.getElementById('departmentsContainer');
        container.innerHTML = '';
        if (depJson && depJson.success && Array.isArray(depJson.data) && depJson.data.length){
          const cols = [document.createElement('div'), document.createElement('div')];
          cols[0].className = 'col-md-6'; cols[1].className = 'col-md-6';
          let shownIndex = 0;
          depJson.data.forEach((d, i) => {
            const id = d.id ?? d.dept_id ?? d.Dept_ID ?? d.ID ?? null;
            const label = d.acronym ?? d.acro ?? d.name ?? d.Dept_name ?? d.name ?? '';
            const display = label || id || 'Dept';
            // skip if user's department
            if (myDeptIds.includes(String(id)) || myDeptNames.includes(display.toLowerCase())) return;
            const wrapper = document.createElement('div'); wrapper.className = 'form-check d-flex align-items-center gap-2 p-2 rounded department-check ' + (shownIndex%2===0 ? 'dept-margin' : 'dept-margin-right');
            const input = document.createElement('input'); input.type = 'checkbox'; input.className = 'form-check-input';
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
        console.error('Failed to load departments', err);
        const container = document.getElementById('departmentsContainer');
        container.innerHTML = '<div class="col-12 text-muted">Failed to load departments</div>';
      }
    }
    async function loadYearFilter() {
      try {
        // Compute current school year (Aug–Jul cycle)
        const now = new Date();
        const nowMonth = now.getMonth() + 1;
        const nowYear = now.getFullYear();
        // If Aug-Dec: SY starts this year; if Jan-Jul: SY started last year
        const currentSYStart = nowMonth >= 8 ? nowYear : nowYear - 1;
        const currentSchoolYear = `${currentSYStart}-${currentSYStart + 1}`;

        // Build the set of years: current + 3 in advance
        const years = new Set();
        for (let i = 0; i <= 3; i++) {
          const s = currentSYStart + i;
          years.add(`${s}-${s + 1}`);
        }

        // Also include any years that come from existing events (e.g. past years)
        const res = await fetch('../api/events.php', {credentials:'same-origin'});
        const j = await res.json();
        if (j.success && Array.isArray(j.data)) {
          j.data.forEach(ev => {
            if (ev.date) {
              const eventDate = new Date(ev.date);
              const m = eventDate.getMonth() + 1;
              const y = eventDate.getFullYear();
              const sy = m >= 8 ? `${y}-${y+1}` : `${y-1}-${y}`;
              years.add(sy);
            }
          });
        }

        // Sort chronologically
        const sortedYears = Array.from(years).sort();

        const ul = document.getElementById('yearDropdown');
        ul.innerHTML = '';
        sortedYears.forEach(y => {
          const li = document.createElement('li');
          const a = document.createElement('a'); a.className='dropdown-item'; a.href='#'; a.textContent=y;
          li.appendChild(a);
          ul.appendChild(li);
        });

        // Determine which year to activate (prefer current SY, fall back to last in list)
        let activeYear = sortedYears.includes(currentSchoolYear)
          ? currentSchoolYear
          : (sortedYears.length ? sortedYears[sortedYears.length - 1] : '');

        if (activeYear) {
          ul.querySelectorAll('.dropdown-item').forEach(item => {
            if (item.textContent === activeYear) item.classList.add('active');
          });
          // Update the button label
          const button = document.querySelector('.h6-year').nextElementSibling.querySelector('.dropdown-toggle');
          const svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar me-1" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>';
          button.innerHTML = svg + ' ' + activeYear;
          selectedYear = activeYear;
        }

        // Add click listeners
        ul.querySelectorAll('.dropdown-item').forEach(item => {
          item.addEventListener('click', function(e) {
            e.preventDefault();
            selectedYear = this.textContent.trim();
            const button = document.querySelector('.h6-year').nextElementSibling.querySelector('.dropdown-toggle');
            const svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar me-1" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>';
            button.innerHTML = svg + ' ' + selectedYear;
            ul.querySelectorAll('.dropdown-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            filterEvents();
          });
        });
      } catch(err) { console.error(err); }
    }
    async function loadSemesterFilter() {
      try {
        const res = await fetch('../api/semesters.php', {credentials:'same-origin'});
        const j = await res.json();
        if (j.success && Array.isArray(j.data)) {
          const ul = document.getElementById('semesterDropdown');
          ul.innerHTML = '';
          const now = new Date();
          const curMM = String(now.getMonth() + 1).padStart(2, '0');
          let autoValue = null;
          j.data.forEach(sem => {
            const li = document.createElement('li');
            const a = document.createElement('a'); a.className='dropdown-item'; a.href='#'; a.textContent=sem.name || sem.semester;
            li.appendChild(a);
            ul.appendChild(li);
            // compute auto-select: exact start month OR range include (handles wrap-around)
            if (autoValue === null && sem.start_date) {
              const sM = String(sem.start_date).padStart(2,'0');
              if (sM === curMM) autoValue = sem.id;
              else if (sem.end_date) {
                const eM = String(sem.end_date).padStart(2,'0');
                const sN = parseInt(sM,10), eN = parseInt(eM,10), mN = parseInt(curMM,10);
                if (sN <= eN) {
                  if (mN >= sN && mN <= eN) autoValue = sem.id;
                } else {
                  // wrap around year end (e.g., start=10 end=03)
                  if (mN >= sN || mN <= eN) autoValue = sem.id;
                }
              }
            }
          });
          let currentSem = '';
          if (autoValue !== null) {
            const currentSemObj = j.data.find(s => s.id == autoValue);
            if (currentSemObj) {
              currentSem = getRawSemester(currentSemObj.name || currentSemObj.semester);
            }
          }
          ul.querySelectorAll('.dropdown-item').forEach(item => {
            if (item.textContent === formatSemester(currentSem)) item.classList.add('active');
          });
          // update button
          const button = document.querySelector('.h6-semester').nextElementSibling.querySelector('.dropdown-toggle');
          const svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book me-1" viewBox="0 0 16 16"><path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/></svg>';
          button.innerHTML = svg + ' ' + formatSemester(currentSem);
          selectedSemester = currentSem;
          // add event listeners
          ul.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
              e.preventDefault();
              selectedSemester = getRawSemester(this.textContent.trim());
              // update button
              const button = document.querySelector('.h6-semester').nextElementSibling.querySelector('.dropdown-toggle');
              const svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book me-1" viewBox="0 0 16 16"><path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/></svg>';
              button.innerHTML = svg + ' ' + this.textContent.trim();
              // update active
              ul.querySelectorAll('.dropdown-item').forEach(i => i.classList.remove('active'));
              this.classList.add('active');
              filterEvents();
            });
          });
        }
      } catch(err) { console.error(err); }
    }
    function filterEvents() {
      const rows = document.querySelectorAll('#eventsTbody tr');
      let visibleCounter = 1;
      let visibleCount = 0;

      rows.forEach(row => {
        const year = row.getAttribute('data-year');
        const semester = row.getAttribute('data-semester');
        const show = (!selectedYear || year === selectedYear) && (!selectedSemester || semester === selectedSemester);
        row.style.display = show ? '' : 'none';

        // Renumber visible rows to start from 1
        if (show) {
          const firstCell = row.querySelector('th');
          if (firstCell) {
            firstCell.textContent = visibleCounter;
            visibleCounter++;
          }
          visibleCount++;
        }
      });

      // Show/hide filter empty-state (only when there ARE events but none match the filter)
      const noFilterAlert = document.getElementById('noFilterResultsAlert');
      const noEventsAlert = document.getElementById('noEventsAlert');
      const hasEvents = rows.length > 0;
      if (noFilterAlert) {
        noFilterAlert.style.display = (hasEvents && visibleCount === 0) ? '' : 'none';
      }
      // Keep the main no-events alert hidden when filter empty-state is visible
      if (noEventsAlert && hasEvents && visibleCount === 0) {
        noEventsAlert.style.display = 'none';
      }
    }
    document.addEventListener('DOMContentLoaded', async () => {
      await loadAndRenderEvents();
      loadDepartments();
      await loadYearFilter();
      await loadSemesterFilter();
      filterEvents();

      // Reveal table after filters have been applied
      const eventsTable = document.querySelector('.event-table table');
      if (eventsTable) {
        eventsTable.classList.remove('events-table-hidden');
        eventsTable.classList.add('events-table-visible');
      }

// document.addEventListener('DOMContentLoaded', async function() {
//   await loadYearFilter();
//   await loadSemesterFilter();
//   loadDepartmentFilter();
//   filterEvents();
  
      
      const confirmArchiveBtn = document.getElementById('confirmArchiveEventBtn');
      if (confirmArchiveBtn) {
        confirmArchiveBtn.addEventListener('click', async function() {
          if (!_archiveEventId) return;
          confirmArchiveBtn.disabled = true;
          confirmArchiveBtn.textContent = 'Archiving...';
          try {
            const res = await fetch('../api/events.php', {
              method: 'PUT',
              headers: {'Content-Type': 'application/json'},
              credentials: 'same-origin',
              body: JSON.stringify({id: _archiveEventId, status: 'archived'})
            });
            const data = await res.json();
            if (data.success) {
              showToast('Event archived successfully', 'success');
              const modal = bootstrap.Modal.getInstance(document.getElementById('archiveEventModal'));
              modal.hide();
              await loadAndRenderEvents();
              filterEvents();
            } else {
              alert('Error archiving event');
            }
          } catch (err) {
            console.error(err);
            alert('Error archiving event');
          } finally {
            confirmArchiveBtn.disabled = false;
            confirmArchiveBtn.textContent = 'Archive';
          }
        });
      }
    });

    function replaceSpaces(input) {
      input.value = input.value.replace(/\s+/g, '_');
    }

    

    async function saveChanges() {
      const form = document.getElementById('editEventForm');
      const formData = new FormData(form);
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
          const modal = bootstrap.Modal.getInstance(document.getElementById('editEventModal'));
          modal.hide();
          // reload
          await loadAndRenderEvents();
          filterEvents();
        } else {
          alert('Failed to update event: ' + (json.error || 'Unknown error'));
        }
      } catch (err) {
        console.error('Submit error', err);
        alert('Error updating event');
      }
    }

    // --- View-only modal helpers ---
    let viewOnlyReason = '';

    function setModalViewOnly(isViewOnly) {
      const form = document.getElementById('editEventForm');
      const saveBtn = document.getElementById('editEventSaveBtn');
      const badge = document.getElementById('editViewOnlyBadge');
      const title = document.getElementById('editEventModalTitle');
      if (!form) return;
      // Toggle all text/date/time inputs
      form.querySelectorAll('input:not([type=hidden]), select, textarea').forEach(el => {
        if (isViewOnly) {
          el.setAttribute('readonly', 'readonly');
          el.setAttribute('disabled', 'disabled');
          el.style.pointerEvents = 'none';
          el.style.backgroundColor = '#f8f9fa';
        } else {
          el.removeAttribute('readonly');
          el.removeAttribute('disabled');
          el.style.pointerEvents = '';
          el.style.backgroundColor = '';
        }
      });
      // Toggle checkboxes
      form.querySelectorAll('input[type=checkbox]').forEach(cb => { cb.disabled = isViewOnly; });
      if (saveBtn) saveBtn.style.display = isViewOnly ? 'none' : '';
      if (badge) badge.style.display = isViewOnly ? '' : 'none';
      if (title) title.textContent = isViewOnly ? 'View Event' : 'Edit Events';
    }

    async function openEditModal(eventId, ev, viewOnly, preloadedEvent) {
      let event = preloadedEvent;
      if (!event) {
        try {
          const eventRes = await fetch(`../api/events.php?id=${eventId}`, {credentials:'same-origin'});
          const eventJson = await eventRes.json();
          if (eventJson.success && eventJson.data) event = eventJson.data;
        } catch(e) { console.error(e); return; }
      }
      if (!event) return;
      document.querySelector('[name="event_id"]').value = event.id;
      document.querySelector('[name="event_name"]').value = event.event_name || '';
      document.querySelector('[name="date"]').value = event.date || '';
      document.querySelector('[name="semester"]').value = event.semester || '';
      document.querySelector('[name="TimeIN_in"]').value = event.TimeIN_in || '';
      document.querySelector('[name="TimeIN_out"]').value = event.TimeIN_out || '';
      document.querySelector('[name="TimeOUT_in"]').value = event.TimeOUT_in || '';
      document.querySelector('[name="TimeOUT_out"]').value = event.TimeOUT_out || '';
      document.querySelectorAll('#departmentsContainer input[type="checkbox"]').forEach(cb => cb.checked = false);
      if (event.other_departments) {
        const otherDepts = (Array.isArray(event.other_departments) ? event.other_departments : String(event.other_departments).split(',').map(s=>s.trim())).map(String);
        document.querySelectorAll('#departmentsContainer input[type="checkbox"]').forEach(cb => {
          if (otherDepts.includes(cb.value)) cb.checked = true;
        });
      }
      setModalViewOnly(viewOnly);
      const modal = new bootstrap.Modal(document.getElementById('editEventModal'));
      modal.show();
    }

    // Reset modal to editable state when it closes
    document.addEventListener('DOMContentLoaded', () => {
      const modalEl = document.getElementById('editEventModal');
      if (modalEl) modalEl.addEventListener('hidden.bs.modal', () => setModalViewOnly(false));
    });

    // small toast helper
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
  </script>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

  <!-- Edit Event Modal -->
  <div class="modal fade" tabindex="-1" id="editEventModal">
    <div class="modal-dialog modal-lg ">
      <div class="modal-content ">
        <div class="modal-header" style="background-color: maroon; color: white;">
          <h5 class="modal-title" id="editEventModalTitle">Edit Events</h5>
          <span id="editViewOnlyBadge" class="badge bg-warning text-dark ms-2" style="display:none;">View Only</span>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editEventForm">
          <input type="hidden" name="event_id" id="editEventId">
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
            <button type="button" id="editEventSaveBtn" class="btn" style="background-color: maroon; color: white;" onclick="saveChanges()">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</body>
</html>
<!-- Archive Event Modal -->
<div class="modal fade" id="archiveEventModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Archive Event</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Archive event <strong id="archive_event_name"></strong>?</p>
        <p class="small text-muted">This will move the event to the archived list.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="confirmArchiveEventBtn" type="button" class="btn btn-danger">Archive</button>
      </div>
    </div>
  </div>
</div>
