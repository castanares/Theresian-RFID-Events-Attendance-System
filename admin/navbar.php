<?php
// navbar.php - shared header/navbar (based on original markup)
?>
<header class="p-3 mb-3 border-bottom bg-light">
  <div class="container ">
    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start">
      <!-- optional burger / nav icon left (kept commented as original) -->
      <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
        <li><a href="#" class="nav-link px-2 link-body-emphasis"><h5 class="dash"><?php echo isset($page_heading) ? $page_heading : 'Dashboard'; ?></h5></a></li>
      </ul>
  <?php if (session_status() === PHP_SESSION_NONE && !headers_sent()) session_start(); ?>
      <div class="dropdown text-end">
            <a href="#" class="d-block link-body-emphasis text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
              <img id="navProfileImg" src="../images/profile-circle-icon-2048x2048-cqe5466q.png" alt="mdo" width="32" height="32" class="rounded-circle" style="object-fit:cover;">
            </a>
            <ul class="dropdown-menu text-small" >
              <li><a class="dropdown-item" href="profile.php#profileInfo">Settings</a></li>
              <li><a class="dropdown-item" href="profile.php">Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#" id="logoutLink">Sign out</a></li>
            </ul>
          </div>
      <div class="account_name">
        <?php
        $displayName = 'OSAS Admin';
        if (isset($_SESSION['user_id'])) {
          $displayName = htmlspecialchars($_SESSION['user_id']);
          try {
            if (file_exists(__DIR__ . '/../api/dbcon.php')) {
              require_once __DIR__ . '/../api/dbcon.php';
              if (isset($conn) && ($conn instanceof mysqli)) {
                $sessVal = $_SESSION['user_id'];
                $userTable = null;
                $resT = $conn->query("SHOW TABLES");
                if ($resT) while ($r = $resT->fetch_row()) { if (stripos($r[0],'user') !== false) { $userTable = $r[0]; break; } }
                if ($userTable) {
                  $cols = [];
                  $cres = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($userTable) . "`");
                  if ($cres) while ($cr = $cres->fetch_assoc()) $cols[] = $cr['Field'];
                  $idCol = null; foreach (['id','user_id','uid','ID'] as $c) if (in_array($c,$cols)) { $idCol = $c; break; }
                  $nameCol = null; foreach (['username','user_name','name','full_name','account_name','email'] as $c) if (in_array($c,$cols)) { $nameCol = $c; break; }
                  if ($idCol && $nameCol) {
                    if (ctype_digit((string)$sessVal)) {
                      $stmt = $conn->prepare("SELECT `$nameCol` FROM `" . $conn->real_escape_string($userTable) . "` WHERE `$idCol` = ? LIMIT 1");
                      if ($stmt) { $stmt->bind_param('i', $sessVal); $stmt->execute(); $r = $stmt->get_result()->fetch_row(); if ($r && $r[0]) $displayName = htmlspecialchars($r[0]); $stmt->close(); }
                    } else {
                      $stmt = $conn->prepare("SELECT `$nameCol` FROM `" . $conn->real_escape_string($userTable) . "` WHERE `$nameCol` = ? LIMIT 1");
                      if ($stmt) { $stmt->bind_param('s', $sessVal); $stmt->execute(); $r = $stmt->get_result()->fetch_row(); if ($r && $r[0]) $displayName = htmlspecialchars($r[0]); $stmt->close(); }
                    }
                  }
                }
              }
            }
          } catch (Exception $e) {
            // ignore
          }
        }
        ?>
        <h6><?php echo $displayName; ?></h6>
      </div>
    <script>
      document.addEventListener('DOMContentLoaded', function(){
        const logout = document.getElementById('logoutLink');
        if (logout) logout.addEventListener('click', async function(e){
          e.preventDefault();
          await fetch('../api/logout.php', { method: 'POST' });
          window.location.href = '../';
        });

        // load user's profile picture (if available) and replace navbar avatar
        (async function loadNavAvatar(){
          try {
            const img = document.getElementById('navProfileImg');
            if (!img) return;
            const res = await fetch('../api/me.php');
            if (!res.ok) return;
            const j = await res.json();
            if (!j || !j.data) return;
            // look for common avatar keys
            const keys = ['profile_pic','profile_picture','avatar','picture','photo'];
            let pic = null;
            const d = j.data;
            for (const k of keys) if (d[k]) { pic = d[k]; break; }
            // also check nested department/user objects
            if (!pic) {
              for (const k of Object.keys(d)) {
                if (d[k] && typeof d[k] === 'object') {
                  for (const kk of keys) if (d[k][kk]) { pic = d[k][kk]; break; }
                }
                if (pic) break;
              }
            }
            if (!pic) return;
            // normalize relative paths: if it doesn't look like a URL, make it relative to /new/
            if (!/^https?:\/\//i.test(pic) && !pic.startsWith('/')) {
              // ensure no leading ./
              pic = (pic.startsWith('..') || pic.startsWith('.')) ? pic : '../' + pic.replace(/^\/+/, '');
            }
            img.src = pic;
          } catch (e) { console.warn('loadNavAvatar', e); }
        })();
      });
    </script>
    </div>
  </div>
</header>
