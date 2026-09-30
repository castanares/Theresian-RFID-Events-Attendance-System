<?php
use App\Config\Config;
use App\Core\Auth;
?>
<div class="space-y-6">

  <!-- Header & Event Selector -->
  <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h2 class="text-xl font-black text-slate-900 tracking-tight">Attendance Reports & Analytics</h2>
      <p class="text-xs text-slate-500 mt-0.5">Generate, filter, and export attendee rosters and absentee lists</p>
    </div>

    <!-- Event Selector Form -->
    <form action="<?= Config::BASE_URL ?>/reports" method="GET" class="flex items-center gap-3">
      <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
      <select name="event_id" onchange="this.form.submit()" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-maroon-700/20">
        <?php foreach ($events as $ev): ?>
          <option value="<?= $ev['id'] ?>" <?= $selectedEventId == $ev['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($ev['event_name']) ?> (<?= date('M j, Y', strtotime($ev['date'])) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <?php if ($event): ?>
  <!-- Event Info & Export Bar -->
  <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
    
    <div>
      <div class="flex items-center gap-2">
        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-maroon-50 text-maroon-800">
          <?= htmlspecialchars($event['host_department_acronym'] ?? 'All College') ?>
        </span>
        <span class="text-xs text-slate-400 font-medium">Semester: <?= htmlspecialchars($event['semester_name']) ?></span>
      </div>
      <h3 class="text-lg font-black text-slate-900 mt-1"><?= htmlspecialchars($event['event_name']) ?></h3>
      <p class="text-xs text-slate-500">
        <?= date('F j, Y', strtotime($event['date'])) ?> • 
        Time-In: <?= date('h:i A', strtotime($event['TimeIn_start'])) ?> - <?= date('h:i A', strtotime($event['TimeIn_end'])) ?> | 
        Time-Out: <?= date('h:i A', strtotime($event['Time_out'])) ?> - <?= date('h:i A', strtotime($event['Timeout_out'])) ?>
      </p>
    </div>

    <!-- Export Actions -->
    <div class="flex items-center gap-2">
      <!-- Print / PDF -->
      <a href="<?= Config::BASE_URL ?>/reports/print/<?= $event['id'] ?>?type=<?= $tab ?>" 
         target="_blank"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-xs transition active:scale-95">
        <i data-lucide="printer" class="w-4 h-4"></i>
        Print Official Report
      </a>

      <!-- CSV Export -->
      <a href="<?= Config::BASE_URL ?>/reports/export-csv/<?= $event['id'] ?>?type=<?= $tab ?>" 
         class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition active:scale-95">
        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
        Export to CSV / Excel
      </a>
    </div>

  </div>

  <!-- Tabs: Attendees vs Absentees -->
  <div class="flex items-center gap-2 border-b border-slate-200">
    <a href="<?= Config::BASE_URL ?>/reports?event_id=<?= $event['id'] ?>&tab=attendees" 
       class="px-5 py-2.5 text-xs font-bold border-b-2 transition <?= $tab === 'attendees' ? 'border-maroon-800 text-maroon-900' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
      Attendees List (<?= count($attendees) ?>)
    </a>
    <a href="<?= Config::BASE_URL ?>/reports?event_id=<?= $event['id'] ?>&tab=absentees" 
       class="px-5 py-2.5 text-xs font-bold border-b-2 transition <?= $tab === 'absentees' ? 'border-maroon-800 text-maroon-900' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
      Absentees / Missing (<?= count($absentees) ?>)
    </a>
  </div>

  <!-- Active Tab Table -->
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
      
      <?php if ($tab === 'attendees'): ?>
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
            <th class="py-3.5 px-4">#</th>
            <th class="py-3.5 px-4">Student ID</th>
            <th class="py-3.5 px-4">Full Name</th>
            <th class="py-3.5 px-4">Course</th>
            <th class="py-3.5 px-4">Department</th>
            <th class="py-3.5 px-4">Time-In</th>
            <th class="py-3.5 px-4">Time-Out</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($attendees)): ?>
            <tr>
              <td colspan="7" class="py-12 text-center text-slate-400">
                No students attended this event yet.
              </td>
            </tr>
          <?php else: ?>
            <?php $i = 1; foreach ($attendees as $a): ?>
              <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3.5 px-4 text-slate-400 font-mono"><?= $i++ ?></td>
                <td class="py-3.5 px-4 font-mono font-bold text-slate-800"><?= htmlspecialchars($a['Student_ID']) ?></td>
                <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($a['Student_name']) ?></td>
                <td class="py-3.5 px-4">
                  <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold"><?= htmlspecialchars($a['Course']) ?></span>
                </td>
                <td class="py-3.5 px-4 text-slate-600 font-medium"><?= htmlspecialchars($a['department_name'] ?? '—') ?></td>
                <td class="py-3.5 px-4 font-mono font-bold text-emerald-700"><?= !empty($a['Time_in']) ? date('h:i:s A', strtotime($a['Time_in'])) : '—' ?></td>
                <td class="py-3.5 px-4 font-mono font-medium text-slate-600"><?= !empty($a['Time_out']) ? date('h:i:s A', strtotime($a['Time_out'])) : '—' ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      <?php else: ?>
      <!-- Absentees Table -->
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
            <th class="py-3.5 px-4">#</th>
            <th class="py-3.5 px-4">Student ID</th>
            <th class="py-3.5 px-4">Full Name</th>
            <th class="py-3.5 px-4">Course</th>
            <th class="py-3.5 px-4">Department</th>
            <th class="py-3.5 px-4 text-center">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($absentees)): ?>
            <tr>
              <td colspan="6" class="py-12 text-center text-slate-400">
                100% Attendance! No absentees recorded.
              </td>
            </tr>
          <?php else: ?>
            <?php $i = 1; foreach ($absentees as $s): ?>
              <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3.5 px-4 text-slate-400 font-mono"><?= $i++ ?></td>
                <td class="py-3.5 px-4 font-mono font-bold text-slate-800"><?= htmlspecialchars($s['ID_Number']) ?></td>
                <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($s['Student_name']) ?></td>
                <td class="py-3.5 px-4">
                  <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold"><?= htmlspecialchars($s['Course']) ?></span>
                </td>
                <td class="py-3.5 px-4 text-slate-600 font-medium"><?= htmlspecialchars($s['department_name'] ?? '—') ?></td>
                <td class="py-3.5 px-4 text-center">
                  <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-800">
                    ABSENT
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      <?php endif; ?>

    </div>
  </div>
  <?php endif; ?>

</div>
