<?php
use App\Config\Config;
use App\Core\Auth;
use App\Core\Csrf;
?>
<div class="max-w-4xl mx-auto space-y-6" x-data="profileHandler()">

  <!-- Profile Header Card -->
  <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center gap-6">
    
    <!-- Profile Photo Upload -->
    <div class="relative group">
      <div class="w-24 h-24 rounded-full overflow-hidden ring-4 ring-maroon-100 bg-slate-100 flex items-center justify-center">
        <?php if (!empty($user['profile_pic']) && file_exists(dirname(__DIR__, 2) . '/public/' . $user['profile_pic'])): ?>
          <img src="<?= Config::BASE_URL . '/' . htmlspecialchars($user['profile_pic']) ?>" alt="Avatar" class="w-full h-full object-cover">
        <?php else: ?>
          <span class="text-2xl font-black text-maroon-900"><?= strtoupper(substr($user['username'], 0, 2)) ?></span>
        <?php endif; ?>
      </div>

      <!-- Upload overlay -->
      <label class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 cursor-pointer transition">
        <i data-lucide="camera" class="w-6 h-6"></i>
        <input type="file" @change="uploadAvatar" accept="image/*" class="hidden">
      </label>
    </div>

    <!-- User Meta -->
    <div class="text-center sm:text-left min-w-0">
      <div class="flex items-center justify-center sm:justify-start gap-2">
        <h2 class="text-xl font-black text-slate-900 tracking-tight">
          <?= htmlspecialchars($user['first_name'] ? ($user['first_name'] . ' ' . $user['last_name']) : $user['username']) ?>
        </h2>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider <?= $user['account_type'] === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>">
          <?= htmlspecialchars($user['account_type']) ?>
        </span>
      </div>
      <p class="text-xs text-slate-500 font-mono mt-0.5">@<?= htmlspecialchars($user['username']) ?> • <?= htmlspecialchars($user['email'] ?: 'No email registered') ?></p>
      
      <p class="text-[11px] text-slate-400 mt-2">
        Last signed in: <?= !empty($user['last_login']) ? date('M j, Y h:i A', strtotime($user['last_login'])) : 'Never' ?>
      </p>
    </div>

  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- Profile Details Form -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
      <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
        <i data-lucide="user-cog" class="w-5 h-5 text-maroon-800"></i>
        Edit Personal Information
      </h3>

      <form action="<?= Config::BASE_URL ?>/profile/update" method="POST" class="space-y-4">
        <?= Csrf::field() ?>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">First Name</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
          </div>
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Last Name</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Email Address</label>
          <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="user@gmail.com" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Phone Number</label>
          <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="09123456789" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Notes / Description</label>
          <textarea name="description" rows="3" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white"><?= htmlspecialchars($user['description'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="w-full py-2.5 bg-maroon-900 hover:bg-maroon-800 text-white text-xs font-bold rounded-xl shadow-xs transition">
          Save Profile Changes
        </button>
      </form>
    </div>

    <!-- Change Password Form -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
      <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
        <i data-lucide="shield-check" class="w-5 h-5 text-emerald-600"></i>
        Change Password
      </h3>

      <form action="<?= Config::BASE_URL ?>/profile/change-password" method="POST" class="space-y-4">
        <?= Csrf::field() ?>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Current Password</label>
          <input type="password" name="current_password" required placeholder="••••••••" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">New Password</label>
          <input type="password" name="new_password" minlength="6" required placeholder="••••••••" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Confirm New Password</label>
          <input type="password" name="confirm_password" minlength="6" required placeholder="••••••••" class="w-full p-2.5 bg-slate-50 border rounded-xl text-xs outline-none focus:bg-white">
        </div>

        <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-black text-white text-xs font-bold rounded-xl shadow-xs transition">
          Update Security Password
        </button>
      </form>
    </div>

  </div>

</div>

<script>
function profileHandler() {
  return {
    async uploadAvatar(e) {
      const file = e.target.files[0];
      if (!file) return;

      const fd = new FormData();
      fd.append('profile_pic', file);

      try {
        const res = await fetch('<?= Config::BASE_URL ?>/profile/upload-photo', {
          method: 'POST',
          body: fd
        });
        const data = await res.json();
        if (data.success) {
          window.location.reload();
        } else {
          alert(data.error || 'Failed to upload image');
        }
      } catch (err) {
        alert('Error uploading avatar');
      }
    }
  };
}
</script>
