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

  <!-- Tabs & Interactive Filter Section -->
  <div x-data="reportViewer(
    '<?= $tab ?>',
    <?= htmlspecialchars(json_encode($attendees ?? []), ENT_QUOTES, 'UTF-8') ?>,
    <?= htmlspecialchars(json_encode($absentees ?? []), ENT_QUOTES, 'UTF-8') ?>
  )" class="space-y-4">
    
    <!-- Tab Navigation & Filter Toolbar -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-slate-200 pb-3">
      
      <!-- Tabs -->
      <div class="flex items-center gap-2">
        <a href="<?= Config::BASE_URL ?>/reports?event_id=<?= $event['id'] ?>&tab=attendees" 
           class="px-4 py-2 text-xs font-bold rounded-xl transition <?= $tab === 'attendees' ? 'bg-maroon-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
          Attendees List (<?= count($attendees) ?>)
        </a>
        <a href="<?= Config::BASE_URL ?>/reports?event_id=<?= $event['id'] ?>&tab=absentees" 
           class="px-4 py-2 text-xs font-bold rounded-xl transition <?= $tab === 'absentees' ? 'bg-maroon-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
          Absentees / Missing (<?= count($absentees) ?>)
        </a>
      </div>

      <!-- Real-time Table Filter Controls -->
      <div class="flex flex-wrap items-center gap-2.5">
        
        <!-- Department Filter -->
        <select x-model="selectedDept" 
                @change="currentPage = 1" 
                class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-maroon-700/20">
          <option value="">All Departments</option>
          <template x-for="d in departments()" :key="d">
            <option :value="d" x-text="d"></option>
          </template>
        </select>

        <!-- Course Filter -->
        <select x-model="selectedCourse" 
                @change="currentPage = 1" 
                class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-maroon-700/20">
          <option value="">All Courses</option>
          <template x-for="c in courses()" :key="c">
            <option :value="c" x-text="c"></option>
          </template>
        </select>

        <!-- Search Input -->
        <div class="relative min-w-[180px]">
          <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
            <i data-lucide="search" class="w-3.5 h-3.5"></i>
          </div>
          <input type="text" 
                 x-model="searchQuery" 
                 @input="currentPage = 1"
                 placeholder="Search student..." 
                 class="w-full pl-8 pr-7 py-1.5 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-maroon-700/20">
          <button x-show="searchQuery" 
                  @click="searchQuery = ''; currentPage = 1" 
                  class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
          </button>
        </div>

        <!-- Clear Button -->
        <button x-show="selectedCourse || selectedDept || searchQuery" 
                @click="clearFilters()" 
                type="button" 
                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-xl text-xs font-bold transition">
          <i data-lucide="filter-x" class="w-3.5 h-3.5"></i>
          Clear
        </button>

      </div>

    </div>

    <!-- Filter Stats Badge -->
    <div class="flex items-center justify-between text-xs text-slate-500">
      <span x-text="badgeText"></span>
      <div class="flex items-center gap-1.5">
        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Show:</label>
        <select x-model.number="perPage" 
                @change="currentPage = 1" 
                class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 outline-none">
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
          <option :value="0">All</option>
        </select>
      </div>
    </div>

    <!-- Active Tab Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        
        <!-- Attendees Table -->
        <template x-if="tab === 'attendees'">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
                <th class="py-3.5 px-4 w-12 text-center">#</th>
                <th class="py-3.5 px-4">Student ID</th>
                <th class="py-3.5 px-4">Full Name</th>
                <th class="py-3.5 px-4">Course</th>
                <th class="py-3.5 px-4">Department</th>
                <th class="py-3.5 px-4">Time-In</th>
                <th class="py-3.5 px-4">Time-Out</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <template x-if="paginatedList.length === 0">
                <tr>
                  <td colspan="7" class="py-12 text-center text-slate-400">
                    No attendees match the filter criteria.
                  </td>
                </tr>
              </template>
              <template x-for="(item, idx) in paginatedList" :key="item.Attendance_ID || (item.Student_ID + '_' + idx)">
                <tr class="hover:bg-slate-50/80 transition">
                  <td class="py-3.5 px-4 text-slate-400 font-mono text-center" 
                      x-text="perPage > 0 ? ((currentPage - 1) * perPage + idx + 1) : (idx + 1)"></td>
                  <td class="py-3.5 px-4 font-mono font-bold text-slate-800" x-text="item.Student_ID"></td>
                  <td class="py-3.5 px-4 font-bold text-slate-900" x-text="item.Student_name"></td>
                  <td class="py-3.5 px-4">
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold" x-text="item.Course"></span>
                  </td>
                  <td class="py-3.5 px-4 text-slate-600 font-medium" x-text="item.department_name || '—'"></td>
                  <td class="py-3.5 px-4 font-mono font-bold text-emerald-700" x-text="formatTime12(item.Time_in)"></td>
                  <td class="py-3.5 px-4 font-mono font-medium text-slate-600" x-text="formatTime12(item.Time_out)"></td>
                </tr>
              </template>
            </tbody>
          </table>
        </template>

        <!-- Absentees Table -->
        <template x-if="tab === 'absentees'">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
                <th class="py-3.5 px-4 w-12 text-center">#</th>
                <th class="py-3.5 px-4">Student ID</th>
                <th class="py-3.5 px-4">Full Name</th>
                <th class="py-3.5 px-4">Course</th>
                <th class="py-3.5 px-4">Department</th>
                <th class="py-3.5 px-4 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <template x-if="paginatedList.length === 0">
                <tr>
                  <td colspan="6" class="py-12 text-center text-slate-400">
                    No absentees match the filter criteria.
                  </td>
                </tr>
              </template>
              <template x-for="(s, idx) in paginatedList" :key="s.ID_Number || (s.id + '_' + idx)">
                <tr class="hover:bg-slate-50/80 transition">
                  <td class="py-3.5 px-4 text-slate-400 font-mono text-center" 
                      x-text="perPage > 0 ? ((currentPage - 1) * perPage + idx + 1) : (idx + 1)"></td>
                  <td class="py-3.5 px-4 font-mono font-bold text-slate-800" x-text="s.ID_Number"></td>
                  <td class="py-3.5 px-4 font-bold text-slate-900" x-text="s.Student_name"></td>
                  <td class="py-3.5 px-4">
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold" x-text="s.Course"></span>
                  </td>
                  <td class="py-3.5 px-4 text-slate-600 font-medium" x-text="s.department_name || '—'"></td>
                  <td class="py-3.5 px-4 text-center">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-800">
                      ABSENT
                    </span>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </template>

      </div>

      <!-- Pagination Footer -->
      <div x-show="filteredList.length > 0 && perPage > 0" class="p-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-slate-500">
        <div>
          Showing 
          <span class="font-bold text-slate-800" x-text="(currentPage - 1) * perPage + 1"></span>
          to 
          <span class="font-bold text-slate-800" x-text="Math.min(currentPage * perPage, filteredList.length)"></span>
          of 
          <span class="font-bold text-slate-800" x-text="filteredList.length"></span>
          records
        </div>

        <div class="flex items-center gap-1" x-show="totalPages > 1">
          <button @click="currentPage--" 
                  :disabled="currentPage <= 1"
                  type="button"
                  class="px-2.5 py-1 rounded-lg border border-slate-200 font-semibold text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
            Prev
          </button>
          <span class="px-3 py-1 font-bold text-slate-800">
            Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
          </span>
          <button @click="currentPage++" 
                  :disabled="currentPage >= totalPages"
                  type="button"
                  class="px-2.5 py-1 rounded-lg border border-slate-200 font-semibold text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
            Next
          </button>
        </div>
      </div>

    </div>

  </div>
  <?php endif; ?>

</div>

<script>
function reportViewer(tab, attendees, absentees) {
  return {
    tab: tab,
    rawAttendees: attendees || [],
    rawAbsentees: absentees || [],
    searchQuery: '',
    selectedCourse: '',
    selectedDept: '',
    perPage: 25,
    currentPage: 1,

    get currentList() {
      return this.tab === 'attendees' ? this.rawAttendees : this.rawAbsentees;
    },

    courses() {
      const set = new Set();
      this.currentList.forEach(item => {
        if (item.Course && item.Course.trim()) set.add(item.Course.trim());
      });
      return Array.from(set).sort();
    },

    departments() {
      const set = new Set();
      this.currentList.forEach(item => {
        const d = item.department_name || item.departments_id;
        if (d && String(d).trim()) set.add(String(d).trim());
      });
      return Array.from(set).sort();
    },

    get filteredList() {
      let list = this.currentList;
      if (this.selectedCourse) {
        const c = this.selectedCourse.toLowerCase();
        list = list.filter(item => (item.Course || '').toLowerCase() === c);
      }
      if (this.selectedDept) {
        const d = this.selectedDept.toLowerCase();
        list = list.filter(item => {
          const dept = (item.department_name || item.departments_id || '').toLowerCase();
          return dept === d;
        });
      }
      if (this.searchQuery.trim()) {
        const q = this.searchQuery.toLowerCase().trim();
        list = list.filter(item => {
          const name = (item.Student_name || '').toLowerCase();
          const id = (item.Student_ID || item.ID_Number || '').toString().toLowerCase();
          const course = (item.Course || '').toLowerCase();
          return name.includes(q) || id.includes(q) || course.includes(q);
        });
      }
      return list;
    },

    get totalPages() {
      if (!this.perPage || this.perPage <= 0) return 1;
      return Math.max(1, Math.ceil(this.filteredList.length / this.perPage));
    },

    get paginatedList() {
      if (!this.perPage || this.perPage <= 0) return this.filteredList;
      const start = (this.currentPage - 1) * this.perPage;
      return this.filteredList.slice(start, start + this.perPage);
    },

    get badgeText() {
      const total = this.currentList.length;
      const filtered = this.filteredList.length;
      if (this.selectedCourse || this.selectedDept || this.searchQuery) {
        return `Showing ${filtered} of ${total} records matching filters`;
      }
      return `Total ${total} records`;
    },

    clearFilters() {
      this.selectedCourse = '';
      this.selectedDept = '';
      this.searchQuery = '';
      this.currentPage = 1;
    },

    formatTime12(dt) {
      if (!dt || dt === '—') return '—';
      let timeStr = dt.includes(' ') ? dt.split(' ')[1] : dt;
      let parts = timeStr.split(':');
      if (parts.length < 2) return dt;
      let hours = parseInt(parts[0], 10);
      let minutes = parts[1];
      let seconds = parts[2] ? parts[2] : null;
      let ampm = hours >= 12 ? 'PM' : 'AM';
      hours = hours % 12;
      hours = hours ? hours : 12;
      let hoursStr = hours < 10 ? '0' + hours : hours;
      return seconds ? `${hoursStr}:${minutes}:${seconds} ${ampm}` : `${hoursStr}:${minutes} ${ampm}`;
    }
  };
}
</script>
