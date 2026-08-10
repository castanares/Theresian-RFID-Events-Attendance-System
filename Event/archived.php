<?php require_once __DIR__ . '/../auth.php'; require_login('../'); ?>
<?php
// Archived events (UI-only) — uses shared sidebar/navbar
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- <title>Archived Events</title> -->
  <link rel="stylesheet" href="../styles/dash_styles.css">
  <link rel="stylesheet" href="../styles/side_styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001" /> 
 <style>
    /* align with scanlog layout and table styling */
  /* sidebar offset variable so we can adjust spacing reliably */
  :root{ --sidebar-offset: 9rem; }
  .archive-wrap{ max-width:1900px; margin:2.5rem auto; padding-left: calc(var(--sidebar-offset) + 1rem); padding-right: 2rem; box-sizing: border-box; }
    .search-row{ display:flex; gap:0.5rem; align-items:center; }
    .list-card { box-shadow:0 6px 20px rgba(0,0,0,0.06); }
    /* ensure table expands to available width */
    #archiveTable{ width:100%; }
    /* reuse scanlog maroon header style */
    .table-maroon th { background-color: #800000 !important; color: white !important; }

    /* Archive type toggle buttons */
    .archive-toggle {
      display: flex;
      gap: 0;
      border-radius: 6px;
      overflow: hidden;
      margin-bottom: 1rem;
    }
    
    .toggle-btn {
      background: #fff;
      border: 1px solid #800000;
      color: #800000;
      padding: 0.5rem 1rem;
      border-radius: 0;
      transition: all 0.3s ease;
    }
    
    .toggle-btn:first-child {
      border-right: none;
    }
    
    .toggle-btn.active {
      background: #800000;
      color: white;
    }
    
    .toggle-btn:hover:not(.active) {
      background: #f5f5f5;
    }

    /* Progressive responsive adjustments */
    @media screen and (max-width: 1440px) {
      /* reduce side offset on large but not full-HD screens */
      :root{ --sidebar-offset: 7rem; }
      .archive-wrap{ padding-right: 3rem; }
    }

    @media (max-width: 1200px){
      :root{ --sidebar-offset: 4rem; }
      .archive-wrap{ padding-right: 1rem; }
    }

    @media (max-width: 991px){
      /* tablet and mobile: remove left offset and add side padding so content fits under sidebar */
      :root{ --sidebar-offset: 0; }
      .archive-wrap{ padding-left: 1rem; padding-right:1rem; }
      .dept-margin, .dept-margin-right { margin-left: 0 !important; margin-right: 0 !important; }
      .search-row{ width:100%; margin-top:.5rem; }
    }

    @media (max-width: 576px){
      /* Stack filters and search vertically on small screens */
      .archive-wrap{ margin-top:1.25rem; }
      .search-row{ width:100%; }
      .search-row input{ width:100%; }
      .form-select.form-select-sm{ width:100% !important; margin-bottom: .5rem; }
      .table th, .table td { font-size: 0.9rem; }
      /* make action buttons easier to tap */
      #archiveTable tbody td .unarchive-btn{ display:block; width:100%; }
      #filtersWrap { width:100%; display:block; }
    }
 
  

  </style>
</head>
<body>
<?php include __DIR__ . '/sidebar.php'; ?>
<?php $page_heading = 'Archived'; include __DIR__ . '/navbar.php'; ?>
<main class="archive-wrap container">
  <!-- Archive Type Toggle -->
  <div class="archive-toggle">
    <button class="toggle-btn active" data-type="students">Student Archive</button>
    <button class="toggle-btn" data-type="events">Event Archive</button>
  </div>

  <!-- Filters Row -->
  <div class="d-flex justify-content-between align-items-center mb-3" id="filtersRow">
    <!-- Student Filters -->
    <div class="d-flex student-filters" style="gap:.5rem; align-items:center;">
      <select id="filterCause" class="form-select form-select-sm">
        <option value="">All Causes</option>
        <option>Graduated</option>
        <option>Discontinued</option>
        <option>Transferred</option>
        <option>Other</option>
      </select>
    </div>

    <!-- Event Filters -->
    <div class="d-flex event-filters" style="gap:.5rem; align-items:center; display:none;">
      <select id="filterEventYear" class="form-select form-select-sm">
        <option value="">All Years</option>
        <option>2024</option>
        <option>2023</option>
        <option>2022</option>
        <option>2021</option>
      </select>
      <select id="filterEventSemester" class="form-select form-select-sm">
        <option value="">All Semesters</option>
      </select>
    </div>

    <!-- Search -->
    <div class="search-row">
      <input id="searchInput" class="form-control form-control-sm" placeholder="Search archived students...">
      <button id="clearBtn" class="btn btn-sm btn-outline-danger">Clear</button>
    </div>
  </div>

  <div class="list-card card p-3">
    <section class="tableList">
      <div class="table-responsive">
        <!-- Student Archive Table -->
        <table class="table border border-dark table-bordered table-maroon" id="studentTable">
          <thead class="border border-dark table-info">
            <tr>
              <th scope="col">#</th>
              <th scope="col">ID</th>
              <th scope="col">Student</th>
              <th scope="col">Course</th>
              <th scope="col">Cause</th>
              <th scope="col">Year</th>
              <th scope="col" class="text-center">Action</th>
            </tr>
          </thead>
          <tbody id="studentTbody">
            <!-- archived student rows will be loaded dynamically by JS -->
          </tbody>
        </table>

        <!-- Event Archive Table -->
        <table class="table border border-dark table-bordered table-maroon" id="eventTable" style="display:none;">
          <thead class="border border-dark table-info">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Event Name</th>
              <th scope="col">Date</th>
              <th scope="col">Status</th>
              <th scope="col" class="text-center">Action</th>
            </tr>
          </thead>
          <tbody id="eventTbody">
            <!-- archived event rows will be loaded dynamically by JS -->
          </tbody>
        </table>
      </div>
    </section>
  </div>
</main>
<?php
// Server-side: attempt to detect current user's department id (reuse helper used in Add_student.php)
$dept_id = null;
if (session_status() === PHP_SESSION_NONE) session_start();
$dbPath = __DIR__ . '/../api/dbcon.php';
if (file_exists($dbPath)) {
  require_once $dbPath; // $conn
}
if (!empty($_SESSION['user_id']) && isset($conn) && ($conn instanceof mysqli)){
  $findColLocal = function($conn,$table,$cands){
    $res = $conn->query("SHOW COLUMNS FROM `".$conn->real_escape_string($table)."`"); if(!$res) return null;
    while($r=$res->fetch_assoc()){ foreach($cands as $c) if (strcasecmp($r['Field'],$c)===0) return $r['Field']; }
    $res->data_seek(0);
    while($r=$res->fetch_assoc()){ $f=strtolower($r['Field']); foreach($cands as $c) if (strpos($f,strtolower($c))!==false) return $r['Field']; }
    return null;
  };
  $userTable = 'user';
  $deptCol = $findColLocal($conn,$userTable,['department_id','dept_id','departments_id','department','dept','department_name']);
  $availableFields = [];
  $resFields = $conn->query("SHOW COLUMNS FROM `".$conn->real_escape_string($userTable)."`");
  if ($resFields) while($rf = $resFields->fetch_assoc()) $availableFields[] = $rf['Field'];
  $candidateIdCols = ['username','user_name','user','id','user_id','email','email_address'];
  $foundIdCols = [];
  foreach($candidateIdCols as $c){ foreach($availableFields as $af){ if (strcasecmp($af,$c)===0 || strpos(strtolower($af), strtolower($c)) !== false){ $foundIdCols[] = $af; break; } } }
  if ($deptCol && count($foundIdCols)>0){
    $whereParts = array_map(function($c){ return "`$c` = ?"; }, $foundIdCols);
    $sql = "SELECT `$deptCol` FROM `$userTable` WHERE (" . implode(' OR ', $whereParts) . ") LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt){
      $vals = array_fill(0, count($foundIdCols), (string)($_SESSION['user_id'] ?? ''));
      $types = str_repeat('s', count($vals));
      $bindParams = array_merge([$types], $vals);
      $refs = [];
      foreach($bindParams as $k => $v) $refs[$k] = &$bindParams[$k];
      call_user_func_array([$stmt,'bind_param'],$refs);
      $stmt->execute(); $res = $stmt->get_result(); if ($r = $res->fetch_assoc()) $dept_id = $r[$deptCol]; $stmt->close();
    }
  }
}
?>
<script>
// Archive type toggle functionality
const toggleButtons = document.querySelectorAll('.toggle-btn');
const studentTable = document.getElementById('studentTable');
const eventTable = document.getElementById('eventTable');
const studentFilters = document.querySelector('.student-filters');
const eventFilters = document.querySelector('.event-filters');
const searchInput = document.getElementById('searchInput');

let currentArchiveType = 'students';
let studentRows = Array.from(studentTable.querySelectorAll('tbody tr'));
let eventRows = Array.from(eventTable.querySelectorAll('tbody tr'));

// Toggle between student and event archives
toggleButtons.forEach(button => {
  button.addEventListener('click', function() {
    const type = this.dataset.type;
    currentArchiveType = type;
    
    // Update button states
    toggleButtons.forEach(btn => btn.classList.remove('active'));
    this.classList.add('active');
    
    // Show/hide tables and filters
    if (type === 'students') {
      studentTable.style.display = 'table';
      eventTable.style.display = 'none';
      studentFilters.style.display = 'flex';
      eventFilters.style.display = 'none';
      searchInput.placeholder = 'Search archived students...';
    } else if (type === 'events') {
      studentTable.style.display = 'none';
      eventTable.style.display = 'table';
      studentFilters.style.display = 'none';
      eventFilters.style.display = 'flex';
      searchInput.placeholder = 'Search archived events...';
      Promise.all([loadEventYearFilter(), loadEventFilters()]).then(() => {
        applyFilters();
      });
    }
    
    // Clear filters and reapply
    clearAllFilters();
    applyFilters();
  });
});

// Filter elements
const filterCause = document.getElementById('filterCause');
const filterEventYear = document.getElementById('filterEventYear');
const filterEventSemester = document.getElementById('filterEventSemester');

// Apply filters based on current archive type
function applyFilters() {
  const q = searchInput.value.toLowerCase().trim();
  
  if (currentArchiveType === 'students') {
    const cause = filterCause.value;
    
    studentRows.forEach(row => {
      const txt = row.textContent.toLowerCase();
      const rowCause = row.dataset.cause || '';
      
      const matchesText = q === '' || txt.includes(q);
      const matchesCause = cause === '' || rowCause === cause;
      
      row.style.display = (matchesText && matchesCause) ? 'table-row' : 'none';
    });
    // Re-number visible rows
    let visibleCounter = 1;
    studentRows.forEach(row => {
      if (row.style.display === 'table-row') {
        row.cells[0].textContent = visibleCounter;
        visibleCounter++;
      }
    });
  } else if (currentArchiveType === 'events') {
    const year = filterEventYear.value;
    const semester = filterEventSemester.value;
    
    eventRows.forEach(row => {
      const txt = row.textContent.toLowerCase();
      const rowYear = row.dataset.year || '';
      const rowSemester = row.dataset.semester || '';
      
      const matchesText = q === '' || txt.includes(q);
      const matchesYear = year === '' || rowYear === year;
      const matchesSemester = semester === '' || rowSemester === semester;
      
      row.style.display = (matchesText && matchesYear && matchesSemester) ? 'table-row' : 'none';
    });
    // Re-number visible rows
    let visibleCounter = 1;
    eventRows.forEach(row => {
      if (row.style.display === 'table-row') {
        row.cells[0].textContent = visibleCounter;
        visibleCounter++;
      }
    });
  }
}

// Event listeners for filters
searchInput.addEventListener('input', applyFilters);
filterCause.addEventListener('change', applyFilters);
filterEventYear.addEventListener('change', applyFilters);
filterEventSemester.addEventListener('change', applyFilters);

// Clear all filters
function clearAllFilters() {
  searchInput.value = '';
  filterCause.value = '';
  filterEventYear.value = '';
  filterEventSemester.value = '';
}

document.getElementById('clearBtn').addEventListener('click', () => {
  clearAllFilters();
  if (currentArchiveType === 'events') {
    Promise.all([loadEventYearFilter(), loadEventFilters()]).then(() => {
      applyFilters();
    });
  } else {
    applyFilters();
  }
});

// Handle restore actions
document.addEventListener('click', function(e) {
  if (e.target && e.target.classList.contains('restore-btn')) {
    const row = e.target.closest('tr');
    if (!row) return;

    // Events: optimistic restore (UI-only)
    // Optimistic UI: remove row
    row.remove();
    const index = eventRows.indexOf(row);
    if (index > -1) eventRows.splice(index, 1);
    showToast('Event restored (UI-only)', 'success');
  }
});

// small toast helper
function showToast(message, type = 'info') {
  const toast = document.createElement('div');
  toast.className = `toast align-items-center text-bg-${type} border-0 position-fixed`;
  toast.style.top = '1rem';
  toast.style.right = '1rem';
  toast.style.zIndex = '2000';
  toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
  document.body.appendChild(toast);
  const bs = new bootstrap.Toast(toast, { delay: 2500 });
  bs.show();
  toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

// Helper function for school year
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

// Load event filters
function loadEventFilters() {
  const semSel = document.getElementById('filterEventSemester');
  return fetch('../api/semesters.php').then(r=>r.json()).then(j=>{
    if(j.success && Array.isArray(j.data)){
      semSel.innerHTML = '<option value="">All Semesters</option>';
      const currentMonth = new Date().getMonth() + 1; // 1-12
      let currentSemIndex = -1;
      j.data.forEach((s, idx) => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.name || s.semester || 'Unnamed';
        semSel.appendChild(opt);
        // Check if this is the current semester
        const startMonth = new Date(s.start_date).getMonth() + 1;
        const endMonth = new Date(s.end_date).getMonth() + 1;
        if (startMonth <= currentMonth && currentMonth <= endMonth) {
          currentSemIndex = idx + 1; // +1 because first option is "All Semesters"
        }
      });
      // Default to the current semester if found
      if (currentSemIndex > 0 && semSel.options[currentSemIndex]) {
        semSel.options[currentSemIndex].selected = true;
      }
    }
  }).catch(err=>console.error(err));
}

// Load year filter for events
function loadEventYearFilter() {
  fetch('../api/events.php').then(r=>r.json()).then(j=>{
    if(j.success && Array.isArray(j.data)){
      const schoolYears = new Set();
      j.data.filter(ev => ev.status === 'archived').forEach(ev => {
        if (ev.date) {
          const schoolYear = getSchoolYear(ev.date);
          schoolYears.add(schoolYear);
        }
      });
      const sortedYears = Array.from(schoolYears).sort();
      const currentYear = getSchoolYear(new Date().toISOString().split('T')[0]);
      if (!sortedYears.includes(currentYear)) {
        sortedYears.unshift(currentYear); // add current year at the top
      }
      const sel = document.getElementById('filterEventYear');
      sel.innerHTML = '<option value="">All Years</option>';
      sortedYears.forEach(y => {
        const opt = document.createElement('option');
        opt.value = y;
        opt.textContent = y;
        if (y === currentYear) opt.selected = true;
        sel.appendChild(opt);
      });
    }
  }).catch(err=>console.error(err));
}

// Load archived students
(function(){
  const tbody = document.querySelector('#studentTable tbody');
  tbody.innerHTML = '<tr><td colspan="7">Loading...</td></tr>';
  const params = new URLSearchParams();
  params.set('status','archived');
  <?php if (!empty($dept_id)) { echo "params.set('departments_id', '".htmlspecialchars($dept_id,ENT_QUOTES)."');\n"; } ?>
  fetch('../api/students.php?'+params.toString()).then(r=>r.json()).then(j=>{
    if(!j.success){ tbody.innerHTML = '<tr><td colspan="7">Error loading</td></tr>'; return; }
    const rows = j.data || [];
    if(rows.length===0){ tbody.innerHTML = '<tr><td colspan="7">No archived students</td></tr>'; return; }
    tbody.innerHTML='';
    rows.forEach((r, idx) => {
      const idNum = r.ID_Number ?? r.id ?? '';
      const name = r.Student_name ?? r.student_name ?? r.name ?? '';
      const course = r.Course ?? r.course ?? '';
      const cause = r.archive_cause ?? r.status ?? '';
      const year = r.archived_stamp ?? '';
      const tr = document.createElement('tr');
      tr.setAttribute('data-course', course);
      tr.setAttribute('data-cause', cause);
      tr.innerHTML = `<td></td><td>${idNum}</td><td>${name}</td><td>${course}</td><td>${cause}</td><td>${year}</td><td class="text-center"><button class="btn btn-sm btn-outline-danger unarchive-btn">Unarchive</button></td>`;
      tbody.appendChild(tr);
    });
    // refresh cached rows
    studentRows = Array.from(document.querySelectorAll('#studentTable tbody tr'));
    // Number the rows
    let counter = 1;
    studentRows.forEach(row => {
      row.cells[0].textContent = counter;
      counter++;
    });
    // Apply current filters
    applyFilters();
  }).catch(err=>{ console.error(err); tbody.innerHTML = '<tr><td colspan="7">Error loading</td></tr>'; });
})();

// Load archived events
(function(){
  const tbody = document.querySelector('#eventTable tbody');
  tbody.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
  fetch('../api/events.php').then(r=>r.json()).then(async j=> {
    if(!j.success){ tbody.innerHTML = '<tr><td colspan="5">Error loading</td></tr>'; return; }
    const allRows = j.data || [];
    const deptFiltered = <?php if (!empty($dept_name)) { echo "allRows.filter(ev => ev.departments_acronym == '$dept_name')"; } else { echo "allRows"; } ?>;
    const userFiltered = <?php if (!empty($_SESSION['user_id'])) { echo "deptFiltered.filter(ev => ev.user_id == '$_SESSION[user_id]')"; } else { echo "deptFiltered"; } ?>;
    const rows = userFiltered.filter(ev => ev.status === 'archived');
    if(rows.length===0){ tbody.innerHTML = '<tr><td colspan="5">No archived events</td></tr>'; return; }

    // fetch semester map
    const semRes = await fetch('../api/semesters.php');
    const semJson = await semRes.json();
    const semesterMap = {};
    if (semJson.success && Array.isArray(semJson.data)) {
      semJson.data.forEach(s => {
        semesterMap[s.id] = s.name || s.semester;
      });
    }

    tbody.innerHTML='';
    rows.forEach((r, idx) => {
      const id = r.id ?? r.event_id ?? '';
      const name = r.event_name ?? r.name ?? '';
      const date = r.date ? new Date(r.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '';
      const status = 'Archived';
      const year = r.date ? getSchoolYear(r.date) : '';
      const semesterId = r.semester_id || r.semester || '';
      const tr = document.createElement('tr');
      tr.setAttribute('data-year', year);
      tr.setAttribute('data-semester', semesterId);
      tr.innerHTML = `<td></td><td>${name}</td><td>${date}</td><td>${status}</td><td class="text-center"><button class="btn btn-sm btn-outline-danger unarchive-btn" data-event-id="${id}">Unarchive</button></td>`;
      tbody.appendChild(tr);
    });
    // refresh cached rows
    eventRows = Array.from(document.querySelectorAll('#eventTable tbody tr'));
    // Number the rows
    let counter = 1;
    eventRows.forEach(row => {
      row.cells[0].textContent = counter;
      counter++;
    });
    // Apply current filters
    applyFilters();
  }).catch(err=>{ console.error(err); tbody.innerHTML = '<tr><td colspan="5">Error loading</td></tr>'; });
})();

// Load event filters
// loadEventFilters(); // removed, now called in toggle
// loadEventYearFilter(); // removed, now called in toggle

// Unarchive flow using a modal instead of confirm()
let _unarchiveRow = null;
let _unarchiveEventId = null;
let _unarchiveEventRow = null;
document.addEventListener('click', function(e){
  const btn = e.target.closest('.unarchive-btn');
  if (!btn) return;
  const row = btn.closest('tr');
  if (!row) return;
  const eventId = btn.getAttribute('data-event-id');
  if (eventId) {
    // Event unarchive
    _unarchiveEventId = eventId;
    _unarchiveEventRow = row;
    const name = row.cells[1] ? row.cells[1].textContent.trim() : '';
    const modalEl = document.getElementById('unarchiveEventModal');
    if (!modalEl) return;
    modalEl.querySelector('#unarchive_event_id').textContent = name;
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
  } else {
    // Student unarchive
    _unarchiveRow = row;
    // show student name in the modal (column 2) instead of the numeric ID
    const name = row.cells[2] ? row.cells[2].textContent.trim() : '';
    const modalEl = document.getElementById('unarchiveModal');
    if (!modalEl) return;
    modalEl.querySelector('#unarchive_id').textContent = name;
    const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modal.show();
  }
});

document.addEventListener('DOMContentLoaded', function(){
  const confirmBtn = document.getElementById('confirmUnarchiveBtn');
  if (!confirmBtn) return;
  confirmBtn.addEventListener('click', async function(){
    if (!_unarchiveRow) return;
    const id = _unarchiveRow.cells[1] ? _unarchiveRow.cells[1].textContent.trim() : '';
    if (!id) return;
    confirmBtn.disabled = true; confirmBtn.textContent = 'Unarchiving...';
    try{
      const payload = { ID_Number: id, status: 'active', cause: null, year: null };
      const res = await fetch('../api/students.php', { method: 'PUT', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
      let j = null;
      if (res.ok){ try{ j = await res.json(); } catch(e){ j = { success:true }; } }
      else { try{ j = await res.json(); } catch(e){ j = { success:false, error: res.statusText }; } }
      if (!j || !j.success){ showToast('Error: '+(j && j.error ? j.error : 'unknown'), 'danger'); return; }
      // clear visible cause/year and remove row after small delay
      const causeCell = _unarchiveRow.cells[4]; if (causeCell) causeCell.textContent = '';
      const yearCell = _unarchiveRow.cells[5]; if (yearCell) yearCell.textContent = new Date().getFullYear();
      showToast('Student unarchived','success');
      const modalEl = document.getElementById('unarchiveModal'); const modal = bootstrap.Modal.getInstance(modalEl); if (modal) modal.hide();
      setTimeout(()=>{ _unarchiveRow.remove(); const i = studentRows.indexOf(_unarchiveRow); if (i>-1) studentRows.splice(i,1); _unarchiveRow = null; }, 600);
    }catch(err){ console.error(err); showToast('Network error: '+(err.message||err),'danger'); }
    finally{ confirmBtn.disabled = false; confirmBtn.textContent = 'Unarchive'; }
  });

  const confirmEventBtn = document.getElementById('confirmUnarchiveEventBtn');
  if (!confirmEventBtn) return;
  confirmEventBtn.addEventListener('click', async function(){
    if (!_unarchiveEventId || !_unarchiveEventRow) return;
    confirmEventBtn.disabled = true; confirmEventBtn.textContent = 'Unarchiving...';
    try{
      const res = await fetch('../api/events.php', {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: _unarchiveEventId, status: 'active'})
      });
      let j = null;
      if (res.ok){ try{ j = await res.json(); } catch(e){ j = { success:true }; } }
      else { try{ j = await res.json(); } catch(e){ j = { success:false, error: res.statusText }; } }
      if (!j || !j.success){ showToast('Error: '+(j && j.error ? j.error : 'unknown'), 'danger'); return; }
      showToast('Event unarchived','success');
      const modalEl = document.getElementById('unarchiveEventModal'); const modal = bootstrap.Modal.getInstance(modalEl); if (modal) modal.hide();
      setTimeout(()=>{ _unarchiveEventRow.remove(); const i = eventRows.indexOf(_unarchiveEventRow); if (i>-1) eventRows.splice(i,1); _unarchiveEventId = null; _unarchiveEventRow = null; }, 600);
    }catch(err){ console.error(err); showToast('Network error: '+(err.message||err),'danger'); }
    finally{ confirmEventBtn.disabled = false; confirmEventBtn.textContent = 'Unarchive'; }
  });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Unarchive modal -->
<div class="modal fade" id="unarchiveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Unarchive Student</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Unarchive student <strong id="unarchive_id"></strong>?</p>
        <p class="small text-muted">This will clear the archive cause and year for the student.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="confirmUnarchiveBtn" type="button" class="btn btn-maroon">Unarchive</button>
      </div>
    </div>
  </div>
</div>
<!-- Unarchive Event modal -->
<div class="modal fade" id="unarchiveEventModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Unarchive Event</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Unarchive event <strong id="unarchive_event_id"></strong>?</p>
        <p class="small text-muted">This will restore the event to active status.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="confirmUnarchiveEventBtn" type="button" class="btn btn-maroon">Unarchive</button>
      </div>
    </div>
  </div>
</div>
</script>
</body>
</html>
