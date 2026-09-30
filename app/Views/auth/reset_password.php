<?php
use App\Config\Config;
use App\Core\Csrf;
?>
<div class="w-full max-w-md bg-white rounded-3xl p-8 sm:p-10 shadow-xl border border-slate-200/80">
  
  <div class="text-center mb-8">
    <div class="w-14 h-14 mx-auto mb-4 bg-maroon-50 text-maroon-800 rounded-2xl flex items-center justify-center ring-8 ring-maroon-50/50">
      <i data-lucide="key-round" class="w-7 h-7"></i>
    </div>
    <h3 class="text-2xl font-bold text-slate-900 tracking-tight">Create New Password</h3>
    <p class="text-xs text-slate-500 mt-1.5">Please choose a strong password with at least 6 characters.</p>
  </div>

  <form action="<?= Config::BASE_URL ?>/reset-password" method="POST" class="space-y-4">
    <?= Csrf::field() ?>

    <div>
      <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">New Password</label>
      <input type="password" name="password" minlength="6" required placeholder="••••••••" 
             class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-maroon-700/30 focus:border-maroon-700 outline-none">
    </div>

    <div>
      <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Confirm Password</label>
      <input type="password" name="confirm_password" minlength="6" required placeholder="••••••••" 
             class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-maroon-700/30 focus:border-maroon-700 outline-none">
    </div>

    <button type="submit" class="w-full py-3 bg-maroon-900 hover:bg-maroon-800 text-white font-semibold rounded-xl text-sm shadow-md transition mt-2">
      Update Password & Log In
    </button>
  </form>

  <div class="mt-6 text-center">
    <a href="<?= Config::BASE_URL ?>/login" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition flex items-center justify-center gap-1">
      <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
      Back to Sign In
    </a>
  </div>

</div>
