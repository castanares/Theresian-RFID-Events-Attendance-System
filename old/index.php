<?php require_once __DIR__ . '/../auth.php'; require_login('/new/'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EVENT Attendance</title>
    <link rel="stylesheet" href="../styles/dash_styles.css">
    <link rel="stylesheet" href="../styles/side_styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001" />
</head>
<style>
  .btn-maroon { background-color: white; color: maroon; }
  .btn-maroon:hover { background-color: maroon; color: white; }
  .table-maroon th { background-color: maroon !important; color: white !important; }
</style>
<body>
<?php include 'sidebar.php'; ?>
<?php $page_heading = 'Dashboard'; include 'navbar.php'; ?>
  <h1 class="text-center text-decoration-underline link-underline-danger">RECENT EVENTS</h1>
  <br>


  <section class="d-md-flex justify-content-md-end align-items-end year-sem">
    <div class="me-3">
      <h6 class="h6-year mb-1">School Year</h6>
      <div class="btn-group border border-dark">
        <button class="btn btn-light dropdown-toggle " type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar me-1" viewBox="0 0 16 16"><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>
          2024-2025
        </button>
        <ul class="dropdown-menu ">
          <li><a class="dropdown-item" href="#">2023-2024</a></li>
          <li><a class="dropdown-item active" href="#">2024-2025</a></li>
          <li><a class="dropdown-item" href="#">2025-2026</a></li>
        </ul>
      </div>
    </div>
    <div class="me-3">
      <h6 class="h6-semester mb-1">Semester</h6>
      <div class="btn-group border border-dark">
        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book me-1" viewBox="0 0 16 16"><path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/></svg>
          1st Semester
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item active" href="#">First</a></li>
          <li><a class="dropdown-item" href="#">Second</a></li>
          <li><a class="dropdown-item" href="#">Summer</a></li>
        </ul>
      </div>
    </div>
    <button class="btn btn-maroon border-dark filter" type="button">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel me-1" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/></svg>
      Filter
    </button>
  </section>

  
  <section class="event-table mt-3">
    <table class="table table-hover align-middle table-bordered border border-dark">
      <thead class="table-maroon">
        <tr>
          <th scope="col" class="text-center">#</th>
          <th scope="col" class="">EVENTS</th>
          <th scope="col" class="text-center">DATE</th>
          <th scope="col" class="text-center">ACTIONS</th>
        </tr>
      </thead>
      <tbody id="eventsTbody">
        <tr><td class="text-center" colspan="4">Loading events...</td></tr>
      </tbody>
    </table>
  </section>

  <script>
    async function loadAndRenderEvents(){
      const tbody = document.getElementById('eventsTbody');
      try{
        // fetch current user info to determine their department ids
        const meRes = await fetch('../api/me.php', {credentials:'same-origin'});
        const meJson = await meRes.json().catch(()=>null);
        let myUserId = null;
        let myDeptList = [];
        if (meJson) {
          myUserId = meJson.user_id ?? null;
          if (meJson.departments_id !== undefined && meJson.departments_id !== null && String(meJson.departments_id).trim() !== '') {
            myDeptList = String(meJson.departments_id).split(',').map(s=>s.trim()).filter(Boolean);
          } else if (meJson.department_id !== undefined && meJson.department_id !== null) {
            if (Array.isArray(meJson.department_id)) myDeptList = meJson.department_id.map(String);
            else myDeptList = [String(meJson.department_id)];
          } else if (meJson.department !== undefined && meJson.department !== null) {
            myDeptList = String(meJson.department).split(',').map(s=>s.trim()).filter(Boolean);
          }
        }

        // fetch all events and filter: show events owned by user OR events where primary departments_id or other_departments include the user's department
        const res = await fetch('../api/events.php', {credentials:'same-origin'});
        let j = null;
        try { j = await res.json(); } catch(e) {
          const text = await res.text().catch(()=>null);
          tbody.innerHTML = '<tr><td class="text-center" colspan="4">Server error: ' + (res.status || '') + ' ' + (text ? text.replace(/</g,'&lt;') : 'No response body') + '</td></tr>';
          return;
        }
        if (!j || !j.success || !Array.isArray(j.data)) { tbody.innerHTML = '<tr><td class="text-center" colspan="4">No events found</td></tr>'; return; }

        // build a set of department ids (strings) for quick lookup
        const myDeptSet = new Set(myDeptList.map(String));

        // filter events
        const filtered = j.data.filter(ev => {
          // show if owner
          if (myUserId !== null && String(ev.user_id) === String(myUserId)) return true;
          // primary departments_id may be CSV
          if (ev.departments_id) {
            const prim = String(ev.departments_id).split(',').map(s=>s.trim()).filter(Boolean);
            for (const p of prim) if (myDeptSet.has(p)) return true;
          }
          // other_departments may be array of ints
          if (Array.isArray(ev.other_departments) && ev.other_departments.length) {
            for (const d of ev.other_departments) if (myDeptSet.has(String(d))) return true;
          }
          return false;
        });

        if (!filtered.length) { tbody.innerHTML = '<tr><td class="text-center" colspan="4">No events for your account or department</td></tr>'; return; }

        // render filtered
        tbody.innerHTML = '';
        filtered.forEach((ev, idx) => {
          const tr = document.createElement('tr');
          const idCell = document.createElement('th'); idCell.scope='row'; idCell.className='text-center'; idCell.textContent = idx+1;
          const nameCell = document.createElement('td'); nameCell.className='fw-bold';
          const link = document.createElement('a'); link.className='text-decoration-none text-dark'; link.href = 'ongoing.php?id=' + encodeURIComponent(ev.id || ev[Object.keys(ev)[0]] || '');
          link.textContent = ev.event_name || ev.name || ev.title || 'Untitled';
          nameCell.appendChild(link);
          const dateCell = document.createElement('td'); dateCell.className='text-center'; dateCell.textContent = ev.date ? new Date(ev.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '';
          const actionCell = document.createElement('td'); actionCell.className='text-center';
          const edit = document.createElement('a'); edit.className='btn btn-sm btn-outline-primary'; edit.href = 'edit_event.php?id=' + encodeURIComponent(ev.id || ev.event_id || ''); edit.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/></svg>';
          const archive = document.createElement('a'); archive.className='btn btn-sm btn-outline-danger ms-1'; archive.style.color = 'red'; archive.href = 'archived.php?id=' + encodeURIComponent(ev.id || ev.event_id || ''); archive.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg>';
          actionCell.appendChild(edit);
          actionCell.appendChild(archive);
          tr.appendChild(idCell); tr.appendChild(nameCell); tr.appendChild(dateCell); tr.appendChild(actionCell);
          tbody.appendChild(tr);
        });
      }catch(err){ console.error(err); document.getElementById('eventsTbody').innerHTML = '<tr><td class="text-center" colspan="4">Failed to load events</td></tr>'; }
    }
    document.addEventListener('DOMContentLoaded', loadAndRenderEvents);
  </script>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
