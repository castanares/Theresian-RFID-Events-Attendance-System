<?php
require_once __DIR__ . '/../auth.php';
require_login('../');

// Fetch stats from database
require_once __DIR__ . '/../api/dbcon.php';

// Determine current semester
$current_month = date('m');
$current_semester_query = $conn->query("SELECT id FROM semester WHERE (MONTH(start) <= $current_month AND MONTH(end) >= $current_month) OR (MONTH(start) > MONTH(end) AND ($current_month >= MONTH(start) OR $current_month <= MONTH(end))) ORDER BY id LIMIT 1");
$current_semester = $current_semester_query->fetch_assoc()['id'] ?? 1; // Default to 1 if not found

// Function to get school year from date
function getSchoolYear($date) {
    $timestamp = strtotime($date);
    $year = date('Y', $timestamp);
    $month = date('n', $timestamp);
    if ($month >= 6) {
        return $year . '-' . ($year + 1);
    } else {
        return ($year - 1) . '-' . $year;
    }
}

$current_school_year = getSchoolYear(date('Y-m-d'));

// Total active students
$totalStudents = $conn->query("SELECT COUNT(*) as count FROM students WHERE status = 'active'")->fetch_assoc()['count'];

// Active events (for current semester and school year)
$activeEvents = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'active' AND semester_id = $current_semester AND CASE WHEN MONTH(date) >= 6 THEN CONCAT(YEAR(date), '-', YEAR(date)+1) ELSE CONCAT(YEAR(date)-1, '-', YEAR(date)) END = '$current_school_year'")->fetch_assoc()['count'];

// Today's scans (for events in current semester and school year)
$todaysScans = $conn->query("SELECT COUNT(a.Attendance_ID) as count FROM attendance_tbl a JOIN events e ON a.Event_ID = e.id WHERE DATE(e.date) = CURDATE() AND e.semester_id = $current_semester AND CASE WHEN MONTH(e.date) >= 6 THEN CONCAT(YEAR(e.date), '-', YEAR(e.date)+1) ELSE CONCAT(YEAR(e.date)-1, '-', YEAR(e.date)) END = '$current_school_year'")->fetch_assoc()['count'];

// Active departments
$activeDepartments = $conn->query("SELECT COUNT(*) as count FROM departments WHERE archived = 0")->fetch_assoc()['count'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Control Panel</title>
  <link rel="stylesheet" href="../styles/dash_styles.css">
  <link rel="stylesheet" href="../styles/side_styles.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001" />
</head>

<style>
  /* Enhanced admin control panel styling */
  :root {
    --maroon: #800000;
    --maroon-dark: #6d0000;
    --card-bg: #fff;
    --card-shadow: 0 8px 32px rgba(0,0,0,0.10);
  }

  .control-wrap {
    margin-left: 10rem;
    margin-right: 2rem;
    margin-top: 1rem;
  }

  .control-card {
    background: var(--card-bg);
    border-radius: 12px;
    box-shadow: var(--card-shadow);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border-left: 4px solid var(--maroon);
  }

  .control-section-title {
    color: var(--maroon);
    font-weight: 700;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #f0f0f0;
  }

  .btn-maroon {
    background: var(--maroon);
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 0.5rem 1rem;
    font-weight: 600;
  }

  .btn-maroon:hover {
    background: var(--maroon-dark);
    color: #fff;
  }

  .btn-outline-maroon {
    color: var(--maroon);
    border: 1px solid var(--maroon);
    background: transparent;
  }

  .btn-outline-maroon:hover {
    background: var(--maroon);
    color: #fff;
  }

  .stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .stat-card {
    background: var(--card-bg);
    padding: 1.2rem;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    text-align: center;
  }

  .stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--maroon);
  }

  .action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
    max-width: 100%;
  }

  .action-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 0.5rem;
  }

  .action-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--maroon);
    color: white;
    border-radius: 50%;
    margin-right: 1rem;
  }

  .table-maroon th {
    background-color: var(--maroon) !important;
    color: white !important;
  }

  .activity-item {
    padding: 0.75rem;
    border-left: 3px solid #e9ecef;
    margin-bottom: 0.5rem;
    background: #f8f9fa;
    border-radius: 0 6px 6px 0;
  }

  .activity-item.recent {
    border-left-color: var(--maroon);
    background: #fff5f5;
  }

  /* Responsive Design */
  @media (max-width: 1440px) and (min-width: 1201px) {
    .action-grid {
      grid-template-columns: repeat(2, minmax(280px, 450px));
      justify-content: center;
    }
  }

  @media (max-width: 1200px) {
    .control-wrap {
      margin-left: 8rem;
      margin-right: 1.5rem;
    }
  }

  @media (max-width: 992px) {
    .control-wrap {
      margin-left: 6rem;
      margin-right: 1rem;
    }
    
    .stat-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    
    .action-grid {
      grid-template-columns: repeat(2, 1fr);
    }
    
    .action-item {
      flex-direction: column;
      align-items: flex-start;
      text-align: left;
    }
    
    .action-item .d-flex {
      margin-bottom: 0.5rem;
    }
  }

  @media (max-width: 768px) {
    .control-wrap {
      margin-left: 1rem;
      margin-right: 1rem;
      margin-top: 0.5rem;
    }
    
    .stat-grid {
      grid-template-columns: 1fr 1fr;
      gap: 0.75rem;
    }
    
    .stat-card {
      padding: 1rem;
    }
    
    .stat-number {
      font-size: 1.5rem;
    }
    
    .action-grid {
      grid-template-columns: 1fr;
    }
    
    .control-card {
      padding: 1rem;
      margin-bottom: 1rem;
    }
    
    .action-item {
      flex-direction: column;
      align-items: flex-start;
      padding: 0.75rem;
    }
    
    .action-item .d-flex {
      margin-bottom: 0.75rem;
      width: 100%;
    }
    
    .action-item .btn {
      width: 100%;
    }
    
    .d-flex.gap-2 {
      flex-direction: column;
      width: 100%;
    }
    
    .d-flex.gap-2 .btn {
      width: 100%;
      margin-bottom: 0.5rem;
    }
  }

  @media (max-width: 576px) {
    .control-wrap {
      margin-left: 0.5rem;
      margin-right: 0.5rem;
    }
    
    h1 {
      font-size: 1.5rem !important;
    }
    
    .stat-grid {
      grid-template-columns: 1fr;
    }
    
    .stat-card {
      padding: 0.75rem;
    }
    
    .control-card {
      padding: 0.75rem;
    }
    
    .control-section-title {
      font-size: 1.1rem;
    }
    
    .action-icon {
      width: 35px;
      height: 35px;
      margin-right: 0.75rem;
    }
    
    .action-item {
      padding: 0.5rem;
    }
    
    .modal-dialog {
      margin: 0.5rem;
    }
    
    .header-actions {
      margin-bottom: 2rem !important;
    }
    
    .header-actions .d-flex {
      width: 100%;
    }
  }

  @media (max-width: 480px) {
    .control-wrap {
      margin: 0.25rem;
    }
    
    .control-section-title {
      font-size: 1rem;
    }
    
    .action-item strong {
      font-size: 0.9rem;
    }
    
    .action-item .text-muted {
      font-size: 0.8rem;
    }
  }
</style>

<style>
  .department-check { transition: background 0.2s, box-shadow 0.2s; }
  .department-check:hover { background: #ffe5e5; box-shadow: 0 2px 8px rgba(128,0,0,0.08); }
  .dept-margin { margin-left: 10rem; }
  .dept-margin-right { margin-right: 10rem; }
  .maroon-text { color: maroon; }
  .form-check-input:checked { background-color: maroon; }
</style>

<body>
<?php $page_heading = 'Control Panel'; include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<main class="control-wrap">
  <!-- Page Header -->
    <h1 class="text-decoration-underline text-center link-underline-danger mb-4">ADMIN CONTROL PANEL</h1>
  <div class="d-flex justify-content-center justify-content-md-end align-items-center mb-4 header-actions">
    <div class="d-flex gap-2 flex-column flex-sm-row">
      <button class="btn btn-maroon" onclick="refreshData()">
        <i class="bi bi-arrow-clockwise me-1"></i><span class="d-none d-sm-inline">Refresh Data</span><span class="d-sm-none">Refresh</span>
      </button>
      <button class="btn btn-outline-maroon" data-bs-toggle="modal" data-bs-target="#backupModal">
        <i class="bi bi-download me-1"></i><span class="d-none d-sm-inline">Backup System</span><span class="d-sm-none">Backup</span>
      </button>
    </div>
  </div>

  <!-- System Overview Stats -->
  <div class="stat-grid">
    <div class="stat-card">
      <div class="stat-number"><?php echo number_format($totalStudents); ?></div>
      <div class="text-muted">Total Students</div>
      <small class="text-success">Active</small>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?php echo $activeEvents; ?></div>
      <div class="text-muted">Active Events</div>
      <small class="text-info">Ongoing</small>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?php echo $todaysScans; ?></div>
      <div class="text-muted">Today's Scans</div>
      <small class="text-warning">Attendance</small>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?php echo $activeDepartments; ?></div>
      <div class="text-muted">Departments</div>
      <small class="text-muted">Active</small>
    </div>
  </div>

  <!-- Main Control Sections -->
  <div class="action-grid">
    <!-- User Management -->
    <div class="control-card">
      <h4 class="control-section-title">
        <i class="bi bi-people-fill me-2"></i>User Management
      </h4>
      
      <div class="action-item">
        <div class="d-flex align-items-center">
          <div class="action-icon">
            <i class="bi bi-person-plus"></i>
          </div>
          <div>
            <strong>Manage Students</strong>
            <div class="text-muted small">Add, edit, archive student records</div>
          </div>

          <!-- User Accounts Modal (UI-only create user) -->
          <div class="modal fade" id="adminModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header" style="background-color: var(--maroon); color: white;">
                  <h5 class="modal-title">User Accounts</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Users</h6>
                    <button class="btn btn-sm btn-maroon" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
                  </div>

                  <div class="table-responsive">
                      <table class="table table-hover" id="userTable">
                        <thead class="table-maroon">
                          <tr>
                            <th>#</th>
                            <th>Department</th>
                            <th>Username</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <!-- users loaded dynamically -->
                        </tbody>
                      </table>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
 <!-- Add User Modal (replaces collapse) -->
      <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
          <div class="modal-content">
            <div class="modal-header" style="background-color: var(--maroon); color: white;">
              <h5 class="modal-title">Add User</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form id="formAddUserModal" onsubmit="return false;">
                <div class="mb-2">
                  <label class="form-label">Department</label>
                  <select class="form-select" id="modalUserName" required>
                    <option value="">Select department...</option>
                    <!-- options will be populated dynamically -->
                  </select>
                </div>
                
                <div class="mb-2">
                  <label class="form-label">Username</label>
                  <input class="form-control" id="modalUserUsername" placeholder="Username" required
                    oninput="this.value=this.value.replace(/\s+/g, '-');"
                    onkeydown="if (event.key === ' ') event.preventDefault();"
                    pattern="^\S+$" title="Spaces are replaced with hyphens">
                </div>
                <div class="mb-2">
                  <label class="form-label">Password</label>
                  <input class="form-control" id="modalUserPassword" placeholder="Password" required>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-maroon" onclick="addUserFromModal()">Save User</button>
            </div>
          </div>
        </div>
      </div>

        </div>
        <a href="students.php" class="btn btn-sm btn-maroon" onclick="setActiveSidebar('students')">Manage</a>
      </div>

      <div class="action-item">
        <div class="d-flex align-items-center">
          <div class="action-icon">
            <i class="bi bi-shield-check"></i>
          </div>
          <div>
            <strong>User Accounts</strong>
            <div class="text-muted small">Create and manage users</div>
          </div>
        </div>
        <button class="btn btn-sm btn-outline-maroon" onclick="manageAdmins()">View</button>
      </div>


<!-- reset password -->
      <div class="action-item">
        <div class="d-flex align-items-center">
          <div class="action-icon">
            <i class="bi bi-key"></i>
          </div>
          <div>
            <strong>Password Reset</strong>
            <div class="text-muted small">Reset user passwords</div>
          </div>
        </div>
        <button class="btn btn-sm btn-outline-maroon" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">Reset</button>
      </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
          <div class="modal-header" style="background-color: var(--maroon); color: white;">
            <h5 class="modal-title">Reset Password</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="formResetPassword" onsubmit="return false;">
              <div class="mb-2">
                <label class="form-label">Username or ID</label>
                <input class="form-control" id="resetIdentifier" placeholder="username or id" required>
              </div>
              <div class="mb-2">
                <label class="form-label">New Password</label>
                <input class="form-control" id="resetNewPassword" placeholder="New password" required>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-maroon" onclick="submitResetPassword()">Apply</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Event Management -->
    <div class="control-card">
      <h4 class="control-section-title">
        <i class="bi bi-calendar-event me-2"></i>Event Management
      </h4>
      
      <div class="action-item">
        <div class="d-flex align-items-center">
          <div class="action-icon">
            <i class="bi bi-list-task"></i>
          </div>
          <div>
            <strong>All Events</strong>
            <div class="text-muted small">View and manage all events</div>
          </div>
        </div>
        <a href="events.php" class="btn btn-sm btn-maroon" onclick="setActiveSidebar('events')">Manage</a>
      </div>

      <div class="action-item">
        <div class="d-flex align-items-center">
          <div class="action-icon">
            <i class="bi bi-plus-lg"></i>
          </div>
          <div>
            <strong>Create Event</strong>
            <div class="text-muted small">Set up new attendance events</div>
          </div>
        </div>
        <button class="btn btn-sm btn-outline-maroon" data-bs-toggle="modal" data-bs-target="#createEventModal">Create</button>
      </div>
      <div>
        
      </div>

      <div class="action-item">
        <div class="d-flex align-items-center">
          <div class="action-icon">
            <i class="bi bi-archive"></i>
          </div>
          <div>
            <strong>Event Archive</strong>
            <div class="text-muted small">View completed events</div>
          </div>
        </div>
        <a href="archived.php" class="btn btn-sm btn-outline-maroon" onclick="setActiveSidebar('archived')">Archive</a>
      </div>
    </div>

    <!-- System Configuration -->
    <div class="control-card">
      <h4 class="control-section-title">
        <i class="bi bi-gear-fill me-2"></i>System Configuration
      </h4>
      
      <div class="action-item">
        <div class="d-flex align-items-center">
          <div class="action-icon">
            <i class="bi bi-building"></i>
          </div>
          <div>
            <strong>Department Settings</strong>
            <div class="text-muted small">Manage college departments</div>
          </div>
        </div>
        <button class="btn btn-sm btn-maroon" data-bs-toggle="modal" data-bs-target="#deptModal">Configure</button>
      </div>

      <div class="action-item">
        <div class="d-flex align-items-center">
          <div class="action-icon">
            <i class="bi bi-calendar3"></i>
          </div>
          <div>
            <strong>Semester Settings</strong>
            <div class="text-muted small">Configure academic periods</div>
          </div>
        </div>
        <button class="btn btn-sm btn-outline-maroon" onclick="manageSemesters()">Configure</button>
      </div>

      <div class="action-item">
        <div class="d-flex align-items-center">
          <div class="action-icon">
            <i class="bi bi-credit-card-2-front"></i>
          </div>
          <div>
            <strong>RFID Management</strong>
            <div class="text-muted small">Configure RFID settings</div>
          </div>
        </div>
        <a href="students.php" class="btn btn-sm btn-outline-maroon" onclick="setActiveSidebar('students')">Configure</a>
      </div>
    </div>

    <!-- Reports & Analytics -->
    <div class="control-card">
      <h4 class="control-section-title">
        <i class="bi bi-graph-up me-2"></i>Reports & Analytics
      </h4>
      
      <div class="action-item">
        <div class="d-flex align-items-center">
          <div class="action-icon">
            <i class="bi bi-file-earmark-text"></i>
          </div>
          <div>
            <strong>Attendance Reports</strong>
            <div class="text-muted small">Generate attendance summaries</div>
          </div>
        </div>
        <button class="btn btn-sm btn-maroon" onclick="generateReports()">Generate</button>
      </div>

      <div class="action-item">
        <div class="d-flex align-items-center">
          <div class="action-icon">
            <i class="bi bi-bar-chart"></i>
          </div>
          <div>
            <strong>Analytics Dashboard</strong>
            <div class="text-muted small">View system analytics</div>
          </div>
        </div>
        <a href="index.php" class="btn btn-sm btn-outline-maroon" onclick="setActiveSidebar('home')">View</a>
      </div>

      <div class="action-item">
        <div class="d-flex align-items-center">
          <div class="action-icon">
            <i class="bi bi-download"></i>
          </div>
          <div>
            <strong>Export Data</strong>
            <div class="text-muted small">Export attendance data</div>
          </div>
        </div>
        <button class="btn btn-sm btn-outline-maroon" onclick="exportData()">Export</button>
      </div>
    </div>
  </div>

</main>

<!-- Department Management Modal -->
<div class="modal fade" id="deptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background-color: var(--maroon); color: white;">
        <h5 class="modal-title">Department Management</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-hover" id="deptTable">
            <thead class="table-maroon">
              <tr>
                <th>ID</th>
                <th>Department Name</th>
                <th>Acronym</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- departments will be loaded via API -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-maroon" onclick="openAddDept()">Add Department</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header" style="background-color: var(--maroon); color: white;">
        <h5 class="modal-title">Edit User</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formEditUser" onsubmit="return false;">
          <input type="hidden" id="editUserId" value="0">
          <div class="mb-2">
            <label class="form-label">Department</label>
            <select class="form-select" id="editUserDepartment">
              <option value="">(none)</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Username</label>
            <input class="form-control" id="editUserUsername" placeholder="Username">
          </div>
          <div class="mb-2">
            <label class="form-label">New Password</label>
            <input class="form-control" id="editUserNewPassword" placeholder="Leave blank to keep current password">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-maroon" onclick="submitEditUser()">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Add/Edit Department Modal -->
<div class="modal fade" id="deptEditModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color: var(--maroon); color: white;">
        <h5 class="modal-title" id="deptEditTitle">Add Department</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formDept" onsubmit="return false;">
          <input type="hidden" id="deptId" value="0">
          <div class="mb-2">
            <label class="form-label">Department Name</label>
            <input class="form-control" id="deptName" placeholder="Department Name" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Acronym</label>
            <input class="form-control" id="deptAcronym" placeholder="Acronym" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-maroon" onclick="saveDept()">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Archive User Confirmation Modal -->
<div class="modal fade" id="archiveUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header  bg-danger text-white"><h5 class="modal-title">Archive User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Are you sure you want to archive <strong id="archive_username_display"></strong> ? This will disable the account but preserve the record.</p>
        <input type="hidden" id="archive_user_id" value="">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmArchiveBtn">Archive</button>
      </div>
    </div>
  </div>
</div>

<!-- Backup System Modal -->
<div class="modal fade" id="backupModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color: var(--maroon); color: white;">
        <h5 class="modal-title">System Backup</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Create a backup of the system database and configuration files.</p>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="backupDB" checked>
          <label class="form-check-label" for="backupDB">
            Include Database
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="backupFiles" checked>
          <label class="form-check-label" for="backupFiles">
            Include Configuration Files
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="backupImages">
          <label class="form-check-label" for="backupImages">
            Include Images and Assets
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-maroon" onclick="startBackup()">Create Backup</button>
      </div>
    </div>
  </div>
</div>

      <!-- Semester Management Modal -->
      <div class="modal fade" id="semesterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header" style="background-color: var(--maroon); color: white;">
              <h5 class="modal-title">Semester Management</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Semesters</h6>
     
              </div>
              <div class="table-responsive">
                <table class="table table-hover" id="semesterTable">
                  <thead class="table-maroon">
                    <tr>
                      <th>#</th>
                      <th>semester</th>
                      <th>start</th>
                      <th>end</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- semesters loaded dynamically -->
                  </tbody>
                </table>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Add/Edit Semester Modal -->
      <div class="modal fade" id="semesterEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
          <div class="modal-content">
            <div class="modal-header" style="background-color: var(--maroon); color: white;">
              <h5 class="modal-title" id="semesterModalTitle">Add Semester</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form id="semesterForm" onsubmit="return false;">
                <input type="hidden" id="semesterFormId" value="">
                <div class="mb-2">
                  <label class="form-label">Semester Name</label>
                  <input class="form-control" id="semesterName" placeholder="e.g. 2025-2" required>
                </div>
                <div class="mb-2">
                  <label class="form-label">Start Month</label>
                  <select class="form-select" id="semesterStart">
                    <option value="">Select month...</option>
                    <option value="01">Jan</option>
                    <option value="02">Feb</option>
                    <option value="03">Mar</option>
                    <option value="04">Apr</option>
                    <option value="05">May</option>
                    <option value="06">Jun</option>
                    <option value="07">Jul</option>
                    <option value="08">Aug</option>
                    <option value="09">Sep</option>
                    <option value="10">Oct</option>
                    <option value="11">Nov</option>
                    <option value="12">Dec</option>
                  </select>
                </div>
                <div class="mb-2">
                  <label class="form-label">End Month</label>
                  <select class="form-select" id="semesterEnd">
                    <option value="">Select month...</option>
                    <option value="01">Jan</option>
                    <option value="02">Feb</option>
                    <option value="03">Mar</option>
                    <option value="04">Apr</option>
                    <option value="05">May</option>
                    <option value="06">Jun</option>
                    <option value="07">Jul</option>
                    <option value="08">Aug</option>
                    <option value="09">Sep</option>
                    <option value="10">Oct</option>
                    <option value="11">Nov</option>
                    <option value="12">Dec</option>
                  </select>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-maroon" onclick="saveSemester()">Save</button>
            </div>
          </div>
        </div>
      </div>

<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background-color: var(--maroon); color: white;">
        <h5 class="modal-title">Create New Event</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="createEventForm" onsubmit="return false;">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="eventName" class="form-label fw-bold">Event Name</label>
              <input type="text" class="form-control" id="eventName" placeholder="Enter event name" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="eventDate" class="form-label fw-bold">Event Date</label>
              <input type="date" class="form-control" id="eventDate" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="semester" class="form-label fw-bold">Semester</label>
            <select class="form-select" id="semester" required disabled>
              <option value="">Select Semester</option>
            </select>
            <input type="hidden" name="semester" id="hiddenSemester">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Include Other Departments (Optional)</label>
            <div id="departmentsContainer" class="row">
              <div class="col-12 text-muted small">Loading departments...</div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Event Time</label>
            <div class="row">
              <div class="col-md-3">
                <label for="timeInStart" class="form-label small">Time In Start</label>
                <input type="time" class="form-control" id="timeInStart" required>
              </div>
              <div class="col-md-3">
                <label for="timeInEnd" class="form-label small">Time In End</label>
                <input type="time" class="form-control" id="timeInEnd" required>
              </div>
              <div class="col-md-3">
                <label for="timeOutStart" class="form-label small">Time Out Start</label>
                <input type="time" class="form-control" id="timeOutStart" required>
              </div>
              <div class="col-md-3">
                <label for="timeOutEnd" class="form-label small">Time Out End</label>
                <input type="time" class="form-control" id="timeOutEnd" required>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-maroon" onclick="submitCreateEvent()">Create Event</button>
      </div>
    </div>
  </div>
</div>

<!-- Attendance Reports Modal -->
<div class="modal fade" id="attendanceReportsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header" style="background-color: var(--maroon); color: white;">
        <h5 class="modal-title">Generate Attendance Report</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="reportFiltersForm" onsubmit="return false;">
          <div class="mb-3">
            <div class="row">
              <div class="col-md-6">
                <label class="form-label">From</label>
                <input type="date" class="form-control" id="reportStartDate">
              </div>
              <div class="col-md-6">
                <label class="form-label">To</label>
                <input type="date" class="form-control" id="reportEndDate">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Semester</label>
            <select class="form-select" id="reportSemester">
              <option value="all">All Semesters</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Department</label>
            <select class="form-select" id="reportDepartment">
              <option value="all">All Departments</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-maroon" onclick="submitAttendanceReport()">Generate Report</button>
      </div>
    </div>
  </div>
</div>

<!-- Export Data Confirmation Modal -->
<div class="modal fade" id="exportDataModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header" style="background-color: var(--maroon); color: white;">
        <h5 class="modal-title">Export Dashboard Data</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Export complete dashboard data including:</p>
        <ul class="mb-3">
          <li><strong>Students</strong> - Active student records with course info</li>
          <li><strong>Events</strong> - Active events with dates and semesters</li>
          <li><strong>Departments</strong> - All active departments</li>
          <li><strong>Attendance</strong> - Complete attendance summary (up to 5000 records)</li>
          <li><strong>Statistics</strong> - System metrics and counts</li>
        </ul>
        <p class="text-muted small">This may take a moment for large datasets. The file will download automatically.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-maroon" onclick="confirmExportData()">
          <i class="bi bi-download me-1"></i>Start Export
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

// Control Panel JavaScript Functions (UI-only implementations)

// Keep user department info global so submit handler can access it
let myDeptList = [];
let myDepartmentsIdCSV = '';

function refreshData() {
  // attempt to find the triggering button; accept optional event or element
  let btn = null;
  try { if (typeof event !== 'undefined' && event && event.target) btn = event.target.closest('button'); } catch(e){}
  if (!btn) btn = document.querySelector('.header-actions .btn-maroon');
  const originalText = btn ? btn.innerHTML : '';
  if (btn) { btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Refreshing...'; btn.disabled = true; }

  // call the real loaders and wait for completion
  Promise.allSettled([
    (typeof loadUsers === 'function') ? loadUsers() : Promise.resolve(),
    (typeof loadDepartments === 'function') ? loadDepartments() : Promise.resolve(),
    (typeof loadSemesters === 'function') ? loadSemesters() : Promise.resolve()
  ]).then(() => {
    if (btn) { btn.innerHTML = originalText; btn.disabled = false; }
    showAlert('Data refreshed successfully!', 'success');
  }).catch((err) => {
    console.error('Refresh error', err);
    if (btn) { btn.innerHTML = originalText; btn.disabled = false; }
    showAlert('Refresh completed with errors (check console)', 'warning');
  });
}

function manageAdmins() {
  // Open the Admin Accounts modal
  const modalEl = document.getElementById('adminModal');
  if (modalEl) {
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
  } else {
    showAlert('Admin management interface would open here', 'info');
  }
}

function addUser(){
  const name = document.getElementById('newUserName').value.trim();
  let user = document.getElementById('newUserUsername').value.trim();
  // replace any whitespace with hyphens to create a valid username
  user = user.replace(/\s+/g, '-');
  const pass = document.getElementById('newUserPassword').value.trim();
  if(!name || !user || !pass){
    showAlert('Please fill all fields', 'danger');
    return;
  }
  // UI-only: append to user table with profile link
  const tbody = document.querySelector('#userTable tbody');
  const newRow = document.createElement('tr');
  const idx = tbody.querySelectorAll('tr').length + 1;
  const profileUrl = `../Event/profile.php?user=${encodeURIComponent(user)}`;
  newRow.innerHTML = `<td>${idx}</td><td><a href="${profileUrl}">${escapeHtml(name)}</a></td><td>${escapeHtml(user)}</td><td><button class="btn btn-sm btn-outline-primary">Edit</button> <button class="btn btn-sm btn-outline-danger">Remove</button></td>`;
  tbody.appendChild(newRow);
  document.getElementById('formAddUser').reset();
  // collapse form
  // kept for compatibility when using the inline form (if any)
  const collapseEl = document.getElementById('addUserForm');
  if (collapseEl) {
    const bsCollapse = bootstrap.Collapse.getInstance(collapseEl) || new bootstrap.Collapse(collapseEl, {toggle:false});
    bsCollapse.hide();
  }
  showAlert('User added (UI-only)', 'success');
}

// small helper to avoid injecting raw HTML
function escapeHtml(unsafe) {
  return unsafe.replace(/[&<>"'`]/g, function (s) {
    return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#96;'})[s];
  });
}

// Helper: return a display month (e.g. 'Aug') from a date-like or month string.
function formatMonthName(dateStr) {
  if (!dateStr) return '';
  // accept 'MM' or 'YYYY-MM-DD' or 'YYYY-MM'
  let mm = null;
  const justMM = String(dateStr).match(/^\d{2}$/);
  if (justMM) mm = parseInt(justMM[0], 10);
  const ymd = String(dateStr).match(/(\d{4})-(\d{2})-(\d{2})/);
  if (ymd) mm = parseInt(ymd[2], 10);
  const ym = String(dateStr).match(/(\d{4})-(\d{2})$/);
  if (ym) mm = parseInt(ym[2], 10);
  if (mm === null) return String(dateStr);
  const d = new Date(2000, mm - 1, 1);
  return d.toLocaleString('en-US', { month: 'short' });
}

// Helper: convert month input (YYYY-MM) or date (YYYY-MM-DD) to canonical YYYY-MM-DD with year 2000
function canonicalizeToYear2000(dateStr, isEnd = false) {
  if (!dateStr) return '';
  // accept bare month 'MM'
  const mmOnly = String(dateStr).match(/^(\d{2})$/);
  if (mmOnly) {
    const mm = parseInt(mmOnly[1], 10);
    const startDay = isEnd ? new Date(2000, mm, 0).getDate() : 1;
    return `2000-${String(mm).padStart(2,'0')}-${String(startDay).padStart(2,'0')}`;
  }
  // YYYY-MM
  const ym = String(dateStr).match(/^(\d{4})-(\d{2})$/);
  if (ym) {
    const mm = parseInt(ym[2], 10);
    const startDay = isEnd ? new Date(2000, mm, 0).getDate() : 1;
    return `2000-${String(mm).padStart(2,'0')}-${String(startDay).padStart(2,'0')}`;
  }
  // full date
  const ymd = String(dateStr).match(/(\d{4})-(\d{2})-(\d{2})/);
  if (ymd) return `2000-${ymd[2]}-${ymd[3]}`;
  return dateStr;
}

// Handler for the new Add User modal
function addUserFromModal(){
  // modalUserName is a select; prefer option value as department identifier
  const deptSelect = document.getElementById('modalUserName');
  const deptValue = (deptSelect && deptSelect.selectedIndex > 0) ? deptSelect.value : '';
  const deptDisplay = (deptSelect && deptSelect.selectedIndex > 0) ? deptSelect.options[deptSelect.selectedIndex].text.trim() : '';
  let user = document.getElementById('modalUserUsername').value.trim();
  // replace any whitespace with hyphens to create a valid username
  user = user.replace(/\s+/g, '-');
  const pass = document.getElementById('modalUserPassword').value.trim();
  if(!deptValue || !user || !pass){
    showAlert('Please fill all fields', 'danger');
    return;
  }

  // send to backend API to create user
  (async () => {
    try{
      const form = new URLSearchParams();
      form.append('username', user);
      form.append('password', pass);
      form.append('account_type', 'user');
      // send department identifier (id or name/acronym depending on select value)
      form.append('department', deptValue);
      const res = await fetch('../api/users.php', { method: 'POST', body: form });
      const data = await res.json();
      if (data && data.success) {
        // refresh users list
        document.getElementById('formAddUserModal').reset();
        const modalEl = document.getElementById('addUserModal');
        const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        bsModal.hide();
        showAlert('User created successfully', 'success');
        loadUsers();
      } else {
        showAlert(data.error || 'Failed to create user', 'danger');
      }
    }catch(err){
      console.error(err);
      showAlert('Server error while creating user', 'danger');
    }
  })();
}

// Load active departments into the Add User modal select
async function loadDepartmentsForUserSelect(){
  const sel = document.getElementById('modalUserName');
  if (!sel) return;
  sel.innerHTML = '<option value="">Loading...</option>';
  try{
    const res = await fetch('../api/departments.php?archived=0');
    const json = await res.json();
    // build a map for easy lookup when rendering users
    window.__deptMap = window.__deptMap || {};
    window.__deptList = json && json.success && Array.isArray(json.data) ? json.data : [];
    sel.innerHTML = '<option value="">Select department...</option>';
    window.__deptMap = {};
    window.__deptList.forEach(d => {
      const name = d.name || d.Dept_name || d.dept_name || '';
      const acro = d.acronym || d.dept_Acronym || d.dept_acronym || '';
      const id = (d.id !== undefined && d.id !== null && d.id !== '') ? d.id : (d.Dept_ID || '');
      const val = id || name || acro;
      const label = name || acro || val;
      const opt = document.createElement('option');
      opt.value = val;
      opt.textContent = label;
      sel.appendChild(opt);
      // map multiple possible keys to the same label
      if (val) window.__deptMap[String(val)] = label;
      if (name) window.__deptMap[String(name)] = label;
      if (acro) window.__deptMap[String(acro)] = label;
    });
    // also populate edit select if present
    const editSel = document.getElementById('editUserDepartment');
    if (editSel) {
      editSel.innerHTML = '<option value="">(none)</option>' + Array.from(sel.options).slice(1).map(o => `<option value="${escapeHtml(o.value)}">${escapeHtml(o.textContent)}</option>`).join('');
    }
  }catch(err){
    console.error('Failed to load departments', err);
    sel.innerHTML = '<option value="">Select department...</option>';
  }
}

// Populate select each time the Add User modal opens
document.getElementById('addUserModal')?.addEventListener('show.bs.modal', loadDepartmentsForUserSelect);

// Load users into User Accounts modal
async function loadUsers(){
  const tbody = document.querySelector('#userTable tbody');
  tbody.innerHTML = '<tr><td colspan="4">Loading...</td></tr>';
  try{
    const res = await fetch('../api/users.php');
    const json = await res.json();
    if(!json.success){ tbody.innerHTML = '<tr><td colspan="4">Failed to load users</td></tr>'; return; }
    const users = json.data || [];
    // cache users for edit lookup (keep full list including admins)
    window.__usersCache = users;
    // ensure departments map is loaded
    if (!window.__deptMap) await loadDepartmentsForUserSelect();
    tbody.innerHTML = '';
    // filter out admin accounts and archived accounts from the displayed list
    function isArchivedUser(u){
      // support various backends: status='archived' or archived=1
      if(!u) return false;
      const s = (u.status !== undefined && u.status !== null) ? String(u.status).toLowerCase() : '';
      const a = (u.archived !== undefined && u.archived !== null) ? String(u.archived) : '';
      if(s === 'archived') return true;
      if(a === '1' || a === 'true' || a === 'yes' || a === 'on') return true;
      return false;
    }

    const visibleUsers = users.filter(u => {
      const role = (u.account_type || u.type || u.role || '').toString().toLowerCase();
      if (role === 'admin') return false;
      if (isArchivedUser(u)) return false;
      return true;
    });
    visibleUsers.forEach((u, i) => {
      const idx = i+1;
      const deptRaw = u.department || '';
      const deptText = (deptRaw !== '' && window.__deptMap && window.__deptMap[String(deptRaw)]) ? window.__deptMap[String(deptRaw)] : (deptRaw || '');
      const username = u.username || '';
      const id = u.id || '';
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${idx}</td><td>${escapeHtml(deptText)}</td><td>${escapeHtml(username)}</td><td><button class="btn btn-sm btn-outline-primary me-1" onclick="openEditUser('${encodeURIComponent(id||'')}', '${encodeURIComponent(username)}')">Edit</button><button class="btn btn-sm btn-outline-danger me-1" onclick="openArchiveUser('${encodeURIComponent(id||'')}', '${encodeURIComponent(username)}')">Archive</button><button class="btn btn-sm btn-outline-secondary" onclick="prefillReset('${encodeURIComponent(username)}')">Reset</button></td>`;
      tbody.appendChild(tr);
    });
    if (visibleUsers.length === 0) tbody.innerHTML = '<tr><td colspan="4">No users found</td></tr>';
  }catch(err){ console.error(err); tbody.innerHTML = '<tr><td colspan="4">Server error</td></tr>'; }
}

// When User Accounts modal opens, load users
document.getElementById('adminModal')?.addEventListener('show.bs.modal', loadUsers);

function openEditUser(encodedId, encodedUsername){
  const id = decodeURIComponent(encodedId || '');
  const username = decodeURIComponent(encodedUsername || '');
  // find user in cache
  const user = (window.__usersCache || []).find(u => String(u.id) === String(id) || u.username === username);
  document.getElementById('editUserId').value = user && user.id ? user.id : '';
  document.getElementById('editUserUsername').value = user && user.username ? user.username : '';
  document.getElementById('editUserNewPassword').value = '';
  // prefill department if available
  const editDept = document.getElementById('editUserDepartment');
  if (editDept) {
    // ensure departments are loaded
    if (!window.__deptMap) loadDepartmentsForUserSelect();
    const raw = user && (user.department !== undefined) ? String(user.department) : '';
    if (raw && editDept.querySelector(`option[value="${raw}"]`)) editDept.value = raw;
    else if (raw && window.__deptMap && window.__deptMap[raw]) {
      // find option by label
      for (const opt of editDept.options) {
        if (opt.textContent === window.__deptMap[raw]) { editDept.value = opt.value; break; }
      }
    } else {
      // try to match by mapped value
      for (const opt of editDept.options) {
        if (window.__deptMap && window.__deptMap[opt.value] === window.__deptMap[raw]) { editDept.value = opt.value; break; }
      }
    }
  }
  const bs = new bootstrap.Modal(document.getElementById('editUserModal'));
  bs.show();
}

// Delete user by id or username
async function deleteUser(encodedId, encodedUsername){
  const id = decodeURIComponent(encodedId || '');
  const username = decodeURIComponent(encodedUsername || '');
  const ident = id || username;
  if(!confirm('Delete user '+ident+'?')) return;
  try{
    const params = new URLSearchParams();
    if (id && /^[0-9]+$/.test(id)) params.append('id', id);
    else params.append('username', username);
    const res = await fetch('../api/users.php', { method: 'DELETE', body: params });
    const data = await res.json();
    if(data.success){ showAlert('User removed','success'); loadUsers(); }
    else showAlert(data.error || 'Failed to remove','danger');
  }catch(err){ console.error(err); showAlert('Server error','danger'); }
}

// Submit changes from Edit User modal
async function submitEditUser(){
  const id = document.getElementById('editUserId').value;
  const newUsername = document.getElementById('editUserUsername').value.trim();
  const newPassword = document.getElementById('editUserNewPassword').value;
  const dept = document.getElementById('editUserDepartment') ? document.getElementById('editUserDepartment').value : '';
  if (!id) { showAlert('Missing user id','danger'); return; }
  if (!newUsername && !newPassword) { showAlert('Nothing to update','info'); return; }
  try{
    const params = new URLSearchParams();
    params.append('id', id);
    if (newUsername) params.append('new_username', newUsername);
    if (newPassword) params.append('new_password', newPassword);
    if (dept) params.append('department', dept);
    const res = await fetch('../api/users.php', { method: 'PUT', body: params.toString(), headers: {'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'} });
    const data = await res.json();
    if (data.success) {
      showAlert('User updated','success');
      const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal')) || new bootstrap.Modal(document.getElementById('editUserModal'));
      modal.hide();
      loadUsers();
    } else {
      showAlert(data.error || 'Failed to update','danger');
    }
  }catch(err){ console.error(err); showAlert('Server error','danger'); }
}

// Prefill reset modal with username
function prefillReset(encodedUsername){
  const username = decodeURIComponent(encodedUsername);
  document.getElementById('resetIdentifier').value = username;
  const modalEl = document.getElementById('resetPasswordModal');
  const bs = new bootstrap.Modal(modalEl);
  bs.show();
}

function resetPasswords() {
  // this function is no longer a stub; open Reset Password modal instead
  const modalEl = document.getElementById('resetPasswordModal');
  if (modalEl) {
    const bs = new bootstrap.Modal(modalEl);
    bs.show();
  } else {
    showAlert('Password reset functionality would be available here', 'info');
  }
}

// Submit reset password to API
async function submitResetPassword(){
  const ident = document.getElementById('resetIdentifier').value.trim();
  const newpass = document.getElementById('resetNewPassword').value;
  if(!ident || !newpass){ showAlert('Please fill both fields','danger'); return; }
  const payload = new URLSearchParams();
  // use id if numeric
  if(/^[0-9]+$/.test(ident)) payload.append('id', ident);
  else payload.append('username', ident);
  payload.append('new_password', newpass);

  try{
    const res = await fetch('../api/users.php', { method: 'PUT', headers: {'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body: payload.toString() });
    const data = await res.json();
    if(data && data.success){
      showAlert('Password updated','success');
      const modal = bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal')) || new bootstrap.Modal(document.getElementById('resetPasswordModal'));
      modal.hide();
    } else {
      showAlert(data.error || 'Failed to update password','danger');
    }
  }catch(err){ console.error(err); showAlert('Server error','danger'); }
}

function createEvent() {
  // Open the Create Event modal
  const modalEl = document.getElementById('createEventModal');
  if (modalEl) {
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
    // Load form data when modal opens
    loadEventFormData();
  } else {
    showAlert('Create Event modal not found', 'danger');
  }
}

// Fetch semesters and departments and populate the form
async function loadEventFormData(){
  try{
    // Set minimum date for event date
    const dateInput = document.getElementById('eventDate');
    if (dateInput) {
      const today = new Date();
      const yyyy = today.getFullYear();
      const mm = String(today.getMonth() + 1).padStart(2, '0');
      const dd = String(today.getDate()).padStart(2, '0');
      dateInput.min = yyyy + '-' + mm + '-' + dd;
    }

    // load semesters
    const semRes = await fetch('../api/semesters.php');
    const semJson = await semRes.json();
    const semSel = document.getElementById('semester');
    if (semJson && semJson.success && Array.isArray(semJson.data)){
      semSel.innerHTML = '<option value="">Select Semester</option>';
      const now = new Date();
      const curMM = String(now.getMonth() + 1).padStart(2, '0');
      let autoValue = null;
      semJson.data.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id ? String(s.id) : (s.name || '');
        opt.textContent = s.name || (s.id ? String(s.id) : 'Unnamed');
        const start = s.start_date ?? s.start ?? s.startDate ?? null;
        const end = s.end_date ?? s.end ?? s.endDate ?? null;
        if (start) opt.dataset.start = String(start).padStart(2,'0');
        if (end) opt.dataset.end = String(end).padStart(2,'0');
        // compute auto-select: exact start month OR range include (handles wrap-around)
        if (autoValue === null && start) {
          const sM = String(start).padStart(2,'0');
          if (sM === curMM) autoValue = opt.value;
          else if (end) {
            const eM = String(end).padStart(2,'0');
            const sN = parseInt(sM,10), eN = parseInt(eM,10), mN = parseInt(curMM,10);
            if (sN <= eN) {
              if (mN >= sN && mN <= eN) autoValue = opt.value;
            } else {
              // wrap around year end (e.g., start=10 end=03)
              if (mN >= sN || mN <= eN) autoValue = opt.value;
            }
          }
        }
        semSel.appendChild(opt);
      });
      if (autoValue !== null) semSel.value = autoValue;
      document.getElementById('hiddenSemester').value = autoValue;

      // Set max date based on selected semester's end month
      const selectedOpt = semSel.querySelector('option[value="' + autoValue + '"]');
      if (selectedOpt && selectedOpt.dataset.end) {
        const endMonth = parseInt(selectedOpt.dataset.end, 10);
        const now = new Date();
        const year = now.getFullYear();
        const lastDay = new Date(year, endMonth, 0).getDate();
        dateInput.max = year + '-' + String(endMonth).padStart(2, '0') + '-' + String(lastDay).padStart(2, '0');
      }

      // Add event listener to update max date when semester changes
      semSel.addEventListener('change', function() {
        const selectedValue = this.value;
        const selectedOpt = this.querySelector('option[value="' + selectedValue + '"]');
        if (selectedOpt && selectedOpt.dataset.end) {
          const endMonth = parseInt(selectedOpt.dataset.end, 10);
          const now = new Date();
          const year = now.getFullYear();
          const lastDay = new Date(year, endMonth, 0).getDate();
          dateInput.max = year + '-' + String(endMonth).padStart(2, '0') + '-' + String(lastDay).padStart(2, '0');
        } else {
          // If no end date, remove max restriction
          dateInput.removeAttribute('max');
        }
        // Update hidden semester
        document.getElementById('hiddenSemester').value = selectedValue;
      });
    } else if (semJson && semJson.success && semJson.data && typeof semJson.data === 'object'){
      // single object response
      const s = semJson.data;
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

      // Set max date based on selected semester's end month
      if (opt.selected && opt.dataset.end) {
        const endMonth = parseInt(opt.dataset.end, 10);
        const now = new Date();
        const year = now.getFullYear();
        const lastDay = new Date(year, endMonth, 0).getDate();
        dateInput.max = year + '-' + String(endMonth).padStart(2, '0') + '-' + String(lastDay).padStart(2, '0');
      }

      // Add event listener to update max date when semester changes
      semSel.addEventListener('change', function() {
        const selectedValue = this.value;
        const selectedOpt = this.querySelector('option[value="' + selectedValue + '"]');
        if (selectedOpt && selectedOpt.dataset.end) {
          const endMonth = parseInt(selectedOpt.dataset.end, 10);
          const now = new Date();
          const year = now.getFullYear();
          const lastDay = new Date(year, endMonth, 0).getDate();
          dateInput.max = year + '-' + String(endMonth).padStart(2, '0') + '-' + String(lastDay).padStart(2, '0');
        } else {
          // If no end date, remove max restriction
          dateInput.removeAttribute('max');
        }
        // Update hidden semester
        document.getElementById('hiddenSemester').value = selectedValue;
      });
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
        const label = d.acronym ?? d.acro ?? d.name ?? d.Dept_name ?? d.dept_name ?? '';
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

async function submitCreateEvent(){
  const name = document.getElementById('eventName').value.trim();
  const date = document.getElementById('eventDate').value;
  const semester = document.getElementById('semester').value;
  const timeInStart = document.getElementById('timeInStart').value;
  const timeInEnd = document.getElementById('timeInEnd').value;
  const timeOutStart = document.getElementById('timeOutStart').value;
  const timeOutEnd = document.getElementById('timeOutEnd').value;
  const deptNodes = Array.from(document.querySelectorAll('#departmentsContainer input[name="departments[]"]:checked'));
  const departments = deptNodes.map(n => n.value);

  if(!name || !date || !semester || !timeInStart || !timeInEnd || !timeOutStart || !timeOutEnd){
    showAlert('Please fill all required fields', 'danger');
    return;
  }

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

  try{
    const res = await fetch('../api/events.php', {
      method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload), credentials: 'same-origin'
    });
    const j = await res.json();
    if (res.ok && j && j.success){
      showAlert('Event created successfully!', 'success');
      const modal = bootstrap.Modal.getInstance(document.getElementById('createEventModal')) || new bootstrap.Modal(document.getElementById('createEventModal'));
      modal.hide();
      document.getElementById('createEventForm').reset();
    } else {
      const msg = (j && j.error) ? j.error : 'Failed to create event';
      showAlert(msg, 'danger');
    }
  }catch(err){ console.error(err); showAlert('Network or server error', 'danger'); }
}

function viewArchive() {
  showAlert('Event archive would be displayed here', 'info');
}

// Semester management: open modal and load semesters list
function manageSemesters() {
  const modalEl = document.getElementById('semesterModal');
  if (!modalEl) { showAlert('Semester modal not found', 'danger'); return; }
  const bs = new bootstrap.Modal(modalEl);
  bs.show();
  loadSemesters();
}

// Load semesters from API and populate table
async function loadSemesters(){
  const tbody = document.querySelector('#semesterTable tbody');
  if (!tbody) return;
  tbody.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
  try{
    const res = await fetch('../api/semesters.php?archived=0');
    const json = await res.json();
  if(!json.success){ tbody.innerHTML = '<tr><td colspan="5">Failed to load</td></tr>'; return; }
  const rows = json.data || [];
    // sort by year (newest first) and term order: 1st, then 2nd, then Summer
    const getSemesterMeta = (s) => {
      const raw = String(s.name || s.semester || '') || '';
      const name = raw.trim();
      let year = 0;
      const yMatch = name.match(/(19|20)\d{2}/);
      if (yMatch) year = parseInt(yMatch[0], 10);
      else {
        const m = name.match(/^(\d{4})/);
        if (m) year = parseInt(m[1], 10);
      }

      // term ordering heuristic: 1 -> first, 2 -> second, 3 -> summer, fallback 99
      let termOrder = 99;
      // check patterns like "2025-1", "2025_2" or "2025 1"
      const afterMatch = name.match(/^(\d{4})[-_\s]?(\d+)/);
      if (afterMatch && afterMatch[2]) {
        const num = parseInt(afterMatch[2], 10);
        if (num === 1) termOrder = 1;
        else if (num === 2) termOrder = 2;
        else if (num >= 3) termOrder = 3;
      } else if (/\b1st\b|\bfirst\b|\b\b1\b/i.test(name)) termOrder = 1;
      else if (/\b2nd\b|\bsecond\b|\b\b2\b/i.test(name)) termOrder = 2;
      else if (/summer|summa|sumer/i.test(name)) termOrder = 3;
      else if (/spring/i.test(name)) termOrder = 2;
      else if (/fall|autumn/i.test(name)) termOrder = 1;

      return { name, year, termOrder };
    };

    rows.sort((a, b) => {
      const A = getSemesterMeta(a);
      const B = getSemesterMeta(b);
      // newest year first
      if (A.year !== B.year) return (B.year || 0) - (A.year || 0);
      // then by term order (1,2,3)
      if (A.termOrder !== B.termOrder) return A.termOrder - B.termOrder;
      // final fallback: alphabetical by name
      return (A.name || '').localeCompare(B.name || '');
    });

    tbody.innerHTML = '';
    rows.forEach((s, i) => {
      const idx = i+1;
      const id = s.id || '';
      const name = s.name || '';
      // display month names only (no year) to make semesters reusable across years
      const start = formatMonthName(s.start_date || s.start || '');
      const end = formatMonthName(s.end_date || s.end || '');
      const startText = start || '';
      const endText = end || '';
  const tr = document.createElement('tr');
  tr.innerHTML = `<td>${idx}</td><td>${escapeHtml(name)}</td><td>${escapeHtml(startText)}</td><td>${escapeHtml(endText)}</td><td><button class="btn btn-sm btn-danger" onclick="openEditSemester('${encodeURIComponent(id)}')">Edit</button></td>`;
      tbody.appendChild(tr);
    });
  if(rows.length===0) tbody.innerHTML = '<tr><td colspan="5">No semesters found</td></tr>';
  }catch(err){ console.error(err); tbody.innerHTML = '<tr><td colspan="5">Server error</td></tr>'; }
}

function openAddSemester(){
  document.getElementById('semesterFormId').value = '';
  document.getElementById('semesterName').value = '';
  // default to Aug for start; leave end blank (optional)
  document.getElementById('semesterStart').value = '08';
  document.getElementById('semesterEnd').value = '';
  document.getElementById('semesterModalTitle').textContent = 'Add Semester';
  const bs = new bootstrap.Modal(document.getElementById('semesterEditModal'));
  bs.show();
}

async function openEditSemester(encodedId){
  const id = decodeURIComponent(encodedId || '');
  if(!id){ showAlert('Missing id','danger'); return; }
  try{
    const res = await fetch('../api/semesters.php?id='+encodeURIComponent(id));
    const json = await res.json();
    if(!json.success){ showAlert('Failed to fetch semester','danger'); return; }
    const s = json.data;
    if(!s){ showAlert('Semester not found','danger'); return; }
  document.getElementById('semesterFormId').value = s.id || '';
  document.getElementById('semesterName').value = s.name || '';
  // convert canonical YYYY-MM-DD to month input MM (accept 'MM', 'YYYY-MM', 'YYYY-MM-DD')
  const startRaw = s.start_date || s.start || '';
  const endRaw = s.end_date || s.end || '';
  const startMM = (startRaw.match(/^(?:\d{4}-)?(\d{2})-(\d{2})$/) || startRaw.match(/^(?:\d{4}-)?(\d{2})$/) || startRaw.match(/^(\d{2})$/));
  const endMM = (endRaw.match(/^(?:\d{4}-)?(\d{2})-(\d{2})$/) || endRaw.match(/^(?:\d{4}-)?(\d{2})$/) || endRaw.match(/^(\d{2})$/));
  document.getElementById('semesterStart').value = startMM ? startMM[1] : '';
  document.getElementById('semesterEnd').value = endMM ? endMM[1] : '';
    document.getElementById('semesterModalTitle').textContent = 'Edit Semester';
    const bs = new bootstrap.Modal(document.getElementById('semesterEditModal'));
    bs.show();
  }catch(err){ console.error(err); showAlert('Server error','danger'); }
}

async function saveSemester(){
  const id = document.getElementById('semesterFormId').value.trim();
  const name = document.getElementById('semesterName').value.trim();
  const start = document.getElementById('semesterStart').value.trim(); // expects 'MM' now
  const end = document.getElementById('semesterEnd').value.trim(); // expects 'MM'
  if(!name){ showAlert('Name required','danger'); return; }
  try{
    if(!id){
      // canonicalize start to year 2000; include end only if provided
      const fd = new FormData(); fd.append('name', name); fd.append('start_date', canonicalizeToYear2000(start, false));
      if (end) fd.append('end_date', canonicalizeToYear2000(end, true));
      const res = await fetch('../api/semesters.php', { method: 'POST', body: fd });
      const json = await res.json();
      if(json.success){ showAlert('Semester added','success'); const bs = bootstrap.Modal.getInstance(document.getElementById('semesterEditModal')) || new bootstrap.Modal(document.getElementById('semesterEditModal')); bs.hide(); loadSemesters(); }
      else showAlert(json.error || 'Failed to add','danger');
    } else {
      // build body; include end only if present
      let body = `id=${encodeURIComponent(id)}&name=${encodeURIComponent(name)}&start_date=${encodeURIComponent(canonicalizeToYear2000(start, false))}`;
      if (end) body += `&end_date=${encodeURIComponent(canonicalizeToYear2000(end, true))}`;
      const res = await fetch('../api/semesters.php', { method: 'PUT', body: body });
      const json = await res.json();
      if(json.success){ showAlert('Semester updated','success'); const bs = bootstrap.Modal.getInstance(document.getElementById('semesterEditModal')) || new bootstrap.Modal(document.getElementById('semesterEditModal')); bs.hide(); loadSemesters(); }
      else showAlert(json.error || 'Failed to update','danger');
    }
  }catch(err){ console.error(err); showAlert('Server error','danger'); }
}



function manageRFID() {
  showAlert('Check the Student page', 'info');
}

function generateReports() {
  // Load semesters and departments into the report modal
  (async () => {
    try {
      // Load semesters
      const semRes = await fetch('../api/semesters.php');
      const semJson = await semRes.json();
      const semSel = document.getElementById('reportSemester');
      if (semJson && semJson.success && Array.isArray(semJson.data)) {
        semJson.data.forEach(s => {
          const opt = document.createElement('option');
          opt.value = s.id || '';
          opt.textContent = s.name || (s.id ? `Semester ${s.id}` : 'Unnamed');
          semSel.appendChild(opt);
        });
      }

      // Load departments
      const deptRes = await fetch('../api/departments.php?archived=0');
      const deptJson = await deptRes.json();
      const deptSel = document.getElementById('reportDepartment');
      if (deptJson && deptJson.success && Array.isArray(deptJson.data)) {
        deptJson.data.forEach(d => {
          const opt = document.createElement('option');
          opt.value = d.id || '';
          opt.textContent = d.name || d.Dept_name || (d.id ? `Dept ${d.id}` : 'Unnamed');
          deptSel.appendChild(opt);
        });
      }
    } catch(err) {
      console.error('Failed to load report filters', err);
      showAlert('Error loading report options', 'danger');
    }
  })();

  // Open the modal
  const modalEl = document.getElementById('attendanceReportsModal');
  const modal = new bootstrap.Modal(modalEl);
  modal.show();
}

function submitAttendanceReport() {
  const startDate = document.getElementById('reportStartDate').value;
  const endDate = document.getElementById('reportEndDate').value;
  const semester = document.getElementById('reportSemester').value;
  const department = document.getElementById('reportDepartment').value;

  // Build query string
  const params = new URLSearchParams();
  if (startDate) params.append('start_date', startDate);
  if (endDate) params.append('end_date', endDate);
  if (semester && semester !== 'all') params.append('semester_id', semester);
  if (department && department !== 'all') params.append('department_id', department);

  // Download report
  const url = 'reports.php' + (params.toString() ? '?' + params.toString() : '');
  window.location.href = url;

  // Close modal
  const modalEl = document.getElementById('attendanceReportsModal');
  const modal = bootstrap.Modal.getInstance(modalEl);
  if (modal) modal.hide();

  showAlert('Report download started', 'success');
}

function exportData() {
  // Open the Export Data confirmation modal
  const modalEl = document.getElementById('exportDataModal');
  if (modalEl) {
    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();
  } else {
    showAlert('Export modal not found', 'danger');
  }
}

function confirmExportData() {
  // Get the button and modal elements
  const btn = document.querySelector('#exportDataModal .btn-maroon');
  const modalEl = document.getElementById('exportDataModal');
  const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
  
  // Update button to show loading state
  const originalText = btn ? btn.innerHTML : '<i class="bi bi-download me-1"></i>Start Export';
  if (btn) { 
    btn.innerHTML = '<i class="bi bi-download me-1"></i>Exporting...'; 
    btn.disabled = true; 
  }
  
  // Close modal and start export
  modal.hide();
  
  // Trigger the download
  window.location.href = 'export.php';
  
  // Re-enable button after a short delay
  setTimeout(() => {
    if (btn) { 
      btn.innerHTML = originalText; 
      btn.disabled = false; 
    }
  }, 2000);
}

function startBackup() {
  const modalEl = document.getElementById('backupModal');
  const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
  // gather options
  const includeDB = document.getElementById('backupDB') && document.getElementById('backupDB').checked;
  const includeFiles = document.getElementById('backupFiles') && document.getElementById('backupFiles').checked;
  const includeImages = document.getElementById('backupImages') && document.getElementById('backupImages').checked;
  modal.hide();
  showAlert('Preparing backup...', 'info');

  (async () => {
    try{
      const body = new FormData();
      body.append('backupDB', includeDB ? '1' : '0');
      body.append('backupFiles', includeFiles ? '1' : '0');
      body.append('backupImages', includeImages ? '1' : '0');
  const res = await fetch('backup.php', { method: 'POST', body: body, credentials: 'include' });
      if (!res.ok) {
        // attempt to read json or text message and show it
        let msg = 'Backup failed on server';
        try { const j = await res.json(); if (j && j.error) msg = j.error; } catch(e){ try { const t = await res.text(); if (t) msg = t; }catch(_){} }
        showAlert(msg, 'danger');
        return;
      }
      const contentType = res.headers.get('Content-Type') || '';
      if (!contentType.includes('zip') && !contentType.includes('application/octet-stream')) {
        // server returned something else (likely error HTML/json). Read and show it.
        let text = await res.text();
        showAlert('Backup failed: ' + (text ? text.substring(0,200) : 'unknown'), 'danger');
        console.error('Non-zip backup response:', text);
        return;
      }
      const blob = await res.blob();
      // derive filename from content-disposition or fallback
      let filename = 'stc_backup.zip';
      const cd = res.headers.get('Content-Disposition');
      if (cd) {
        const m = cd.match(/filename\*?=([^;]+)/);
        if (m && m[1]) filename = m[1].replace(/\"/g,'').trim();
      }
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = filename;
      document.body.appendChild(a); a.click(); a.remove();
      window.URL.revokeObjectURL(url);
      showAlert('Backup downloaded', 'success');
    }catch(err){ console.error(err); showAlert('Backup failed (see console)', 'danger'); }
  })();
}

function showAlert(message, type = 'info') {
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type} position-fixed`;
  alertDiv.style.top = '1rem';
  alertDiv.style.right = '1rem';
  alertDiv.style.zIndex = '2000';
  alertDiv.style.maxWidth = '300px';
  alertDiv.textContent = message;
  
  document.body.appendChild(alertDiv);
  
  setTimeout(() => {
    alertDiv.remove();
  }, 3000);
}

// Auto-refresh activity feed (simulated)
function updateActivityFeed() {
  const activities = [
    'New student scan: LOPEZ, ANA MARIE (4227302)',
    'Event status updated: "Orientation Week"',
    'Department added: "Graduate Studies"',
    'RFID card batch imported: 50 new cards'
  ];
  
  // This would normally fetch real data from the server
  console.log('Activity feed would update with real-time data');
}

// Initialize auto-refresh
setInterval(updateActivityFeed, 30000); // Every 30 seconds

// Load event form data when modal is shown
document.getElementById('createEventModal')?.addEventListener('show.bs.modal', loadEventFormData);

// Departments API integration
async function loadDepartments(){
  try{
    const res = await fetch('../api/departments.php?archived=0');
    const data = await res.json();
    if(!data.success) { showAlert('Failed to load departments','danger'); return; }
    const tbody = document.querySelector('#deptTable tbody');
    tbody.innerHTML = '';
    // keep a cache so we can reference by index when id column is missing
    window.departmentsCache = data.data || [];
    window.departmentsCache.forEach((d, i) => {
      // determine display id (number) and actual id if present
      let displayId = i + 1;
      let realId = null;
      // try to find a likely id property
      for (const k in d) {
        if (/\bid\b/i.test(k)) { realId = d[k]; break; }
      }
      const name = d.Dept_name || d.dept_name || d.name || '';
      const acro = d.dept_Acronym || d.dept_acronym || d.acronym || '';
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${displayId}</td><td>${escapeHtml(name)}</td><td>${escapeHtml(acro)}</td><td><button class="btn btn-sm btn-outline-primary me-1" onclick="openEditDept(${i})">Edit</button><button class="btn btn-sm btn-outline-danger" onclick="archiveDept(${i})">Archive</button></td>`;
      tbody.appendChild(tr);
    });
  }catch(err){
    console.error(err);
    showAlert('Error loading departments','danger');
  }
}

function openAddDept(){
  document.getElementById('deptEditTitle').textContent = 'Add Department';
  document.getElementById('deptId').value = '0';
  document.getElementById('deptName').value = '';
  document.getElementById('deptAcronym').value = '';
  const modalEl = document.getElementById('deptEditModal');
  const bs = new bootstrap.Modal(modalEl);
  bs.show();
}

function openEditDept(index){
  const d = window.departmentsCache && window.departmentsCache[index];
  if (!d) { showAlert('Department not found','danger'); return; }
  document.getElementById('deptEditTitle').textContent = 'Edit Department';
  // set internal id if present
  let realId = null;
  for (const k in d) { if (/\bid\b/i.test(k)) { realId = d[k]; break; } }
  document.getElementById('deptId').value = realId ? realId : 0;
  // store index for fallback
  document.getElementById('deptId').dataset.idx = index;
  document.getElementById('deptName').value = d.Dept_name || d.dept_name || d.name || '';
  document.getElementById('deptAcronym').value = d.dept_Acronym || d.dept_acronym || d.acronym || '';
  const modalEl = document.getElementById('deptEditModal');
  const bs = new bootstrap.Modal(modalEl);
  bs.show();
}

async function saveDept(){
  const id = parseInt(document.getElementById('deptId').value || '0', 10);
  const name = document.getElementById('deptName').value.trim();
  const acronym = document.getElementById('deptAcronym').value.trim();
  if(!name){ showAlert('Name is required','danger'); return; }

  try{
    if(id === 0){
      const fd = new FormData(); fd.append('name', name); fd.append('acronym', acronym);
      const res = await fetch('../api/departments.php', { method: 'POST', body: fd });
      const data = await res.json();
      if(data.success){
        showAlert('Department added','success');
        const modal = bootstrap.Modal.getInstance(document.getElementById('deptEditModal')) || new bootstrap.Modal(document.getElementById('deptEditModal'));
        modal.hide();
        loadDepartments();
      } else {
        showAlert(data.error || 'Failed to add','danger');
      }
    } else {
      // if we don't have a real id, fallback to using the original name to identify row
      const idx = parseInt(document.getElementById('deptId').dataset.idx || '-1', 10);
      let body;
      if (idx >= 0 && (!id || id === 0)) {
        const original = window.departmentsCache && window.departmentsCache[idx];
        const originalName = original && (original.Dept_name || original.dept_name || original.name);
        body = `name=${encodeURIComponent(originalName)}&new_name=${encodeURIComponent(name)}&acronym=${encodeURIComponent(acronym)}`;
      } else {
        body = `id=${encodeURIComponent(id)}&name=${encodeURIComponent(name)}&acronym=${encodeURIComponent(acronym)}`;
      }
      const res = await fetch('../api/departments.php', { method: 'PUT', body: body });
      const data = await res.json();
      if(data.success){
        showAlert('Department updated','success');
        const modal = bootstrap.Modal.getInstance(document.getElementById('deptEditModal')) || new bootstrap.Modal(document.getElementById('deptEditModal'));
        modal.hide();
        loadDepartments();
      } else {
        showAlert(data.error || 'Failed to update','danger');
      }
    }
  }catch(err){ console.error(err); showAlert('Server error','danger'); }
}

async function archiveDept(id){
  if(!confirm('Archive this department?')) return;
  try{
    // id may be an index number; support passing index
    let realId = null;
    if (typeof id === 'number') {
      const d = window.departmentsCache && window.departmentsCache[id];
      if (d) {
        for (const k in d) { if (/\bid\b/i.test(k)) { realId = d[k]; break; } }
      }
    }
    let body;
    if (realId) body = `id=${encodeURIComponent(realId)}`;
    else {
      // fallback: send name to identify row
      const d = window.departmentsCache && window.departmentsCache[id];
      const name = d && (d.Dept_name || d.dept_name || d.name);
      body = `name=${encodeURIComponent(name)}`;
    }
    const res = await fetch('../api/departments.php', { method: 'DELETE', body: body });
    const data = await res.json();
    if(data.success){ showAlert('Department archived','success'); loadDepartments(); }
    else showAlert(data.error || 'Failed to archive','danger');
  }catch(err){ console.error(err); showAlert('Server error','danger'); }
}

// Load departments when modal opens
document.getElementById('deptModal')?.addEventListener('show.bs.modal', loadDepartments);

// Open archive user modal (from Users list)
function openArchiveUser(encodedId, encodedUsername){
  const id = decodeURIComponent(encodedId || '');
  const username = decodeURIComponent(encodedUsername || '');
  document.getElementById('archive_user_id').value = id || username || '';
  document.getElementById('archive_username_display').textContent = username || id || '';
  const modalEl = document.getElementById('archiveUserModal');
  const bs = new bootstrap.Modal(modalEl);
  bs.show();
}

// Perform archive (set status=archived) when confirmed
document.getElementById('confirmArchiveBtn')?.addEventListener('click', async function(){
  const idOrUsername = document.getElementById('archive_user_id').value || '';
  if(!idOrUsername) { showAlert('Missing identifier','danger'); return; }
  // hide modal
  try{ const modalEl = document.getElementById('archiveUserModal'); const m = bootstrap.Modal.getInstance(modalEl); if (m) m.hide(); } catch(e){}
  // build params; prefer numeric id when possible
  const params = new URLSearchParams();
  if (/^[0-9]+$/.test(idOrUsername)) params.append('id', idOrUsername);
  else params.append('username', idOrUsername);
  params.append('status', 'archived');
  try{
    const res = await fetch('../api/users.php', { method: 'PUT', headers: {'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body: params.toString() });
    const data = await res.json();
    if (data && data.success) { showAlert('User archived','success'); loadUsers(); }
    else showAlert(data.error || 'Failed to archive','danger');
  }catch(err){ console.error(err); showAlert('Server error','danger'); }
});
</script>

</body>
</html>
