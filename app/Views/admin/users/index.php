<?php
use App\Config\Config;
use App\Core\Auth;
use App\Core\Csrf;
?>
<div class="space-y-6" x-data="userManager()">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="text-xl font-black text-slate-900 tracking-tight">Department Accounts & Users</h2>
      <p class="text-xs text-slate-500 mt-0.5">Manage department staff credentials, roles, and account statuses</p>
    </div>

    <button @click="openCreateModal()" 
            type="button" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-maroon-900 hover:bg-maroon-800 text-white font-bold text-xs shadow-md transition active:scale-95">
      <i data-lucide="user-plus" class="w-4 h-4"></i>
      Create Department Account
    </button>
  </div>

  <!-- Users Table -->
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
            <th class="py-3.5 px-4">User Account</th>
            <th class="py-3.5 px-4">Assigned Department</th>
            <th class="py-3.5 px-4">Role</th>
            <th class="py-3.5 px-4">Contact Info</th>
            <th class="py-3.5 px-4">Last Active</th>
            <th class="py-3.5 px-4 text-center">Status</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($users as $u): ?>
            <tr class="hover:bg-slate-50/80 transition">
              <td class="py-3.5 px-4 font-bold text-slate-900">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-maroon-50 text-maroon-800 flex items-center justify-center font-bold text-xs ring-1 ring-maroon-200">
                    <?= strtoupper(substr($u['username'], 0, 2)) ?>
                  </div>
                  <div>
                    <span class="block text-slate-900 font-bold"><?= htmlspecialchars($u['username']) ?></span>
                    <?php if (!empty($u['first_name'])): ?>
                      <span class="text-[11px] text-slate-400"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td class="py-3.5 px-4">
                <span class="px-2.5 py-0.5 rounded-md font-bold text-[11px] bg-slate-100 text-slate-700">
                  <?= htmlspecialchars($u['department_acronym'] ?: ($u['departments_id'] == 0 ? 'OSAS / Admin' : 'Dept #' . $u['departments_id'])) ?>
                </span>
              </td>
              <td class="py-3.5 px-4">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider <?= $u['account_type'] === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' ?>">
                  <?= htmlspecialchars($u['account_type']) ?>
                </span>
              </td>
              <td class="py-3.5 px-4 text-slate-600 font-mono">
                <?= htmlspecialchars($u['email'] ?: 'No email') ?>
              </td>
              <td class="py-3.5 px-4 text-slate-400">
                <?= !empty($u['last_login']) ? date('M j, Y h:i A', strtotime($u['last_login'])) : 'Never' ?>
              </td>
              <td class="py-3.5 px-4 text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $u['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' ?>">
                  <?= ucfirst($u['status']) ?>
                </span>
              </td>
              <td class="py-3.5 px-4 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-1.5">
                  
                  <!-- Reset Password -->
                  <button @click="openResetModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>')" 
                          title="Reset Password"
                          class="p-2 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                    <i data-lucide="key" class="w-4 h-4"></i>
                  </button>

                  <!-- Edit -->
                  <button @click="openEditModal(<?= htmlspecialchars(json_encode($u)) ?>)" 
                          title="Edit User"
                          class="p-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                  </button>

                  <!-- Toggle Active/Deactivate -->
                  <?php if ($u['id'] !== Auth::id()): ?>
                  <form action="<?= Config::BASE_URL ?>/admin/users/toggle-status/<?= $u['id'] ?>" method="POST" class="inline">
                    <?= Csrf::field() ?>
                    <button type="submit" 
                            title="<?= $u['status'] === 'active' ? 'Deactivate Account' : 'Activate Account' ?>"
                            class="p-2 rounded-lg <?= $u['status'] === 'active' ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' ?> transition">
                      <i data-lucide="<?= $u['status'] === 'active' ? 'user-x' : 'user-check' ?>" class="w-4 h-4"></i>
                    </button>
                  </form>
                  <?php endif; ?>

                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Create / Edit User Modal -->
  <div x-show="showModal" x-cloak 
       class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
       x-transition>
    <div @click.away="showModal = false" class="w-full max-w-lg bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100">
      
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-black text-slate-900" x-text="isEdit ? 'Edit Department Account' : 'Create Department Account'"></h3>
        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>

      <form :action="isEdit ? '<?= Config::BASE_URL ?>/admin/users/update/' + form.id : '<?= Config::BASE_URL ?>/admin/users/store'" method="POST" class="space-y-4">
        <?= Csrf::field() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Username</label>
            <input type="text" name="username" x-model="form.username" :disabled="isEdit" required placeholder="e.g. Crim-Department" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white disabled:opacity-60">
          </div>

          <div x-show="!isEdit">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Password</label>
            <input type="password" name="password" :required="!isEdit" placeholder="••••••••" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Assigned Department</label>
            <select name="departments_id" x-model="form.departments_id" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
              <option value="0">None / OSAS Administrator</option>
              <?php foreach ($departments as $d): ?>
                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['dept_Acronym']) ?> - <?= htmlspecialchars($d['Dept_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Account Role</label>
            <select name="account_type" x-model="form.account_type" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
              <option value="user">Department Staff (User)</option>
              <option value="admin">Administrator (OSAS Admin)</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Email</label>
            <input type="email" name="email" x-model="form.email" placeholder="dept@gmail.com" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Phone</label>
            <input type="text" name="phone" x-model="form.phone" placeholder="09123456789" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
          </div>
        </div>

        <div class="flex gap-2 justify-end pt-4 border-t border-slate-100">
          <button type="button" @click="showModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Cancel</button>
          <button type="submit" class="px-6 py-2 rounded-xl text-xs font-bold bg-maroon-900 text-white hover:bg-maroon-800 shadow-md">
            <span x-text="isEdit ? 'Save Changes' : 'Create Account'"></span>
          </button>
        </div>
      </form>

    </div>
  </div>

  <!-- Direct Reset Password Modal -->
  <div x-show="showResetModal" x-cloak 
       class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
       x-transition>
    <div @click.away="showResetModal = false" class="w-full max-w-md bg-white rounded-2xl p-6 shadow-2xl border border-slate-100">
      <h4 class="text-base font-bold text-slate-900 mb-1">Reset Password</h4>
      <p class="text-xs text-slate-500 mb-4">Set a new password for <strong x-text="resetUsername"></strong>.</p>

      <form :action="'<?= Config::BASE_URL ?>/admin/users/reset-password/' + resetUserId" method="POST" class="space-y-4">
        <?= Csrf::field() ?>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">New Password</label>
          <input type="password" name="new_password" minlength="6" required placeholder="••••••••" class="w-full p-3 bg-slate-50 border rounded-xl text-sm outline-none focus:bg-white">
        </div>

        <div class="flex gap-2 justify-end pt-2">
          <button type="button" @click="showResetModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600">Cancel</button>
          <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white">Override Password</button>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
function userManager() {
  return {
    showModal: false,
    showResetModal: false,
    isEdit: false,
    resetUserId: null,
    resetUsername: '',
    form: { id: null, username: '', password: '', departments_id: '0', account_type: 'user', email: '', phone: '' },

    openCreateModal() {
      this.isEdit = false;
      this.form = { id: null, username: '', password: '', departments_id: '0', account_type: 'user', email: '', phone: '' };
      this.showModal = true;
    },

    openEditModal(u) {
      this.isEdit = true;
      this.form = {
        id: u.id,
        username: u.username,
        departments_id: u.departments_id,
        account_type: u.account_type,
        email: u.email || '',
        phone: u.phone || ''
      };
      this.showModal = true;
    },

    openResetModal(id, name) {
      this.resetUserId = id;
      this.resetUsername = name;
      this.showResetModal = true;
    }
  };
}
</script>
