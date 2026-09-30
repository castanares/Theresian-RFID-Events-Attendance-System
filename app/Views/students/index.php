<?php
use App\Config\Config;
use App\Core\Auth;
use App\Core\Csrf;
?>
<div class="space-y-6" x-data="studentManager()">

  <!-- Action Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="text-xl font-black text-slate-900 tracking-tight">Student Directory</h2>
      <p class="text-xs text-slate-500 mt-0.5">Manage registered students, RFID card assignments, and enrollment status</p>
    </div>

    <button @click="openCreateModal()" 
            type="button" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-maroon-900 hover:bg-maroon-800 text-white font-bold text-xs shadow-md hover:shadow-lg transition active:scale-95">
      <i data-lucide="user-plus" class="w-4 h-4"></i>
      Register Student
    </button>
  </div>

  <!-- Search & Filter Toolbar -->
  <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    
    <!-- Status Tabs -->
    <div class="flex items-center gap-2">
      <a href="<?= Config::BASE_URL ?>/students?status=active" 
         class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= ($currentFilter['status'] ?? 'active') === 'active' ? 'bg-maroon-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
        Active Students
      </a>
      <a href="<?= Config::BASE_URL ?>/students?status=archived" 
         class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition <?= ($currentFilter['status'] ?? '') === 'archived' ? 'bg-maroon-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
        Archived
      </a>
    </div>

    <!-- Search Form -->
    <form action="<?= Config::BASE_URL ?>/students" method="GET" class="flex items-center gap-2 max-w-md w-full">
      <input type="hidden" name="status" value="<?= htmlspecialchars($currentFilter['status'] ?? 'active') ?>">
      <div class="relative flex-1">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
          <i data-lucide="search" class="w-4 h-4"></i>
        </div>
        <input type="text" 
               name="search" 
               value="<?= htmlspecialchars($search ?? '') ?>" 
               placeholder="Search by name, ID, or RFID..." 
               class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white outline-none focus:ring-2 focus:ring-maroon-700/20">
      </div>
      <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-xs font-bold rounded-xl hover:bg-slate-900 transition">
        Search
      </button>
    </form>

  </div>

  <!-- Students Table -->
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
            <th class="py-3.5 px-4">Student ID</th>
            <th class="py-3.5 px-4">Full Name</th>
            <th class="py-3.5 px-4">RFID UID</th>
            <th class="py-3.5 px-4">Course</th>
            <th class="py-3.5 px-4">Department</th>
            <th class="py-3.5 px-4 text-center">Status</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($students)): ?>
            <tr>
              <td colspan="7" class="py-12 text-center text-slate-400">
                <i data-lucide="users" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                No students found.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($students as $s): ?>
              <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3.5 px-4 font-mono font-bold text-slate-800"><?= htmlspecialchars($s['ID_Number']) ?></td>
                <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($s['Student_name']) ?></td>
                <td class="py-3.5 px-4 font-mono text-emerald-700 font-semibold"><?= htmlspecialchars($s['RFID']) ?></td>
                <td class="py-3.5 px-4">
                  <span class="px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold"><?= htmlspecialchars($s['Course']) ?></span>
                </td>
                <td class="py-3.5 px-4 text-slate-600 font-medium"><?= htmlspecialchars($s['department_name'] ?? $s['departments_id']) ?></td>
                <td class="py-3.5 px-4 text-center">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $s['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' ?>">
                    <?= ucfirst($s['status']) ?>
                  </span>
                </td>
                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    
                    <?php if ($s['status'] === 'active'): ?>
                    <button @click="openEditModal(<?= htmlspecialchars(json_encode($s)) ?>)" 
                            title="Edit Student"
                            type="button" 
                            class="p-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                      <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>

                    <button @click="openArchiveModal(<?= $s['id'] ?>, '<?= htmlspecialchars($s['Student_name']) ?>')" 
                            title="Archive Student"
                            type="button" 
                            class="p-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">
                      <i data-lucide="user-x" class="w-4 h-4"></i>
                    </button>
                    <?php else: ?>
                    <form action="<?= Config::BASE_URL ?>/students/restore/<?= $s['id'] ?>" method="POST" class="inline">
                      <?= Csrf::field() ?>
                      <button type="submit" title="Restore Student" class="p-2 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
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

  <!-- Register / Edit Student Modal -->
  <div x-show="showModal" x-cloak 
       class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
       x-transition>
    <div @click.away="showModal = false" class="w-full max-w-lg bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100">
      
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-black text-slate-900 tracking-tight" x-text="isEdit ? 'Edit Student Record' : 'Register Student'"></h3>
          <p class="text-xs text-slate-500 mt-0.5">Enter student academic details and RFID tag number</p>
        </div>
        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <form :action="isEdit ? '<?= Config::BASE_URL ?>/students/update/' + form.id : '<?= Config::BASE_URL ?>/students/store'" method="POST" class="space-y-4">
        <?= Csrf::field() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Student ID Number</label>
            <input type="text" name="ID_Number" x-model="form.ID_Number" required placeholder="e.g. 4226347" class="w-full p-3 bg-slate-50 border rounded-xl text-sm font-mono focus:bg-white outline-none">
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">RFID UID Code</label>
            <input type="text" name="RFID" x-model="form.RFID" required placeholder="Tap or type RFID..." class="w-full p-3 bg-slate-50 border rounded-xl text-sm font-mono focus:bg-white outline-none">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Student Full Name</label>
          <input type="text" name="Student_name" x-model="form.Student_name" required placeholder="Lastname, Firstname M." class="w-full p-3 bg-slate-50 border rounded-xl text-sm focus:bg-white outline-none">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Course / Program</label>
            <input type="text" name="Course" x-model="form.Course" required placeholder="e.g. BSIT" class="w-full p-3 bg-slate-50 border rounded-xl text-sm focus:bg-white outline-none">
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Department</label>
            <select name="departments_id" x-model="form.departments_id" required class="w-full p-3 bg-slate-50 border rounded-xl text-sm focus:bg-white outline-none">
              <?php foreach ($departments as $dept): ?>
                <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['dept_Acronym']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="flex gap-2 justify-end pt-4 border-t border-slate-100">
          <button type="button" @click="showModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold bg-maroon-900 hover:bg-maroon-800 text-white shadow-md transition">
            <span x-text="isEdit ? 'Save Changes' : 'Register Student'"></span>
          </button>
        </div>

      </form>

    </div>
  </div>

  <!-- Archive Student Modal -->
  <div x-show="showArchiveModal" x-cloak 
       class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
       x-transition>
    <div @click.away="showArchiveModal = false" class="w-full max-w-md bg-white rounded-2xl p-6 shadow-2xl border border-slate-100">
      <h4 class="text-base font-bold text-slate-900 mb-2">Archive Student Record</h4>
      <p class="text-xs text-slate-500 mb-4">Select the reason for archiving <strong x-text="archiveStudentName"></strong>.</p>

      <form :action="'<?= Config::BASE_URL ?>/students/archive/' + archiveStudentId" method="POST" class="space-y-4">
        <?= Csrf::field() ?>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Reason / Cause</label>
          <select name="cause" class="w-full p-2.5 bg-slate-50 border rounded-lg text-xs">
            <option value="Graduated">Graduated</option>
            <option value="Transferred">Transferred</option>
            <option value="Discontinued">Discontinued</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="flex gap-2 justify-end pt-2">
          <button type="button" @click="showArchiveModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Cancel</button>
          <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-red-600 hover:bg-red-700 text-white">Archive Record</button>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
function studentManager() {
  return {
    showModal: false,
    showArchiveModal: false,
    isEdit: false,
    archiveStudentId: null,
    archiveStudentName: '',
    form: {
      id: null,
      ID_Number: '',
      RFID: '',
      Student_name: '',
      Course: '',
      departments_id: '1'
    },

    openCreateModal() {
      this.isEdit = false;
      this.form = { id: null, ID_Number: '', RFID: '', Student_name: '', Course: '', departments_id: '1' };
      this.showModal = true;
    },

    openEditModal(s) {
      this.isEdit = true;
      this.form = {
        id: s.id,
        ID_Number: s.ID_Number,
        RFID: s.RFID,
        Student_name: s.Student_name,
        Course: s.Course,
        departments_id: s.departments_id
      };
      this.showModal = true;
    },

    openArchiveModal(id, name) {
      this.archiveStudentId = id;
      this.archiveStudentName = name;
      this.showArchiveModal = true;
    }
  };
}
</script>
