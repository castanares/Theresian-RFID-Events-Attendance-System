<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>College Sign In</title>
  <link rel="stylesheet" href="../styles/dash_styles.css">
  <link rel="stylesheet" href="../styles/side_styles.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <img src="../images/stctandag-logo.png" alt="logo">
        <h2>STC RFID Attendance</h2>
        <p>secure sign in to access the event attendance system.</p>
      </div>

      <div class="form-panel">
        <h4 class="form-heading">Sign in to your account</h4>
        <p class="text-muted">Use your Assigned username and password.</p>
        <form onsubmit="return false;" id="signinForm">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-lock" fill="maroon"></i></span>
              <input type="text" name="id" class="form-control" placeholder="Username" required>
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
            <a href="#" class="forgot">Forgot password?</a>
          </div>
          <div class="d-grid mt-3">
            <button id="signinBtn" class="btn btn-primary signin" type="button">Sign In</button>
          </div>
          <p class="text-center text-muted mt-3">Need help? Contact the OSAS office.</p>
        </form>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // tiny faux sign-in animation (UI-only)
    document.getElementById('signinBtn').addEventListener('click', async function(){
      const btn = this;
      const form = document.getElementById('signinForm');
      const username = form.id.value.trim();
      const password = form.password.value.trim();
      if (!username || !password) {
        alert('Please enter username and password.');
        return;
      }
      btn.disabled = true;
      const original = btn.textContent;
      btn.textContent = 'Signing in...';
      try {
        const res = await fetch('api/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username, password })
        });
        const data = await res.json();
        if (data.success) {
          // redirect based on account type
          if (data.account_type === 'admin') {
            window.location.href = 'admin/index.php';
          } else {
            window.location.href = 'Event/index.php';
          }
        } else {
          alert('Login failed: ' + (data.error || 'Unknown error'));
          btn.disabled = false;
          btn.textContent = original;
        }
      } catch (e) {
        alert('Error: ' + e.message);
        btn.disabled = false;
        btn.textContent = original;
      }
    });
  </script>
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
