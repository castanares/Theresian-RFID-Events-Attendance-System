<?php
require_once __DIR__ . '/../auth.php';
require_login('../');
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
      
      .archive-toggle {
        width: 100%;
      }
      
      .toggle-btn {
        flex: 1;
        text-align: center;
      }
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
    <button class="toggle-btn" data-type="departments">Department Archive</button>
    <button class="toggle-btn" data-type="accounts">Account Archive</button>
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

        <!-- debug panel removed -->

        <!-- Unarchive confirmation modal (matches Event archived.php) -->
        <div class="modal fade" id="unarchiveModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header"><h5 class="modal-title">Unarchive Student</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <p id="unarchiveModalBody"></p>
                <p class="small text-muted" id="unarchiveModalDesc"></p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="confirmUnarchiveBtn" type="button" class="btn btn-maroon">Unarchive</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Event Archive Table -->
        <table class="table border border-dark table-bordered table-maroon" id="eventTable" style="display:none;">
          <thead class="border border-dark table-info">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Event Name</th>
              <th scope="col">Department</th>
              <th scope="col">Date</th>
              <th scope="col">Participants</th>
              <th scope="col">Status</th>
              <th scope="col" class="text-center">Action</th>
            </tr>
          </thead>
          <tbody id="eventTbody">
            <!-- archived event rows will be loaded dynamically by JS -->
          </tbody>
        </table>

        <!-- Department Archive Table -->
        <table class="table border border-dark table-bordered table-maroon" id="deptTable" style="display:none; margin-top:1rem;">
          <thead class="border border-dark table-info">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Acronym</th>
              <th scope="col">Name</th>
              <th scope="col">Status</th>
              <th scope="col" class="text-center">Action</th>
            </tr>
          </thead>
          <tbody id="deptTbody">
            <!-- department rows will be loaded dynamically -->
          </tbody>
        </table>

        <!-- Account Archive Table -->
        <table class="table border border-dark table-bordered table-maroon" id="accountTable" style="display:none; margin-top:1rem;">
          <thead class="border border-dark table-info">
            <tr>
              <th scope="col">#</th>
              <th scope="col">Username</th>
              <th scope="col">Email</th>
              <th scope="col">Department</th>
              <th scope="col">Status</th>
              <th scope="col" class="text-center">Action</th>
            </tr>
          </thead>
          <tbody id="accountTbody">
            <!-- archived account rows will be loaded dynamically by JS -->
          </tbody>
        </table>

      </div>
    </section>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Archive type toggle functionality
const toggleButtons = document.querySelectorAll('.toggle-btn');
const studentTable = document.getElementById('studentTable');
const eventTable = document.getElementById('eventTable');
const deptTable = document.getElementById('deptTable');
const accountTable = document.getElementById('accountTable');
const studentFilters = document.querySelector('.student-filters');
const eventFilters = document.querySelector('.event-filters');
const searchInput = document.getElementById('searchInput');

let currentArchiveType = 'students';
let studentRows = Array.from(studentTable.querySelectorAll('tbody tr'));
let eventRows = Array.from(eventTable.querySelectorAll('tbody tr'));
let accountRows = [];

// Debug helper: log which branch provided account rows
function logAccountLoad(branch, count) {
  try { console.debug(`[archived.php] loadArchivedAccounts - branch=${branch}, rows=${count}`); } catch(e) {}
}

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
      deptTable.style.display = 'none';
      accountTable.style.display = 'none';
      studentFilters.style.display = 'flex';
      eventFilters.style.display = 'none';
      searchInput.placeholder = 'Search archived students...';
      clearAllFilters();
      applyFilters();
    } else if (type === 'events') {
      studentTable.style.display = 'none';
      eventTable.style.display = 'table';
      deptTable.style.display = 'none';
      accountTable.style.display = 'none';
      studentFilters.style.display = 'none';
      eventFilters.style.display = 'flex';
      searchInput.placeholder = 'Search archived events...';
      Promise.all([loadEventYearFilter(), loadEventFilters()]).then(() => {
        loadArchivedEvents().then(() => {
          applyFilters();
        });
      });
    } else if (type === 'departments') {
      studentTable.style.display = 'none';
      eventTable.style.display = 'none';
      deptTable.style.display = 'table';
      accountTable.style.display = 'none';
      studentFilters.style.display = 'none';
      eventFilters.style.display = 'none';
      searchInput.placeholder = 'Search archived departments...';
      clearAllFilters();
      applyFilters();
    } else if (type === 'accounts') {
      studentTable.style.display = 'none';
      eventTable.style.display = 'none';
      deptTable.style.display = 'none';
      accountTable.style.display = 'table';
      studentFilters.style.display = 'none';
      eventFilters.style.display = 'none';
      searchInput.placeholder = 'Search archived accounts...';
      clearAllFilters();
      loadArchivedAccounts().then(()=>applyFilters());
    }
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
      
      row.style.display = (matchesText && matchesCause) ? '' : 'none';
    });
    // Re-number visible rows (skip the "empty message" row which uses a single TD with colspan)
    let visibleCounter = 1;
    studentRows.forEach(row => {
      if (row.style.display === 'none') return;
      const firstCell = row.cells[0];
      if (!firstCell) return;
      if (firstCell.hasAttribute && firstCell.hasAttribute('colspan')) return; // skip empty-message row
      firstCell.textContent = visibleCounter;
      visibleCounter++;
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
      const matchesSemester = semester === '' || rowSemester == semester;
      
      row.style.display = (matchesText && matchesYear && matchesSemester) ? '' : 'none';
    });
    // Re-number visible rows (skip empty-message rows)
    let visibleCounter = 1;
    eventRows.forEach(row => {
      if (row.style.display === 'none') return;
      const firstCell = row.cells[0];
      if (!firstCell) return;
      if (firstCell.hasAttribute && firstCell.hasAttribute('colspan')) return;
      firstCell.textContent = visibleCounter;
      visibleCounter++;
    });
  } else if (currentArchiveType === 'departments') {
    const deptRows = Array.from(deptTable.querySelectorAll('tbody tr'));
    deptRows.forEach(row => {
      const txt = row.textContent.toLowerCase();
      const matchesText = q === '' || txt.includes(q);
      row.style.display = matchesText ? '' : 'none';
    });
    // Re-number visible rows (skip empty-message rows)
    let visibleCounter = 1;
    deptRows.forEach(row => {
      if (row.style.display === 'none') return;
      const firstCell = row.cells[0];
      if (!firstCell) return;
      if (firstCell.hasAttribute && firstCell.hasAttribute('colspan')) return;
      firstCell.textContent = visibleCounter;
      visibleCounter++;
    });
  }
  else if (currentArchiveType === 'accounts') {
    const acctRows = accountRows;
    acctRows.forEach(row => {
      const txt = row.textContent.toLowerCase();
      const matchesText = q === '' || txt.includes(q);
      row.style.display = matchesText ? '' : 'none';
    });
    // re-number (skip empty-message rows)
    let visible = 1;
    acctRows.forEach(row => {
      if (row.style.display === 'none') return;
      const firstCell = row.cells[0];
      if (!firstCell) return;
      if (firstCell.hasAttribute && firstCell.hasAttribute('colspan')) return;
      firstCell.textContent = visible;
      visible++;
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

// Handle unarchive/restore actions
document.addEventListener('click', function(e) {
  if (e.target && (e.target.classList.contains('unarchive-btn') || e.target.classList.contains('restore-btn') || e.target.classList.contains('unarchive-dept-btn'))) {
    const btn = e.target;
    const row = btn.closest('tr');
    if (!row) return;

    const isStudent = btn.classList.contains('unarchive-btn');
    const isDept = btn.classList.contains('unarchive-dept-btn');
    const itemType = isStudent ? 'Student' : (isDept ? 'Department' : 'Event');

    if (isStudent) {
      // open unarchive confirmation modal and store the row info on the modal element
      const idNumber = row.dataset.idNumber || row.dataset.internalId || row.dataset.id || '';
      const nameCell = row.querySelector('td:nth-child(3)');
      const displayName = nameCell ? nameCell.textContent.trim() : '';
      const modalEl = document.getElementById('unarchiveModal');
      modalEl.querySelector('.modal-title').textContent = 'Unarchive Student';
      document.getElementById('unarchiveModalBody').textContent = `Unarchive student: ${displayName}?`;
      document.getElementById('unarchiveModalDesc').textContent = 'This will clear the archive cause and year for the student.';
      modalEl.dataset.idNumber = idNumber;
      modalEl.dataset.rowIndex = studentRows.indexOf(row);
      modalEl.dataset.rowHtmlIndex = Array.from(row.parentElement.children).indexOf(row);
      modalEl.dataset.itemType = 'student';
      modalEl.querySelector('#confirmUnarchiveBtn').textContent = 'Unarchive';
      const bsModal = new bootstrap.Modal(modalEl);
      bsModal.show();
      return;
    }

    if (isDept) {
      // Departments: show confirmation modal
      const deptName = row.querySelector('td:nth-child(3)') ? row.querySelector('td:nth-child(3)').textContent.trim() : '';
      const modalEl = document.getElementById('unarchiveModal');
      modalEl.querySelector('.modal-title').textContent = 'Unarchive Department';
      document.getElementById('unarchiveModalBody').textContent = `Unarchive department: ${deptName}?`;
      document.getElementById('unarchiveModalDesc').textContent = 'This will set the department status to active.';
      modalEl.dataset.deptId = row.dataset.id;
      modalEl.dataset.deptName = deptName;
      modalEl.dataset.deptAcronym = row.dataset.acronym || '';
      modalEl.dataset.itemType = 'department';
      modalEl.querySelector('#confirmUnarchiveBtn').textContent = 'Unarchive';
      const bsModal = new bootstrap.Modal(modalEl);
      bsModal.show();
      return;
    }

    // Events: show confirmation modal
    const eventName = row.querySelector('td:nth-child(2)') ? row.querySelector('td:nth-child(2)').textContent.trim() : '';
    const modalEl = document.getElementById('unarchiveModal');
    modalEl.querySelector('.modal-title').textContent = 'Restore Event';
    document.getElementById('unarchiveModalBody').textContent = `Restore event: ${eventName}?`;
    document.getElementById('unarchiveModalDesc').textContent = 'This will set the event status to active.';
    modalEl.dataset.eventId = row.dataset.id;
    modalEl.dataset.itemType = 'event';
    modalEl.querySelector('#confirmUnarchiveBtn').textContent = 'Restore';
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
    return;
  }
});

// small helper to escape HTML in text
// small helper to escape HTML in text (coerce to string to handle numbers)
function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  str = String(str);
  if (!str) return '';
  return str.replace(/[&<>"']/g, function(m) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]; });
}

// Fetch archived departments and render into table
async function fetchDepartments() {
  try {
    const res = await fetch('../api/departments.php?archived=1');
    const json = await res.json();
    if (!json.success) return;
    const rows = json.data || [];
    const tbody = document.getElementById('deptTbody');
    tbody.innerHTML = '';
    rows.forEach((d, idx) => {
      // d may have normalized keys (id,name,acronym,archived) or raw keys
      const id = d.id || d.Dept_ID || d.id || '';
      const name = d.name || d.Dept_name || d.dept_name || Object.values(d)[1] || '';
      const acronym = d.acronym || d.dept_Acronym || d.dept_acronym || '';
      const status = d.archived ? 'Archived' : 'Active';
      const badgeClass = status === 'Archived' ? 'bg-secondary' : 'bg-success';
      const badgeText = status;
      const tr = document.createElement('tr');
      if (id) tr.dataset.id = id;
      if (acronym) tr.dataset.acronym = acronym;
      tr.dataset.status = status;
      tr.innerHTML = `
        <td>${idx + 1}</td>
        <td>${escapeHtml(acronym)}</td>
        <td>${escapeHtml(name)}</td>
        <td><span class="badge ${badgeClass}">${badgeText}</span></td>
        <td class="text-center"><button class="btn btn-sm btn-outline-danger unarchive-dept-btn">Unarchive</button></td>
      `;
      tbody.appendChild(tr);
    });
    if (!rows.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="5" class="text-center text-muted">No archived departments found.</td>
        </tr>
      `;
    }
  } catch (err) {
    console.error('Failed to load departments', err);
  }
}

// Load departments once script runs
fetchDepartments();

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

// Load archived events
async function loadArchivedEvents() {
  try {
    const res = await fetch('../api/events.php?status=archived');
    const json = await res.json();
    if (!json.success) return;
    const rows = json.data || [];
    const tbody = document.getElementById('eventTbody');
    tbody.innerHTML = '';
    rows.forEach((e, idx) => {
      // Normalize keys
      const id = e.id || e.ID || '';
      const eventName = e.event_name || e.name || '';
      const date = e.date || '';
      const deptId = e.departments_id || '';
      const status = e.status || 'archived';
      // Build department acronyms string
      let deptAcronyms = [];
      if (e.department_name && e.department_name !== 'Unknown') {
        deptAcronyms.push(e.department_name);
      }
      if (e.other_departments_info && Array.isArray(e.other_departments_info)) {
        e.other_departments_info.forEach(dept => {
          const acronym = dept.dept_Acronym || dept.acronym || dept.Acronym;
          if (acronym && !deptAcronyms.includes(acronym)) {
            deptAcronyms.push(acronym);
          }
        });
      }
      const deptName = deptAcronyms.length > 0 ? deptAcronyms.join(', ') : 'Unknown';
      const participants = e.participants || 0;
      // Status badge
      const badgeClass = status === 'archived' ? 'bg-secondary' : 'bg-success';
      const badgeText = status === 'archived' ? 'Archived' : 'Active';

      const tr = document.createElement('tr');
      if (id) tr.dataset.id = id;
      tr.dataset.year = getSchoolYear(date);
      tr.dataset.status = status;
      tr.dataset.semester = e.semester_id;
      tr.innerHTML = `
        <td>${idx + 1}</td>
        <td>${escapeHtml(eventName)}</td>
        <td>${escapeHtml(deptName)}</td>
        <td>${date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : ''}</td>
        <td>${participants}</td>
        <td><span class="badge ${badgeClass}">${badgeText}</span></td>
        <td class="text-center"><button class="btn btn-sm btn-outline-danger restore-btn">Restore</button></td>
      `;
      tbody.appendChild(tr);
    });
    if (!rows.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="7" class="text-center text-muted">No archived events found.</td>
        </tr>
      `;
    }
    // refresh cached rows
    eventRows = Array.from(document.querySelectorAll('#eventTable tbody tr'));
  } catch (err) {
    console.error('Failed to load archived events', err);
  }
}
async function loadArchivedStudents() {
  try {
    const res = await fetch('../api/students.php?status=archived');
    let json = null;
    try { json = await res.json(); } catch (err) { /* ignore */ }
    let rows = (json && json.data) ? json.data : [];

    // If the API returned no rows for the archived filter, fall back to fetching all
    // and perform a client-side detection of archived records. This covers schemas
    // that store an archive flag as integer, boolean, or use only archive columns.
    if (!rows.length) {
      console.debug('No rows returned for ?status=archived. Fetching all students and filtering client-side');
      const allRes = await fetch('../api/students.php');
  try { json = await allRes.json(); } catch (err) { json = null; }
  const allRows = (json && json.data) ? json.data : [];
      // filter: status === 'archived' OR archive_cause/non-empty OR archived_stamp/non-empty
      rows = allRows.filter(s => {
        const statusVal = (s.status || s.Status || '').toString().toLowerCase();
        const cause = s.archive_cause || s.archiveCause || s.cause || s.Archive_Cause || '';
        const year = s.archived_stamp || s.archived_year || s.year || s.archived || '';
        return statusVal === 'archived' || (cause && cause.toString().trim() !== '') || (year && year.toString().trim() !== '');
      });
    }

  const tbody = document.getElementById('studentTbody');
    tbody.innerHTML = '';
    rows.forEach((s, idx) => {
      // Normalize common keys
      const internalId = s.id || s.ID || s.student_id || '';
      const idNumber = s.ID_Number || s.id_number || s.IDNUMBER || '';
      const studentName = s.Student_name || s.student_name || s.StudentName || s.name || Object.values(s)[2] || '';
      const course = s.course || s.Course || s.program || '';
      const cause = s.archive_cause || s.archiveCause || s.Archive_Cause || s.cause || '';
      const year = s.archived_stamp || s.archived_year || s.archived || s.year || '';

      const tr = document.createElement('tr');
      if (internalId) tr.dataset.internalId = internalId;
      if (idNumber) tr.dataset.idNumber = idNumber;
      tr.dataset.course = course;
      tr.dataset.year = year;
      tr.dataset.cause = cause;
      tr.innerHTML = `
        <td>${idx + 1}</td>
        <td>${escapeHtml(idNumber || internalId)}</td>
        <td>${escapeHtml(studentName)}</td>
        <td>${escapeHtml(course)}</td>
        <td>${escapeHtml(cause)}</td>
        <td>${escapeHtml(year)}</td>
        <td class="text-center"><button class="btn btn-sm btn-outline-danger unarchive-btn">Unarchive</button></td>
      `;
      tbody.appendChild(tr);
    });
    // if no rows were added, show a friendly empty message
    if (!rows.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="7" class="text-center text-muted">No archived students found.</td>
        </tr>
      `;
    }
  // refresh cached rows
  studentRows = Array.from(document.querySelectorAll('#studentTable tbody tr'));
  } catch (err) {
    console.error('Failed to load archived students', err);
    const statusEl = document.getElementById('archiveStatus');
    if (statusEl) statusEl.textContent = 'Failed to load archived students: ' + err.message;
  }
}

// Load archived accounts
async function loadArchivedAccounts() {
  try {
    // Fetch all users and filter by status === 'archived' (case-insensitive)
    const res = await fetch('../api/users.php');
    let json = null;
    try { json = await res.json(); } catch (_) { json = null; }

    let rows = [];
    if (json) {
      if (Array.isArray(json.data)) rows = json.data;
      else if (Array.isArray(json)) rows = json;
      else if (json.success && Array.isArray(json.data)) rows = json.data;
    }
    if (!Array.isArray(rows)) rows = [];

    // Strictly filter by status field == 'archived' (case-insensitive)
    rows = rows.filter(u => {
      const st = (u.status || u.Status || '').toString().toLowerCase();
      return st === 'archived';
    });
    logAccountLoad('fetched-and-filtered-status-archived', rows.length);

    // build department lookup maps to display friendly dept info and contact email
    const deptById = {};
    const deptByKey = {}; // acronym/name(lower) -> {name,email}
    try {
      const dres = await fetch('../api/departments.php');
      const djson = await dres.json();
      const depts = (djson && Array.isArray(djson.data)) ? djson.data : (Array.isArray(djson) ? djson : []);
      depts.forEach(d => {
        const id = d.id || d.Dept_ID || d.Dept_ID || d.DeptID || '';
        const name = d.name || d.Dept_name || d.dept_name || '';
        const acronym = d.acronym || d.dept_Acronym || d.dept_acronym || '';
        const email = d.contact_email || d.email || d.contactEmail || '';
        const entry = { name: name || acronym || String(id || ''), email: (email || '').toString() };
        if (id) deptById[String(id)] = entry;
        if (acronym) deptByKey[String(acronym).toLowerCase()] = entry;
        if (name) deptByKey[String(name).toLowerCase()] = entry;
      });
    } catch (e) {
      // ignore dept map build errors; will fall back to raw values
    }

    const tbody = document.getElementById('accountTbody');
    tbody.innerHTML = '';
  for (let idx = 0; idx < rows.length; idx++) {
      const u = rows[idx];
      const id = u.id || u.ID || u.user_id || '';
      const username = u.username || u.user_name || u.Username || '';
      // detect user email from several common field names; do NOT use username as email
      const emailCandidates = [u.email, u.Email, u.contact_email, u.contactEmail, u.user_email, u.userEmail, u.mail, u.mbox];
      let userEmail = '';
      for (const c of emailCandidates) {
        if (c && String(c).trim() !== '') { userEmail = String(c).trim(); break; }
      }
      const dept = u.departments_id || u.dept || u.department || '';
      const status = (u.status || u.Status || 'archived').toString();
      const lowerStatus = status.toLowerCase();
      const badgeClass = lowerStatus === 'archived' ? 'bg-secondary' : 'bg-success';
      const badgeText = lowerStatus === 'archived' ? 'Archived' : escapeHtml(status);
      // Resolve department display and prefer department contact email (like profile page)
      let deptDisplay = '';
      let emailDisplay = '';

      // Candidate dept identifiers on user row
      const candidateIds = [u.departments_id, u.dept_id, u.department_id, u.department, u.dept, u.department_name, u.dept_acronym, u.deptAcronym];
      let found = null;
      for (const c of candidateIds) {
        if (!c && c !== 0) continue;
        const s = String(c).trim();
        if (!s) continue;
        // numeric id lookup
        if (/^\d+$/.test(s) && deptById.hasOwnProperty(s)) { found = deptById[s]; break; }
        const lower = s.toLowerCase();
        if (deptByKey.hasOwnProperty(lower)) { found = deptByKey[lower]; break; }
      }

      if (found) {
        deptDisplay = found.name || '';
        emailDisplay = found.email || '';
      } else {
        // fallback to raw dept field if present
        deptDisplay = String(dept || '');
        emailDisplay = '';
      }

      // If department contact email not available, try fetching department profile (same endpoint used by profile.php)
      if ((!emailDisplay || emailDisplay.trim() === '')) {
        // try to find a numeric dept id in candidateIds
        let numericDeptId = null;
        for (const c of candidateIds) {
          if (!c && c !== 0) continue;
          const s = String(c).trim();
          if (/^\d+$/.test(s)) { numericDeptId = s; break; }
        }
        if (numericDeptId) {
          try {
            const pres = await fetch('../api/profile.php?type=department&dept_id=' + encodeURIComponent(numericDeptId), { credentials: 'same-origin' });
            const pjson = await pres.json();
            if (pjson && pjson.success && pjson.data) {
              const pd = pjson.data;
              // prefer contact_email (profile.php uses contact_email)
              const pdEmail = pd.contact_email || pd.contactEmail || pd.email || pd.contact || '';
              if (pdEmail && String(pdEmail).trim() !== '') emailDisplay = String(pdEmail).trim();
              const pdName = pd.name || pd.Dept_name || pd.dept_name || pd.dept || '';
              if (pdName && String(pdName).trim() !== '') deptDisplay = String(pdName).trim();
            }
          } catch (e) {
            // swallow fetch errors; we'll fall back to user email
          }
        }
      }

      // If department contact email still not available, fall back to user's email (if present)
      if ((!emailDisplay || emailDisplay.trim() === '') && userEmail) emailDisplay = userEmail;

      const tr = document.createElement('tr');
      if (id) tr.dataset.id = id;
      tr.innerHTML = `
        <td>${idx + 1}</td>
        <td>${escapeHtml(username)}</td>
        <td>${escapeHtml(emailDisplay)}</td>
        <td>${escapeHtml(deptDisplay)}</td>
        <td><span class="badge ${badgeClass}">${badgeText}</span></td>
        <td class="text-center"><button class="btn btn-sm btn-outline-danger unarchive-account-btn">Unarchive</button></td>
      `;
      tbody.appendChild(tr);
  }
    // if empty
    if (!rows.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center text-muted">No account is archived</td>
        </tr>
      `;
    }
    // refresh cached rows
      accountRows = Array.from(document.querySelectorAll('#accountTable tbody tr'));
      logAccountLoad('populated-table', accountRows.length);
  } catch (err) {
    console.error('Failed to load archived accounts', err);
  }
}

  // Prefetch accounts on page load so the Accounts tab shows DB data when clicked
  document.addEventListener('DOMContentLoaded', function(){ loadArchivedAccounts(); });

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

// load archived students now
loadArchivedStudents();

// no debug toggle (cleaned up)

// Unarchive flow (Event-style): open modal on button click, confirm via modal
let _unarchiveRow = null;
document.addEventListener('click', function(e){
  const btn = e.target.closest('.unarchive-btn');
  if (!btn) return;
  const row = btn.closest('tr');
  if (!row) return;
  _unarchiveRow = row;
  const name = row.cells[2] ? row.cells[2].textContent.trim() : '';
  const modalEl = document.getElementById('unarchiveModal');
  if (!modalEl) return;
  modalEl.querySelector('.modal-title').textContent = 'Unarchive Student';
  document.getElementById('unarchiveModalBody').textContent = `Unarchive student: ${name}?`;
  document.getElementById('unarchiveModalDesc').textContent = 'This will clear the archive cause and year for the student.';
  modalEl.dataset.itemType = 'student';
  modalEl.querySelector('#confirmUnarchiveBtn').textContent = 'Unarchive';
  const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
  modal.show();
});

document.addEventListener('DOMContentLoaded', function(){
  const confirmBtn = document.getElementById('confirmUnarchiveBtn');
  if (!confirmBtn) return;
  confirmBtn.addEventListener('click', async function(){
    const modalEl = document.getElementById('unarchiveModal');
    const itemType = modalEl.dataset.itemType;
    if (itemType === 'event') {
      // Handle event restore
      const eventId = modalEl.dataset.eventId;
      if (!eventId) return;
      confirmBtn.disabled = true; confirmBtn.textContent = 'Restoring...';
      try {
        const payload = new FormData();
        payload.append('id', eventId);
        payload.append('status', 'active');
        const res = await fetch('../api/events.php', { method: 'POST', body: payload });
        let j = null;
        if (res.ok){ try{ j = await res.json(); } catch(e){ j = { success:true }; } }
        else { try{ j = await res.json(); } catch(e){ j = { success:false, error: res.statusText }; } }
        if (!j || !j.success){ showToast('Error: '+(j && j.error ? j.error : 'unknown'), 'danger'); return; }
        showToast('Event restored','success');
        const modal = bootstrap.Modal.getInstance(modalEl); if (modal) modal.hide();
        // Remove the row
        const row = document.querySelector(`#eventTable tbody tr[data-id="${eventId}"]`);
        if (row) {
          row.remove();
          const index = eventRows.indexOf(row);
          if (index > -1) eventRows.splice(index, 1);
        }
      } catch(err){ console.error(err); showToast('Network error: '+(err.message||err),'danger'); }
      finally{ confirmBtn.disabled = false; confirmBtn.textContent = 'Restore'; }
      return;
    }
    if (itemType === 'department') {
      // Handle department unarchive
      const deptId = modalEl.dataset.deptId;
      const deptName = modalEl.dataset.deptName;
      const deptAcronym = modalEl.dataset.deptAcronym;
      if (!deptId && !deptName && !deptAcronym) return;
      confirmBtn.disabled = true; confirmBtn.textContent = 'Unarchiving...';
      try {
        const payload = new URLSearchParams();
        if (deptId) payload.append('id', deptId);
        else if (deptName) payload.append('name', deptName);
        else if (deptAcronym) payload.append('acronym', deptAcronym);
        payload.append('archived', '0');
        const res = await fetch('../api/departments.php', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
          body: payload.toString()
        });
        const data = await res.json();
        if (data && data.success) {
          showToast('Department unarchived successfully', 'success');
          const modal = bootstrap.Modal.getInstance(modalEl); if (modal) modal.hide();
          // Remove the row
          const row = document.querySelector(`#deptTable tbody tr[data-id="${deptId}"]`);
          if (row) row.remove();
          // Refresh page after toast
          setTimeout(() => location.reload(), 1500);
        } else {
          const err = (data && data.error) ? data.error : 'Unarchive failed';
          showToast(`Error: ${err}`, 'danger');
        }
      } catch(err){ console.error(err); showToast('Network error: '+(err.message||err),'danger'); }
      finally{ confirmBtn.disabled = false; confirmBtn.textContent = 'Unarchive'; }
      return;
    }
      // Handle student unarchive (existing logic)
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
      const modal = bootstrap.Modal.getInstance(modalEl); if (modal) modal.hide();
      setTimeout(()=>{ _unarchiveRow.remove(); const i = studentRows.indexOf(_unarchiveRow); if (i>-1) studentRows.splice(i,1); _unarchiveRow = null; }, 600);
    }catch(err){ console.error(err); showToast('Network error: '+(err.message||err),'danger'); }
    finally{ confirmBtn.disabled = false; confirmBtn.textContent = 'Unarchive'; }
  });
});

// Handle account unarchive button click -> show confirmation modal
document.addEventListener('click', function(e){
  const btn = e.target.closest('.unarchive-account-btn');
  if (!btn) return;
  const row = btn.closest('tr'); if (!row) return;
  const username = row.cells[1] ? row.cells[1].textContent.trim() : '';
  const modalEl = document.getElementById('unarchiveModal');
  if (!modalEl) return;
  modalEl.querySelector('.modal-title').textContent = 'Unarchive Account';
  document.getElementById('unarchiveModalBody').textContent = `Unarchive account: ${username}?`;
  document.getElementById('unarchiveModalDesc').textContent = 'This will reactivate the user account.';
  modalEl.dataset.itemType = 'account';
  modalEl.dataset.accountId = row.dataset.id || '';
  modalEl.querySelector('#confirmUnarchiveBtn').textContent = 'Unarchive';
  const bsModal = new bootstrap.Modal(modalEl); bsModal.show();
});

// When confirm button is used for accounts, perform PUT ../api/users.php to reactivate
document.addEventListener('DOMContentLoaded', function(){
  const confirmBtn = document.getElementById('confirmUnarchiveBtn');
  if (!confirmBtn) return;
  confirmBtn.addEventListener('click', async function(){
    const modalEl = document.getElementById('unarchiveModal');
    if (!modalEl) return;
    const itemType = modalEl.dataset.itemType;
    if (itemType === 'account') {
      const accId = modalEl.dataset.accountId;
      if (!accId) return;
      confirmBtn.disabled = true; confirmBtn.textContent = 'Unarchiving...';
      try {
        const payload = new URLSearchParams();
        payload.append('id', accId);
        payload.append('status', 'active');
        const res = await fetch('../api/users.php', { method: 'PUT', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: payload.toString() });
        let j = null;
        try { j = await res.json(); } catch(_) { j = null; }
        if (!j || !j.success) { showToast('Error: ' + (j && j.error ? j.error : 'unknown'), 'danger'); return; }
        showToast('Account unarchived', 'success');
        const modal = bootstrap.Modal.getInstance(modalEl); if (modal) modal.hide();
        // remove row from table
        const row = document.querySelector(`#accountTable tbody tr[data-id="${accId}"]`);
        if (row) row.remove();
        if (accountRows && Array.isArray(accountRows)) {
          const i = accountRows.indexOf(row);
          if (i > -1) accountRows.splice(i, 1);
        }
      } catch (err) { console.error(err); showToast('Network error: ' + (err.message||err), 'danger'); }
      finally { confirmBtn.disabled = false; confirmBtn.textContent = 'Unarchive'; }
    }
  });
});

// Load event filters
loadEventFilters();
loadEventYearFilter();
</script>
</body>
</html>
