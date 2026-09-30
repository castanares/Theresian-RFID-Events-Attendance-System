<?php
use App\Config\Config;
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'Sign In - STC RFID System') ?></title>
  <link rel="shortcut icon" href="https://college.stctandag.edu.ph/assets/img/logo.png?v=1001">
  
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            maroon: {
              50: '#fdf2f2',
              100: '#fde8e8',
              500: '#e02424',
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
  <script src="https://unpkg.com/lucide@latest"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
  <style>
    [x-cloak] { display: none !important; }
  </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-gradient-to-br from-slate-100 via-slate-50 to-maroon-50/30">

  <?= $content ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      lucide.createIcons();
    });
  </script>
</body>
</html>
