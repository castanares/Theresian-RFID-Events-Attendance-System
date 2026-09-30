<?php
use App\Config\Config;
use App\Core\Auth;
use App\Core\Csrf;
?>
<div class="space-y-6" x-data="eventManager()">

  <!-- Action Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="text-xl font-black text-slate-900 tracking-tight">Events Management</h2>
      <p class="text-xs text-slate-500 mt-0.5">Schedule, configure, and monitor institutional event attendance</p>
    </div>

    <button @click="openCreateModal()" 
            type="button" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-maroon-900 hover:bg-maroon-800 text-white font-bold text-xs shadow-md hover:shadow-lg transition active:scale-95">
      <i data-lucide="calendar-plus" class="w-4 h-4"></i>
      Create New Event
    </button>
  </div>

  <!-- Filters & Tabs Bar -->
  <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    
    <!-- Status Tabs -->
    <div class="flex items-center gap-2">
      <a href="<?= Config::BASE_URL ?>/events?status=active" 
         class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= ($currentFilter['status'] ?? 'active') === 'active' ? 'bg-maroon-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
        Active Events
      </a>
      <a href="<?= Config::BASE_URL ?>/events?status=archived" 
         class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= ($currentFilter['status'] ?? '') === 'archived' ? 'bg-maroon-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
        Archived
      </a>
    </div>

    <!-- Semester Dropdown Filter -->
    <div class="flex items-center gap-3">
      <label class="text-xs font-semibold text-slate-500">Filter Semester:</label>
      <select @change="window.location.href = '<?= Config::BASE_URL ?>/events?status=<?= $currentFilter['status'] ?>&semester_id=' + $event.target.value" 
              class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-maroon-700/20">
        <option value="">All Semesters</option>
        <?php foreach ($semesters as $sem): ?>
          <option value="<?= $sem['id'] ?>" <?= ($currentFilter['semester_id'] ?? '') == $sem['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($sem['semester']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

  </div>

  <!-- Events Table / Card Grid -->
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
            <th class="py-3.5 px-4">Event Name</th>
            <th class="py-3.5 px-4">Date</th>
            <th class="py-3.5 px-4">Host / Scope</th>
            <th class="py-3.5 px-4">Time-In Window</th>
            <th class="py-3.5 px-4">Time-Out Window</th>
            <th class="py-3.5 px-4 text-center">Attendees</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($events)): ?>
            <tr>
              <td colspan="7" class="py-12 text-center text-slate-400">
                <i data-lucide="calendar-x" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                No events found matching current filter.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($events as $ev): ?>
              <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3.5 px-4 font-bold text-slate-900">
                  <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full <?= $ev['status'] === 'active' ? 'bg-emerald-500' : 'bg-slate-300' ?>"></span>
                    <?= htmlspecialchars($ev['event_name']) ?>
                  </div>
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-700 whitespace-nowrap">
                  <?= date('M j, Y', strtotime($ev['date'])) ?>
                </td>
                <td class="py-3.5 px-4">
                  <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $ev['departments_id'] == 0 ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                    <?= $ev['departments_id'] == 0 ? 'All Departments' : htmlspecialchars($ev['host_department'] ?: 'Department') ?>
                  </span>
                </td>
                <td class="py-3.5 px-4 font-mono text-slate-600 whitespace-nowrap">
                  <?= date('h:i A', strtotime($ev['TimeIn_start'])) ?> - <?= date('h:i A', strtotime($ev['TimeIn_end'])) ?>
                </td>
                <td class="py-3.5 px-4 font-mono text-slate-600 whitespace-nowrap">
                  <?= date('h:i A', strtotime($ev['Time_out'])) ?> - <?= date('h:i A', strtotime($ev['Timeout_out'])) ?>
                </td>
                <td class="py-3.5 px-4 text-center">
                  <span class="inline-block px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-bold text-xs">
                    <?= number_format($ev['attendee_count'] ?? 0) ?>
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    
                    <!-- Scan Button -->
                    <?php if ($ev['status'] === 'active'): ?>
                    <a href="<?= Config::BASE_URL ?>/scan?id=<?= $ev['id'] ?>" 
                       title="Open Scanner"
                       class="p-2 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
                      <i data-lucide="scan" class="w-4 h-4"></i>
                    </a>
                    <?php endif; ?>

                    <!-- View Report -->
                    <a href="<?= Config::BASE_URL ?>/reports?event_id=<?= $ev['id'] ?>" 
                       title="View Report"
                       class="p-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                      <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                    </a>

                    <!-- Edit -->
                    <?php if ($ev['status'] === 'active'): ?>
                    <button @click="openEditModal(<?= htmlspecialchars(json_encode($ev)) ?>)" 
                            title="Edit Event"
                            type="button" 
                            class="p-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                      <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>

                    <!-- Archive -->
                    <form action="<?= Config::BASE_URL ?>/events/archive/<?= $ev['id'] ?>" method="POST" onsubmit="return confirm('Archive this event?');" class="inline">
                      <?= Csrf::field() ?>
                      <button type="submit" title="Archive Event" class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">
                        <i data-lucide="archive" class="w-4 h-4"></i>
                      </button>
                    </form>
                    <?php else: ?>
                    <!-- Restore -->
                    <form action="<?= Config::BASE_URL ?>/events/restore/<?= $ev['id'] ?>" method="POST" class="inline">
                      <?= Csrf::field() ?>
                      <button type="submit" title="Restore Event" class="p-2 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                      </button>
                    </form>
                    <?php endif; ?>

                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Create / Edit Event Modal -->
  <div x-show="showModal" x-cloak 
       class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto"
       x-transition>
    <div @click.away="showModal = false" class="w-full max-w-2xl bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100 my-8">
      
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-black text-slate-900 tracking-tight" x-text="isEdit ? 'Edit Event Schedule' : 'Create New Event'"></h3>
          <p class="text-xs text-slate-500 mt-0.5">Configure event schedule, hosting department, and scanning time windows</p>
        </div>
        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <form :action="isEdit ? '<?= Config::BASE_URL ?>/events/update/' + form.id : '<?= Config::BASE_URL ?>/events/store'" method="POST" class="space-y-4">
        <?= Csrf::field() ?>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Event Name</label>
          <input type="text" name="event_name" x-model="form.event_name" required placeholder="e.g. Panaghiusa 2026" class="w-full p-3 bg-slate-50 border rounded-xl text-sm focus:bg-white outline-none">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Date</label>
            <input type="date" name="date" x-model="form.date" required class="w-full p-3 bg-slate-50 border rounded-xl text-sm focus:bg-white outline-none">
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Semester</label>
            <select name="semester_id" x-model="form.semester_id" required class="w-full p-3 bg-slate-50 border rounded-xl text-sm focus:bg-white outline-none">
              <?php foreach ($semesters as $sem): ?>
                <option value="<?= $sem['id'] ?>"><?= htmlspecialchars($sem['semester']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Host Department</label>
          <select name="departments_id" x-model="form.departments_id" required class="w-full p-3 bg-slate-50 border rounded-xl text-sm focus:bg-white outline-none">
            <option value="0">All Departments (School-Wide Event)</option>
            <?php foreach ($departments as $dept): ?>
              <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['Dept_name']) ?> (<?= htmlspecialchars($dept['dept_Acronym']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Time Windows -->
        <div class="p-4 bg-slate-50 border rounded-2xl space-y-3">
          <span class="text-xs font-black uppercase tracking-wider text-maroon-900 block">Attendance Time Windows</span>
          
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
              <label class="block text-[11px] font-bold text-slate-600 mb-1">Time-In Start</label>
              <input type="time" name="TimeIn_start" x-model="form.TimeIn_start" required class="w-full p-2 bg-white border rounded-lg text-xs">
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 mb-1">Time-In End</label>
              <input type="time" name="TimeIn_end" x-model="form.TimeIn_end" required class="w-full p-2 bg-white border rounded-lg text-xs">
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 mb-1">Time-Out Start</label>
              <input type="time" name="Time_out" x-model="form.Time_out" required class="w-full p-2 bg-white border rounded-lg text-xs">
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 mb-1">Time-Out End</label>
              <input type="time" name="Timeout_out" x-model="form.Timeout_out" required class="w-full p-2 bg-white border rounded-lg text-xs">
            </div>
          </div>
        </div>

        <div class="flex gap-2 justify-end pt-4 border-t border-slate-100">
          <button type="button" @click="showModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold bg-maroon-900 hover:bg-maroon-800 text-white shadow-md transition">
            <span x-text="isEdit ? 'Save Changes' : 'Create Event'"></span>
          </button>
        </div>

      </form>

    </div>
  </div>

</div>

<script>
function eventManager() {
  return {
    showModal: false,
    isEdit: false,
    form: {
      id: null,
      event_name: '',
      date: new Date().toISOString().split('T')[0],
      semester_id: '1',
      departments_id: '0',
      TimeIn_start: '07:00',
      TimeIn_end: '08:30',
      Time_out: '12:00',
      Timeout_out: '12:30',
    },

    openCreateModal() {
      this.isEdit = false;
      this.form = {
        id: null,
        event_name: '',
        date: new Date().toISOString().split('T')[0],
        semester_id: '1',
        departments_id: '0',
        TimeIn_start: '07:00',
        TimeIn_end: '08:30',
        Time_out: '12:00',
        Timeout_out: '12:30',
      };
      this.showModal = true;
    },

    openEditModal(ev) {
      this.isEdit = true;
      this.form = {
        id: ev.id,
        event_name: ev.event_name,
        date: ev.date,
        semester_id: ev.semester_id,
        departments_id: ev.departments_id,
        TimeIn_start: ev.TimeIn_start,
        TimeIn_end: ev.TimeIn_end,
        Time_out: ev.Time_out,
        Timeout_out: ev.Timeout_out,
      };
      this.showModal = true;
    }
  };
}
</script>
