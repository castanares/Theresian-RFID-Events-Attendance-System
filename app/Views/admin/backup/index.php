<?php
use App\Config\Config;
use App\Core\Csrf;
?>
<div class="space-y-6">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h2 class="text-xl font-black text-slate-900 tracking-tight">Database Backup & Maintenance</h2>
      <p class="text-xs text-slate-500 mt-0.5">Create, download, and manage system database snapshots safely</p>
    </div>

    <!-- Create Backup Action -->
    <form action="<?= Config::BASE_URL ?>/admin/backup/create" method="POST">
      <?= Csrf::field() ?>
      <button type="submit" 
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition active:scale-95">
        <i data-lucide="database-backup" class="w-4 h-4"></i>
        Create Instant DB Snapshot
      </button>
    </form>
  </div>

  <!-- Security Notice Card -->
  <div class="bg-blue-50 border border-blue-200/80 rounded-2xl p-5 text-xs text-blue-900 flex items-start gap-3.5">
    <i data-lucide="shield-check" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
    <div class="space-y-1">
      <h4 class="font-bold text-blue-950">Protected Storage Directory</h4>
      <p class="leading-relaxed">All backups are stored in the server's private <code>storage/backups/</code> directory with direct web browser access strictly blocked for total data protection.</p>
    </div>
  </div>

  <!-- Existing Backups List -->
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
      <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Available Backup Snapshots</h3>
      <span class="text-xs font-bold text-slate-400"><?= count($files) ?> Files</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 uppercase tracking-wider font-semibold">
            <th class="py-3.5 px-4">Filename</th>
            <th class="py-3.5 px-4">Size</th>
            <th class="py-3.5 px-4">Date Generated</th>
            <th class="py-3.5 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($files)): ?>
            <tr><td colspan="4" class="py-12 text-center text-slate-400">No backup files created yet. Click above to generate one.</td></tr>
          <?php else: ?>
            <?php foreach ($files as $f): ?>
              <tr class="hover:bg-slate-50/80 transition">
                <td class="py-3.5 px-4 font-mono font-bold text-slate-800 flex items-center gap-2">
                  <i data-lucide="file-archive" class="w-4 h-4 text-maroon-700"></i>
                  <?= htmlspecialchars($f['name']) ?>
                </td>
                <td class="py-3.5 px-4 font-mono text-slate-600"><?= $f['size_mb'] ?> MB</td>
                <td class="py-3.5 px-4 text-slate-500"><?= $f['modified'] ?></td>
                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-2">
                    
                    <!-- Download -->
                    <a href="<?= Config::BASE_URL ?>/admin/backup/download/<?= urlencode($f['name']) ?>" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition">
                      <i data-lucide="download" class="w-3.5 h-3.5"></i>
                      Download
                    </a>

                    <!-- Delete -->
                    <form action="<?= Config::BASE_URL ?>/admin/backup/delete/<?= urlencode($f['name']) ?>" method="POST" onsubmit="return confirm('Delete this backup snapshot?');" class="inline">
                      <?= Csrf::field() ?>
                      <button type="submit" class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                      </button>
                    </form>

                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
