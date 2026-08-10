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
  <title>Admin Profile</title>
  <link rel="stylesheet" href="../styles/dash_styles.css">
  <link rel="stylesheet" href="../styles/side_styles.css">
  <link rel="stylesheet" href="admin_styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001" />
  <style>
    .admin-wrap{ 
      max-width:1700px; 
      margin-left:10rem; 
      margin-right:3rem; 
      padding-top: 2rem;
    }
    
    .profile-header {
      background: linear-gradient(135deg, #800000 0%, #a00000 100%);
      color: white;
      padding: 2rem;
      border-radius: 12px;
      margin-bottom: 2rem;
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }
    
    .profile-avatar {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 4px solid rgba(255,255,255,0.3);
      background: rgba(255,255,255,0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
      margin-bottom: 1rem;
    }
    
    .profile-card {
      background: #fff;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 6px 18px rgba(0,0,0,0.08);
      margin-bottom: 1.5rem;
    }
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    .form-label {
      font-weight: 600;
      color: #333;
      margin-bottom: 0.5rem;
    }
    
    .form-control {
      border: 2px solid #e9ecef;
      border-radius: 8px;
      padding: 0.75rem 1rem;
      transition: all 0.3s ease;
    }
    
    .form-control:focus {
      border-color: #800000;
      box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.1);
    }
    
    .btn-maroon {
      background-color: #800000;
      border-color: #800000;
      color: white;
      padding: 0.75rem 2rem;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .btn-maroon:hover {
      background-color: #600000;
      border-color: #600000;
      color: white;
      transform: translateY(-1px);
    }
    
    .btn-outline-maroon {
      border: 2px solid #800000;
      color: #800000;
      background: transparent;
      padding: 0.75rem 2rem;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .btn-outline-maroon:hover {
      background-color: #800000;
      color: white;
      transform: translateY(-1px);
    }
    
    .activity-item {
      padding: 1rem;
      border-left: 4px solid #800000;
      background: rgba(128, 0, 0, 0.05);
      margin-bottom: 1rem;
      border-radius: 0 8px 8px 0;
    }
    
    .badge-maroon {
      background-color: #800000;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.875rem;
    }
    
    .settings-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 0;
      border-bottom: 1px solid #e9ecef;
    }
    
    .settings-item:last-child {
      border-bottom: none;
    }
    
    .form-switch .form-check-input:checked {
      background-color: #800000;
      border-color: #800000;
    }
    
    @media (max-width: 768px) {
      .admin-wrap {
        margin-left: 2rem;
        margin-right: 1rem;
        padding-top: 1rem;
      }
      
      .profile-header {
        text-align: center;
        padding: 1.5rem;
      }
      
      .row > div {
        margin-bottom: 1rem;
      }
    }
  </style>
</head>
<body>
  <?php $page_heading = 'Admin Profile'; include 'navbar.php'; ?>
  <?php include 'sidebar.php'; ?>

<main class="admin-wrap">
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
  <h2 class="mb-2" id="adminFullName">Admin Administrator</h2>
  <p class="mb-1 opacity-75" id="adminRole">System Administrator</p>
        <div class="d-flex flex-column flex-md-row gap-2 justify-content-center justify-content-md-start">
          <span class="badge-maroon"><i class="bi bi-shield-check me-1"></i> Super Admin</span>
          <span class="badge badge-light"><i class="bi bi-calendar me-1"></i> Last login: <span id="lastLoginText">—</span></span>
        </div>
      </div>
      <div class="col-md-auto">
        <button class="btn btn-light btn-sm" id="editBtnAdmin" onclick="editProfile()">
          <i class="bi bi-pencil me-1"></i> Edit Profile
        </button>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Profile Information -->
    <div class="col-lg-8" id="profileInfo">
      <div class="profile-card">
        <h5 class="mb-4"><i class="bi bi-person-lines-fill text-maroon me-2"></i>Personal Information</h5>
        <form id="profileForm">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" class="form-control" value="" disabled id="firstName">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" class="form-control" value="" disabled id="lastName">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" value="" disabled id="email">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="tel" class="form-control" value="" disabled id="phone">
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-control" rows="3" disabled id="description"></textarea>
          </div>
          
          <!-- <div class="form-group">
            <label class="form-label">Employee ID</label>
            <input type="text" class="form-control" value="ADM-2024-001" disabled>
          </div> -->
      
          
          <div class="d-flex gap-2" id="profileActions" style="display: none;">
            <button type="button" class="btn btn-maroon" onclick="saveProfile()">
              <i class="bi bi-check-lg me-1"></i> Save Changes
            </button>
            <button type="button" class="btn btn-outline-maroon" onclick="cancelEdit()">
              <i class="bi bi-x-lg me-1"></i> Cancel
            </button>
          </div>
        </form>
      </div>

      <!-- Security Settings -->
      <div class="profile-card">
        <h5 class="mb-4"><i class="bi bi-shield-lock text-maroon me-2"></i>Security Settings</h5>
        <div class="settings-item">
          <div>
            <h6 class="mb-1">Change Password</h6>
            <p class="text-muted mb-0">Update your account password</p>
          </div>
          <button class="btn btn-outline-maroon btn-sm" data-bs-toggle="modal" data-bs-target="#passwordModal">
            <i class="bi bi-key me-1"></i> Change
          </button>
        </div>
        
        <!-- Attendance-related toggles removed per request -->
      </div>
    </div>

    <!-- Activity & Quick Actions -->
    <div class="col-lg-4">
      <!-- Quick Actions -->
      <div class="profile-card">
        <h5 class="mb-4"><i class="bi bi-lightning text-maroon me-2"></i>Quick Actions</h5>
        <div class="d-grid gap-2">
          <a href="students.php" class="btn btn-maroon" onclick="setActiveSidebar('students')">
            <i class="bi bi-people me-2"></i>Manage Students
          </a>
          <a href="events.php" class="btn btn-outline-maroon" onclick="setActiveSidebar('events')">
            <i class="bi bi-calendar-event me-2"></i>View Events
          </a>
          <a href="control_panel.php" class="btn btn-outline-maroon" onclick="setActiveSidebar('control')">
            <i class="bi bi-gear me-2"></i>Control Panel
          </a>
          <!-- <button class="btn btn-outline-maroon" onclick="generateReport()">
            <i class="bi bi-file-earmark-text me-2"></i>Generate Report
          </button> -->
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="profile-card">
        <h5 class="mb-4"><i class="bi bi-clock-history text-maroon me-2"></i>Recent Activity</h5>
        <div class="mb-3">
          <label class="form-label">Search Events</label>
          <div class="input-group">
            <input type="search" id="eventSearchInput" class="form-control" placeholder="Search newly created events by name or department">
            <button class="btn btn-outline-maroon" id="eventSearchBtn" type="button">Search</button>
          </div>
        </div>
        <div id="eventSearchResults" class="mb-2"></div>
        <div id="staticActivityItems">
          <div class="activity-item">
            <h6 class="mb-1">Student Added</h6>
            <p class="text-muted mb-1">Added new student: Juan Dela Cruz</p>
            <small class="text-muted">2 hours ago</small>
          </div>
          
          <div class="activity-item">
            <h6 class="mb-1">Event Created</h6>
            <p class="text-muted mb-1">Created "Tech Summit 2024" event</p>
            <small class="text-muted">5 hours ago</small>
          </div>
          
          <div class="activity-item">
            <h6 class="mb-1">System Backup</h6>
            <p class="text-muted mb-1">Automated system backup completed</p>
            <small class="text-muted">1 day ago</small>
          </div>
        </div>
        
        <div class="text-center mt-3">
          <a href="#" id="viewAllActivityLink" class="text-maroon text-decoration-none">
            <i class="bi bi-arrow-right me-1"></i>View All Activity
          </a>
        </div>
      </div>

      <!-- Account Statistics -->
      <!-- <div class="profile-card">
        <h5 class="mb-4"><i class="bi bi-graph-up text-maroon me-2"></i>Account Stats</h5>
        <div class="row text-center">
          <div class="col-6">
            <h4 class="text-maroon mb-0">156</h4>
            <small class="text-muted">Students Added</small>
          </div>
          <div class="col-6">
            <h4 class="text-maroon mb-0">24</h4>
            <small class="text-muted">Events Created</small>
          </div>
        </div>
        <hr>
        <div class="row text-center">
          <div class="col-6">
            <h4 class="text-maroon mb-0">89</h4>
            <small class="text-muted">Days Active</small>
          </div>
          <div class="col-6">
            <h4 class="text-maroon mb-0">1,234</h4>
            <small class="text-muted">Total Scans</small>
          </div>
        </div>
      </div> -->
    </div>
  </div>
</main>

<!-- All Activity Modal -->
<div class="modal fade" id="allActivityModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-maroon text-white">
        <h5 class="modal-title">All Recent Events</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="allActivityList"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-maroon" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

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
            <input id="currentPasswordInput" type="password" class="form-control" required>
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

// Simple Bootstrap toast notifier
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

// Normalize a returned image path to a root-relative URL so it loads from /new/ consistently
function normalizePath(p) {
  if (!p) return '';
  const s = String(p);
  if (/^https?:\/\//i.test(s)) return s; // already absolute URL
  if (s.startsWith('/')) return s;        // already root-relative
  // If backend returned a relative like "images/profiles/x.jpg", prefix with "/new/"
  return '../' + s.replace(/^\/+/, '');
}

// Format last login string into a friendly label like "Today, 9:30 AM" or fallback to locale string
function formatLastLogin(raw) {
  if (!raw) return '—';
  let d = null;
  if (typeof raw === 'number') {
    // assume millis
    d = new Date(raw);
  } else if (/^\d+$/.test(String(raw))) {
    const str = String(raw);
    // 10 digits -> seconds; 13 -> millis
    const ms = str.length === 10 ? parseInt(str, 10) * 1000 : parseInt(str, 10);
    d = new Date(ms);
  } else {
    const parsed = Date.parse(String(raw));
    if (!isNaN(parsed)) d = new Date(parsed);
  }
  if (!d || isNaN(d.getTime())) return '—';
  const now = new Date();
  const sameDay = d.toDateString() === now.toDateString();
  const yest = new Date(now); yest.setDate(now.getDate() - 1);
  const sameYest = d.toDateString() === yest.toDateString();
  const time = d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
  if (sameDay) return `Today, ${time}`;
  if (sameYest) return `Yesterday, ${time}`;
  return d.toLocaleString([], { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function editProfile() {
  // Enable form fields (inputs + textareas)
  const formInputs = document.querySelectorAll('#profileForm input, #profileForm textarea');
  formInputs.forEach(el => el.disabled = false);
  // attendance toggles removed

  // Show action buttons
  document.getElementById('profileActions').style.display = 'flex';

  // Update edit button (avoid relying on event)
  const btn = document.getElementById('editBtnAdmin');
  if (btn) {
    btn.innerHTML = '<i class="bi bi-x-lg me-1"></i> Cancel';
    btn.onclick = cancelEdit;
  }
}

function cancelEdit() {
  // Disable form fields (inputs + textareas)
  const formInputs = document.querySelectorAll('#profileForm input, #profileForm textarea');
  formInputs.forEach(el => el.disabled = true);
  // attendance toggles removed

  // restore values from initial snapshot
  if (window.__adminInitial) {
    document.getElementById('firstName').value = window.__adminInitial.first_name || '';
    document.getElementById('lastName').value = window.__adminInitial.last_name || '';
    document.getElementById('email').value = window.__adminInitial.email || '';
    document.getElementById('phone').value = window.__adminInitial.phone || '';
    document.getElementById('description').value = window.__adminInitial.description || '';
  // attendance toggles removed
  }

  // Hide action buttons
  document.getElementById('profileActions').style.display = 'none';

  // Reset edit button (avoid relying on event)
  const btn = document.getElementById('editBtnAdmin');
  if (btn) {
    btn.innerHTML = '<i class="bi bi-pencil me-1"></i> Edit Profile';
    btn.onclick = editProfile;
  }
}

async function saveProfile() {
  try {
    // Detect no changes before calling API
    const curr = getCurrentAdminProfileFormValues();
    if (window.__adminInitial) {
      const same = ['first_name','last_name','email','phone','description'].every(k => (String(curr[k] || '') === String(window.__adminInitial[k] || '')));
      if (same) {
        notify('warning', 'No changes to save.');
        return;
      }
    }

    const payload = {
      // first/last name will be honored only if current account is admin (API-enforced)
      first_name: document.getElementById('firstName').value.trim(),
      last_name: document.getElementById('lastName').value.trim(),
      email: document.getElementById('email').value.trim(),
      phone: document.getElementById('phone').value.trim(),
      description: document.getElementById('description').value.trim(),
      // attendance-related toggles removed
    };
    const res = await fetch('../api/profile.php?type=admin', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (json.success) {
      const affected = typeof json.affected_rows === 'number' ? json.affected_rows : null;
      if (affected === 0) notify('info', 'No changes made.');
      else notify('success', 'Profile updated successfully!');
      await loadAdminProfile();
      cancelEdit();
    } else {
      notify('danger', 'Failed to update: ' + (json.error || 'Unknown error'));
    }
  } catch (e) {
    notify('danger', 'Error: ' + e.message);
  }
}

function changePassword() {
  // Validate inputs
  const current = document.getElementById('currentPasswordInput').value || '';
  const nw = document.getElementById('newPasswordInput').value || '';
  const conf = document.getElementById('confirmPasswordInput').value || '';
  if (nw.length < 6) { notify('warning', 'New password must be at least 6 characters.'); return; }
  if (nw !== conf) { notify('warning', 'New password and confirmation do not match.'); return; }

  // Build payload - users API accepts PUT with new_password and username or id
  const payload = new URLSearchParams();
  payload.append('new_password', nw);
  // prefer username if available
  if (window.__adminInitial && window.__adminInitial.id) payload.append('id', window.__adminInitial.id);
  else if (window.__adminInitial && window.__adminInitial.username) payload.append('username', window.__adminInitial.username);
  if (current && current.length > 0) payload.append('current_password', current);

  fetch('../api/users.php', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    credentials: 'same-origin',
    body: payload.toString()
  }).then(r => r.json()).then(json => {
    if (json.success) {
      notify('success', 'Password updated successfully.');
      // clear inputs
      document.getElementById('currentPasswordInput').value = '';
      document.getElementById('newPasswordInput').value = '';
      document.getElementById('confirmPasswordInput').value = '';
      bootstrap.Modal.getInstance(document.getElementById('passwordModal')).hide();
    } else {
      notify('danger', 'Failed to update password: ' + (json.error || 'Unknown error'));
    }
  }).catch(e => { notify('danger', 'Error: ' + e.message); });
}

function generateReport() { alert('Generating system report...'); }
// Attendance toggle listeners removed

async function loadAdminProfile() {
  try {
    const res = await fetch('../api/profile.php?type=admin', { credentials: 'same-origin' });
    const json = await res.json();
    if (!json.success || !json.data) return;
    const d = json.data;
  // populate fields
    document.getElementById('firstName').value = d.first_name || '';
    document.getElementById('lastName').value = d.last_name || '';
    document.getElementById('email').value = d.email || '';
    document.getElementById('phone').value = d.phone || '';
  document.getElementById('description').value = d.description || '';
  // attendance toggles removed
    // header labels
    const full = (d.first_name || '') + (d.last_name ? (' ' + d.last_name) : '');
    document.getElementById('adminFullName').textContent = full.trim() || (d.username || 'Admin');
    document.getElementById('adminRole').textContent = d.account_type === 'admin' ? 'System Administrator' : 'Department User';
    // Last login label
    try {
      const lbl = document.getElementById('lastLoginText');
      if (lbl) lbl.textContent = formatLastLogin(d.last_login || null);
    } catch (_) {}

    // Track initial state for change detection
    window.__adminInitial = {
      first_name: d.first_name || '',
      last_name: d.last_name || '',
      email: d.email || '',
      phone: d.phone || '',
      description: d.description || '',
      username: d.username || '',
      id: d.id || null,
      profile_pic: d.profile_pic || null
    };
    // show avatar if profile_pic exists
    try {
      const avatar = document.getElementById('profileAvatar');
      const icon = document.getElementById('profileAvatarIcon');
      if (window.__adminInitial.profile_pic) {
        if (avatar) {
          const url = normalizePath(window.__adminInitial.profile_pic);
          avatar.style.backgroundImage = `url("${url}")`;
          avatar.style.backgroundSize = 'cover';
          avatar.style.backgroundPosition = 'center';
        }
        if (icon) icon.style.display = 'none';
      } else {
        if (avatar) avatar.style.backgroundImage = '';
        if (icon) icon.style.display = '';
      }
    } catch(e) { /* ignore */ }
  } catch (e) { /* ignore */ }
}

function getCurrentAdminProfileFormValues() {
  return {
    first_name: document.getElementById('firstName').value.trim(),
    last_name: document.getElementById('lastName').value.trim(),
    email: document.getElementById('email').value.trim(),
    phone: document.getElementById('phone').value.trim(),
    description: document.getElementById('description').value.trim(),
    // attendance-related toggles removed
  };
}

document.addEventListener('DOMContentLoaded', loadAdminProfile);

// Events: load newly created events (last 7 days) and provide a search box
let __allEventsCache = [];
async function loadRecentEvents() {
  try {
    const res = await fetch('../api/events.php', { credentials: 'same-origin' });
    const json = await res.json();
    if (!json.success || !Array.isArray(json.data)) return;
    __allEventsCache = json.data;
    renderEventSearchResults(filterNewEvents(__allEventsCache));
  } catch (e) { console.error('loadRecentEvents error', e); }
}

function filterNewEvents(events, days = 7) {
  const cutoff = Date.now() - (days * 24 * 60 * 60 * 1000);
  return events.filter(ev => {
    const ts = ev.timestamp || ev.created_at || ev.created || ev.date || null;
    if (!ts) return false;
    const t = Date.parse(ts);
    if (isNaN(t)) return false;
    return t >= cutoff;
  });
}

function renderEventSearchResults(events) {
  const el = document.getElementById('eventSearchResults');
  if (!el) return;
  if (!events || events.length === 0) {
    el.innerHTML = '<div class="text-muted">No recent events found.</div>';
    // show static items when there are no dynamic events
    const staticEl = document.getElementById('staticActivityItems'); if (staticEl) staticEl.style.display = '';
    return;
  }
  const rows = events.slice(0,3).map(ev => {
    const when = ev.timestamp || ev.created_at || ev.created || ev.date || '';
    const dept = ev.department_name || (ev.departments_id ? String(ev.departments_id) : '—');
    const participants = (typeof ev.participants === 'number') ? ev.participants : (ev.participants || 0);
    return `<div class="activity-item">
      <h6 class="mb-1">${escapeHtml(ev.event_name || ev.name || ev.title || 'Untitled')}</h6>
      <p class="text-muted mb-1">Dept: ${escapeHtml(String(dept))} • Created: ${escapeHtml(String(when))}</p>
      <small class="text-muted">Participants: ${participants}</small>
    </div>`;
  }).join('');
  el.innerHTML = rows;
  // hide static items when showing dynamic events
  const staticEl = document.getElementById('staticActivityItems'); if (staticEl) staticEl.style.display = 'none';
}

function escapeHtml(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]); }

function doEventSearch(query) {
  const q = (query || '').trim().toLowerCase();
  if (!q) return renderEventSearchResults(filterNewEvents(__allEventsCache));
  const results = __allEventsCache.filter(ev => {
    const name = (ev.event_name || ev.name || ev.title || '').toLowerCase();
    const dept = (ev.department_name || (ev.departments_id ? String(ev.departments_id) : '')).toLowerCase();
    return name.includes(q) || dept.includes(q);
  });
  renderEventSearchResults(filterNewEvents(results));
}

document.getElementById('eventSearchBtn').addEventListener('click', function(){ doEventSearch(document.getElementById('eventSearchInput').value); });
document.getElementById('eventSearchInput').addEventListener('keypress', function(e){ if (e.key === 'Enter') { e.preventDefault(); doEventSearch(this.value); } });

// Profile picture upload flow
document.getElementById('profileAvatar').addEventListener('click', function(){ document.getElementById('profilePicInput').click(); });
document.getElementById('profilePicInput').addEventListener('change', async function(e){
  const f = this.files && this.files[0];
  if (!f) return;
  if (f.size > 2 * 1024 * 1024) { notify('warning','Image must be <= 2MB'); return; }
  const fd = new FormData(); fd.append('profile_pic', f);
  try {
    const res = await fetch('../api/upload_profile_pic.php', { method:'POST', credentials:'same-origin', body: fd });
    const j = await res.json();
    if (!j.success) { notify('danger','Upload failed: ' + (j.error || 'Unknown')); return; }
    // update profile with returned path
    const raw = j.path || j.url || '';
    const path = normalizePath(raw);
    const upd = { description: document.getElementById('description').value.trim() };
    // call profile PUT to persist profile_pic field if API supports it; if not, upload endpoint already updated DB
    try {
      await fetch('../api/profile.php?type=admin', { method: 'PUT', credentials: 'same-origin', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ profile_pic: path }) });
    } catch(e) { /* ignore */ }
    // preview
    const avatar = document.getElementById('profileAvatar');
    const icon = document.getElementById('profileAvatarIcon');
    if (avatar) { avatar.style.backgroundImage = `url("${path}")`; avatar.style.backgroundSize='cover'; avatar.style.backgroundPosition='center'; }
    if (icon) icon.style.display = 'none';
    if (window.__adminInitial) window.__adminInitial.profile_pic = path;
    notify('success','Profile picture updated');
  } catch (err) { notify('danger','Upload error: ' + err.message); }
});

function renderAllEvents(events) {
  const el = document.getElementById('allActivityList');
  if (!el) return;
  if (!events || events.length === 0) { el.innerHTML = '<div class="text-muted">No events found.</div>'; return; }
  const rows = events.map(ev => {
    const when = ev.timestamp || ev.created_at || ev.created || ev.date || '';
    const dept = ev.department_name || (ev.departments_id ? String(ev.departments_id) : '—');
    const participants = (typeof ev.participants === 'number') ? ev.participants : (ev.participants || 0);
    return `<div class="activity-item">
      <h6 class="mb-1">${escapeHtml(ev.event_name || ev.name || ev.title || 'Untitled')}</h6>
      <p class="text-muted mb-1">Dept: ${escapeHtml(String(dept))} • Created: ${escapeHtml(String(when))}</p>
      <small class="text-muted">Participants: ${participants}</small>
    </div>`;
  }).join('');
  el.innerHTML = rows;
}

document.getElementById('viewAllActivityLink').addEventListener('click', function(e){
  e.preventDefault();
  // open modal and render all recent events (default new filter applied)
  renderAllEvents(filterNewEvents(__allEventsCache, 30));
  const m = new bootstrap.Modal(document.getElementById('allActivityModal'));
  m.show();
});

// start loading events after profile
document.addEventListener('DOMContentLoaded', loadRecentEvents);
</script>
</body>
</html>
