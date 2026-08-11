<?php
// sidebar.php - exact sidebar from original HTML with PHP active class
 $current = basename($_SERVER['SCRIPT_NAME']);

 
?>

<!-- Mobile hamburger toggle button (visible only on < 992px) -->
<button class="sidebar-toggle-btn d-lg-none" id="sidebarToggleBtn" aria-label="Toggle sidebar" aria-expanded="false" aria-controls="mainSidebar">
  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
    <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
  </svg>
</button>

<!-- Backdrop overlay for mobile sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

<section> 
  <div class="sidebar" id="mainSidebar">
        <nav class="text-center">
          <img src="stctandag-logo.png" alt="stc_logo" class="mx-auto d-block stc-logo" srcset="">
          <div class="d-flex flex-column align-items-center <?php echo $current === 'index.php' ? 'active-sidebar' : '' ?>">
            <a href="index.php" data-key="home" class="link-body-emphasis text-decoration-none">
              <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-house text-light" viewBox="0 0 16 16">
                <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
              </svg>
              <ul class="nav">
                <li><p class="side_home text-light px-3 pt-2">Dashboard</p></li>
              </ul>
            </a>
          </div>

          <div class="d-flex flex-column align-items-center <?php echo $current === 'events.php' ? 'active-sidebar' : '' ?>">
            <a href="events.php" data-key="events" class="mb-2 link-body-emphasis text-decoration-none">
              <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="white" class="bi bi-list-task" viewBox="0 0 16 16" style="margin-top: 1rem !important;">
                <path fill-rule="evenodd" d="M2 2.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5V3a.5.5 0 0 0-.5-.5zM3 3H2v1h1z"/>
                <path d="M5 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5M5.5 7a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1zm0 4a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1z"/>
                <path fill-rule="evenodd" d="M1.5 7a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H2a.5.5 0 0 1-.5-.5zM2 7h1v1H2zm0 3.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm1 .5H2v1h1z"/>
              </svg>
              <ul class="nav mt-3">
                <li><p class="ongoing text-light px-4 pt-2">All Events</p></li>
              </ul>
            </a>
          </div>

          
          <div class="d-flex flex-column align-items-center <?php echo $current === 'control_panel.php' ? 'active-sidebar' : '' ?>">
            <a href="control_panel.php" data-key="control" class="mb-2 link-body-emphasis text-decoration-none">
              <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="white" class="bi bi-person-fill-gear" viewBox="0 0 16 16" style="margin-top: 1rem !important;">
                <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0m-9 8c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4m9.886-3.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0"/>
              </svg>
              <ul class="nav">
                <li><p class="Add text-light px-2 pt-3">Control Panel</p></li>
              </ul>
            </a>
          </div>
  
          <div class="d-flex flex-column align-items-center <?php echo $current === 'students.php' ? 'active-sidebar' : '' ?>">
            <a href="students.php" data-key="students" class="mb-2 link-body-emphasis text-decoration-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="white" class="bi bi-person-plus" viewBox="0 0 16 16" style="margin-top: 1rem !important;">
                <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H1s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
                </svg>
              <ul class="nav">
                <li><p class="students text-light px-4 pt-3">Students</p></li>
              </ul>
            </a>
          </div>
          
          

          <div class="d-flex flex-column align-items-center pt-2 archive-section  <?php echo $current === 'archived.php' ? 'active-sidebar' : '' ?> ">
                <a href="archived.php" data-key="archived" class="mb-2 link-body-emphasis text-decoration-none">
              <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="white" class="bi bi-trash3-fill" viewBox="0 0 16 16"  >
                <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
              </svg>
                <ul class="nav">
                  <li><p class="archived text-light px-4 pt-3">Archived</p></li>
                </ul>
              </a>
            </div>

        </nav>
      </div>
</section>

<script>
(function() {
  const sidebar  = document.getElementById('mainSidebar');
  const overlay  = document.getElementById('sidebarOverlay');
  const toggleBtn = document.getElementById('sidebarToggleBtn');
  if (!sidebar || !toggleBtn) return;

  let isOpen = false;

  function openSidebar() {
    isOpen = true;
    sidebar.classList.add('mobile-open');
    overlay.classList.add('active');
    toggleBtn.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden'; // prevent background scroll
  }

  function closeSidebar() {
    isOpen = false;
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('active');
    toggleBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  toggleBtn.addEventListener('click', function() {
    if (isOpen) closeSidebar(); else openSidebar();
  });

  // Close when clicking overlay backdrop
  overlay.addEventListener('click', closeSidebar);

  // Close when pressing Escape
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && isOpen) closeSidebar();
  });

  // Close sidebar on link click (navigate away)
  const navAnchors = sidebar.querySelectorAll('a[data-key]');
  navAnchors.forEach(function(a) {
    a.addEventListener('click', function() {
      if (window.innerWidth < 992) closeSidebar();
    });
  });

  // On resize to desktop: remove mobile state
  window.addEventListener('resize', function() {
    if (window.innerWidth >= 992) {
      sidebar.classList.remove('mobile-open');
      overlay.classList.remove('active');
      document.body.style.overflow = '';
      isOpen = false;
    }
  });
})();
</script>

<script>
// Persistent active sidebar using localStorage. Local selection overrides server PHP active if present.
document.addEventListener('DOMContentLoaded', function(){
  const navAnchors = document.querySelectorAll('#mainSidebar nav a[data-key]');
  const containerItems = Array.from(document.querySelectorAll('#mainSidebar nav .d-flex'));

  function clearActive(){ containerItems.forEach(it=>it.classList.remove('active-sidebar')); }

  // Apply stored active if exists (overrides PHP active)
  const stored = localStorage.getItem('activeSidebar');
  if(stored){
    const match = document.querySelector('#mainSidebar nav a[data-key="'+stored+'"]');
    if(match){
      clearActive();
      const parent = match.closest('.d-flex');
      if(parent) parent.classList.add('active-sidebar');
    }
  }

  // Wire clicks: store selection and give immediate feedback
  navAnchors.forEach(a=>{
    a.addEventListener('click', function(e){
      const key = a.getAttribute('data-key');
      try{ localStorage.setItem('activeSidebar', key); }catch(e){}
      // feedback
      const parent = a.closest('.d-flex');
      if(!parent) return;
      // remove clicked from siblings
      containerItems.forEach(s=>s.classList.remove('clicked-sidebar'));
      parent.classList.add('clicked-sidebar');
      // also apply active immediately so user sees persistent state
      clearActive(); parent.classList.add('active-sidebar');
      // navigation will proceed normally
    });
  });
});
</script>
