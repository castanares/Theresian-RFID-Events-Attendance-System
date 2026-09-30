<?php
use App\Core\Session;

$success = Session::flash('success');
$error   = Session::flash('error');
$info    = Session::flash('info');
$warning = Session::flash('warning');
?>
<?php if ($success || $error || $info || $warning): ?>
<div class="px-4 sm:px-6 lg:px-8 pt-4">
  
  <?php if ($success): ?>
  <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium shadow-xs"
       x-data="{ show: true }" x-show="show" x-transition>
    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
    <span class="flex-1"><?= htmlspecialchars($success) ?></span>
    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
      <i data-lucide="x" class="w-4 h-4"></i>
    </button>
  </div>
  <?php endif; ?>

  <?php if ($error): ?>
  <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium shadow-xs"
       x-data="{ show: true }" x-show="show" x-transition>
    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0"></i>
    <span class="flex-1"><?= htmlspecialchars($error) ?></span>
    <button @click="show = false" class="text-red-500 hover:text-red-700">
      <i data-lucide="x" class="w-4 h-4"></i>
    </button>
  </div>
  <?php endif; ?>

  <?php if ($warning): ?>
  <div class="flex items-center gap-3 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm font-medium shadow-xs"
       x-data="{ show: true }" x-show="show" x-transition>
    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 flex-shrink-0"></i>
    <span class="flex-1"><?= htmlspecialchars($warning) ?></span>
    <button @click="show = false" class="text-amber-500 hover:text-amber-700">
      <i data-lucide="x" class="w-4 h-4"></i>
    </button>
  </div>
  <?php endif; ?>

  <?php if ($info): ?>
  <div class="flex items-center gap-3 p-4 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 text-sm font-medium shadow-xs"
       x-data="{ show: true }" x-show="show" x-transition>
    <i data-lucide="info" class="w-5 h-5 text-sky-600 flex-shrink-0"></i>
    <span class="flex-1"><?= htmlspecialchars($info) ?></span>
    <button @click="show = false" class="text-sky-500 hover:text-sky-700">
      <i data-lucide="x" class="w-4 h-4"></i>
    </button>
  </div>
  <?php endif; ?>

</div>
<?php endif; ?>
