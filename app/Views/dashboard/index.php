<?php
use App\Config\Config;
use App\Core\Auth;
?>
<div class="space-y-6">

  <!-- Welcome Hero Banner -->
  <div class="bg-gradient-to-r from-maroon-900 via-maroon-950 to-maroon-900 rounded-3xl p-6 sm:p-8 text-white shadow-md relative overflow-hidden flex flex-col md:flex-row md:items-center md:justify-between gap-6">
    <div class="relative z-10 max-w-xl">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-maroon-200 border border-white/10 mb-3">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        System Online & Ready
      </span>
      <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
        Good day, <?= htmlspecialchars(Auth::username()) ?>!
      </h2>
      <p class="text-sm text-maroon-200/90 mt-1.5">
        Managing <?= htmlspecialchars($stats['current_semester']) ?> (S.Y. <?= htmlspecialchars($stats['school_year']) ?>)
      </p>
    </div>

    <!-- Quick Scan Action -->
    <div class="relative z-10 flex flex-wrap gap-3">
      <a href="<?= Config::BASE_URL ?>/scan" 
         class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white text-maroon-900 font-bold text-sm shadow-lg hover:bg-slate-100 hover:shadow-xl transition active:scale-95">
        <i data-lucide="scan-line" class="w-5 h-5 text-emerald-600"></i>
        Start Live Scanner
      </a>
    </div>
  </div>

  <!-- Stats Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    
    <!-- Active Students -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
        <i data-lucide="users" class="w-6 h-6"></i>
      </div>
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Active Students</p>
        <h4 class="text-2xl font-black text-slate-900 tracking-tight"><?= number_format($stats['total_students']) ?></h4>
      </div>
    </div>

    <!-- Today's Scans -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
        <i data-lucide="check-check" class="w-6 h-6"></i>
      </div>
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Today's Scans</p>
        <h4 class="text-2xl font-black text-slate-900 tracking-tight"><?= number_format($stats['today_scans']) ?></h4>
      </div>
    </div>

    <!-- Active Semester -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
        <i data-lucide="graduation-cap" class="w-6 h-6"></i>
      </div>
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Current Semester</p>
        <h4 class="text-lg font-bold text-slate-900 truncate"><?= htmlspecialchars($stats['current_semester']) ?></h4>
      </div>
    </div>

    <!-- School Year -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
        <i data-lucide="calendar-days" class="w-6 h-6"></i>
      </div>
      <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Academic Year</p>
        <h4 class="text-lg font-bold text-slate-900">S.Y. <?= htmlspecialchars($stats['school_year']) ?></h4>
      </div>
    </div>

  </div>

  <!-- Ongoing Event Card (if any today) -->
  <?php if ($ongoingEvent): ?>
  <div class="bg-white rounded-2xl border-2 border-emerald-500/40 p-6 shadow-sm relative overflow-hidden">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div class="space-y-1">
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
            ACTIVE EVENT NOW
          </span>
          <span class="text-xs text-slate-400 font-medium">Host: <?= htmlspecialchars($ongoingEvent['host_department'] ?: 'All College') ?></span>
        </div>
        <h3 class="text-xl font-black text-slate-900 tracking-tight"><?= htmlspecialchars($ongoingEvent['event_name']) ?></h3>
        <p class="text-xs text-slate-500">
          Time-In: <span class="font-semibold text-slate-700"><?= date('h:i A', strtotime($ongoingEvent['TimeIn_start'])) ?> - <?= date('h:i A', strtotime($ongoingEvent['TimeIn_end'])) ?></span> 
          • Time-Out: <span class="font-semibold text-slate-700"><?= date('h:i A', strtotime($ongoingEvent['Time_out'])) ?> - <?= date('h:i A', strtotime($ongoingEvent['Timeout_out'])) ?></span>
        </p>
      </div>

      <a href="<?= Config::BASE_URL ?>/scan?id=<?= $ongoingEvent['id'] ?>" 
         class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md transition active:scale-95">
        <i data-lucide="scan" class="w-4 h-4"></i>
        Open RFID Station
      </a>
    </div>
  </div>
  <?php endif; ?>

  <!-- Charts & Scheduled Events Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- 7-Day Activity Chart -->
    <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-base font-bold text-slate-900 tracking-tight">Attendance Activity</h3>
          <p class="text-xs text-slate-400">Total RFID scans recorded over the last 7 days</p>
        </div>
        <i data-lucide="trending-up" class="w-5 h-5 text-slate-400"></i>
      </div>
      <div class="h-64">
        <canvas id="scansChart"></canvas>
      </div>
    </div>

    <!-- Scheduled Events Today -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-base font-bold text-slate-900 tracking-tight">Today's Schedule</h3>
          <span class="text-xs font-bold text-maroon-700 bg-maroon-50 px-2.5 py-1 rounded-lg">
            <?= count($todayEvents) ?> <?= count($todayEvents) === 1 ? 'Event' : 'Events' ?>
          </span>
        </div>

        <?php if (empty($todayEvents)): ?>
          <div class="py-12 text-center text-slate-400 text-xs">
            <i data-lucide="calendar-x" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
            No events scheduled for today.
          </div>
        <?php else: ?>
          <div class="space-y-3 overflow-y-auto max-h-56 pr-1">
            <?php foreach ($todayEvents as $ev): ?>
              <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                <div class="min-w-0 pr-2">
                  <h5 class="text-xs font-bold text-slate-800 truncate"><?= htmlspecialchars($ev['event_name']) ?></h5>
                  <p class="text-[11px] text-slate-500 mt-0.5">
                    <?= date('h:i A', strtotime($ev['TimeIn_start'])) ?> - <?= date('h:i A', strtotime($ev['Timeout_out'])) ?>
                  </p>
                </div>
                <a href="<?= Config::BASE_URL ?>/scan?id=<?= $ev['id'] ?>" class="p-2 rounded-lg bg-white border shadow-xs text-slate-700 hover:text-maroon-800 transition">
                  <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="pt-4 border-t border-slate-100 mt-4">
        <a href="<?= Config::BASE_URL ?>/events" class="text-xs font-bold text-maroon-700 hover:text-maroon-900 flex items-center justify-between group">
          <span>View all upcoming events</span>
          <i data-lucide="chevron-right" class="w-4 h-4 group-hover:translate-x-1 transition"></i>
        </a>
      </div>
    </div>

  </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const chartData = <?= json_encode($scans7Days) ?>;
  const labels = chartData.map(d => d.label);
  const counts = chartData.map(d => d.count);

  const ctx = document.getElementById('scansChart').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Scans',
        data: counts,
        borderColor: '#6b0f0f',
        backgroundColor: 'rgba(107, 15, 15, 0.08)',
        borderWidth: 2.5,
        fill: true,
        tension: 0.35,
        pointBackgroundColor: '#6b0f0f',
        pointRadius: 4,
        pointHoverRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { precision: 0, font: { size: 11 } },
          grid: { color: '#f1f5f9' }
        },
        x: {
          ticks: { font: { size: 11 } },
          grid: { display: false }
        }
      }
    }
  });
});
</script>
