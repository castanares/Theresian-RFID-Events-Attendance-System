<?php require_once __DIR__ . '/../auth.php'; require_login('/new/'); ?>
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
        <div class="profile-avatar mx-auto mx-md-0">
          <i class="bi bi-person-fill"></i>
        </div>
      </div>
      <div class="col-md text-center text-md-start">
        <h2 class="mb-2">CBTA Department</h2>
        <p class="mb-3 opacity-75">College of Business Technology and Arts</p>
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
              <input type="text" class="form-control" value="College of Business Technology and Arts" disabled id="deptName">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Department Code</label>
              <input type="text" class="form-control" value="CBTA" disabled id="deptCode">
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Contact Email</label>
              <input type="email" class="form-control" value="cbta@stctandag.edu.ph" disabled id="deptEmail">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Contact Phone</label>
              <input type="tel" class="form-control" value="+63 912 345 6789" disabled id="deptPhone">
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Department Head</label>
            <input type="text" class="form-control" value="Dr. Maria Santos, Ph.D." disabled id="deptHead">
          </div>
          
          <div class="mb-3">
            <label class="form-label">Office Location</label>
            <input type="text" class="form-control" value="Administration Building, 2nd Floor, Room 201" disabled id="deptLocation">
          </div>
          
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" rows="3" disabled id="deptDescription">The College of Business Technology and Arts offers comprehensive programs in business administration, information technology, and creative arts, preparing students for successful careers in the modern economy.</textarea>
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

      <!-- Department Settings -->
      <div class="profile-card" id="profileInfo">
        <div class="card-header-maroon">
          <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Department Settings</h5>
        </div>
        
        <div class="info-item">
          <div>
            <div class="info-label">Event Notifications</div>
            <small class="text-muted">Receive notifications for new events</small>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="eventNotifications" checked>
          </div>
        </div>
        
        <div class="info-item">
          <div>
            <div class="info-label">Auto-approve Student Registrations</div>
            <small class="text-muted">Automatically approve student event registrations</small>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="autoApprove">
          </div>
        </div>
        
        <div class="info-item">
          <div>
            <div class="info-label">Email Reminders</div>
            <small class="text-muted">Send email reminders for upcoming events</small>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="emailReminders" checked>
          </div>
        </div>
        
        <!-- <div class="info-item">
          <div>
            <div class="info-label">QR Code Access</div>
            <small class="text-muted">Allow QR code scanning for attendance</small>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="qrAccess" checked>
          </div>
        </div> -->
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
              <div class="stat-number">24</div>
              <div class="stat-label">Active Events</div>
            </div>
          </div>
          <div class="col-6 mb-3">
            <div class="stat-card">
              <div class="stat-number">456</div>
              <div class="stat-label">Total Students</div>
            </div>
          </div>
          <div class="col-6 mb-3">
            <div class="stat-card">
              <div class="stat-number">89%</div>
              <div class="stat-label">Attendance Rate</div>
            </div>
          </div>
          <div class="col-6 mb-3">
            <div class="stat-card">
              <div class="stat-number">12</div>
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
          <a href="Add_student.php" class="btn btn-maroon">
            <i class="bi bi-person-plus me-2"></i>Add Student
          </a>
          <a href="index.php" class="btn btn-outline-maroon">
            <i class="bi bi-calendar-event me-2"></i>View Events
          </a>
          <a href="scanlog.php" class="btn btn-outline-maroon">
            <i class="bi bi-qr-code-scan me-2"></i>Scan Logs
          </a>
          <button class="btn btn-outline-maroon" onclick="generateReport()">
            <i class="bi bi-file-earmark-text me-2"></i>Generate Report
          </button>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="profile-card">
        <div class="card-header-maroon">
          <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Activity</h5>
        </div>
        
        <div class="event-item">
          <h6 class="mb-1 text-maroon">Student Registration</h6>
          <p class="mb-1">New student registered: Juan Dela Cruz</p>
          <small class="text-muted"><i class="bi bi-clock me-1"></i>2 hours ago</small>
        </div>
        
        <div class="event-item">
          <h6 class="mb-1 text-maroon">Event Attendance</h6>
          <p class="mb-1">Tech Summit 2024 - 45 attendees</p>
          <small class="text-muted"><i class="bi bi-clock me-1"></i>5 hours ago</small>
        </div>
        
        <div class="event-item">
          <h6 class="mb-1 text-maroon">QR Scan Activity</h6>
          <p class="mb-1">Multiple check-ins recorded</p>
          <small class="text-muted"><i class="bi bi-clock me-1"></i>1 day ago</small>
        </div>
        
        <div class="text-center mt-3">
          <a href="scanlog.php" class="text-maroon text-decoration-none">
            <i class="bi bi-arrow-right me-1"></i>View All Activity
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editProfile() {
  // Enable form fields
  const fields = ['deptName', 'deptCode', 'deptEmail', 'deptPhone', 'deptHead', 'deptLocation', 'deptDescription'];
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
  const fields = ['deptName', 'deptCode', 'deptEmail', 'deptPhone', 'deptHead', 'deptLocation', 'deptDescription'];
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

function saveProfile() {
  // Simulate save
  alert('Department profile updated successfully!');
  cancelEdit();
}

function generateReport() {
  alert('Generating department activity report...');
}

// Settings toggle handlers
document.getElementById('eventNotifications').addEventListener('change', function() {
  const message = this.checked ? 'Event notifications enabled!' : 'Event notifications disabled!';
  console.log(message);
});

document.getElementById('autoApprove').addEventListener('change', function() {
  const message = this.checked ? 'Auto-approve enabled!' : 'Auto-approve disabled!';
  console.log(message);
});

document.getElementById('emailReminders').addEventListener('change', function() {
  const message = this.checked ? 'Email reminders enabled!' : 'Email reminders disabled!';
  console.log(message);
});

document.getElementById('qrAccess').addEventListener('change', function() {
  const message = this.checked ? 'QR code access enabled!' : 'QR code access disabled!';
  console.log(message);
});

// Smooth scroll for anchor links
document.addEventListener('DOMContentLoaded', function() {
  const hash = window.location.hash;
  if (hash) {
    const element = document.querySelector(hash);
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' });
    }
  }
});
</script>
</body>
</html>
