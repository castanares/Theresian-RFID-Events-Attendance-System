<?php
use App\Config\Config;
use App\Core\Auth;
use App\Core\Session;
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? Config::APP_NAME) ?></title>
  <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001">
  
  <!-- Tailwind CSS Play CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            maroon: {
              50: '#fdf2f2',
              100: '#fde8e8',
              200: '#fbd5d5',
              300: '#f8b4b4',
              400: '#f98080',
              500: '#e02424',
              600: '#c81e1e',
              700: '#9b1c1c',
              800: '#771d1d',
              900: '#6b0f0f',
              950: '#450a0a',
            }
          }
        }
      }
    }
  </script>

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <!-- Alpine.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    [x-cloak] { display: none !important; }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #f1f5f9; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
  </style>
</head>
<body class="h-full flex overflow-hidden text-slate-800 font-sans" 
      x-data="{ 
        sidebarOpen: false,
        isCollapsed: localStorage.getItem('sidebar_collapsed') === 'true',
        toggleSidebar() {
          this.isCollapsed = !this.isCollapsed;
          localStorage.setItem('sidebar_collapsed', this.isCollapsed);
          this.$nextTick(() => lucide.createIcons());
        }
      }">

  <!-- Desktop & Mobile Collapsible Sidebar -->
  <?php require dirname(__DIR__) . '/components/sidebar.php'; ?>

  <!-- Main Content Area -->
  <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">
    
    <!-- Top Navbar with Collapse Button -->
    <?php require dirname(__DIR__) . '/components/navbar.php'; ?>

    <!-- Flash Alerts -->
    <?php require dirname(__DIR__) . '/components/alerts.php'; ?>

    <!-- Main View Content -->
    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
      <?= $content ?>
    </main>

  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      lucide.createIcons();
    });

    window.SoundBeeper = {
      ctx: null,
      init() {
        if (!this.ctx) {
          this.ctx = new (window.AudioContext || window.webkitAudioContext)();
        }
      },
      beepSuccess() {
        this.init();
        const osc = this.ctx.createOscillator();
        const gain = this.ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, this.ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(1760, this.ctx.currentTime + 0.15);
        gain.gain.setValueAtTime(0.3, this.ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, this.ctx.currentTime + 0.15);
        osc.connect(gain);
        gain.connect(this.ctx.destination);
        osc.start();
        osc.stop(this.ctx.currentTime + 0.15);
      },
      beepWarning() {
        this.init();
        const osc = this.ctx.createOscillator();
        const gain = this.ctx.createGain();
        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(330, this.ctx.currentTime);
        gain.gain.setValueAtTime(0.3, this.ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, this.ctx.currentTime + 0.3);
        osc.connect(gain);
        gain.connect(this.ctx.destination);
        osc.start();
        osc.stop(this.ctx.currentTime + 0.3);
      },
      beepError() {
        this.init();
        const osc = this.ctx.createOscillator();
        const gain = this.ctx.createGain();
        osc.type = 'square';
        osc.frequency.setValueAtTime(220, this.ctx.currentTime);
        gain.gain.setValueAtTime(0.4, this.ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, this.ctx.currentTime + 0.4);
        osc.connect(gain);
        gain.connect(this.ctx.destination);
        osc.start();
        osc.stop(this.ctx.currentTime + 0.4);
      }
    };
  </script>
</body>
</html>
