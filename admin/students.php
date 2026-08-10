<?php
require_once __DIR__ . '/../auth.php';
// require_login('/new/');
require_login('../');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Students</title>
    <link rel="stylesheet" href="../styles/dash_styles.css">
    <link rel="stylesheet" href="../styles/side_styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      /* Theme variables */
      :root{
        --maroon: maroon;
        --maroon-dark: #6d0000;
        --card-bg: #fff;
        --card-shadow: 0 8px 32px rgba(0,0,0,0.10);
      }

      /* Utility / accent classes */
      .create { border-left: 3px solid var(--maroon) !important; }
      .bg-maroon { background-color: var(--maroon); }
      .maroon-text { color: var(--maroon); }
      .create-submit:hover { background-color: #fff; }
      .table-maroon th { background-color: var(--maroon) !important; color: #fff !important; }

      /* Page card (increased size for design) */
      .page-card {
        background: var(--card-bg);
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        padding: 1.75rem;
        margin-left:9rem;
        margin-right: 2rem;
      }
  
      /* Controls that sit above the card */
      .controls-row { margin-bottom: 1rem; }
      .search-input .form-control { border-right: none; }
      .search-input .input-group-text { background: var(--card-bg); border-left: none; }

      /* Buttons */
      .btn-maroon { background: var(--maroon); color: #fff; border: none; }
      .btn-maroon:hover { background: var(--maroon-dark); color: #fff; }

      /* Table visuals */
      #studentsTable tbody tr:hover { background: rgba(128,0,0,0.04); }
      .table td, .table th { vertical-align: middle; }

      /* Filter section styling */
      .filter-section {
        margin-left: 9rem;
        margin-right: 2rem;
        margin-bottom: 1rem;
      }

      .filter-card {
        background: var(--card-bg);
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        padding: 1rem;
        margin-bottom: 1rem;
      }

      .filter-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: nowrap;
      }

      @media (max-width: 1200px) {
        .filter-controls {
          flex-wrap: wrap;
          gap: 0.5rem;
        }
        
        .search-input {
          min-width: 200px !important;
        }
      }

      @media (max-width: 768px) {
        .page-card, .filter-section {
          margin-left: 1rem;
          margin-right: 1rem;
        }
        
        .filter-controls {
          flex-direction: column;
          align-items: stretch;
          gap: 0.75rem;
        }
        
        .filter-controls > div {
          width: 100%;
          justify-content: center;
        }
        
        .search-input { 
          min-width: 100% !important;
        }
        
        .filter-controls .form-select {
          width: auto !important;
          flex: 1;
        }
      }
    </style>
</head>
<body>
<?php $page_heading = 'Manage Students'; include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Filter Section -->
<div class="filter-section">
  <div class="filter-card">
    <div class="filter-controls">
      <!-- Left side - Filters -->
      <div class="d-flex align-items-center gap-2">
        <label class="fw-bold text-nowrap me-2">Filters:</label>
        <select id="filterDepartment" class="form-select form-select-sm" style="width: 140px;">
          <option value="">All Departments</option>
          <option>CASTE</option>
          <option>COC</option>
          <option>CON</option>
          <option>CBTA</option>
          <option>Senior High School</option>
          <option>Junior High School</option>
          <option>Elementary</option>
        </select>
        
        <button id="clearFilters" class="btn btn-sm btn-outline-danger text-nowrap">
          <i class="bi bi-x-circle me-1"></i>Clear
        </button>
      </div>

      <!-- Right side - Search and Add -->
      <div class="d-flex align-items-center gap-2">
        <div class="search-input" style="min-width:220px;">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input id="searchInput" class="form-control" placeholder="Search students...">
          </div>
        </div>
        <button class="btn btn-maroon text-nowrap" data-bs-toggle="modal" data-bs-target="#addModal">
          <i class="bi bi-plus-lg me-1"></i>Add Student
        </button>
        <!-- <a href="events.php" class="btn btn-outline-secondary text-nowrap">
          <i class="bi bi-calendar-event me-1"></i>Events
        </a> -->
      </div>
    </div>
  </div>
</div>

<section class="p-0">
  <div class="container-fluid">
    <div class="page-card">
      <div class="row align-items-center mb-3">
        <div class="col-12 col-md-8">
          <h4 class="m-0">Student Management</h4>
          <p class="text-muted mb-0">Manage student records, RFID assignments, and department information</p>
        </div>
        <div class="col-12 col-md-4 text-md-end">
          <small class="text-muted">Total Students: <span id="totalCount" class="fw-bold text-maroon">156</span></small>
        </div>
      </div>

      <div class="table-responsive">
        <table id="studentsTable" class="table table-hover align-middle">
        <thead class="table-maroon">
          <tr>
            <th>#</th>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Department</th>
            <th>Course</th>
            <th>RFID Value</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="studentsTbody">
          <!-- rows will be loaded dynamically -->
        </tbody>
        </table>
      </div>
          <!-- Toast container for notifications -->
          <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index:1080">
            <div id="toastContainer"></div>
          </div>

    </div>
  </div>
</section>

<!-- Add Student Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form id="addStudentForm">
        <div class="modal-header" style="background-color: var(--maroon); color: white;">
          <h5 class="modal-title">Add New Student</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Student ID Number</label>
            <input type="number" name="student_id" class="form-control" placeholder="e.g., 4227300" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Full Name</label>
            <input name="student_name" class="form-control" placeholder="LASTNAME, FIRSTNAME MIDDLENAME" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Department</label>
            <select name="department" class="form-select" required>
              <option value="">Select Department</option>
            </select>
          </div>
           <div class="mb-3">
            <label class="form-label fw-bold">Course</label>
            <input name="course" class="form-control" placeholder="e.g., BSIT" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">RFID Card Number</label>
            <div class="input-group">
              <input name="rfid_value" class="form-control" placeholder="Scan RFID card" required>
             
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-maroon" id="addStudentBtn">
            <i class="bi bi-plus-lg me-1"></i>Add Student
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form id="editStudentForm">
        <input type="hidden" name="original_ID_Number">
        <div class="modal-header" style="background-color: var(--maroon); color: white;">
          <h5 class="modal-title">Edit Student</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Student ID Number</label>
            <input type="number" name="ID_Number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Full Name</label>
            <input name="student_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Department</label>
            <select name="department" class="form-select" required>
              <option value="">Select Department</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Course</label>
            <input name="course" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">RFID Card Number</label>
            <input name="rfid_value" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-maroon" id="saveEditBtn">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Archive Confirm Modal (same structure as Event/Add_student.php) -->
<div class="modal fade" id="archiveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Archive Student</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Archive student <strong id="archiveMessage"></strong></p>
        <div class="mb-2">
          <label class="form-label">Cause</label>
          <select id="archiveCause" name="cause" class="form-select" required>
            <option value="">Select cause</option>
            <option value="Graduated">Graduated</option>
            <option value="Transferred">Transferred</option>
            <option value="Discontinued">Discontinued</option>
            <option value="Other">Other</option>
          </select>
          <div class="invalid-feedback">Please select a cause for archiving.</div>
        </div>
        <div class="mb-2">
          <label class="form-label">Year</label>
          <input id="archiveYear" type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="confirmArchiveBtn" type="button" class="btn btn-maroon">Archive</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script>
// Toast helper
function showToast(message, type = 'info', timeout = 4000){
  const container = document.getElementById('toastContainer');
  if (!container) { alert(message); return; }
  const id = 't' + Date.now();
  const bg = type === 'error' ? 'bg-danger text-white' : (type === 'success' ? 'bg-success text-white' : 'bg-secondary text-white');
  const html = `
    <div id="${id}" class="toast ${bg}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="${timeout}">
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>`;
  container.insertAdjacentHTML('beforeend', html);
  const el = document.getElementById(id);
  const t = new bootstrap.Toast(el);
  t.show();
  el.addEventListener('hidden.bs.toast', ()=> el.remove());
}

  // Enhanced filtering and search functionality
  const searchInput = document.getElementById('searchInput');
  const filterDepartment = document.getElementById('filterDepartment');
  const filterStatus = document.getElementById('filterStatus') || null;
  const clearFilters = document.getElementById('clearFilters');
  const studentsTable = document.getElementById('studentsTable');
  const totalCount = document.getElementById('totalCount');
  
  let allRows = Array.from(studentsTable.querySelectorAll('tbody tr'));

  function applyFilters() {
    const searchTerm = searchInput.value.toLowerCase();
    const deptFilter = filterDepartment.value;
  const statusFilter = filterStatus ? filterStatus.value : '';
    
    let visibleCount = 0;

    allRows.forEach(row => {
      const text = row.textContent.toLowerCase();
      const dept = row.getAttribute('data-dept');
      const status = row.getAttribute('data-status');
      
      const matchesSearch = searchTerm === '' || text.includes(searchTerm);
      const matchesDept = deptFilter === '' || dept === deptFilter;
      const matchesStatus = statusFilter === '' || status === statusFilter;
      
      if (matchesSearch && matchesDept && matchesStatus) {
        row.style.display = '';
        visibleCount++;
        // Update the number in the first cell to match filtered count
        row.querySelector('td:first-child').textContent = visibleCount;
      } else {
        row.style.display = 'none';
      }
    });
    
    totalCount.textContent = visibleCount;
  }

  // Event listeners for filters
  searchInput.addEventListener('input', applyFilters);
  if (filterDepartment) filterDepartment.addEventListener('change', applyFilters);
  if (filterStatus) filterStatus.addEventListener('change', applyFilters);
  
  clearFilters.addEventListener('click', function() {
    searchInput.value = '';
    filterDepartment.value = '';
    if (filterStatus) filterStatus.value = '';
    applyFilters();
  });

  // ...existing code... (Add form POST handler defined later)

  // Simulated RFID scan function
  function scanRFID() {
    const rfidInput = document.querySelector('input[name="rfid_value"]');
    const randomRFID = Math.floor(Math.random() * 9000000000) + 1000000000;
    rfidInput.value = randomRFID;
    
    // Show scan feedback
    const scanBtn = event.target;
    const originalText = scanBtn.innerHTML;
    scanBtn.innerHTML = '<i class="bi bi-check-lg"></i> Scanned';
    scanBtn.className = 'btn btn-success';
    
    setTimeout(() => {
      scanBtn.innerHTML = originalText;
      scanBtn.className = 'btn btn-outline-secondary';
    }, 2000);
  }

  // Initialize count
  applyFilters();
</script>
<script>
// After adding a student, refresh the list
document.getElementById('addStudentForm').addEventListener('reset', function(){
  // small delay to allow modal hide animations to finish
  setTimeout(() => { loadStudents(); }, 300);
});

// helper escapeHtml for safety (simple)
function escapeHtml(str){ if (str===null || str===undefined) return ''; return String(str).replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[m]); }

// Load department acronyms for filters and modal selects
async function loadDepartmentAcronyms(){
  try{
    const res = await fetch('../api/departments.php');
    const j = await res.json();
    if (!j.success) return;
    const rows = j.data || [];
    const acros = rows.map(r => r.acronym || r.dept_Acronym || r.dept_acronym).filter(Boolean);
  const sel = document.getElementById('filterDepartment');
  const modalSel = document.querySelector('#addStudentForm select[name="department"]');
  const editModalSel = document.querySelector('#editStudentForm select[name="department"]');
    if (sel) { sel.innerHTML = '<option value="">All Departments</option>'; acros.forEach(a=>{ const o=document.createElement('option'); o.value=a; o.textContent=a; sel.appendChild(o); }); }
  if (modalSel){ modalSel.innerHTML = '<option value="">Select Department</option>'; acros.forEach(a=>{ const o=document.createElement('option'); o.value=a; o.textContent=a; modalSel.appendChild(o); }); }
  if (editModalSel){ editModalSel.innerHTML = '<option value="">Select Department</option>'; acros.forEach(a=>{ const o=document.createElement('option'); o.value=a; o.textContent=a; editModalSel.appendChild(o); }); }
  }catch(err){ console.error('Failed to load departments', err); }
}

// Load students list (admin — all departments)
async function loadStudents(){
  try{
    const tbody = document.getElementById('studentsTbody');
    const totalCount = document.getElementById('totalCount');
  tbody.innerHTML = '<tr><td colspan="8">Loading...</td></tr>';
  const res = await fetch('../api/admin_students.php?status=active');
    const j = await res.json();
    if (!j.success){ tbody.innerHTML = '<tr><td colspan="8">Error loading students</td></tr>'; return; }
    const rows = j.data || [];
    if (!rows.length){ tbody.innerHTML = '<tr><td colspan="8">No students found</td></tr>'; totalCount.textContent = '0'; return; }
    tbody.innerHTML = '';
    rows.forEach((r, idx)=>{
      const idNum = r.ID_Number ?? r.IDNumber ?? r.id ?? '';
      const name = r.Student_name ?? r.student_name ?? r.name ?? '';
      const deptA = r.departments_acronym ?? r.dept_acronym ?? r.acronym ?? '';
      const course = r.Course ?? r.course ?? '';
      const rfid = r.RFID ?? r.rfid ?? '';
      const status = r.status ?? '';
  const tr = document.createElement('tr');
  tr.setAttribute('data-dept', deptA);
  tr.setAttribute('data-status', status);
  tr.innerHTML = `<td>${idx+1}</td><td>${escapeHtml(idNum)}</td><td>${escapeHtml(name)}</td><td>${escapeHtml(deptA)}</td><td>${escapeHtml(course)}</td><td><code>${escapeHtml(rfid)}</code></td><td><span class="badge bg-${status.toLowerCase()==='active'?'success':'secondary'}">${escapeHtml(status)}</span></td><td><button class="btn btn-sm btn-outline-primary me-1 edit-btn" data-id="${escapeHtml(idNum)}"><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-danger archive-btn" data-id="${escapeHtml(idNum)}" data-name="${escapeHtml(name)}"><i class="bi bi-archive"></i></button></td>`;
      tbody.appendChild(tr);
    });
    allRows = Array.from(document.querySelectorAll('#studentsTable tbody tr'));
    totalCount.textContent = String(rows.length);
  }catch(err){ console.error(err); const tbody = document.getElementById('studentsTbody'); if (tbody) tbody.innerHTML = '<tr><td colspan="8">Error loading students</td></tr>'; }
}

// initialize
loadDepartmentAcronyms();
loadStudents();

// Ensure modal elements are appended to document.body to avoid accidental nesting
['addModal','editModal','archiveModal'].forEach(id => {
  const el = document.getElementById(id);
  if (el && el.parentElement !== document.body) document.body.appendChild(el);
});

// Wire up dynamic edit/archive button handlers using event delegation
document.getElementById('studentsTable').addEventListener('click', function(e){
  const editBtn = e.target.closest('.edit-btn');
  const archiveBtn = e.target.closest('.archive-btn');
  if (editBtn){
    const id = editBtn.getAttribute('data-id');
    openEditModal(id);
  } else if (archiveBtn){
    const id = archiveBtn.getAttribute('data-id');
    const name = archiveBtn.getAttribute('data-name') || '';
    openArchiveModal(id, name);
  }
});

// Add Student POST
document.getElementById('addStudentForm').addEventListener('submit', async function(e){
  e.preventDefault();
  const btn = document.getElementById('addStudentBtn');
  btn.disabled = true;
  const form = e.target;
  // require ID_Number and Student_name client-side before sending
  if (!form.checkValidity()){
    form.reportValidity();
    btn.disabled = false;
    return;
  }
  const rfidValue = form.querySelector('input[name="rfid_value"]').value.trim();
  
  // Check if RFID already exists
  const rfidExists = allRows.some(row => {
    const rowRfid = row.querySelector('td:nth-child(6)').textContent.trim();
    return rowRfid === rfidValue;
  });
  
  if (rfidExists) {
    showToast('Error: This RFID is already registered', 'error');
    btn.disabled = false;
    return;
  }
  
  const data = {
    ID_Number: form.querySelector('input[name="student_id"]').value.trim(),
    Student_name: form.querySelector('input[name="student_name"]').value.trim(),
    departments_acronym: form.querySelector('select[name="department"]').value,
    Course: form.querySelector('input[name="course"]').value.trim(),
    RFID: rfidValue
  };
  try{
    const res = await fetch('../api/admin_students.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(data) });
    let j = null;
    try{ j = await res.json(); }catch(e){ /* ignore malformed json */ }

    if (res.status === 409) {
      const msg = (j && j.error) ? j.error : 'RFID already registered';
      showToast(msg, 'error');
      return;
    }

    if (!res.ok || !j || !j.success) {
      throw new Error((j && j.error) || 'Failed to add student');
    }

    // close modal, reset form, reload and notify
    bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();
    form.reset();
    loadStudents();
    showToast('Student added', 'success');
  }catch(err){
    showToast('Add failed: ' + (err.message || err), 'error');
  }finally{ btn.disabled = false; }
});

// Ensure department select is populated when Add modal opens (in case loader ran earlier before departments were available)
document.getElementById('addModal').addEventListener('show.bs.modal', function(){
  const modalSel = document.querySelector('#addStudentForm select[name="department"]');
  if (modalSel && modalSel.options.length <= 1){
    loadDepartmentAcronyms();
  }
});

// Edit helper: open modal and populate
async function openEditModal(idNumber){
  try{
    const res = await fetch('../api/admin_students.php?id=' + encodeURIComponent(idNumber));
    const j = await res.json();
  if (!j.success) return showToast('Failed to load student', 'error');
    const row = j.data && j.data[0];
  if (!row) return showToast('Student not found', 'error');
    const form = document.getElementById('editStudentForm');
    form.querySelector('input[name="ID_Number"]').value = row.ID_Number || row.ID || '';
    // store original ID so PUT can identify the record even if ID_Number is changed
    const orig = row.ID_Number || row.ID || '';
    if (form.querySelector('input[name="original_ID_Number"]')) form.querySelector('input[name="original_ID_Number"]').value = orig;
    form.querySelector('input[name="student_name"]').value = row.Student_name || row.student_name || '';
    const deptSel = form.querySelector('select[name="department"]');
    if (deptSel) deptSel.value = row.departments_acronym || row.dept_acronym || '';
    form.querySelector('input[name="course"]').value = row.Course || row.course || '';
    form.querySelector('input[name="rfid_value"]').value = row.RFID || row.rfid || '';
    // hide any currently open modal to avoid nested/backdrop issues
    document.querySelectorAll('.modal.show').forEach(openEl => {
      const inst = bootstrap.Modal.getInstance(openEl);
      if (inst) inst.hide();
    });

    // show modal (modal elements were moved to document.body earlier)
    const m = new bootstrap.Modal(document.getElementById('editModal'));
    m.show();
  }catch(err){ console.error(err); showToast('Failed to load student', 'error'); }
}

// Save edit (PUT)
document.getElementById('editStudentForm').addEventListener('submit', async function(e){
  e.preventDefault();
  const btn = document.getElementById('saveEditBtn'); btn.disabled = true;
  const form = e.target;
  const payload = {
    old_ID_Number: form.querySelector('input[name="original_ID_Number"]').value || form.querySelector('input[name="ID_Number"]').value,
    ID_Number: form.querySelector('input[name="ID_Number"]').value,
    Student_name: form.querySelector('input[name="student_name"]').value.trim(),
    departments_acronym: form.querySelector('select[name="department"]').value,
    Course: form.querySelector('input[name="course"]').value.trim(),
    RFID: form.querySelector('input[name="rfid_value"]').value.trim()
  };
  try{
    const res = await fetch('../api/admin_students.php', { method: 'PUT', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    let j = null;
    try{ j = await res.json(); }catch(e){ /* ignore */ }

    if (res.status === 409) {
      const msg = (j && j.error) ? j.error : 'RFID already registered';
      showToast(msg, 'error');
      return;
    }

    if (!res.ok || !j || !j.success) {
      throw new Error((j && j.error) || 'Update failed');
    }

    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
    loadStudents();
    showToast('Student updated', 'success');
  }catch(err){ showToast('Update failed: ' + (err.message || err), 'error'); }
  finally{ btn.disabled = false; }
});

// Archive flow
let _archiveTarget = null;
function openArchiveModal(idNumber, studentName){
  _archiveTarget = idNumber;
  const displayName = studentName || idNumber || '';
  document.getElementById('archiveMessage').textContent = displayName + '?';
  const _cause = document.getElementById('archiveCause');
  if (_cause){ _cause.value = ''; _cause.classList.remove('is-invalid'); }
  // set current year as default
  const _year = document.getElementById('archiveYear');
  if (_year) _year.value = String(new Date().getFullYear());
  const m = new bootstrap.Modal(document.getElementById('archiveModal'));
  m.show();
}
// Clear invalid state when user changes selection
const __archiveCauseEl = document.getElementById('archiveCause');
if (__archiveCauseEl){ __archiveCauseEl.addEventListener('change', function(){ this.classList.remove('is-invalid'); }); }

document.getElementById('confirmArchiveBtn').addEventListener('click', async function(){
  if (!_archiveTarget) return;
  const btn = this; btn.disabled = true;
  const causeEl = document.getElementById('archiveCause');
  const cause = causeEl ? causeEl.value.trim() : '';
  // enforce required cause
  if (!cause){
    if (causeEl){ causeEl.classList.add('is-invalid'); causeEl.focus(); }
    btn.disabled = false;
    return;
  }
  const year = document.getElementById('archiveYear').value.trim();
  const payload = { ID_Number: _archiveTarget };
  payload.cause = cause;
  if (year) payload.year = year;
  try{
    const res = await fetch('../api/admin_students.php', { method: 'DELETE', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const j = await res.json();
    if (!j.success) throw new Error(j.error || 'Archive failed');
    bootstrap.Modal.getInstance(document.getElementById('archiveModal')).hide();
    loadStudents();
    showToast('Student archived', 'success');
  }catch(err){ showToast('Archive failed: ' + (err.message || err), 'error'); }
  finally{ btn.disabled = false; _archiveTarget = null; }
});
</script>
</body>
</html>