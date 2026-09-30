<?php
use App\Config\Config;
use App\Core\Csrf;
?>
<div class="space-y-6">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="text-xl font-black text-slate-900 tracking-tight">System Archive Center</h2>
      <p class="text-xs text-slate-500 mt-0.5">Manage, restore, or permanently purge archived records</p>
    </div>
  </div>

  <!-- Tab Bar -->
  <div class="flex items-center gap-2 border-b border-slate-200">
    <a href="<?= Config::BASE_URL ?>/admin/archived?tab=students" 
       class="px-5 py-2.5 text-xs font-bold border-b-2 transition <?= $tab === 'students' ? 'border-maroon-800 text-maroon-900' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
      Archived Students (<?= count($archivedStudents) ?>)
    </a>
    <a href="<?= Config::BASE_URL ?>/admin/archived?tab=events" 
       class="px-5 py-2.5 text-xs font-bold border-b-2 transition <?= $tab === 'events' ? 'border-maroon-800 text-maroon-900' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
      Archived Events (<?= count($archivedEvents) ?>)
    </a>
    <a href="<?= Config::BASE_URL ?>/admin/archived?tab=departments" 
       class="px-5 py-2.5 text-xs font-bold border-b-2 transition <?= $tab === 'departments' ? 'border-maroon-800 text-maroon-900' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
      Archived Departments (<?= count($archivedDepts) ?>)
    </a>
    <a href="<?= Config::BASE_URL ?>/admin/archived?tab=users" 
       class="px-5 py-2.5 text-xs font-bold border-b-2 transition <?= $tab === 'users' ? 'border-maroon-800 text-maroon-900' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
      Deactivated Users (<?= count($archivedUsers) ?>)
    </a>
  </div>

  <!-- Tables by Tab -->
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
      
      <!-- 1. STUDENTS TAB -->
      <?php if ($tab === 'students'): ?>
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
            <th class="py-3.5 px-4">Student ID</th>
            <th class="py-3.5 px-4">Full Name</th>
            <th class="py-3.5 px-4">Course</th>
            <th class="py-3.5 px-4">Archival Cause</th>
            <th class="py-3.5 px-4">Year Archived</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($archivedStudents)): ?>
            <tr><td colspan="6" class="py-12 text-center text-slate-400">No archived students found.</td></tr>
          <?php else: ?>
            <?php foreach ($archivedStudents as $s): ?>
              <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3.5 px-4 font-mono font-bold text-slate-800"><?= htmlspecialchars($s['ID_Number']) ?></td>
                <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($s['Student_name']) ?></td>
                <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded bg-slate-100 font-bold"><?= htmlspecialchars($s['Course']) ?></span></td>
                <td class="py-3.5 px-4 text-red-600 font-semibold"><?= htmlspecialchars($s['cause'] ?: 'Archived') ?></td>
                <td class="py-3.5 px-4 font-mono text-slate-500"><?= htmlspecialchars($s['archived_stamp'] ?: '—') ?></td>
                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <form action="<?= Config::BASE_URL ?>/students/restore/<?= $s['id'] ?>" method="POST" class="inline">
                      <?= Csrf::field() ?>
                      <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold text-xs">Restore</button>
                    </form>
                    <form action="<?= Config::BASE_URL ?>/admin/archived/permanent-delete/student/<?= $s['id'] ?>" method="POST" onsubmit="return confirm('Permanently delete student record?');" class="inline">
                      <?= Csrf::field() ?>
                      <button type="submit" class="px-3 py-1 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-semibold text-xs">Purge</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- 2. EVENTS TAB -->
      <?php elseif ($tab === 'events'): ?>
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
            <th class="py-3.5 px-4">Event Name</th>
            <th class="py-3.5 px-4">Event Date</th>
            <th class="py-3.5 px-4">Host</th>
            <th class="py-3.5 px-4">Semester</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($archivedEvents)): ?>
            <tr><td colspan="5" class="py-12 text-center text-slate-400">No archived events found.</td></tr>
          <?php else: ?>
            <?php foreach ($archivedEvents as $ev): ?>
              <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($ev['event_name']) ?></td>
                <td class="py-3.5 px-4 text-slate-600"><?= date('F j, Y', strtotime($ev['date'])) ?></td>
                <td class="py-3.5 px-4 font-semibold text-slate-700"><?= htmlspecialchars($ev['host_department'] ?: 'All College') ?></td>
                <td class="py-3.5 px-4 text-slate-500"><?= htmlspecialchars($ev['semester_name']) ?></td>
                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <form action="<?= Config::BASE_URL ?>/events/restore/<?= $ev['id'] ?>" method="POST" class="inline">
                      <?= Csrf::field() ?>
                      <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold text-xs">Restore</button>
                    </form>
                    <form action="<?= Config::BASE_URL ?>/admin/archived/permanent-delete/event/<?= $ev['id'] ?>" method="POST" onsubmit="return confirm('Permanently delete event and its scans?');" class="inline">
                      <?= Csrf::field() ?>
                      <button type="submit" class="px-3 py-1 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-semibold text-xs">Purge</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- 3. DEPARTMENTS TAB -->
      <?php elseif ($tab === 'departments'): ?>
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
            <th class="py-3.5 px-4">Department Name</th>
            <th class="py-3.5 px-4">Acronym</th>
            <th class="py-3.5 px-4">Head</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($archivedDepts)): ?>
            <tr><td colspan="4" class="py-12 text-center text-slate-400">No archived departments.</td></tr>
          <?php else: ?>
            <?php foreach ($archivedDepts as $d): ?>
              <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($d['Dept_name']) ?></td>
                <td class="py-3.5 px-4 font-mono font-bold text-maroon-800"><?= htmlspecialchars($d['dept_Acronym']) ?></td>
                <td class="py-3.5 px-4 text-slate-600"><?= htmlspecialchars($d['department_head'] ?: '—') ?></td>
                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <form action="<?= Config::BASE_URL ?>/departments/restore/<?= $d['id'] ?>" method="POST" class="inline">
                      <?= Csrf::field() ?>
                      <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold text-xs">Restore</button>
                    </form>
                    <form action="<?= Config::BASE_URL ?>/admin/archived/permanent-delete/department/<?= $d['id'] ?>" method="POST" onsubmit="return confirm('Permanently delete department?');" class="inline">
                      <?= Csrf::field() ?>
                      <button type="submit" class="px-3 py-1 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-semibold text-xs">Purge</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- 4. USERS TAB -->
      <?php elseif ($tab === 'users'): ?>
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
            <th class="py-3.5 px-4">Username</th>
            <th class="py-3.5 px-4">Role</th>
            <th class="py-3.5 px-4">Email</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($archivedUsers)): ?>
            <tr><td colspan="4" class="py-12 text-center text-slate-400">No deactivated user accounts.</td></tr>
          <?php else: ?>
            <?php foreach ($archivedUsers as $u): ?>
              <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($u['username']) ?></td>
                <td class="py-3.5 px-4"><span class="px-2 py-0.5 rounded bg-slate-100 font-semibold"><?= htmlspecialchars($u['account_type']) ?></span></td>
                <td class="py-3.5 px-4 text-slate-600"><?= htmlspecialchars($u['email'] ?: '—') ?></td>
                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <form action="<?= Config::BASE_URL ?>/admin/users/toggle-status/<?= $u['id'] ?>" method="POST" class="inline">
                      <?= Csrf::field() ?>
                      <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold text-xs">Reactivate</button>
                    </form>
                    <form action="<?= Config::BASE_URL ?>/admin/archived/permanent-delete/user/<?= $u['id'] ?>" method="POST" onsubmit="return confirm('Permanently delete account?');" class="inline">
                      <?= Csrf::field() ?>
                      <button type="submit" class="px-3 py-1 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-semibold text-xs">Purge</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      <?php endif; ?>

    </div>
  </div>

</div>
