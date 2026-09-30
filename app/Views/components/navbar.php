<?php
use App\Config\Config;
use App\Core\Auth;
?>
<header class="sticky top-0 z-30 bg-white border-b border-slate-200/80 shadow-xs px-4 sm:px-6 py-3 flex items-center justify-between">
  
  <!-- Left Side: Mobile Hamburger & Page Title -->
  <div class="flex items-center gap-3">
    <!-- Hamburger Button (Mobile) -->
    <button @click="sidebarOpen = true" 
            type="button" 
            class="p-2 -ml-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg lg:hidden transition"
            aria-label="Open sidebar">
      <i data-lucide="menu" class="w-6 h-6"></i>
    </button>

    <div>
      <h2 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight leading-none">
        <?= htmlspecialchars($title ?? 'Dashboard') ?>
      </h2>
      <p class="text-xs text-slate-500 mt-0.5 hidden sm:block"><?= Config::INSTITUTION_NAME ?></p>
    </div>
  </div>

  <!-- Right Side: Clock & Fast Actions -->
  <div class="flex items-center gap-3">
    
    <!-- Live Clock -->
    <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100/80 text-slate-700 text-xs font-medium border border-slate-200"
         x-data="{ time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }) }"
         x-init="setInterval(() => time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }), 1000)">
      <i data-lucide="clock" class="w-3.5 h-3.5 text-maroon-700"></i>
      <span x-text="time"></span>
    </div>

    <!-- Live Scan Shortcut Button -->
    <a href="<?= Config::BASE_URL ?>/scan" 
       class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-maroon-900 text-white text-xs font-semibold hover:bg-maroon-800 shadow-xs transition active:scale-95">
      <i data-lucide="scan" class="w-3.5 h-3.5"></i>
      <span class="hidden sm:inline">Launch Scanner</span>
    </a>

  </div>

</header>
