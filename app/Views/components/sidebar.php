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
<aside :class="{
          'translate-x-0': sidebarOpen,
          '-translate-x-full': !sidebarOpen,
          'lg:w-20': isCollapsed,
          'lg:w-72': !isCollapsed
       }"
       class="fixed inset-y-0 left-0 z-50 bg-maroon-900 text-white flex flex-col transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto select-none shadow-xl lg:shadow-none">
  
  <!-- Logo & Institutional Header -->
  <div class="p-4 sm:p-5 border-b border-maroon-800/80 flex items-center justify-between gap-3">
    <div class="flex items-center gap-3 overflow-hidden">
      <img src="<?= Config::BASE_URL ?>/images/stctandag-logo.png" alt="STC Logo" class="w-10 h-10 object-contain drop-shadow flex-shrink-0">
      <div x-show="!isCollapsed" x-transition.opacity.duration.200ms class="min-w-0">
        <h1 class="font-bold text-sm leading-tight tracking-wide truncate">STC Tandag</h1>
        <p class="text-[11px] text-maroon-200 font-medium truncate">RFID Attendance</p>
      </div>
    </div>

    <!-- Desktop Collapse Toggle Button -->
    <button @click="toggleSidebar()" 
            type="button" 
            title="Toggle Sidebar Collapse"
            class="hidden lg:flex p-1.5 rounded-lg text-maroon-300 hover:text-white hover:bg-white/10 transition">
      <i :data-lucide="isCollapsed ? 'chevron-right' : 'chevron-left'" class="w-4 h-4"></i>
    </button>
  </div>

  <!-- Role Badge -->
  <div x-show="!isCollapsed" x-transition.opacity.duration.200ms class="px-5 py-2.5 bg-maroon-950/40 flex items-center justify-between text-xs">
    <span class="text-maroon-200 text-[11px]">Logged in as:</span>
    <span class="px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider text-[10px] <?= Auth::isAdmin() ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' ?>">
      <?= htmlspecialchars(Auth::role()) ?>
    </span>
  </div>

  <!-- Navigation Links -->
  <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto overflow-x-hidden">
    
    <!-- 1. Dashboard -->
    <a href="<?= Config::BASE_URL ?>/dashboard" 
       :title="isCollapsed ? 'Dashboard' : ''"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition <?= $route === '/dashboard' || $route === '/' ? 'bg-white/15 text-white font-bold shadow-inner' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="layout-dashboard" class="w-5 h-5 flex-shrink-0"></i>
      <span x-show="!isCollapsed" x-transition.opacity.duration.200ms class="truncate">Dashboard</span>
    </a>

    <!-- 2. Live RFID Scanner -->
    <a href="<?= Config::BASE_URL ?>/scan" 
       :title="isCollapsed ? 'Live RFID Scanner' : ''"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition <?= $route === '/scan' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-bold' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="scan-line" class="w-5 h-5 flex-shrink-0 text-emerald-400 animate-pulse"></i>
      <span x-show="!isCollapsed" x-transition.opacity.duration.200ms class="truncate">Live Scanner</span>
    </a>

    <!-- 3. Events Management -->
    <a href="<?= Config::BASE_URL ?>/events" 
       :title="isCollapsed ? 'Events Management' : ''"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition <?= str_starts_with($route, '/events') ? 'bg-white/15 text-white font-bold shadow-inner' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="calendar" class="w-5 h-5 flex-shrink-0"></i>
      <span x-show="!isCollapsed" x-transition.opacity.duration.200ms class="truncate">Events Schedule</span>
    </a>

    <!-- 4. Students Directory -->
    <a href="<?= Config::BASE_URL ?>/students" 
       :title="isCollapsed ? 'Students Directory' : ''"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition <?= str_starts_with($route, '/students') ? 'bg-white/15 text-white font-bold shadow-inner' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="users" class="w-5 h-5 flex-shrink-0"></i>
      <span x-show="!isCollapsed" x-transition.opacity.duration.200ms class="truncate">Student Directory</span>
    </a>

    <!-- 5. Reports & Absentees -->
    <a href="<?= Config::BASE_URL ?>/reports" 
       :title="isCollapsed ? 'Reports & Absentees' : ''"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition <?= str_starts_with($route, '/reports') ? 'bg-white/15 text-white font-bold shadow-inner' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="file-bar-chart" class="w-5 h-5 flex-shrink-0"></i>
      <span x-show="!isCollapsed" x-transition.opacity.duration.200ms class="truncate">Reports & Logs</span>
    </a>

    <!-- ══════════════════ ADMIN-ONLY SECTION ══════════════════ -->
    <?php if (Auth::isAdmin()): ?>
    <div x-show="!isCollapsed" x-transition.opacity.duration.200ms class="pt-4 pb-1 px-3 text-[10px] font-extrabold uppercase tracking-wider text-maroon-300/80">
      Administration
    </div>
    <div x-show="isCollapsed" class="pt-3 pb-1 border-t border-maroon-800/60 my-2"></div>

    <!-- User Accounts / Control Panel -->
    <a href="<?= Config::BASE_URL ?>/admin/users" 
       :title="isCollapsed ? 'Department Accounts' : ''"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition <?= str_starts_with($route, '/admin/users') ? 'bg-white/15 text-white font-bold shadow-inner' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="shield" class="w-5 h-5 flex-shrink-0 text-amber-300"></i>
      <span x-show="!isCollapsed" x-transition.opacity.duration.200ms class="truncate">User Accounts</span>
    </a>

    <!-- Academic Departments -->
    <a href="<?= Config::BASE_URL ?>/departments" 
       :title="isCollapsed ? 'Departments' : ''"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition <?= str_starts_with($route, '/departments') ? 'bg-white/15 text-white font-bold shadow-inner' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="building-2" class="w-5 h-5 flex-shrink-0"></i>
      <span x-show="!isCollapsed" x-transition.opacity.duration.200ms class="truncate">Departments</span>
    </a>

    <!-- System Archive Center -->
    <a href="<?= Config::BASE_URL ?>/admin/archived" 
       :title="isCollapsed ? 'Archive Center' : ''"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition <?= str_starts_with($route, '/admin/archived') ? 'bg-white/15 text-white font-bold shadow-inner' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="archive" class="w-5 h-5 flex-shrink-0"></i>
      <span x-show="!isCollapsed" x-transition.opacity.duration.200ms class="truncate">Archive Center</span>
    </a>

    <!-- Semesters & Academic Calendar -->
    <a href="<?= Config::BASE_URL ?>/admin/semesters" 
       :title="isCollapsed ? 'Semesters & Terms' : ''"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition <?= str_starts_with($route, '/admin/semesters') ? 'bg-white/15 text-white font-bold shadow-inner' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="calendar-range" class="w-5 h-5 flex-shrink-0"></i>
      <span x-show="!isCollapsed" x-transition.opacity.duration.200ms class="truncate">Semesters & Terms</span>
    </a>

    <!-- Backup & System Maintenance -->
    <a href="<?= Config::BASE_URL ?>/admin/backup" 
       :title="isCollapsed ? 'Database Backups' : ''"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition <?= str_starts_with($route, '/admin/backup') ? 'bg-white/15 text-white font-bold shadow-inner' : 'text-maroon-100 hover:bg-white/10 hover:text-white' ?>">
      <i data-lucide="database-backup" class="w-5 h-5 flex-shrink-0"></i>
      <span x-show="!isCollapsed" x-transition.opacity.duration.200ms class="truncate">Database Backup</span>
    </a>
    <?php endif; ?>

  </nav>

  <!-- User Footer Profile -->
  <div class="p-3 border-t border-maroon-800/80 bg-maroon-950/30">
    <div class="flex items-center justify-between">
      <a href="<?= Config::BASE_URL ?>/profile" class="flex items-center gap-3 group min-w-0" :title="isCollapsed ? 'View Profile' : ''">
        <?php $avatar = Auth::user()['profile_pic'] ?? null; ?>
        <?php if ($avatar && file_exists(dirname(__DIR__, 2) . '/public/' . $avatar)): ?>
          <img src="<?= Config::BASE_URL . '/' . htmlspecialchars($avatar) ?>" alt="Avatar" class="w-9 h-9 rounded-full object-cover ring-2 ring-maroon-400 flex-shrink-0">
        <?php else: ?>
          <div class="w-9 h-9 rounded-full bg-maroon-700 text-maroon-100 flex items-center justify-center font-bold text-xs ring-2 ring-maroon-500/50 flex-shrink-0">
            <?= strtoupper(substr(Auth::username(), 0, 1)) ?>
          </div>
        <?php endif; ?>
        <div x-show="!isCollapsed" x-transition.opacity.duration.200ms class="min-w-0">
          <p class="text-xs font-bold text-white truncate group-hover:text-amber-200 transition">
            <?= htmlspecialchars(Auth::username()) ?>
          </p>
          <p class="text-[11px] text-maroon-300 truncate">Account Settings</p>
        </div>
      </a>

      <!-- Logout -->
      <a href="<?= Config::BASE_URL ?>/logout" 
         title="Sign Out"
         class="p-2 text-maroon-300 hover:text-white hover:bg-maroon-800/60 rounded-lg transition flex-shrink-0">
        <i data-lucide="log-out" class="w-4 h-4"></i>
      </a>
    </div>
  </div>

</aside>
