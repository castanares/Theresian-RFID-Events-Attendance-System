<?php
use App\Config\Config;
use App\Core\Auth;
?>
<div class="space-y-6" x-data="rfidScanner(<?= $eventId ?? 'null' ?>)">

  <!-- Header & Event Status Banner -->
  <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    
    <div>
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold <?= $event ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
          <span class="w-1.5 h-1.5 rounded-full <?= $event ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500' ?> mr-1.5"></span>
          <?= $event ? 'SCANNER ONLINE' : 'NO ACTIVE EVENT' ?>
        </span>
        <span class="text-xs text-slate-400">Station Active</span>
      </div>

      <?php if ($event): ?>
        <h2 class="text-xl font-black text-slate-900 tracking-tight mt-1">
          <?= htmlspecialchars($event['event_name']) ?>
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          <?= date('F j, Y', strtotime($event['date'])) ?> • 
          Time-In: <span class="font-semibold text-slate-700"><?= date('h:i A', strtotime($event['TimeIn_start'])) ?> - <?= date('h:i A', strtotime($event['TimeIn_end'])) ?></span> | 
          Time-Out: <span class="font-semibold text-slate-700"><?= date('h:i A', strtotime($event['Time_out'])) ?> - <?= date('h:i A', strtotime($event['Timeout_out'])) ?></span>
        </p>
      <?php else: ?>
        <h2 class="text-xl font-bold text-slate-700 mt-1">Please select an event to begin scanning</h2>
      <?php endif; ?>
    </div>

    <!-- Manual Entry & Event Selector Actions -->
    <div class="flex items-center gap-3">
      <?php if ($event): ?>
      <button @click="manualModal = true" 
              type="button" 
              class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition active:scale-95">
        <i data-lucide="keyboard" class="w-4 h-4"></i>
        Manual ID Check-In
      </button>
      <?php endif; ?>

      <a href="<?= Config::BASE_URL ?>/events" 
         class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-maroon-900 hover:bg-maroon-800 text-white font-semibold text-xs shadow-xs transition active:scale-95">
        <i data-lucide="list-filter" class="w-4 h-4"></i>
        Switch Event
      </a>
    </div>

  </div>

  <?php if ($event): ?>
  <!-- Active Scanner Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left Column: RFID Tapping Input & Flash Feedback -->
    <div class="lg:col-span-1 space-y-4">
      
      <!-- Auto-focused RFID Card -->
      <div class="bg-gradient-to-br from-maroon-900 via-maroon-950 to-maroon-900 rounded-2xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="flex items-center justify-between mb-4">
          <span class="text-xs font-bold uppercase tracking-wider text-maroon-200">Tap RFID Card</span>
          <i data-lucide="nfc" class="w-6 h-6 text-maroon-200 animate-pulse"></i>
        </div>

        <!-- Hidden / Auto-focused Input -->
        <div class="relative">
          <input type="text" 
                 x-ref="rfidInput"
                 x-model="rfidCode"
                 @keydown.enter.prevent="handleScan"
                 @blur="keepFocus"
                 autofocus
                 autocomplete="off"
                 placeholder="Ready for scan..."
                 class="w-full py-3.5 px-4 bg-white/15 border border-white/20 rounded-xl text-white placeholder-maroon-300/60 font-mono text-sm tracking-wider focus:outline-none focus:ring-2 focus:ring-white/40">
        </div>

        <p class="text-[11px] text-maroon-300/80 mt-3 text-center">
          Scanner is active and ready. Just tap student card.
        </p>
      </div>

      <!-- Student Flash Feedback Card -->
      <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs transition-all duration-200"
           :class="feedback ? (feedback.success ? 'ring-2 ring-emerald-500' : 'ring-2 ring-red-500') : ''">
        
        <div class="text-center py-2" x-show="!feedback">
          <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
            <i data-lucide="user-check" class="w-8 h-8 opacity-60"></i>
          </div>
          <h4 class="text-sm font-bold text-slate-700">Awaiting Tap</h4>
          <p class="text-xs text-slate-400 mt-1">Student information will appear here</p>
        </div>

        <!-- Populated Feedback -->
        <div x-show="feedback" x-cloak class="space-y-4">
          
          <!-- Status Banner -->
          <div class="p-3 rounded-xl text-center text-xs font-black uppercase tracking-wider"
               :class="feedback?.type === 'success' ? 'bg-emerald-100 text-emerald-800' : (feedback?.type === 'warning' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')">
            <span x-text="feedback?.message"></span>
          </div>

          <!-- Student Info (if student found) -->
          <template x-if="feedback?.student">
            <div class="flex items-center gap-4 pt-2">
              <div class="w-16 h-16 rounded-2xl bg-slate-100 overflow-hidden ring-2 ring-maroon-800/20 flex-shrink-0 flex items-center justify-center text-slate-400">
                <i data-lucide="user" class="w-8 h-8"></i>
              </div>
              <div class="min-w-0">
                <h4 class="text-base font-bold text-slate-900 truncate" x-text="feedback.student.Student_name"></h4>
                <p class="text-xs text-slate-500 font-mono mt-0.5" x-text="'ID: ' + feedback.student.ID_Number"></p>
                <div class="flex items-center gap-2 mt-1">
                  <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-maroon-50 text-maroon-800" x-text="feedback.student.Course"></span>
                  <span class="text-xs text-slate-400" x-text="feedback.student.dept_Acronym"></span>
                </div>
              </div>
            </div>
          </template>

          <!-- Scan Time Timestamp -->
          <div class="text-center pt-2 border-t border-slate-100 text-[11px] text-slate-400">
            Recorded at: <span class="font-bold text-slate-700" x-text="feedback?.time || 'Just now'"></span>
          </div>

        </div>

      </div>

    </div>

    <!-- Right Column: Live Attendees Table -->
    <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col">
      
      <!-- Feed Header with Count Badge -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
          <h3 class="text-base font-bold text-slate-900 tracking-tight">Real-Time Attendee Feed</h3>
          <p class="text-xs text-slate-400">Live attendance log for this session</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200"
                x-text="badgeText">
          </span>
        </div>
      </div>

      <!-- Filter & Search Toolbar -->
      <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/80 mb-4 flex flex-col sm:flex-row sm:items-center gap-2.5">
        
        <!-- Search Input -->
        <div class="relative flex-1">
          <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
            <i data-lucide="search" class="w-3.5 h-3.5"></i>
          </div>
          <input type="text" 
                 x-model="searchQuery" 
                 @input="currentPage = 1"
                 placeholder="Search student name or ID..." 
                 class="w-full pl-8 pr-7 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-800 placeholder-slate-400 outline-none focus:ring-2 focus:ring-maroon-700/20">
          <button x-show="searchQuery" 
                  @click="searchQuery = ''; currentPage = 1" 
                  class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
          </button>
        </div>

        <!-- Filter by Course -->
        <div class="flex items-center gap-1.5">
          <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap hidden md:inline">Course:</label>
          <select x-model="selectedCourse" 
                  @change="currentPage = 1" 
                  class="px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-maroon-700/20">
            <option value="">All Courses</option>
            <template x-for="c in courses()" :key="c">
              <option :value="c" x-text="c"></option>
            </template>
          </select>
        </div>

        <!-- Rows Per Page Selector -->
        <div class="flex items-center gap-1.5">
          <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap hidden md:inline">Show:</label>
          <select x-model.number="perPage" 
                  @change="currentPage = 1" 
                  class="px-2 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-maroon-700/20">
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
            <option :value="0">All</option>
          </select>
        </div>

        <!-- Clear Filter Button -->
        <button x-show="selectedCourse || searchQuery" 
                @click="clearFilters()" 
                type="button" 
                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-xs font-bold transition">
          <i data-lucide="filter-x" class="w-3.5 h-3.5"></i>
          <span>Clear</span>
        </button>

      </div>

      <!-- Attendees Table -->
      <div class="overflow-x-auto flex-1 min-h-[300px] max-h-[520px]">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold sticky top-0 bg-white">
              <th class="py-3 px-3 w-10 text-center">#</th>
              <th class="py-3 px-3">Student ID</th>
              <th class="py-3 px-3">Full Name</th>
              <th class="py-3 px-3">Course</th>
              <th class="py-3 px-3">Time In</th>
              <th class="py-3 px-3">Time Out</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template x-if="paginatedAttendees.length === 0">
              <tr>
                <td colspan="6" class="py-12 text-center text-slate-400">
                  <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
                    <i data-lucide="inbox" class="w-5 h-5"></i>
                  </div>
                  <span x-text="attendees.length === 0 ? 'No attendance records yet. Start scanning cards.' : 'No attendees match the selected filter.'"></span>
                </td>
              </tr>
            </template>
            <template x-for="(item, idx) in paginatedAttendees" :key="item.Attendance_ID || (item.Student_ID + '_' + idx)">
              <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3 px-3 text-center text-slate-400 font-mono font-medium" 
                    x-text="perPage > 0 ? ((currentPage - 1) * perPage + idx + 1) : (idx + 1)"></td>
                <td class="py-3 px-3 font-mono font-medium text-slate-800" x-text="item.Student_ID"></td>
                <td class="py-3 px-3 font-bold text-slate-900" x-text="item.Student_name"></td>
                <td class="py-3 px-3">
                  <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold" x-text="item.Course"></span>
                </td>
                <td class="py-3 px-3 font-mono text-emerald-700 font-bold" x-text="formatTime12(item.Time_in)"></td>
                <td class="py-3 px-3 font-mono text-slate-600 font-medium" x-text="formatTime12(item.Time_out)"></td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Pagination Controls Footer -->
      <div x-show="filteredAttendees.length > 0 && perPage > 0" class="pt-4 mt-2 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-slate-500">
        <div>
          Showing 
          <span class="font-bold text-slate-800" x-text="(currentPage - 1) * perPage + 1"></span>
          to 
          <span class="font-bold text-slate-800" x-text="Math.min(currentPage * perPage, filteredAttendees.length)"></span>
          of 
          <span class="font-bold text-slate-800" x-text="filteredAttendees.length"></span>
          entries
        </div>

        <div class="flex items-center gap-1 self-end sm:self-auto" x-show="totalPages > 1">
          <button @click="setPage(currentPage - 1)" 
                  :disabled="currentPage <= 1"
                  type="button"
                  class="px-2.5 py-1 rounded-lg border border-slate-200 font-semibold text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
            Prev
          </button>

          <span class="px-3 py-1 font-bold text-slate-800">
            Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
          </span>

          <button @click="setPage(currentPage + 1)" 
                  :disabled="currentPage >= totalPages"
                  type="button"
                  class="px-2.5 py-1 rounded-lg border border-slate-200 font-semibold text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
            Next
          </button>
        </div>
      </div>

    </div>

  </div>

  <!-- Manual Check-In Modal -->
  <div x-show="manualModal" x-cloak 
       class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
       x-transition>
    <div @click.away="manualModal = false" class="w-full max-w-md bg-white rounded-2xl p-6 shadow-2xl border border-slate-100">
      <div class="flex items-center justify-between mb-4">
        <h4 class="text-base font-bold text-slate-900">Manual Student Check-In</h4>
        <button @click="manualModal = false" class="text-slate-400 hover:text-slate-600">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <p class="text-xs text-slate-500 mb-4">Check in a student who forgot or damaged their RFID card using their Student ID number.</p>

      <form @submit.prevent="submitManual" class="space-y-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Student ID Number</label>
          <input type="text" x-model="manualIdNumber" required placeholder="e.g. 4226347" class="w-full p-3 bg-slate-50 border rounded-xl text-sm font-mono focus:bg-white outline-none">
        </div>

        <div x-show="manualMsg" class="p-3 rounded-lg text-xs font-semibold" :class="manualMsgSuccess ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'" x-text="manualMsg"></div>

        <div class="flex gap-2 justify-end pt-2">
          <button type="button" @click="manualModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
          <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-maroon-900 hover:bg-maroon-800 text-white shadow-xs">Record Attendance</button>
        </div>
      </form>
    </div>
  </div>

  <?php endif; ?>

</div>

<script>
function rfidScanner(eventId) {
  return {
    eventId: eventId,
    rfidCode: '',
    feedback: null,
    attendees: <?= json_encode($attendees ?? []) ?>,
    searchQuery: '',
    selectedCourse: '',
    perPage: 25,
    currentPage: 1,
    manualModal: false,
    manualIdNumber: '',
    manualMsg: '',
    manualMsgSuccess: false,

    init() {
      this.$nextTick(() => {
        this.focusInput();
      });
      // Polling for live attendee updates every 4 seconds
      if (this.eventId) {
        setInterval(() => this.fetchLogs(), 4000);
      }
    },

    focusInput() {
      if (this.manualModal) return;
      const active = document.activeElement;
      if (active && (active.tagName === 'INPUT' || active.tagName === 'SELECT' || active.tagName === 'TEXTAREA')) {
        if (active !== this.$refs.rfidInput) {
          return; // Don't interrupt user typing in search/filter
        }
      }
      if (this.$refs.rfidInput) {
        this.$refs.rfidInput.focus();
      }
    },

    keepFocus() {
      if (!this.manualModal) {
        setTimeout(() => this.focusInput(), 200);
      }
    },

    courses() {
      const set = new Set();
      (this.attendees || []).forEach(a => {
        if (a.Course && a.Course.trim()) {
          set.add(a.Course.trim());
        }
      });
      return Array.from(set).sort();
    },

    get filteredAttendees() {
      let list = this.attendees || [];
      if (this.selectedCourse) {
        const c = this.selectedCourse.toLowerCase();
        list = list.filter(a => (a.Course || '').toLowerCase() === c);
      }
      if (this.searchQuery && this.searchQuery.trim()) {
        const q = this.searchQuery.toLowerCase().trim();
        list = list.filter(a => 
          (a.Student_name || '').toLowerCase().includes(q) ||
          (a.Student_ID || '').toString().toLowerCase().includes(q) ||
          (a.Course || '').toLowerCase().includes(q)
        );
      }
      return list;
    },

    get totalPages() {
      if (!this.perPage || this.perPage <= 0) return 1;
      return Math.max(1, Math.ceil(this.filteredAttendees.length / this.perPage));
    },

    get paginatedAttendees() {
      if (!this.perPage || this.perPage <= 0) {
        return this.filteredAttendees;
      }
      const start = (this.currentPage - 1) * this.perPage;
      return this.filteredAttendees.slice(start, start + this.perPage);
    },

    get badgeText() {
      const total = (this.attendees || []).length;
      const filtered = this.filteredAttendees.length;
      if (this.selectedCourse && this.searchQuery) {
        return `${filtered} in ${this.selectedCourse} matching search`;
      }
      if (this.selectedCourse) {
        return `${filtered} student${filtered !== 1 ? 's' : ''} in ${this.selectedCourse}`;
      }
      if (this.searchQuery) {
        return `${filtered} of ${total} matching search`;
      }
      return `${total} Checked In`;
    },

    clearFilters() {
      this.selectedCourse = '';
      this.searchQuery = '';
      this.currentPage = 1;
      this.focusInput();
    },

    setPage(p) {
      if (p >= 1 && p <= this.totalPages) {
        this.currentPage = p;
      }
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
      hours = hours ? hours : 12; // 0 becomes 12
      let hoursStr = hours < 10 ? '0' + hours : hours;
      return seconds ? `${hoursStr}:${minutes}:${seconds} ${ampm}` : `${hoursStr}:${minutes} ${ampm}`;
    },

    async handleScan() {
      const code = this.rfidCode.trim();
      if (!code || !this.eventId) return;

      this.rfidCode = ''; // Clear immediately for next scan
      try {
        const res = await fetch('<?= Config::BASE_URL ?>/api/scan', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ event_id: this.eventId, rfid: code })
        });
        const data = await res.json();
        this.feedback = data;

        if (data.success) {
          window.SoundBeeper.beepSuccess();
          await this.fetchLogs();
        } else if (data.type === 'warning') {
          window.SoundBeeper.beepWarning();
          await this.fetchLogs();
        } else {
          window.SoundBeeper.beepError();
        }
      } catch (e) {
        this.feedback = { success: false, message: 'Server communication error', type: 'error' };
        window.SoundBeeper.beepError();
      } finally {
        this.focusInput();
      }
    },

    async fetchLogs() {
      if (!this.eventId) return;
      try {
        const res = await fetch('<?= Config::BASE_URL ?>/api/attendance-logs/' + this.eventId);
        const data = await res.json();
        if (data.success) {
          this.attendees = data.data;
        }
      } catch (e) { /* ignore polling errors */ }
    },

    async submitManual() {
      if (!this.manualIdNumber) return;
      this.manualMsg = '';
      try {
        const res = await fetch('<?= Config::BASE_URL ?>/api/manual-checkin', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ event_id: this.eventId, id_number: this.manualIdNumber })
        });
        const data = await res.json();
        this.manualMsgSuccess = data.success;
        this.manualMsg = data.message;
        if (data.success) {
          window.SoundBeeper.beepSuccess();
          this.manualIdNumber = '';
          this.fetchLogs();
          setTimeout(() => { this.manualModal = false; this.manualMsg = ''; }, 1500);
        } else {
          window.SoundBeeper.beepError();
        }
      } catch (e) {
        this.manualMsgSuccess = false;
        this.manualMsg = 'Connection error.';
      }
    }
  };
}
</script>
