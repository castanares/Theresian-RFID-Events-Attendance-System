<?php
use App\Config\Config;
use App\Core\Auth;
use App\Core\Csrf;
?>
<div class="space-y-6" x-data="departmentManager()">

  <!-- Action Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="text-xl font-black text-slate-900 tracking-tight">Academic Departments</h2>
      <p class="text-xs text-slate-500 mt-0.5">Manage college faculties, department acronyms, and heads of office</p>
    </div>

    <button @click="openCreateModal()" 
            type="button" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-maroon-900 hover:bg-maroon-800 text-white font-bold text-xs shadow-md transition active:scale-95">
      <i data-lucide="plus" class="w-4 h-4"></i>
      Add Department
    </button>
  </div>

  <!-- Departments Grid -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($departments as $dept): ?>
      <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
        <div>
          <div class="flex items-start justify-between gap-2 mb-3">
            <span class="px-3 py-1 rounded-lg font-mono font-black text-sm bg-maroon-50 text-maroon-800 border border-maroon-100">
              <?= htmlspecialchars($dept['dept_Acronym']) ?>
            </span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold <?= $dept['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' ?>">
              <?= ucfirst($dept['status']) ?>
            </span>
          </div>

          <h3 class="text-base font-bold text-slate-900 leading-snug">
            <?= htmlspecialchars($dept['Dept_name']) ?>
          </h3>

          <div class="mt-4 space-y-1.5 text-xs text-slate-500">
            <?php if (!empty($dept['department_head'])): ?>
              <p class="flex items-center gap-2">
                <i data-lucide="user-check" class="w-3.5 h-3.5 text-slate-400"></i>
                <span>Head: <strong class="text-slate-700"><?= htmlspecialchars($dept['department_head']) ?></strong></span>
              </p>
            <?php endif; ?>

            <?php if (!empty($dept['contact_email'])): ?>
              <p class="flex items-center gap-2">
                <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                <span><?= htmlspecialchars($dept['contact_email']) ?></span>
              </p>
            <?php endif; ?>
          </div>
        </div>

        <div class="pt-4 mt-6 border-t border-slate-100 flex items-center justify-end gap-2">
          <button @click="openEditModal(<?= htmlspecialchars(json_encode($dept)) ?>)" 
                  class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
            Edit
          </button>

          <?php if ($dept['status'] === 'active'): ?>
          <form action="<?= Config::BASE_URL ?>/departments/archive/<?= $dept['id'] ?>" method="POST" onsubmit="return confirm('Archive department?');" class="inline">
            <?= Csrf::field() ?>
            <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold transition">
              Archive
            </button>
          </form>
          <?php else: ?>
          <form action="<?= Config::BASE_URL ?>/departments/restore/<?= $dept['id'] ?>" method="POST" class="inline">
            <?= Csrf::field() ?>
            <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold transition">
              Restore
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Create / Edit Modal -->
  <div x-show="showModal" x-cloak 
       class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
       x-transition>
    <div @click.away="showModal = false" class="w-full max-w-lg bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100">
      
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-black text-slate-900" x-text="isEdit ? 'Edit Department' : 'Add Department'"></h3>
        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <form :action="isEdit ? '<?= Config::BASE_URL ?>/departments/update/' + form.id : '<?= Config::BASE_URL ?>/departments/store'" method="POST" class="space-y-4">
        <?= Csrf::field() ?>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Full Department Name</label>
          <input type="text" name="Dept_name" x-model="form.Dept_name" required placeholder="e.g. College of Business, Technology and Accountancy" class="w-full p-3 bg-slate-50 border rounded-xl text-sm outline-none focus:bg-white">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Acronym</label>
            <input type="text" name="dept_Acronym" x-model="form.dept_Acronym" required placeholder="e.g. CBTA" class="w-full p-3 bg-slate-50 border rounded-xl text-sm font-mono outline-none focus:bg-white">
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Department Head</label>
            <input type="text" name="department_head" x-model="form.department_head" placeholder="e.g. Dr. Jane Doe" class="w-full p-3 bg-slate-50 border rounded-xl text-sm outline-none focus:bg-white">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Contact Email</label>
            <input type="email" name="contact_email" x-model="form.contact_email" placeholder="dept@gmail.com" class="w-full p-3 bg-slate-50 border rounded-xl text-sm outline-none focus:bg-white">
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Contact Phone</label>
            <input type="text" name="contact_phone" x-model="form.contact_phone" placeholder="09123456789" class="w-full p-3 bg-slate-50 border rounded-xl text-sm outline-none focus:bg-white">
          </div>
        </div>

        <div class="flex gap-2 justify-end pt-4 border-t border-slate-100">
          <button type="button" @click="showModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600">Cancel</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold bg-maroon-900 text-white hover:bg-maroon-800 shadow-md">
            <span x-text="isEdit ? 'Save Changes' : 'Create Department'"></span>
          </button>
        </div>
      </form>

    </div>
  </div>

</div>

<script>
function departmentManager() {
  return {
    showModal: false,
    isEdit: false,
    form: { id: null, Dept_name: '', dept_Acronym: '', department_head: '', contact_email: '', contact_phone: '' },

    openCreateModal() {
      this.isEdit = false;
      this.form = { id: null, Dept_name: '', dept_Acronym: '', department_head: '', contact_email: '', contact_phone: '' };
      this.showModal = true;
    },

    openEditModal(d) {
      this.isEdit = true;
      this.form = {
        id: d.id,
        Dept_name: d.Dept_name,
        dept_Acronym: d.dept_Acronym,
        department_head: d.department_head || '',
        contact_email: d.contact_email || '',
        contact_phone: d.contact_phone || ''
      };
      this.showModal = true;
    }
  };
}
</script>
