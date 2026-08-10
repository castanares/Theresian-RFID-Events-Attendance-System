<?php require_once __DIR__ . '/../auth.php'; require_login('../'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - EVENT Attendance</title>
    <link rel="stylesheet" href="../styles/dash_styles.css">
    <link rel="stylesheet" href="../styles/side_styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001" />
</head>
<style>
  .btn-maroon { 
    background-color: maroon; 
    color: white; 
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
  }
  .btn-maroon:hover { 
    background-color: #600000; 
    color: white; 
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  }
  
  .btn-outline-maroon {
    border: 2px solid maroon;
    color: maroon;
    background: transparent;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
  }
  
  .btn-outline-maroon:hover {
    background-color: maroon;
    color: white;
    transform: translateY(-1px);
  }
  
  .profile-container {
    max-width: 1700px;
    margin: 2rem auto;
    margin-left:10rem;
    padding: 0 1rem;
  }
  
  .profile-header {
    background: linear-gradient(135deg, #800000 0%, #a00000 100%);
    color: white;
    padding: 2rem 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    position: relative;
    overflow: hidden;
  }
  
  .profile-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    transform: translate(50%, -50%);
  }
  
  .profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.3);
    background: rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    margin-bottom: 1.5rem;
    position: relative;
    z-index: 1;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
  }
  
  .profile-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border: 1px solid rgba(0,0,0,0.05);
  }
  
  .card-header-maroon {
    background: linear-gradient(45deg, maroon, #a00000);
    color: white;
    padding: 1rem 2rem;
    margin: -2rem -2rem 2rem -2rem;
    border-radius: 15px 15px 0 0;
  }
  
  .form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
  }
  
  .form-control:focus {
    border-color: maroon;
    box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.1);
  }
  
  .form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
  }
  
  .info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #f0f0f0;
  }
  
  .info-item:last-child {
    border-bottom: none;
  }
  
  .info-label {
    font-weight: 600;
    color: #666;
  }
  
  .info-value {
    color: #333;
    font-weight: 500;
  }
  
  .badge-maroon {
    background-color: maroon;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
  }
  
  .badge-success {
    background-color: #28a745;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
  }
  
  .stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border-left: 4px solid maroon;
  }
  
  .stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: maroon;
    margin-bottom: 0.5rem;
  }
  
  .stat-label {
    color: #666;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
  
  .event-item {
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
  }
  
  .event-item:hover {
    border-color: maroon;
    box-shadow: 0 4px 12px rgba(128, 0, 0, 0.1);
  }
  
  .text-maroon {
    color: maroon !important;
  }
  
  @media (max-width: 768px) {
    .profile-container {
      margin: 1rem auto;
      padding: 0 0.5rem;
    }
    
    .profile-header {
      padding: 2rem 1rem;
      text-align: center;
    }
    
    .profile-card {
      padding: 1.5rem;
    }
    
    .card-header-maroon {
      margin: -1.5rem -1.5rem 2rem -1.5rem;
      padding: 1rem 1.5rem;
    }
  }
</style>

<body>
<?php include 'sidebar.php'; ?>
<?php $page_heading = 'User Profile'; include 'navbar.php'; ?>

<div class="profile-container">
  <!-- Profile Header -->
  <div class="profile-header">
    <div class="row align-items-center">
      <div class="col-md-auto text-center text-md-start">
        <div class="profile-avatar mx-auto mx-md-0" id="profileAvatar" style="cursor:pointer;" title="Click to change profile picture">
          <i class="bi bi-person-fill" id="profileAvatarIcon"></i>
        </div>
        <input type="file" id="profilePicInput" accept="image/*" style="display:none">
      </div>
      <div class="col-md text-center text-md-start">
  <h2 class="mb-2" id="deptHeaderName">Department</h2>
  <p class="mb-3 opacity-75" id="deptHeaderSubtitle"></p>
        <div class="d-flex flex-column flex-md-row gap-2 justify-content-center justify-content-md-start">
          <span class="badge-success"><i class="bi bi-check-circle me-1"></i> Active Department</span>
          <span class="badge badge-light"><i class="bi bi-calendar me-1"></i> Last activity: Today, 10:15 AM</span>
        </div>
      </div>
      <div class="col-md-auto mt-3 mt-md-0">
        <button class="btn btn-light" onclick="editProfile()" id="editBtn">
          <i class="bi bi-pencil me-1"></i> Edit Profile
        </button>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Department Information -->
    <div class="col-lg-8">
      <div class="profile-card">
        <div class="card-header-maroon">
          <h5 class="mb-0"><i class="bi bi-building me-2"></i>Department Information</h5>
        </div>
        
        <form id="profileForm">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Department Name</label>
              <input type="text" class="form-control" value="" disabled id="deptName" readonly>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Department Code</label>
              <input type="text" class="form-control" value="" disabled id="deptCode" readonly>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Contact Email</label>
              <input type="email" class="form-control" value="" disabled id="deptEmail">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Contact Phone</label>
              <input type="tel" class="form-control" value="" disabled id="deptPhone">
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Department Head</label>
            <input type="text" class="form-control" value="" disabled id="deptHead">
          </div>
          
          
          
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" rows="3" disabled id="deptDescription"></textarea>
          </div>
          
          <div id="profileActions" style="display: none;">
            <button type="button" class="btn btn-maroon me-2" onclick="saveProfile()">
              <i class="bi bi-check-lg me-1"></i> Save Changes
            </button>
            <button type="button" class="btn btn-outline-maroon" onclick="cancelEdit()">
              <i class="bi bi-x-lg me-1"></i> Cancel
            </button>
          </div>
        </form>
      </div>

      <!-- Security: Change Password -->
      <div class="profile-card" id="profileInfo">
        <div class="card-header-maroon">
          <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Security</h5>
        </div>

        <div class="settings-item d-flex align-items-center">
          <div>
            <h6 class="mb-1">Change Password</h6>
            <p class="text-muted mb-0">Update your account password</p>
          </div>
          <div class="ms-auto">
            <button class="btn btn-outline-maroon btn-sm" data-bs-toggle="modal" data-bs-target="#passwordModal">
              <i class="bi bi-key me-1"></i> Change
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics & Quick Actions -->
    <div class="col-lg-4">
      <!-- Department Statistics -->
      <div class="profile-card">
        <div class="card-header-maroon">
          <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Department Statistics</h5>
        </div>
        
        <div class="row">
          <div class="col-6 mb-3">
            <div class="stat-card">
              <div class="stat-number" id="statActiveEvents">0</div>
              <div class="stat-label">Active Events</div>
            </div>
          </div>
          <div class="col-6 mb-3">
            <div class="stat-card">
              <div class="stat-number" id="statTotalStudents">0</div>
              <div class="stat-label">Total Students</div>
            </div>
          </div>
          <div class="col-6 mb-3">
            <div class="stat-card">
              <div class="stat-number" id="statAttendanceRate">0%</div>
              <div class="stat-label">Attendance Rate</div>
            </div>
          </div>
          <div class="col-6 mb-3">
            <div class="stat-card">
              <div class="stat-number" id="statThisMonth">0</div>
              <div class="stat-label">This Month</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="profile-card">
        <div class="card-header-maroon">
          <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
        </div>
        
        <div class="d-grid gap-2">
          <a href="Add_student.php" class="btn btn-maroon" onclick="setActiveSidebar('students')">
            <i class="bi bi-person-plus me-2"></i>Add Student
          </a>
          <a href="index.php" class="btn btn-outline-maroon" onclick="setActiveSidebar('home')">
            <i class="bi bi-calendar-event me-2"></i>View Events
          </a>
          <a href="scanlog.php" class="btn btn-outline-maroon" onclick="setActiveSidebar('scanlog')">
            <i class="bi bi-qr-code-scan me-2"></i>Scan Logs
          </a>
          <!-- <button class="btn btn-outline-maroon" onclick="generateReport()">
            <i class="bi bi-file-earmark-text me-2"></i>Generate Report
          </button> -->
        </div>
      </div>

     
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Function to set active sidebar state
function setActiveSidebar(key) {
  try {
    localStorage.setItem('activeSidebar', key);
  } catch(e) {
    // Handle localStorage errors silently
  }
}

// Simple Bootstrap toast notifier (matches admin UX)
function notify(type, message, delay = 3000) {
  const toastEl = document.createElement('div');
  const bg = (type === 'success') ? 'bg-success' : (type === 'warning') ? 'bg-warning text-dark' : (type === 'info') ? 'bg-info text-dark' : 'bg-danger';
  toastEl.className = `toast align-items-center text-white ${bg} border-0 position-fixed top-0 end-0 m-3`;
  toastEl.setAttribute('role', 'alert');
  toastEl.style.zIndex = 1080;
  toastEl.innerHTML = `
    <div class="d-flex">
      <div class="toast-body">${message}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  `;
  document.body.appendChild(toastEl);
  const t = new bootstrap.Toast(toastEl, { autohide: true, delay });
  t.show();
}

// Normalize image path so it always loads correctly from /new/
function normalizePath(p) {
  if (!p) return '';
  const s = String(p);
  if (/^https?:\/\//i.test(s)) return s;
  if (s.startsWith('/')) return s;
  return '/Theresian-RFID-Events-Attendance-System/' + s.replace(/^\/+/, '');
}

function editProfile() {
  // Enable form fields
  const fields = ['deptName', 'deptCode', 'deptEmail', 'deptPhone', 'deptHead', 'deptDescription'];
  fields.forEach(fieldId => {
    document.getElementById(fieldId).disabled = false;
  });
  
  // Show action buttons
  document.getElementById('profileActions').style.display = 'block';
  
  // Update edit button
  const editBtn = document.getElementById('editBtn');
  editBtn.innerHTML = '<i class="bi bi-x-lg me-1"></i> Cancel';
  editBtn.setAttribute('onclick', 'cancelEdit()');
}

function cancelEdit() {
  // Disable form fields
  const fields = ['deptName', 'deptCode', 'deptEmail', 'deptPhone', 'deptHead', 'deptDescription'];
  fields.forEach(fieldId => {
    document.getElementById(fieldId).disabled = true;
  });
  
  // Hide action buttons
  document.getElementById('profileActions').style.display = 'none';
  
  // Reset edit button
  const editBtn = document.getElementById('editBtn');
  editBtn.innerHTML = '<i class="bi bi-pencil me-1"></i> Edit Profile';
  editBtn.setAttribute('onclick', 'editProfile()');
}

async function saveProfile() {
  try {
    const payload = {
      contact_email: document.getElementById('deptEmail').value.trim(),
      contact_phone: document.getElementById('deptPhone').value.trim(),
      department_head: document.getElementById('deptHead').value.trim(),
  // office_location removed
      description: document.getElementById('deptDescription').value.trim(),
    };
    // Pass dept_id for admins who might not have a bound department
    const params = new URLSearchParams(window.location.search);
    const deptArg = params.get('dept_id');
    if (deptArg) payload.dept_id = parseInt(deptArg, 10);
    const res = await fetch('../api/profile.php?type=department' + (deptArg ? ('&dept_id=' + encodeURIComponent(deptArg)) : ''), {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (json.success) {
      notify('success', 'Department profile updated successfully!');
      await loadDepartmentProfile();
      cancelEdit();
    } else {
      notify('danger', 'Failed to update: ' + (json.error || 'Unknown error'));
    }
  } catch (e) {
    notify('danger', 'Error: ' + e.message);
  }
}

function generateReport() {
  notify('info', 'Generating department activity report...');
}

// Settings toggle handlers (guarded — elements may not exist after UI changes)
(function(){
  const evNotif = document.getElementById('eventNotifications');
  if (evNotif) evNotif.addEventListener('change', function() {
    const message = this.checked ? 'Event notifications enabled!' : 'Event notifications disabled!';
    console.log(message);
  });

  const autoAp = document.getElementById('autoApprove');
  if (autoAp) autoAp.addEventListener('change', function() {
    const message = this.checked ? 'Auto-approve enabled!' : 'Auto-approve disabled!';
    console.log(message);
  });

  const emailRem = document.getElementById('emailReminders');
  if (emailRem) emailRem.addEventListener('change', function() {
    const message = this.checked ? 'Email reminders enabled!' : 'Email reminders disabled!';
    console.log(message);
  });
})();

// Guard optional QR access toggle; element may not exist
(function(){
  const qrEl = document.getElementById('qrAccess');
  if (qrEl) {
    qrEl.addEventListener('change', function() {
      const message = this.checked ? 'QR code access enabled!' : 'QR code access disabled!';
      console.log(message);
    });
  }
})();

// Avatar upload wiring
(function() {
  const avatar = document.getElementById('profileAvatar');
  const icon = document.getElementById('profileAvatarIcon');
  const fileInput = document.getElementById('profilePicInput');
  if (!avatar || !fileInput) return;

  // Click avatar to open file chooser
  avatar.addEventListener('click', () => fileInput.click());

  // Handle file selection and upload
  fileInput.addEventListener('change', async () => {
    try {
      const file = fileInput.files && fileInput.files[0];
      if (!file) return;
      const fd = new FormData();
      fd.append('profile_pic', file);
      const res = await fetch('../api/upload_profile_pic.php', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      });
      const json = await res.json();
  if (json && (json.success || json.path)) {
        const url = normalizePath(json.path || json.profile_pic || '');
        if (url) {
          avatar.style.backgroundImage = `url("${url}")`;
          avatar.style.backgroundSize = 'cover';
          avatar.style.backgroundPosition = 'center';
          avatar.style.backgroundRepeat = 'no-repeat';
          if (icon) icon.style.display = 'none';
        }
        notify('success', 'Profile picture updated.');
      } else {
        notify('danger', 'Failed to upload profile picture: ' + (json && json.error ? json.error : 'Unknown error'));
      }
    } catch (e) {
      notify('danger', 'Error uploading profile picture: ' + e.message);
    } finally {
      // reset input so same file can be selected again if needed
      fileInput.value = '';
    }
  });
})();

async function loadDepartmentProfile() {
  try {
    // 1) Try to show the CURRENT USER's department in the header immediately
    try {
      const meRes = await fetch('../api/me.php', { credentials: 'same-origin' });
      const meJson = await meRes.json();
      // Do NOT require meJson.logged; some sessions only set user_id
      if (meJson && meJson.department_info) {
        const dept = Array.isArray(meJson.department_info) ? meJson.department_info[0] : meJson.department_info;
        if (dept) {
          document.getElementById('deptHeaderName').textContent = dept.name || 'Department';
          document.getElementById('deptHeaderSubtitle').textContent = dept.acronym || '';
        }
      }
    } catch (_) { /* non-blocking */ }

    // 2) Load department profile data for the form (and optionally override header when viewing a specific dept)
    const params = new URLSearchParams(window.location.search);
    const deptArg = params.get('dept_id');
    const url = '../api/profile.php?type=department' + (deptArg ? ('&dept_id=' + encodeURIComponent(deptArg)) : '');
    const res = await fetch(url, { credentials: 'same-origin' });
    const json = await res.json();
    if (!json.success || !json.data) return;
    const d = json.data;
    window.__deptId = d.id || 0;
    // Populate fields
    document.getElementById('deptName').value = d.name || '';
    document.getElementById('deptCode').value = d.acronym || '';
    document.getElementById('deptEmail').value = d.contact_email || '';
    document.getElementById('deptPhone').value = d.contact_phone || '';
    document.getElementById('deptHead').value = d.department_head || '';
  // office_location removed from form
    document.getElementById('deptDescription').value = d.description || '';
    // Header behavior:
    // - If viewing a specific dept via ?dept_id, reflect that
    // - Else, if user's dept header wasn't set by api/me.php, fallback to API data
    const headerNameEl = document.getElementById('deptHeaderName');
    const headerSubEl = document.getElementById('deptHeaderSubtitle');
    if (deptArg) {
      headerNameEl.textContent = d.name || 'Department';
      headerSubEl.textContent = d.acronym || '';
    } else {
      const current = (headerNameEl.textContent || '').trim();
      if (!current || current.toLowerCase() === 'department') {
        headerNameEl.textContent = d.name || 'Department';
        headerSubEl.textContent = d.acronym || '';
      }
    }
  } catch (e) { /* ignore */ }
}

document.addEventListener('DOMContentLoaded', loadDepartmentProfile);

// Also load current user's avatar on DOM ready
document.addEventListener('DOMContentLoaded', async function() {
  try {
    const avatar = document.getElementById('profileAvatar');
    const icon = document.getElementById('profileAvatarIcon');
    if (!avatar) return;
    // Reuse admin profile endpoint to get current user info, including profile_pic
    const res = await fetch('../api/profile.php?type=admin', { credentials: 'same-origin' });
    const json = await res.json();
    if (json && json.success && json.data && json.data.profile_pic) {
      const url = normalizePath(json.data.profile_pic);
      avatar.style.backgroundImage = `url("${url}")`;
      avatar.style.backgroundSize = 'cover';
      avatar.style.backgroundPosition = 'center';
      avatar.style.backgroundRepeat = 'no-repeat';
      if (icon) icon.style.display = 'none';
    } else {
      // no profile pic: show icon
      if (icon) icon.style.display = '';
    }
  } catch (_) { /* ignore */ }
  // after avatar/profile load take a stab at loading stats
  try { if (typeof loadDepartmentStats === 'function') loadDepartmentStats(); } catch(_){}
});
</script>

<script>
// Load department statistics: active events, total students, attendance rate, events this month
async function loadDepartmentStats() {
  try {
    const deptId = window.__deptId || null;
    // fetch events (server returns participants per event)
    const eventsUrl = '../api/events.php' + (deptId ? ('?departments_id=' + encodeURIComponent(deptId)) : '');
    const evRes = await fetch(eventsUrl, { credentials: 'same-origin' });
    const evJson = await evRes.json();
    const events = (evJson && evJson.success && Array.isArray(evJson.data)) ? evJson.data : [];
    // determine deptId for current user (prefer explicit query param, else use api/me)
    let deptIdLocal = deptId;
    const isIncludedInDept = (e, dId) => {
      try {
        if (!dId) return false;
        if (e.departments_id && Number(e.departments_id) === Number(dId)) return true;
        if (Array.isArray(e.other_departments) && e.other_departments.map(x=>Number(x)).includes(Number(dId))) return true;
        if (e.departments && typeof e.departments === 'string' && e.departments.split(',').map(s=>s.trim()).includes(String(dId))) return true;
      } catch(_){ }
      return false;
    };

    if (!deptIdLocal) {
      try {
        const meRes = await fetch('../api/me.php', { credentials: 'same-origin' });
        const meJson = await meRes.json();
        if (meJson && meJson.department_info) {
          const md = Array.isArray(meJson.department_info) ? meJson.department_info[0] : meJson.department_info;
          deptIdLocal = md && (md.id || md.Dept_ID || md.dept_id || md.ID) ? (md.id || md.Dept_ID || md.dept_id || md.ID) : deptIdLocal;
        }
      } catch(_){ }
    }

    // Filter events for this department when deptIdLocal provided (match primary departments_id or other_departments array)
    const relevantEvents = (deptIdLocal && Number(deptIdLocal) > 0) ? events.filter(e => isIncludedInDept(e, deptIdLocal)) : events;

    // determine current semester (try to pick the semester whose start/end include current month)
    let currentSemesterId = null;
    let currentSemesterName = null;
    try {
      const semRes = await fetch('../api/semesters.php', { credentials: 'same-origin' });
      const semJson = await semRes.json();
      if (semJson && semJson.success && Array.isArray(semJson.data) && semJson.data.length) {
        const sems = semJson.data;
        const nowMonth = (new Date()).getMonth() + 1;
        for (const s of sems) {
          const sd = s.start_date ? parseInt(String(s.start_date).replace(/^0+/,''), 10) : null;
          const ed = s.end_date ? parseInt(String(s.end_date).replace(/^0+/,''), 10) : null;
          if (sd && ed && nowMonth >= sd && nowMonth <= ed) {
            currentSemesterId = s.id ?? s.ID ?? s.semester_id ?? null;
            currentSemesterName = s.name ?? s.semester ?? null;
            break;
          }
        }
        if (!currentSemesterId) {
          // fallback: use first (latest) semester row
          const s = sems[0];
          currentSemesterId = s.id ?? s.ID ?? s.semester_id ?? null;
          currentSemesterName = s.name ?? s.semester ?? null;
        }
      }
    } catch(_){}

    const activeDeptEvents = events.filter(e => {
      const s = (e.status||'').toString().toLowerCase();
      if (s === 'archived' || s === 'cancelled') return false;
      // ensure this event includes the user's department
      if (!deptIdLocal) return false;
      if (!isIncludedInDept(e, deptIdLocal)) return false;
      // ensure event belongs to current semester when available
      if (currentSemesterId) {
        const evSemId = e.semester_id ?? e.semester ?? e.semesterId ?? null;
        // match numeric ids or names
        if (evSemId === null || evSemId === undefined) return false;
        if (String(evSemId) === String(currentSemesterId)) return true;
        if (currentSemesterName && String(evSemId).toLowerCase() === String(currentSemesterName).toLowerCase()) return true;
        return false;
      }
      return true;
    });
    const activeEvents = activeDeptEvents.length;

    // Events this month: robust timestamp parsing
    function parseEventTs(ts) {
      if (ts === null || ts === undefined) return NaN;
      // numeric values (number or numeric string)
      if (typeof ts === 'number') {
        // if seconds -> convert to ms; if already ms, use directly
        return ts < 1e12 ? ts * 1000 : ts;
      }
      if (typeof ts === 'string' && /^\d+$/.test(ts)) {
        if (ts.length === 10) return Number(ts) * 1000;
        if (ts.length === 13) return Number(ts);
        const n = Number(ts);
        return n < 1e12 ? n * 1000 : n;
      }
      // otherwise try Date.parse on common date formats
      const parsed = Date.parse(ts);
      return isNaN(parsed) ? NaN : parsed;
    }

    const now = new Date();
  const monthName = now.toLocaleString(undefined, { month: 'long' });
    const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1).getTime();
    const endOfMonth = new Date(now.getFullYear(), now.getMonth()+1, 1).getTime();
    const eventsThisMonth = relevantEvents.filter(e => {
      const ts = e.timestamp ?? e.date ?? e.event_date ?? e.eventDate ?? e.created_at ?? null;
      const t = parseEventTs(ts);
      if (isNaN(t)) return false;
      return t >= startOfMonth && t < endOfMonth;
    }).length;
    // Today's scans: count unique attendance (Time_in) for department students for department events scheduled TODAY and not archived
    const startOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate()).getTime();
    const endOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate()+1).getTime();
    const nonArchivedEvents = relevantEvents.filter(e => { const s = (e.status||'').toString().toLowerCase(); return (s !== 'archived' && s !== 'cancelled'); });
    const eventsScheduledToday = nonArchivedEvents.filter(e => {
      // ensure the event includes the user's department
      if (!isIncludedInDept(e, deptIdLocal)) return false;
      const ts = e.timestamp ?? e.date ?? e.event_date ?? e.eventDate ?? null;
      const t = parseEventTs(ts);
      if (isNaN(t)) return false;
      return t >= startOfDay && t < endOfDay;
    });

    // Fetch students for this department to ensure scans count only department students
    let totalStudents = 0;
    const studentIdSet = new Set();
    const studentIdSamples = [];
    const normalizeId = (x) => {
      if (x === null || x === undefined) return '';
      let s = String(x).trim();
      s = s.replace(/^0+/, '');
      return s;
    };
    try {
      const studentsUrl = '../api/students.php' + (deptIdLocal ? ('?departments_id=' + encodeURIComponent(deptIdLocal) + '&status=active') : '?status=active');
      const stRes = await fetch(studentsUrl, { credentials: 'same-origin' });
      const stJson = await stRes.json();
      if (stJson && stJson.success && Array.isArray(stJson.data)) {
        const students = stJson.data;
        totalStudents = students.length;
        for (const s of students) {
          const fields = [s.ID_Number, s.id, s.ID, s.Student_ID, s.student_number, s.IDNumber, s.IDNum];
          for (const f of fields) {
            if (f === undefined || f === null) continue;
            const n = normalizeId(f);
            if (n) studentIdSet.add(n);
            studentIdSet.add(String(f).trim());
          }
          if (studentIdSamples.length < 5) studentIdSamples.push(s.ID_Number ?? s.id ?? s.ID ?? '');
        }
      }
    } catch (e) {
      totalStudents = 0;
    }

    let todaysScans = 0;
    try {
      const matchedTodaySet = new Set();
      const attFetches = eventsScheduledToday.map(ev => {
        const id = ev.id || ev.event_id || ev.Event_ID;
        if (!id) return Promise.resolve([]);
        return fetch('../api/attendance.php?event_id=' + encodeURIComponent(id), { credentials: 'same-origin' })
          .then(r => r.json()).then(j => Array.isArray(j.data) ? j.data : []).catch(() => []);
      });
      const attResults = await Promise.all(attFetches);
      for (const rows of attResults) {
        for (const row of rows) {
          const timeVal = row.Time_in ?? row.TimeIn ?? row.time_in ?? row.timein ?? row.Time_in ?? row.time_stamp ?? row.timestamp ?? null;
          if (!timeVal) continue;
          const t = Date.parse(timeVal);
          if (isNaN(t)) continue;
          if (t < startOfDay || t >= endOfDay) continue;
          const sid = row.Student_ID ?? row.ID_Number ?? row.student_id ?? row.studentId ?? null;
          if (!sid) continue;
          const raw = String(sid).trim();
          const norm = normalizeId(raw);
          // Only count students that belong to this department (if we have a studentIdSet)
          let matchesDept = false;
          if (studentIdSet.size === 0) {
            // fallback: assume row belongs to department
            matchesDept = true;
          } else if (studentIdSet.has(norm) || studentIdSet.has(raw) || studentIdSet.has(String(Number(raw)))) {
            matchesDept = true;
          }
          if (matchesDept) matchedTodaySet.add(norm || raw);
        }
      }
      todaysScans = matchedTodaySet.size;
    } catch (e) {
      // ignore and keep todaysScans as is
    }

    // total participants across events (sum participants if provided) — used for fallback
    let participantsTotal = 0;
    for (const ev of relevantEvents) {
      const p = (ev.participants !== undefined && ev.participants !== null) ? Number(ev.participants) : 0;
      if (!isNaN(p)) participantsTotal += p;
    }

    // Attempt to compute unique attendees by querying attendance API per event
    // Only count attendees that match the department's students (using studentIdSet)
    let matchedUniqueAttendees = new Set();
    const attendanceSamples = [];
    try {
      const fetches = relevantEvents.map(ev => {
        if (!ev || (!ev.id && !ev.event_id && !ev.Event_ID)) return Promise.resolve([]);
        const id = ev.id || ev.event_id || ev.Event_ID;
        const url = '../api/attendance.php?event_id=' + encodeURIComponent(id);
        return fetch(url, { credentials: 'same-origin' }).then(r => r.json()).then(j => Array.isArray(j.data) ? j.data : []).catch(() => []);
      });
      const results = await Promise.all(fetches);
      for (const list of results) {
        for (const row of list) {
          const sid = row.Student_ID ?? row.ID_Number ?? row.student_id ?? row.studentId ?? null;
          if (!sid) continue;
          const raw = String(sid).trim();
          const norm = normalizeId(raw);
          if (attendanceSamples.length < 10) attendanceSamples.push(raw);
          if (studentIdSet.size === 0) {
            // fallback: if no student list available, add raw id
            matchedUniqueAttendees.add(raw);
          } else {
            // check normalized and raw membership
            if (studentIdSet.has(norm) || studentIdSet.has(raw) || studentIdSet.has(String(Number(raw)))) {
              matchedUniqueAttendees.add(raw);
            }
          }
        }
      }
    } catch (e) {
      matchedUniqueAttendees = new Set();
    }

    // debug: log counts and samples so issues can be inspected in browser console
    try {
      console.debug('Dept stats debug:', {
        deptId,
        totalStudents,
        studentIdSample: studentIdSamples,
        relevantEvents: relevantEvents.length,
        attendanceSample: attendanceSamples.slice(0,10),
        matchedUniqueAttendees: matchedUniqueAttendees.size,
        participantsTotal
      });
    } catch(_){}

    // Compute attendance rate: only count matchedUniqueAttendees (attendees that are in the students list)
    let attendanceRate = 0;
    let attendeeCount = (typeof matchedUniqueAttendees !== 'undefined' && matchedUniqueAttendees.size) ? matchedUniqueAttendees.size : participantsTotal;
    // clamp to totalStudents to avoid >100% from mismatched ids
    if (totalStudents > 0 && attendeeCount > totalStudents) attendeeCount = totalStudents;
    if (totalStudents > 0) {
      attendanceRate = Math.round((attendeeCount / totalStudents) * 100);
      if (attendanceRate > 100) attendanceRate = 100;
    } else if (relevantEvents.length > 0) {
      const avgPerEvent = Math.round(participantsTotal / Math.max(1, relevantEvents.length));
      attendanceRate = avgPerEvent; // fallback raw number
    }

    // write to DOM (guard elements may not exist)
    const aEl = document.getElementById('statActiveEvents'); if (aEl) aEl.textContent = activeEvents;
    const tEl = document.getElementById('statTotalStudents'); if (tEl) tEl.textContent = totalStudents;
  const rEl = document.getElementById('statAttendanceRate'); if (rEl) rEl.textContent = String(todaysScans);
  // change label text to "Today's Scans"
  const rLabel = (rEl && rEl.parentElement) ? rEl.parentElement.querySelector('.stat-label') : null;
  if (rLabel) rLabel.textContent = "Today's Scans";
  const mEl = document.getElementById('statThisMonth'); if (mEl) mEl.textContent = monthName;

  } catch (e) {
    console.error('Failed to load department stats', e);
  }
}

// After department profile loads, refresh stats (profile loader sets window.__deptId)
const origLoadDepartmentProfile = window.loadDepartmentProfile;
if (typeof origLoadDepartmentProfile === 'function') {
  window.loadDepartmentProfile = async function() {
    await origLoadDepartmentProfile();
    try { await loadDepartmentStats(); } catch(_){}
  }
}

</script>

<!-- Password Change Modal -->
<div class="modal fade" id="passwordModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-maroon text-white">
        <h5 class="modal-title"><i class="bi bi-key me-2"></i>Change Password</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="passwordForm">
          <div class="form-group">
            <label class="form-label">Current Password</label>
            <input id="currentPasswordInput" type="password" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">New Password</label>
            <input id="newPasswordInput" type="password" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input id="confirmPasswordInput" type="password" class="form-control" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-maroon" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-maroon" onclick="changePassword()">
          <i class="bi bi-check-lg me-1"></i>Update Password
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function changePassword() {
  const current = document.getElementById('currentPasswordInput').value || '';
  const nw = document.getElementById('newPasswordInput').value || '';
  const conf = document.getElementById('confirmPasswordInput').value || '';
  if (nw.length < 6) { notify('warning', 'New password must be at least 6 characters.'); return; }
  if (nw !== conf) { notify('warning', 'New password and confirmation do not match.'); return; }

  const params = new URLSearchParams();
  params.append('new_password', nw);
  if (current && current.length > 0) params.append('current_password', current);
  // Attempt to include current user's id or username if available
  if (window.__deptId) {
    // nothing: dept page doesn't have user id; leave to server to use session
  }

  fetch('../api/users.php', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    credentials: 'same-origin',
    body: params.toString()
  }).then(r => r.json()).then(json => {
    if (json && json.success) {
        notify('success', 'Password updated successfully.');
      // clear inputs
      document.getElementById('currentPasswordInput').value = '';
      document.getElementById('newPasswordInput').value = '';
      document.getElementById('confirmPasswordInput').value = '';
      try { bootstrap.Modal.getInstance(document.getElementById('passwordModal')).hide(); } catch (_) {}
    } else {
        notify('danger', 'Failed to update password: ' + (json && json.error ? json.error : 'Unknown error'));
    }
  }).catch(e => { notify('danger', 'Error: ' + e.message); });
}
</script>
</body>
</html>
