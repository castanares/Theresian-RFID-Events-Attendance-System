<?php
require_once __DIR__ . '/../auth.php';
require_login('../');

// Server-side: attempt to populate the logged-in user's department so the input always shows a value
$dept_id = null;
$dept_name = null;
if (session_status() === PHP_SESSION_NONE) session_start();
// include DB connection if available
$dbPath = __DIR__ . '/../api/dbcon.php';
if (file_exists($dbPath)) {
  require_once $dbPath; // provides $conn (mysqli)
}

if (!empty($_SESSION['user_id']) && isset($conn) && ($conn instanceof mysqli)) {
  // helper to find a column in a table (exact or fuzzy)
  $findColLocal = function($conn,$table,$cands){
    $res = $conn->query("SHOW COLUMNS FROM `".$conn->real_escape_string($table)."`");
    if (!$res) return null;
    while($r=$res->fetch_assoc()){
      foreach($cands as $c) if (strcasecmp($r['Field'],$c)===0) return $r['Field'];
    }
    $res->data_seek(0);
    while($r=$res->fetch_assoc()){
      $f=strtolower($r['Field']);
      foreach($cands as $c) if (strpos($f,strtolower($c))!==false) return $r['Field'];
    }
    return null;
  };

  $userTable = 'user';
  // detect department column as before
  $deptCol = $findColLocal($conn,$userTable,['department_id','dept_id','departments_id','department','dept','department_name']);

  // gather available columns in the user table so we can try multiple matching keys
  $availableFields = [];
  $resFields = $conn->query("SHOW COLUMNS FROM `".$conn->real_escape_string($userTable)."`");
  if ($resFields) {
    while($rf = $resFields->fetch_assoc()) $availableFields[] = $rf['Field'];
  }

  // candidate identifier columns to attempt matching against the session user value
  $candidateIdCols = ['username','user_name','user','id','user_id','email','email_address'];
  $foundIdCols = [];
  $lowerFields = array_map('strtolower',$availableFields);
  foreach($candidateIdCols as $c) {
    foreach($availableFields as $af) {
      if (strcasecmp($af,$c) === 0 || strpos(strtolower($af), strtolower($c)) !== false) { $foundIdCols[] = $af; break; }
    }
  }

  // If we have both a dept column and at least one id-like column, try a multi-column WHERE (OR) to find the user row
  if ($deptCol && count($foundIdCols) > 0) {
    // build WHERE clause like: `col1` = ? OR `col2` = ? ...
    $whereParts = array_map(function($c){ return "`$c` = ?"; }, $foundIdCols);
    $sql = "SELECT `$deptCol` FROM `$userTable` WHERE (" . implode(' OR ', $whereParts) . ") LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
      // bind same session value for each placeholder
      $vals = array_fill(0, count($foundIdCols), (string)($_SESSION['user_id'] ?? ''));
      $types = str_repeat('s', count($vals));
      $bindParams = array_merge([$types], $vals);
      // mysqli requires references
      $refs = [];
      foreach($bindParams as $k => $v) $refs[$k] = &$bindParams[$k];
      call_user_func_array([$stmt, 'bind_param'], $refs);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($r = $res->fetch_assoc()) {
        $dept_id = $r[$deptCol];
      }
      $stmt->close();
    }
  }

  // try to resolve department name from departments table
  if ($dept_id !== null) {
    $deptTableCandidates = ['departments','department','dept','tbl_departments'];
    foreach($deptTableCandidates as $dt){
      $res2 = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($dt) . "'");
      if (!$res2) continue;
      if ($res2->num_rows === 0) continue;
      $idColCandidates = ['id','dept_id','Dept_ID','departments_id','Departments_ID'];
      $nameColCandidates = ['name','Dept_name','dept_name','dept_Acronym','dept_acronym','acronym','department_name'];
      $dIdCol = $findColLocal($conn,$dt,$idColCandidates);
      $dNameCol = $findColLocal($conn,$dt,$nameColCandidates);
      if (!$dIdCol) $dIdCol = $findColLocal($conn,$dt,['id']);
      if (!$dNameCol) $dNameCol = $findColLocal($conn,$dt,['name']);
      if ($dIdCol) {
        $sql2 = "SELECT ".($dNameCol ? "`$dNameCol`" : "*") ." FROM `$dt` WHERE `$dIdCol` = ? LIMIT 1";
        $stmt2 = $conn->prepare($sql2);
        if ($stmt2) {
          $stmt2->bind_param('s',$dept_id);
          $stmt2->execute();
          $r2 = $stmt2->get_result()->fetch_assoc();
          if ($r2) {
            if ($dNameCol && isset($r2[$dNameCol])) $dept_name = $r2[$dNameCol];
            else {
              foreach([$dNameCol,'name','Dept_name','dept_name','dept_Acronym'] as $c) if ($c && isset($r2[$c])) { $dept_name = $r2[$c]; break; }
            }
          }
          $stmt2->close();
        }
      }
      if ($dept_name !== null) break;
    }
  }
}
?>
<?php
// opt-in debug overlay: visit this page with ?debug=1 to see what was detected
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
  echo '<div style="position:fixed;left:1rem;top:1rem;z-index:9999;background:#fff;border:1px solid #ccc;padding:10px;font-family:monospace;line-height:1.2;">';
  echo '<strong>DEBUG</strong><br>';
  echo 'session_user_id: '.htmlspecialchars((string)($_SESSION['user_id'] ?? ''), ENT_QUOTES).'<br>';
  echo 'dept_id: '.htmlspecialchars((string)($dept_id ?? ''), ENT_QUOTES).'<br>';
  echo 'dept_name: '.htmlspecialchars((string)($dept_name ?? ''), ENT_QUOTES).'<br>';
  echo 'user idCol: '.htmlspecialchars((string)($idCol ?? ''), ENT_QUOTES).'<br>';
  echo 'user deptCol: '.htmlspecialchars((string)($deptCol ?? ''), ENT_QUOTES).'<br>';
  echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

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
        margin-left:7rem;
        /* min-height: 520px; */
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

      /* Pagination */
      .pagination .page-item.active .page-link {
        background-color: maroon;
        border-color: maroon;
        color: #fff;
      }
      .pagination .page-link {
        color: maroon;
      }
      .pagination .page-link:hover {
        color: #6d0000;
        background-color: #ffe5e5;
        border-color: #d9b0b0;
      }
      .pagination .page-item.disabled .page-link {
        color: #aaa;
      }
      .rows-per-page-label { font-size: 0.875rem; color: #555; }

      /* Small components */
      .avatar-sm { width:50px; height:40px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-weight:700; }
      .avatar-bg { background: linear-gradient(135deg,#7a1414,#a02b2b); }

      @media (max-width: 576px) {
        .search-input { margin-bottom: 0.5rem; }
      }
    </style>
  </head>
<body>
<?php include 'sidebar.php'; ?>
<?php $page_heading = 'Students'; include 'navbar.php'; ?>

<section class="p-4">
  <div class="container-fluid">
    <!-- Controls: search and add placed outside the card for clearer layout -->
    <div class="row align-items-center mb-3">
      <div class="col-12 col-md-6">
        <!-- left side reserved for breadcrumbs or page info if needed -->
      </div>
      <div class="col-12 col-md-6 d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
        <div class="search-input me-2" style="min-width:220px;">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input id="searchInput" class="form-control" placeholder="Search students by name, id, dept...">
          </div>
        </div>
        <button class="btn btn-maroon" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg me-1"></i>Add Student</button>
      </div>
    </div>

    <div class="page-card">
      <div class="row align-items-center mb-3">
        <div class="col-12">
          <h4 class="m-0">Students</h4>
        </div>
      </div>

    

      <div class="table-responsive">
        <table id="studentsTable" class="table table-hover align-middle">
        <thead class="table-maroon">
          <tr>
            <th>#</th>
            <th>ID Number</th>
            <th>Student Name</th>
            <th>Course</th>
            <th>RFID Value</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <!-- rows loaded from API -->
        </tbody>
        </table>
      </div>
      <!-- Pagination controls -->
      <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="rows-per-page-label">Rows per page:</span>
          <select id="rowsPerPage" class="form-select form-select-sm" style="width:auto;">
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
          <span id="paginationInfo" class="rows-per-page-label ms-2"></span>
        </div>
        <nav>
          <ul id="paginationControls" class="pagination pagination-sm mb-0"></ul>
        </nav>
      </div>
    </div>
  </div>
</section>

<!-- Toast container for notifications -->
<div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3" style="z-index:1080">
  <div id="toastContainer"></div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form id="addStudentForm">
        <div class="modal-header" style="background-color: maroon; color: white;">
          <h5 class="modal-title">Add New Student</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Student ID Number</label>
            <input type="number" name="ID_Number" class="form-control" placeholder="e.g., 4227300" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Full Name</label>
            <input name="Student_name" class="form-control" placeholder="LASTNAME, FIRSTNAME MIDDLENAME" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Department</label>
            <input type="text" name="Dept" id="dept" class="form-control" readonly value="<?php echo htmlspecialchars($dept_name ?? $dept_id ?? '', ENT_QUOTES); ?>">
            <!-- hidden department id (submitted) -->
            <input type="hidden" name="departments_id" id="departments_id" value="<?php echo htmlspecialchars($dept_id ?? '', ENT_QUOTES); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Course</label>
            <input name="Course" class="form-control" placeholder="e.g., BSIT" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">RFID Card Number</label>
            <input name="RFID" class="form-control" placeholder="Scan RFID card" required>
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

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script>
  // Search handled inside pagination logic (see below)
</script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const tableBody = document.querySelector('#studentsTable tbody');
  const form = document.getElementById('addStudentForm');
  const addBtn = document.getElementById('addStudentBtn');

  function esc(s){ return String(s===undefined||s===null ? '' : s); }

  const departmentsId = document.getElementById('departments_id') ? document.getElementById('departments_id').value : '';

  // --- Pagination state ---
  let allRows = [];        // all rendered TR elements
  let filteredRows = [];   // rows visible after search
  let currentPage = 1;

  function getRowsPerPage() {
    return parseInt(document.getElementById('rowsPerPage').value, 10) || 10;
  }

  function renderPage() {
    const rpp = getRowsPerPage();
    const total = filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(total / rpp));
    if (currentPage > totalPages) currentPage = totalPages;
    const start = (currentPage - 1) * rpp;
    const end = start + rpp;

    // Show/hide rows and fix # numbering
    allRows.forEach(tr => tr.style.display = 'none');
    filteredRows.forEach((tr, i) => {
      tr.style.display = (i >= start && i < end) ? '' : 'none';
      // update # cell to reflect overall position
      if (tr.cells[0]) tr.cells[0].textContent = i + 1;
    });

    // Info text
    const infoEl = document.getElementById('paginationInfo');
    if (infoEl) {
      if (total === 0) {
        infoEl.textContent = 'No results';
      } else {
        infoEl.textContent = `Showing ${start + 1}–${Math.min(end, total)} of ${total}`;
      }
    }

    // Build pagination buttons
    const ul = document.getElementById('paginationControls');
    ul.innerHTML = '';
    const maxBtns = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxBtns / 2));
    let endPage = Math.min(totalPages, startPage + maxBtns - 1);
    if (endPage - startPage < maxBtns - 1) startPage = Math.max(1, endPage - maxBtns + 1);

    const mkLi = (label, page, disabled, active) => {
      const li = document.createElement('li');
      li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
      const a = document.createElement('a');
      a.className = 'page-link'; a.href = '#'; a.innerHTML = label;
      if (!disabled) a.addEventListener('click', ev => { ev.preventDefault(); currentPage = page; renderPage(); });
      li.appendChild(a); ul.appendChild(li);
    };

    mkLi('&laquo;', 1, currentPage === 1, false);
    mkLi('&lsaquo;', currentPage - 1, currentPage === 1, false);
    for (let p = startPage; p <= endPage; p++) mkLi(p, p, false, p === currentPage);
    mkLi('&rsaquo;', currentPage + 1, currentPage === totalPages, false);
    mkLi('&raquo;', totalPages, currentPage === totalPages, false);
  }

  function applySearch() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    filteredRows = allRows.filter(tr => {
      const cells = [...tr.querySelectorAll('td')].slice(1, 5);
      return cells.map(td => td.textContent.toLowerCase()).join(' ').includes(q);
    });
    currentPage = 1;
    renderPage();
  }

  // Wire up search and rows-per-page
  document.getElementById('searchInput').addEventListener('input', applySearch);
  document.getElementById('rowsPerPage').addEventListener('change', () => { currentPage = 1; renderPage(); });

  async function loadStudents(){
    tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';
    try{
      const params = new URLSearchParams();
      if (departmentsId) params.set('departments_id', departmentsId);
      params.set('status','active');
      const url = '../api/students.php' + (params.toString() ? '?'+params.toString() : '');
      const res = await fetch(url);
      const json = await res.json();
      if (!json.success){ tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Error loading students</td></tr>'; return; }
      const rows = json.data || [];
      tableBody.innerHTML = '';
      allRows = [];
      if (rows.length === 0){
        const emptyTr = document.createElement('tr');
        emptyTr.innerHTML = '<td colspan="6" class="text-center text-muted">No students found</td>';
        tableBody.appendChild(emptyTr);
        allRows = [emptyTr];
        filteredRows = [emptyTr];
        renderPage();
        return;
      }
      rows.forEach((r) => {
        const tr = document.createElement('tr');
        const idNum = esc(r.ID_Number ?? r.id ?? r.ID ?? '');
        const name = esc(r.Student_name ?? r.student_name ?? r.name ?? '');
        const course = esc(r.Course ?? r.course ?? '');
        const rfid = esc(r.RFID ?? r.rfid ?? '');
        const dept = esc(r.departments_id ?? '');
        tr.innerHTML = `
          <td></td>
          <td>${idNum}</td>
          <td>${name}</td>
          <td>${course}</td>
          <td>${rfid}</td>
          <td>
            <button class="btn btn-sm btn-outline-primary edit-btn me-1" data-id="${encodeURIComponent(idNum)}" data-name="${encodeURIComponent(name)}" data-course="${encodeURIComponent(course)}" data-rfid="${encodeURIComponent(rfid)}" data-dept="${encodeURIComponent(dept)}">Edit</button>
            <button class="btn btn-sm btn-outline-danger archive-btn" data-id="${encodeURIComponent(idNum)}">Archive</button>
          </td>`;
        tableBody.appendChild(tr);
        allRows.push(tr);
      });
      filteredRows = [...allRows];
      currentPage = 1;
      renderPage();
    }catch(err){ console.error(err); tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Error loading students</td></tr>'; }
  }
  // expose to global so other scripts (archive modal handlers) can call it
  window.loadStudents = loadStudents;

  // handle add student
  // form can act as Add or Edit depending on editMode
  let editMode = false;
  let editingId = null; // ID_Number of the student being edited

  if (form){
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      addBtn.disabled = true;
      addBtn.textContent = editMode ? 'Saving...' : 'Adding...';
      const fd = new FormData(form);
      const payload = {
        ID_Number: fd.get('ID_Number'),
        Student_name: fd.get('Student_name'),
        departments_id: fd.get('departments_id'),
        Course: fd.get('Course'),
        RFID: fd.get('RFID')
      };
      try{
        let res, j;
        if (editMode && editingId){
          // Send the old ID_Number for the WHERE clause, and new ID_Number in a separate field
          const newId = fd.get('ID_Number');
          payload.old_ID_Number = editingId; // API will use this to find the record
          // Only update ID_Number if it changed
          if (newId !== editingId) {
            payload.ID_Number = newId;
          } else {
            payload.ID_Number = editingId;
          }
          res = await fetch('../api/students.php', { method: 'PUT', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          j = await res.json();
        } else {
          res = await fetch('../api/students.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          j = await res.json();
        }
        if (j.success){
          // close modal
          const modalEl = document.getElementById('addModal');
          const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
          modal.hide();
          form.reset();
          // reset edit state
          editMode = false; editingId = null;
          await loadStudents();
          showToast(editMode ? 'Student updated successfully' : 'Student added successfully', 'success');
        } else {
          showToast('Error: '+(j.error||'unknown'), 'danger');
        }
      }catch(err){ console.error(err); showToast('Network error', 'danger'); }
      addBtn.disabled = false;
      if (!editMode) addBtn.textContent = 'Add';
    });
  }

  // delegate archive clicks
  tableBody.addEventListener('click', async function(e){
    const archiveBtn = e.target.closest('.archive-btn');
    if (archiveBtn) {
      const id = decodeURIComponent(archiveBtn.dataset.id || '');
      // prefer showing the student name in the modal (column 3 in the row) while keeping the id for the request
      const row = archiveBtn.closest('tr');
      const name = row && row.cells[2] ? row.cells[2].textContent.trim() : id;
      // open archive modal to pick cause and confirm
      const archiveModalEl = document.getElementById('archiveModal');
      const archiveModal = bootstrap.Modal.getInstance(archiveModalEl) || new bootstrap.Modal(archiveModalEl);
      archiveModalEl.querySelector('#archive_id').textContent = name;
      archiveModalEl.querySelector('select[name="cause"]').value = '';
      archiveModalEl.querySelector('input[name="year"]').value = new Date().getFullYear();
      // keep the id on the element dataset for the actual API call
      archiveModalEl.dataset.id = id;
      archiveModal.show();
      return;
    }

    // handle edit button
    const editBtn = e.target.closest('.edit-btn');
    if (editBtn){
      const id = decodeURIComponent(editBtn.dataset.id || '');
      const name = decodeURIComponent(editBtn.dataset.name || '');
      const course = decodeURIComponent(editBtn.dataset.course || '');
      const rfid = decodeURIComponent(editBtn.dataset.rfid || '');
      const deptId = decodeURIComponent(editBtn.dataset.dept || '');
      // populate edit form
      const editForm = document.getElementById('editStudentForm');
      editForm.elements['ID_Number'].value = id;
      editForm.elements['Student_name'].value = name;
      editForm.elements['Course'].value = course;
      editForm.elements['RFID'].value = rfid;
      editForm.elements['departments_id_edit'].value = deptId || '';
      // Store original ID in hidden field for API update
      editForm.elements['original_ID_Number'].value = id;
      
      // Fetch all departments and find the matching one
      if (deptId) {
        fetch('../api/departments.php')
          .then(r => r.json())
          .then(j => {
            if (j.success && j.data && j.data.length > 0) {
              const dept = j.data.find(d => (d.id || d.Dept_ID || d.ID) == deptId);
              if (dept) {
                const deptName = dept.name || dept.Dept_name || dept.dept_name || deptId;
                editForm.elements['Dept'].value = deptName;
              } else {
                editForm.elements['Dept'].value = deptId;
              }
            } else {
              editForm.elements['Dept'].value = deptId;
            }
          })
          .catch(() => {
            editForm.elements['Dept'].value = deptId;
          });
      } else {
        editForm.elements['Dept'].value = '';
      }
      // show edit modal
      const editModal = new bootstrap.Modal(document.getElementById('editModal'));
      editModal.show();
      return;
    }
  });

  // reset add modal when hidden
  const modalEl = document.getElementById('addModal');
  modalEl.addEventListener('hidden.bs.modal', function(){
    // clear form and reset edit flags
    form.reset();
    editMode = false;
    editingId = null;
  });

  // reset edit modal when hidden
  const editModalEl = document.getElementById('editModal');
  editModalEl.addEventListener('hidden.bs.modal', function(){
    document.getElementById('editStudentForm').reset();
  });

  // initial load
  loadStudents();
}
);
</script>

<!-- Edit Student Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form id="editStudentForm">
        <input type="hidden" name="original_ID_Number" id="original_ID_Number">
        <div class="modal-header" style="background-color: maroon; color: white;">
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
            <input name="Student_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Department</label>
            <input type="text" name="Dept" id="deptEdit" class="form-control" readonly>
            <input type="hidden" name="departments_id" id="departments_id_edit">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Course</label>
            <input name="Course" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">RFID Card Number</label>
            <input name="RFID" class="form-control">
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

<!-- Archive modal -->
<div class="modal fade" id="archiveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Archive Student</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Archive student <strong id="archive_id"></strong></p>
        <div class="mb-2">
          <label class="form-label">Cause</label>
          <select id="archiveCause" name="cause" class="form-select">
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
          <input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button id="confirmArchiveBtn" type="button" class="btn btn-maroon">Archive</button>
      </div>
    </div>
  </div>
</div>

<script>
// toast helper
function showToast(msg, type='success', timeout=3000){
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const id = 't' + Date.now();
  const bg = type === 'danger' ? 'bg-danger text-white' : (type === 'success' ? 'bg-success text-white' : 'bg-info text-white');
  const html = `
    <div id="${id}" class="toast ${bg}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="${timeout}">
      <div class="d-flex">
        <div class="toast-body">${msg}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>`;
  container.insertAdjacentHTML('beforeend', html);
  const el = document.getElementById(id);
  const t = new bootstrap.Toast(el);
  t.show();
  el.addEventListener('hidden.bs.toast', ()=> el.remove());
}

// Handle edit student form submission
document.getElementById('editStudentForm').addEventListener('submit', async function(e){
  e.preventDefault();
  const btn = document.getElementById('saveEditBtn');
  btn.disabled = true;
  btn.textContent = 'Saving...';
  const form = e.target;
  const oldId = form.elements['original_ID_Number'].value;
  const newId = form.elements['ID_Number'].value;
  const payload = {
    old_ID_Number: oldId,
    ID_Number: newId,
    Student_name: form.elements['Student_name'].value.trim(),
    departments_id: form.elements['departments_id_edit'].value,
    Course: form.elements['Course'].value.trim(),
    RFID: form.elements['RFID'].value.trim()
  };
  try{
    const res = await fetch('../api/students.php', { method: 'PUT', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const j = await res.json();
    if (!j.success) throw new Error(j.error || 'Update failed');
    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
    form.reset();
    await loadStudents();
    showToast('Student updated', 'success');
  }catch(err){
    showToast('Update failed: ' + (err.message || err), 'danger');
  }finally{
    btn.disabled = false;
    btn.textContent = 'Save Changes';
  }
});

  document.getElementById('confirmArchiveBtn').addEventListener('click', async function(){
    const modalEl = document.getElementById('archiveModal');
    const id = modalEl.dataset.id;
    const cause = modalEl.querySelector('select[name="cause"]').value;
    const year = modalEl.querySelector('input[name="year"]').value;
    if(!id) return;
    if(!cause) {
      const causeEl = modalEl.querySelector('select[name="cause"]');
      causeEl.classList.add('is-invalid');
      causeEl.focus();
      return;
    }
    // call API DELETE with cause and year
    const btn = document.getElementById('confirmArchiveBtn');
    btn.disabled = true; btn.textContent = 'Archiving...';
    try{
      const res = await fetch('../api/students.php', { method: 'DELETE', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ ID_Number: id, cause: cause, year: year }) });
      let j = null;
      if (res.ok){
        try { j = await res.json(); } catch(e) { /* non-json but ok - treat as success */ j = { success: true }; }
      } else {
        try { j = await res.json(); } catch(e){ j = { success: false, error: res.statusText || 'HTTP error' }; }
      }
      if(j && j.success){
        showToast('Student archived');
        const modal = bootstrap.Modal.getInstance(modalEl); if(modal) modal.hide();
        await loadStudents();
      } else {
        showToast('Error: '+(j.error||'unknown'),'danger');
      }
    }catch(err){ console.error(err); showToast('Network error: '+(err.message||err),'danger'); }
    finally{ btn.disabled = false; btn.textContent = 'Archive'; }
  });
</script>

<script>
  // Clear invalid state when user changes selection
  document.getElementById('archiveCause').addEventListener('change', function() {
    this.classList.remove('is-invalid');
  });
</script>
</body>
</html>
