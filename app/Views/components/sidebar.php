<?php
use App\Config\Config;
use App\Core\Auth;

$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$base = Config::BASE_URL;
$route = '/' . trim(str_starts_with($currentUri, $base) ? substr($currentUri, strlen($base)) : $currentUri, '/');
if ($route === '//') $route = '/';
?>
<!-- Mobile Backdrop -->
<div x-show="sidebarOpen" x-cloak 
     @click="sidebarOpen = false" 
     class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden transition-opacity"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"></div>

<!-- Sidebar Container -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-50 w-72 bg-maroon-900 text-white flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto">
  
  <!-- Logo & Institutional Header -->
  <div class="p-6 border-b border-maroon-800/80 flex items-center gap-3">
    <img src="<?= Config::BASE_URL ?>/images/stctandag-logo.png" alt="STC Logo" class="w-11 h-11 object-contain drop-shadow">
    <div>
      <h1 class="font-bold text-base leading-tight tracking-wide">STC Tandag</h1>
      <p class="text-xs text-maroon-200 font-medium">RFID Attendance System</p>
    </div>
  </div>

  <!-- Role Badge -->
  <div class="px-6 py-3 bg-maroon-950/40 flex items-center justify-between text-xs">
    <span class="text-maroon-200">Logged in as:</span>
    <span class="px-2.5 py-0.5 rounded-full font-semibold uppercase tracking-wider text-[10px] <?= Auth::isAdmin() ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' ?>">
      <?= htmlspecialchars(Auth::role()) ?>
    </span>
  </div>

  <!-- Navigation Links -->
  <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
    
    <!-- Dashboard -->
    <a href="<?= Config::BASE_URL ?>/dashboard" 
       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition <?= $route === '/dashboard' || $route === '/' ? 'bg-white/15 text-white font-semibold shadow-inner' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
      Dashboard
    </a>

    <!-- Live Scanner -->
    <a href="<?= Config::BASE_URL ?>/scan" 
       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition <?= $route === '/scan' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-semibold' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="scan-line" class="w-5 h-5 text-emerald-400 animate-pulse"></i>
      <span>Live RFID Scanner</span>
    </a>

    <!-- Events -->
    <a href="<?= Config::BASE_URL ?>/events" 
       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition <?= str_starts_with($route, '/events') ? 'bg-white/15 text-white font-semibold shadow-inner' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="calendar" class="w-5 h-5"></i>
      Events Management
    </a>

    <!-- Students -->
    <a href="<?= Config::BASE_URL ?>/students" 
       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition <?= str_starts_with($route, '/students') ? 'bg-white/15 text-white font-semibold shadow-inner' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="users" class="w-5 h-5"></i>
      Students Directory
    </a>

    <!-- Reports -->
    <a href="<?= Config::BASE_URL ?>/reports" 
       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition <?= str_starts_with($route, '/reports') ? 'bg-white/15 text-white font-semibold shadow-inner' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="file-bar-chart" class="w-5 h-5"></i>
      Reports & Absentees
    </a>

    <!-- Admin Only: Departments -->
    <?php if (Auth::isAdmin()): ?>
    <div class="pt-4 pb-1 px-3 text-[11px] font-bold uppercase tracking-wider text-maroon-300/80">
      Administration
    </div>
    <a href="<?= Config::BASE_URL ?>/departments" 
       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition <?= str_starts_with($route, '/departments') ? 'bg-white/15 text-white font-semibold shadow-inner' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="building-2" class="w-5 h-5"></i>
      Departments
    </a>
    <?php endif; ?>

  </nav>

  <!-- User Footer Profile -->
  <div class="p-4 border-t border-maroon-800/80 bg-maroon-950/30">
    <div class="flex items-center justify-between">
      <a href="<?= Config::BASE_URL ?>/profile" class="flex items-center gap-3 group min-w-0">
        <?php $avatar = Auth::user()['profile_pic'] ?? null; ?>
        <?php if ($avatar && file_exists(dirname(__DIR__, 2) . '/public/' . $avatar)): ?>
          <img src="<?= Config::BASE_URL . '/' . htmlspecialchars($avatar) ?>" alt="Avatar" class="w-9 h-9 rounded-full object-cover ring-2 ring-maroon-400">
        <?php else: ?>
          <div class="w-9 h-9 rounded-full bg-maroon-700 text-maroon-100 flex items-center justify-center font-bold text-sm ring-2 ring-maroon-500/50">
            <?= strtoupper(substr(Auth::username(), 0, 1)) ?>
          </div>
        <?php endif; ?>
        <div class="min-w-0">
          <p class="text-sm font-medium text-white truncate group-hover:text-amber-200 transition">
            <?= htmlspecialchars(Auth::username()) ?>
          </p>
          <p class="text-xs text-maroon-300 truncate">View Profile</p>
        </div>
      </a>

      <!-- Logout -->
      <a href="<?= Config::BASE_URL ?>/logout" 
         title="Sign Out"
         class="p-2 text-maroon-300 hover:text-white hover:bg-maroon-800/60 rounded-lg transition">
        <i data-lucide="log-out" class="w-5 h-5"></i>
      </a>
    </div>
  </div>

</aside>
