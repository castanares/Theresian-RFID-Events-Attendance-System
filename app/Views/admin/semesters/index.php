<?php
use App\Config\Config;
use App\Core\Csrf;
?>
<div class="space-y-6" x-data="semesterManager()">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="text-xl font-black text-slate-900 tracking-tight">Academic Semesters & Terms</h2>
      <p class="text-xs text-slate-500 mt-0.5">Configure academic terms, semester date ranges, and school year cycles</p>
    </div>

    <button @click="openCreateModal()" 
            type="button" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-maroon-900 hover:bg-maroon-800 text-white font-bold text-xs shadow-md transition active:scale-95">
      <i data-lucide="plus" class="w-4 h-4"></i>
      Add Term / Semester
    </button>
  </div>

  <!-- Current Active Semester Card -->
  <?php if ($current): ?>
  <div class="bg-gradient-to-r from-purple-900 to-indigo-950 rounded-2xl p-6 text-white shadow-sm flex items-center justify-between">
    <div>
      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-white/20 text-purple-200 border border-white/20 mb-2">
        Active Semester (Resolved by Date)
      </span>
      <h3 class="text-2xl font-black tracking-tight"><?= htmlspecialchars($current['semester']) ?></h3>
      <p class="text-xs text-purple-200 mt-1">
        Date Range: <?= date('M j', strtotime($current['start'])) ?> - <?= date('M j', strtotime($current['end'])) ?>
      </p>
    </div>
    <div class="hidden sm:block">
      <i data-lucide="graduation-cap" class="w-12 h-12 text-purple-300/40"></i>
    </div>
  </div>
  <?php endif; ?>

  <!-- Semesters Table -->
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
            <th class="py-3.5 px-4">Semester / Term</th>
            <th class="py-3.5 px-4">Start Month/Day</th>
            <th class="py-3.5 px-4">End Month/Day</th>
            <th class="py-3.5 px-4 text-center">Status</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($semesters as $sem): ?>
            <tr class="hover:bg-slate-50/80 transition">
              <td class="py-3.5 px-4 font-bold text-slate-900"><?= htmlspecialchars($sem['semester']) ?></td>
              <td class="py-3.5 px-4 font-mono text-slate-700"><?= date('F j', strtotime($sem['start'])) ?></td>
              <td class="py-3.5 px-4 font-mono text-slate-700"><?= date('F j', strtotime($sem['end'])) ?></td>
              <td class="py-3.5 px-4 text-center">
                <?php if ($current && $current['id'] == $sem['id']): ?>
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">Active Now</span>
                <?php else: ?>
                  <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">Off-Season</span>
                <?php endif; ?>
              </td>
              <td class="py-3.5 px-4 text-right whitespace-nowrap">
                <button @click="openEditModal(<?= htmlspecialchars(json_encode($sem)) ?>)" 
                        class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition">
                  Edit Range
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Create / Edit Modal -->
  <div x-show="showModal" x-cloak 
       class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
       x-transition>
    <div @click.away="showModal = false" class="w-full max-w-md bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100">
      
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-black text-slate-900" x-text="isEdit ? 'Edit Semester Range' : 'Add Semester'"></h3>
        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <form :action="isEdit ? '<?= Config::BASE_URL ?>/admin/semesters/update/' + form.id : '<?= Config::BASE_URL ?>/admin/semesters/store'" method="POST" class="space-y-4">
        <?= Csrf::field() ?>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Semester Name</label>
          <input type="text" name="semester" x-model="form.semester" required placeholder="e.g. 1st Semester" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Start Date</label>
            <input type="date" name="start" x-model="form.start" required class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">End Date</label>
            <input type="date" name="end" x-model="form.end" required class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
          </div>
        </div>

        <div class="flex gap-2 justify-end pt-4 border-t border-slate-100">
          <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Cancel</button>
          <button type="submit" class="px-6 py-2 rounded-xl text-xs font-bold bg-maroon-900 text-white hover:bg-maroon-800 shadow-md">
            <span x-text="isEdit ? 'Save Range' : 'Create Semester'"></span>
          </button>
        </div>
      </form>

    </div>
  </div>

</div>

<script>
function semesterManager() {
  return {
    showModal: false,
    isEdit: false,
    form: { id: null, semester: '', start: '2000-08-01', end: '2000-12-31' },

    openCreateModal() {
      this.isEdit = false;
      this.form = { id: null, semester: '', start: '2000-08-01', end: '2000-12-31' };
      this.showModal = true;
    },

    openEditModal(s) {
      this.isEdit = true;
      this.form = { id: s.id, semester: s.semester, start: s.start, end: s.end };
      this.showModal = true;
    }
  };
}
</script>
