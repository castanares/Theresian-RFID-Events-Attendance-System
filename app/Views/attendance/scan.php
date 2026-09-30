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
      
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-base font-bold text-slate-900 tracking-tight">Real-Time Attendee Feed</h3>
          <p class="text-xs text-slate-400">Live attendance log for this session</p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
          <span x-text="attendees.length"></span> Checked In
        </span>
      </div>

      <!-- Attendees Table -->
      <div class="overflow-x-auto flex-1 max-h-[500px]">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
              <th class="py-3 px-3">Student ID</th>
              <th class="py-3 px-3">Full Name</th>
              <th class="py-3 px-3">Course</th>
              <th class="py-3 px-3">Time In</th>
              <th class="py-3 px-3">Time Out</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template x-if="attendees.length === 0">
              <tr>
                <td colspan="5" class="py-12 text-center text-slate-400">
                  No attendance records yet. Start scanning cards above.
                </td>
              </tr>
            </template>
            <template x-for="item in attendees" :key="item.Attendance_ID">
              <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3 px-3 font-mono font-medium text-slate-800" x-text="item.Student_ID"></td>
                <td class="py-3 px-3 font-bold text-slate-900" x-text="item.Student_name"></td>
                <td class="py-3 px-3">
                  <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold" x-text="item.Course"></span>
                </td>
                <td class="py-3 px-3 font-mono text-emerald-700 font-medium" x-text="item.Time_in"></td>
                <td class="py-3 px-3 font-mono text-slate-500" x-text="item.Time_out || '—'"></td>
              </tr>
            </template>
          </tbody>
        </table>
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
      if (this.$refs.rfidInput) {
        this.$refs.rfidInput.focus();
      }
    },

    keepFocus() {
      if (!this.manualModal) {
        setTimeout(() => this.focusInput(), 200);
      }
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
