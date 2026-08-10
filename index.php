
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>College Sign In</title>
  <link rel="stylesheet" href="styles/dash_styles.css">
  <link rel="stylesheet" href="styles/side_styles.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
  <style>
    :root { --maroon: #6b0f0f; }
    body { background: linear-gradient(180deg,#faf8f8,#f2eded); min-height:100vh; display:flex; align-items:center; }
    .auth-wrap { width: 100%; max-width: 1100px; margin: 6vh auto; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: stretch; }
    .brand-panel { background: var(--maroon); color: #fff; border-radius: 12px; padding: 2.25rem; display:flex; flex-direction:column; align-items:center; justify-content:center; }
    .brand-panel img { width: 160px; margin-bottom: 1rem; }
    .brand-panel h2 { letter-spacing: 1px; font-weight:700; }
    .brand-panel p { opacity:0.9; max-width: 320px; text-align:center; }
    .form-panel { background: #fff; border-radius: 12px; padding: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.07); }
    .form-heading { color: var(--maroon); font-weight:700; margin-bottom: 0.5rem; }
    .input-group-text { background: #fff; border-right:0; }
    /* Ensure bootstrap icons are not stretched inside input-group-text */

    .form-control { border-left:0; }
    .form-control:focus { box-shadow: none; border-color: var(--maroon); }
    .btn-primary.signin { background: var(--maroon); border: none; }
    .meta-row { display:flex; justify-content:space-between; align-items:center; margin-top: .5rem; }
    .forgot { font-size: .9rem; }
    @media (max-width: 900px) { .auth-wrap { grid-template-columns: 1fr; padding: 1rem; } .brand-panel { order:2; } }

    .input-group-text .bi {
      font-size: 1rem; /* match normal text size */
      line-height: 1;   /* prevent extra height */
      display: inline-block;
      vertical-align: middle;
     
    }

  </style>
</head>
<body>
  <main class="container">
    <div class="auth-wrap">
      <div class="brand-panel">
        <img src="images/stctandag-logo.png" alt="logo">
        <h2>STC RFID Attendance</h2>
        <p>secure sign in to access the event attendance system.</p>
      </div>

      <div class="form-panel">
        <h4 class="form-heading">Sign in to your account</h4>
        <p class="text-muted">Use your assigned username and password.</p>
        
        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_message); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
   
        
        <form method="POST" action="" id="signinForm">
          <input type="hidden" name="login" value="1">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-lock" fill="maroon"></i></span>
              <input type="text" name="username" class="form-control" placeholder="Username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
              <span class="input-group-text">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="maroon" class="bi bi-lock" viewBox="0 0 16 16">
                  <path fill-rule="evenodd" d="M8 0a4 4 0 0 1 4 4v2.05a2.5 2.5 0 0 1 2 2.45v5a2.5 2.5 0 0 1-2.5 2.5h-7A2.5 2.5 0 0 1 2 13.5v-5a2.5 2.5 0 0 1 2-2.45V4a4 4 0 0 1 4-4M4.5 7A1.5 1.5 0 0 0 3 8.5v5A1.5 1.5 0 0 0 4.5 15h7a1.5 1.5 0 0 0 1.5-1.5v-5A1.5 1.5 0 0 0 11.5 7zM8 1a3 3 0 0 0-3 3v2h6V4a3 3 0 0 0-3-3"/>
                </svg>
                </span>
              <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
          </div>
          <div class="meta-row">
            <div class="form-check">
              <input class="form-check-input input-danger" type="checkbox" id="remember">
              <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <a href="#" class="forgot" id="forgotLink">Forgot password?</a>
          </div>
          <div class="d-grid mt-3">
            <button id="signinBtn" class="btn btn-primary signin" type="submit">Sign In</button>
          </div>
          <p class="text-center text-muted mt-3">Need help? Contact the OSAS office.</p>
        </form>

      </div>
    </div>
  </main>

  <!-- OTP Verification Modal (Admin) -->
  <div class="modal fade" id="otpModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <div class="modal-header" style="background: var(--maroon); color: #fff;">
          <h5 class="modal-title"><i class="bi bi-shield-check me-2"></i>Verify OTP</h5>
        </div>
        <div class="modal-body">
          <p class="text-muted">An OTP has been sent to your email address. Enter the 6-digit code below.</p>
          <div class="alert alert-info" id="otpEmailDisplay" style="font-size: 0.9rem;">
            <!-- Email will be displayed here -->
          </div>
          <form id="otpForm" onsubmit="return false;">
            <div class="mb-3">
              <label class="form-label">One-Time Password</label>
              <input type="text" class="form-control form-control-lg text-center" id="otpInput" placeholder="000000" maxlength="6" inputmode="numeric" required pattern="\d{6}">
              <small class="text-muted">Valid for 10 minutes</small>
            </div>
          </form>
          <div id="otpError" class="alert alert-danger" style="display:none;"></div>
          <div id="otpSuccess" class="alert alert-success" style="display:none;"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="otpCancel" onclick="resetLoginForm()">Cancel</button>
          <button type="button" class="btn btn-primary signin" id="otpSubmit" onclick="verifyOTP()">Verify OTP</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Forgot Password Modal -->
  <div class="modal fade" id="forgotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <div class="modal-header" style="background: var(--maroon); color: #fff;">
          <h5 class="modal-title"><i class="bi bi-key me-2"></i>Forgot Password</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="small text-muted">Enter the username and the email address registered to that account. We'll send instructions to reset the password if they match.</p>
          <div class="mb-2">
            <label class="form-label">Username</label>
            <input type="text" id="forgotUsername" class="form-control" placeholder="username">
          </div>
          <div class="mb-2">
            <label class="form-label">Registered Email</label>
            <input type="email" id="forgotEmail" class="form-control" placeholder="you@example.com">
          </div>
          <div id="forgotStatus" style="display:none;" class="mt-2"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn" id="forgotSubmit" style="background: var(--maroon); color:#fff;">Send Reset</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Global variable to store current login attempt
    let currentLoginAttempt = { username: '', email: '', password: '' };

    // AJAX login via API
    document.getElementById('signinForm').addEventListener('submit', async function(e){
      e.preventDefault();
      const btn = document.getElementById('signinBtn');
      btn.disabled = true;
      const original = btn.innerHTML;
      btn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Signing in...';

      const form = new FormData(this);
      const payload = {
        username: form.get('username'),
        password: form.get('password')
      };

      try{
        const res = await fetch('api/login.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
        const json = await res.json();
        if (json && json.success) {
          const acct = json.account_type || 'user';
          
          // Admin and regular users go directly to their respective dashboards
          if (acct === 'admin' || acct === 'superadmin') {
            window.location.href = 'admin/index.php';
          } else {
            // Regular user - redirect to event page
            window.location.href = 'Event/index.php';
          }
        } else {
          // show error
          const msg = (json && json.error) ? json.error : 'Invalid credentials';
          const alert = document.createElement('div');
          alert.className = 'alert alert-danger';
          alert.textContent = msg;
          document.querySelector('.form-panel').prepend(alert);
          btn.disabled = false;
          btn.innerHTML = original;
        }
      }catch(err){
        console.error(err);
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger';
        alert.textContent = 'Server error: ' + err.message;
        document.querySelector('.form-panel').prepend(alert);
        btn.disabled = false;
        btn.innerHTML = original;
      }
    });

    // Verify OTP
    async function verifyOTP() {
      try {
        const otp = document.getElementById('otpInput')?.value.trim() || '';
        const btn = document.getElementById('otpSubmit');
        const errorDiv = document.getElementById('otpError');
        const successDiv = document.getElementById('otpSuccess');

        console.log('verifyOTP called');
        console.log('1. OTP Input element:', !!document.getElementById('otpInput'));
        console.log('2. Error div element:', !!errorDiv);
        console.log('3. Success div element:', !!successDiv);
        console.log('4. Button element:', !!btn);
        console.log('5. Current Login Attempt:', currentLoginAttempt);

        // Try to get elements if they're still not found
        if (!errorDiv) {
          console.error('ERROR DIV MISSING - Attempting to find it anyway...');
          const allDivs = Array.from(document.querySelectorAll('div')).filter(d => d.id === 'otpError');
          console.log('Found divs with id=otpError:', allDivs.length);
          if (allDivs.length > 0) {
            console.log('Found error div:', allDivs[0]);
          }
        }

        if (!otp || otp.length !== 6 || !/^\d{6}$/.test(otp)) {
          if (errorDiv) {
            errorDiv.textContent = 'Please enter a valid 6-digit OTP';
            errorDiv.style.display = 'block';
          } else {
            alert('Please enter a valid 6-digit OTP');
          }
          return;
        }

        if (btn) {
          btn.disabled = true;
          btn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Verifying...';
        }

        console.log('=== Verifying OTP ===');
        console.log('Username:', currentLoginAttempt.username);
        console.log('OTP:', otp);
        
        const res = await fetch('api/verify_otp.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ username: currentLoginAttempt.username, otp: otp })
        });
        
        console.log('Verify response status:', res.status);
        const text = await res.text();
        console.log('Raw verify response:', text);
        const json = JSON.parse(text);
        console.log('Parsed verify response:', json);

        if (json && json.success) {
          if (successDiv) {
            successDiv.textContent = json.message;
            successDiv.style.display = 'block';
          }
          if (errorDiv) {
            errorDiv.style.display = 'none';
          }
          
          // Check if this is a password reset flow
          if (currentLoginAttempt.isPasswordReset) {
            // Redirect to password reset page after OTP verification
            setTimeout(() => {
              window.location.href = 'reset_password.php';
            }, 1000);
          } else {
            // Regular login - redirect to admin panel
            setTimeout(() => {
              window.location.href = 'admin/index.php';
            }, 1000);
          }
        } else {
          const errMsg = json && json.error ? json.error : 'OTP verification failed';
          if (errorDiv) {
            errorDiv.textContent = errMsg;
            errorDiv.style.display = 'block';
          } else {
            alert('Error: ' + errMsg);
          }
          if (successDiv) {
            successDiv.style.display = 'none';
          }
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'Verify OTP';
          }
        }
      } catch(err) {
        console.error('Verify OTP error:', err);
        const errorDiv = document.getElementById('otpError');
        const successDiv = document.getElementById('otpSuccess');
        const btn = document.getElementById('otpSubmit');
        
        if (errorDiv) {
          errorDiv.textContent = 'Server error: ' + err.message;
          errorDiv.style.display = 'block';
        } else {
          alert('Error: ' + err.message);
        }
        if (successDiv) {
          successDiv.style.display = 'none';
        }
        if (btn) {
          btn.disabled = false;
          btn.innerHTML = 'Verify OTP';
        }
      }
    }

    // Reset login form and hide OTP modal
    function resetLoginForm() {
      document.getElementById('signinForm').reset();
      document.getElementById('otpInput').value = '';
      currentLoginAttempt = { username: '', email: '', password: '', isPasswordReset: false };
      bootstrap.Modal.getInstance(document.getElementById('otpModal')).hide();
    }

    // Allow Enter key in OTP input to submit
    document.getElementById('otpInput')?.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        verifyOTP();
      }
    });
    
    // Auto-dismiss alerts after 3 seconds
    (function autoDismissAlerts(){
      const DISMISS_MS = 3000;

      // remove a single alert element after timeout
      function scheduleRemoval(el){
        if (!el) return;
        // avoid double-scheduling
        if (el.dataset.dismissScheduled) return;
        el.dataset.dismissScheduled = '1';
        setTimeout(() => {
          try{ el.classList.remove('show'); }catch(e){}
          // smooth removal: if inside a bootstrap fade/alert, try to remove after transition
          setTimeout(() => { if (el.parentNode) el.parentNode.removeChild(el); }, 200);
        }, DISMISS_MS);
      }

      // schedule existing alerts
      document.querySelectorAll('.alert').forEach(scheduleRemoval);

      // observe for newly added alerts (e.g., created by the AJAX flow)
      const observer = new MutationObserver((mutations) => {
        for (const m of mutations){
          for (const node of m.addedNodes){
            if (node.nodeType !== 1) continue;
            if (node.classList && node.classList.contains('alert')) scheduleRemoval(node);
            // also check descendants
            node.querySelectorAll && node.querySelectorAll('.alert').forEach(scheduleRemoval);
          }
        }
      });
      observer.observe(document.body, { childList: true, subtree: true });
    })();
    
    // Forgot password flow (maroon themed modal)
    document.getElementById('forgotLink')?.addEventListener('click', function(e){
      e.preventDefault();
      const modalEl = document.getElementById('forgotModal');
      if (!modalEl) return;
      const bs = new bootstrap.Modal(modalEl);
      // clear previous state: reset username/email inputs and status
      const u = document.getElementById('forgotUsername');
      const m = document.getElementById('forgotEmail');
      const status = document.getElementById('forgotStatus');
      if (u) u.value = '';
      if (m) m.value = '';
      if (status) { status.style.display = 'none'; status.className = ''; status.textContent = ''; }
      bs.show();
    });

    document.getElementById('forgotSubmit')?.addEventListener('click', async function(){
      const username = document.getElementById('forgotUsername').value.trim();
      const email = document.getElementById('forgotEmail').value.trim();
      const statusEl = document.getElementById('forgotStatus');
      if(!username || !email){
        statusEl.style.display = '';
        statusEl.className = 'alert alert-warning';
        statusEl.textContent = 'Please enter both username and registered email.';
        return;
      }

      // First, attempt to look up the username to determine account_type
      try {
        let acctType = null;
        try {
          const qres = await fetch('api/users.php?username=' + encodeURIComponent(username));
          const qjson = await qres.json();
          if (qjson && qjson.success && Array.isArray(qjson.data)) {
            const found = qjson.data.find(u => (u.username && u.username.toLowerCase() === username.toLowerCase()) || (u.user && u.user.toLowerCase() === username.toLowerCase()));
            if (found) acctType = (found.account_type || found.role || found.type || '').toLowerCase();
          }
        } catch(e) {
          // ignore and fallback
        }

        if (!acctType) {
          const all = await (await fetch('api/users.php')).json();
          if (all && all.success && Array.isArray(all.data)) {
            const found = all.data.find(u => (u.username && u.username.toLowerCase() === username.toLowerCase()) || (u.user && u.user.toLowerCase() === username.toLowerCase()));
            if (found) acctType = (found.account_type || found.role || found.type || '').toLowerCase();
          }
        }

        // If admin, send OTP using EmailJS
        if (acctType === 'admin' || acctType === 'superadmin') {
          const btn = document.getElementById('forgotSubmit');
          const original = btn.innerHTML;
          btn.disabled = true;
          btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Sending OTP...';
          
          // Generate 6-digit OTP
          const otp = String(Math.floor(Math.random() * 1000000)).padStart(6, '0');
          const datetime = new Date().toLocaleString();
          
          // Initialize EmailJS
          try {
            emailjs.init('_AZJHl2zYxm20rv7x');
          } catch(initErr) {
            // Initialization error
          }
          
          // Send OTP via EmailJS using .then() pattern (same as working test)
          const templateParams = {
            to_email: email,
            otp: otp,
            datetime: datetime
          };
          
          emailjs.send('service_xn6f8ys', 'template_imljmkl', templateParams)
            .then((response) => {
              statusEl.textContent = 'OTP sent! Saving to database...';
              
              // If EmailJS send succeeds, save OTP to database
              return fetch('api/save_otp.php', {
                method: 'POST',
                body: new URLSearchParams({ username: username, email: email, otp: otp })
              })
              .then(res => {
                return res.text();
              })
              .then(text => {
                return JSON.parse(text);
              })
              .then(saveJson => {
                
                if (saveJson.success) {
                  // Close forgot modal
                  const forgotModal = bootstrap.Modal.getInstance(document.getElementById('forgotModal'));
                  if (forgotModal) forgotModal.hide();
                  
                  // Store admin username and email for password reset flow
                  currentLoginAttempt.username = username;
                  currentLoginAttempt.email = email;
                  currentLoginAttempt.isPasswordReset = true;
                  
                  // Show OTP modal
                  const otpModalEl = document.getElementById('otpModal');
                  if (otpModalEl) {
                    const emailDisplay = document.getElementById('otpEmailDisplay');
                    if (emailDisplay) {
                      const masked = email.substring(0, 2) + '*'.repeat(email.split('@')[0].length - 2) + '@' + email.split('@')[1];
                      emailDisplay.textContent = 'OTP sent to: ' + masked;
                      emailDisplay.className = 'alert alert-info';
                    }
                    
                    const otpInput = document.getElementById('otpInput');
                    if (otpInput) otpInput.value = '';
                    
                    const otpError = document.getElementById('otpError');
                    if (otpError) {
                      otpError.style.display = 'none';
                      otpError.textContent = '';
                    }
                    
                    const otpSuccess = document.getElementById('otpSuccess');
                    if (otpSuccess) {
                      otpSuccess.style.display = 'none';
                      otpSuccess.textContent = '';
                    }
                    
                    const modalTitle = otpModalEl.querySelector('.modal-title');
                    if (modalTitle) modalTitle.innerHTML = '<i class="bi bi-key me-2"></i>Verify Identity for Password Reset';
                    
                    const otpModal = new bootstrap.Modal(otpModalEl);
                    otpModal.show();
                    
                    statusEl.style.display = 'none';
                  }
                } else {
                  console.error('✗ Save OTP failed:', saveJson.error);
                  throw new Error('Failed to save OTP: ' + saveJson.error);
                }
              });
            })
            .catch((error) => {
              statusEl.style.display = '';
              statusEl.className = 'alert alert-danger';
              statusEl.textContent = 'OTP generated but failed to send email. Please contact your administrator.';
              btn.disabled = false;
              btn.innerHTML = original;
            });
          
          return;
        }

        if (acctType === 'user' || acctType === 'student') {
          // Close forgot password modal first
          const forgotModal = bootstrap.Modal.getInstance(document.getElementById('forgotModal'));
          if (forgotModal) forgotModal.hide();
          
          // show OSAS notice modal instead of sending reset
          const osas = new bootstrap.Modal(document.getElementById('osasNoticeModal'));
          osas.show();
          return; // stop here
        }
      } catch(e) {
        // if lookup fails, continue to attempt the reset to avoid blocking non-critical flow
        console.warn('User lookup failed, continuing with reset attempt', e);
      }

      // optimistic UI: show spinner
      const btn = document.getElementById('forgotSubmit');
      const original = btn.innerHTML;
      btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Sending...';

      try{
        // Attempt to call users API to trigger a reset. The API supports PUT new_password, but there is no dedicated "send reset email" endpoint in this project.
        // We'll POST to ../api/users.php?action=send_reset (best-effort) — if the endpoint doesn't exist, we'll fallback to a UI-only confirmation message.
  const payload = new URLSearchParams();
  payload.append('username', username);
  payload.append('email', email);
  // best-effort action parameter
  payload.append('action', 'send_reset_by_username_email');

        const res = await fetch('api/users.php', { method: 'POST', body: payload });
        let json;
        try { json = await res.json(); } catch(e){ json = null; }

        if (json && json.success) {
          statusEl.style.display = '';
          statusEl.className = 'alert alert-success';
          statusEl.textContent = json.message || 'Reset instructions sent if the account exists.';
          // auto-hide and close modal after a short delay
          setTimeout(() => { try{ bootstrap.Modal.getInstance(document.getElementById('forgotModal')).hide(); }catch(e){} }, 1400);
        } else {
          // fallback: the API likely doesn't implement send_reset. Provide a helpful UI message instead.
          statusEl.style.display = '';
          statusEl.className = 'alert alert-info';
          statusEl.textContent = 'If an account with that identifier exists, instructions to reset the password would be sent. If you are an administrator contact support.';
        }
      }catch(err){
        console.error(err);
        statusEl.style.display = '';
        statusEl.className = 'alert alert-danger';
        statusEl.textContent = 'Server error while attempting to send reset instructions.';
      } finally {
        btn.disabled = false; btn.innerHTML = original;
      }
    });
  </script>
  
  <!-- OSAS Notice Modal for user accounts -->
  <div class="modal fade" id="osasNoticeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <div class="modal-header" style="background: var(--maroon); color: #fff;">
          <h5 class="modal-title"><i class="bi bi-exclamation-circle me-2"></i>Account Recovery</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">Users need to go to the OSAS office for account recovery. Please contact the OSAS office for assistance with username/password issues.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    // Apply any `fill` attribute on <i class="bi"> icons to their CSS color
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.bi[fill]').forEach(function(el){
        try {
          var fillValue = el.getAttribute('fill');
          if (fillValue) {
            el.style.color = fillValue;
            // ensure currentColor is used by icon font
            el.setAttribute('data-fill-applied', 'true');
          }
        } catch (e) {
          // ignore
        }
      });
    });
  </script>
</body>
</html>
