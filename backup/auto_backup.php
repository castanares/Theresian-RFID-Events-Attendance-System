<?php
/**
 * auto_backup.php
 * ─────────────────────────────────────────────────────────────
 * Automatic weekly backup script.
 * Saves ZIP (or tar.gz fallback) directly to the /backup folder.
 *
 * Can be triggered in two ways:
 *  1. Via Windows Task Scheduler / Linux cron (CLI):
 *     php path\to\auto_backup.php
 *
 *  2. Via HTTP hit from a cron-like service (secured by secret token):
 *     GET /Theresian-RFID-Events-Attendance-System/backup/auto_backup.php?token=YOUR_SECRET
 *
 * Keeps only the last 4 weekly backups automatically (≈ 1 month of history).
 * ─────────────────────────────────────────────────────────────
 */

define('AUTO_BACKUP_SECRET', 'stc_autobackup_2024_secret');   // ← change this
define('MAX_BACKUPS_TO_KEEP', 4);                              // keep 4 weekly backups

// ── Security ────────────────────────────────────────────────
$isCli = (php_sapi_name() === 'cli');
if (!$isCli) {
    // HTTP access: require secret token
    $token = $_GET['token'] ?? ($_POST['token'] ?? '');
    if (!hash_equals(AUTO_BACKUP_SECRET, $token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Forbidden']);
        exit;
    }
}

// ── Paths ────────────────────────────────────────────────────
$rootDir   = dirname(__DIR__);                                 // project root
$backupDir = __DIR__;                                          // /backup folder (this file lives here)
$logFile   = $backupDir . '/auto_backup.log';
$timestamp = date('Ymd_His');
$zipName   = "auto_backup_{$timestamp}.zip";
$tempZip   = $backupDir . DIRECTORY_SEPARATOR . $zipName;

// ── Logging helper ────────────────────────────────────────────
function logMsg(string $msg): void {
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    if (php_sapi_name() === 'cli') echo $line;
}

logMsg("=== Auto backup started ===");

// ── Ensure backup dir exists and is writable ─────────────────
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}
if (!is_writable($backupDir)) {
    logMsg("ERROR: backup directory is not writable: {$backupDir}");
    exit(1);
}

// ── tar.gz fallback helper ────────────────────────────────────
function createTarGz(string $outputPath, array $filesToAdd): bool {
    $tar = '';
    foreach ($filesToAdd as $realPath => $arcPath) {
        if (!file_exists($realPath)) continue;
        $contents = file_get_contents($realPath);
        if ($contents === false) continue;
        $fileSize = strlen($contents);
        $header   = str_pad(substr($arcPath, 0, 100), 100, "\0");
        $header  .= str_pad('0000644', 8, ' ', STR_PAD_LEFT);
        $header  .= str_pad('0000000', 8, ' ', STR_PAD_LEFT);
        $header  .= str_pad('0000000', 8, ' ', STR_PAD_LEFT);
        $header  .= str_pad(decoct($fileSize), 12, '0', STR_PAD_LEFT);
        $header  .= str_pad(decoct(time()), 12, '0', STR_PAD_LEFT);
        $header  .= '        ';   // checksum placeholder
        $header  .= '0';
        $header  .= str_pad('', 100, "\0");
        $header  .= "ustar\0" . '00';
        $header  .= str_pad('', 32, "\0") . str_pad('', 32, "\0");
        $header  .= str_pad('0000000', 8, ' ', STR_PAD_LEFT);
        $header  .= str_pad('0000000', 8, ' ', STR_PAD_LEFT);
        $header  .= str_pad('', 155, "\0");
        $header   = str_pad($header, 512, "\0");
        $checksum = 0;
        for ($i = 0; $i < 512; $i++) $checksum += ord($header[$i]);
        $header   = substr_replace($header, str_pad(decoct($checksum), 6, '0', STR_PAD_LEFT) . "\0 ", 148, 8);
        $tar     .= $header;
        $tar     .= $contents . str_repeat("\0", (512 - ($fileSize % 512)) % 512);
    }
    $tar .= str_repeat("\0", 1024);
    $compressed = gzencode($tar, 6);
    if ($compressed === false) return false;
    return file_put_contents($outputPath, $compressed) !== false;
}

// ── Choose archive format ─────────────────────────────────────
$usesTarGz = !class_exists('ZipArchive');
if ($usesTarGz) {
    $tempZip = $backupDir . DIRECTORY_SEPARATOR . "auto_backup_{$timestamp}.tar.gz";
    logMsg("ZipArchive not available, using tar.gz format.");
} else {
    $zip = new ZipArchive();
    if ($zip->open($tempZip, ZipArchive::CREATE) !== true) {
        logMsg("ERROR: Cannot create zip at: {$tempZip}");
        exit(1);
    }
}

$addedFiles = [];

// ── 1. Database dump (SQL) ────────────────────────────────────
$sqlFile = $rootDir . '/stc_event_db.sql';
if (file_exists($sqlFile)) {
    if ($usesTarGz) $addedFiles[$sqlFile] = 'stc_event_db.sql';
    else $zip->addFile($sqlFile, 'stc_event_db.sql');
    logMsg("Added SQL dump.");
} else {
    // Try mysqli dump
    $dbCfg = $rootDir . '/api/dbcon.php';
    if (file_exists($dbCfg)) {
        // Extract credentials without executing the full script
        $dbcontent = file_get_contents($dbCfg);
        preg_match('/\$username\s*=\s*["\']([^"\']+)/', $dbcontent, $mU);
        preg_match('/\$password\s*=\s*["\']([^"\'"]*)/', $dbcontent, $mP);
        preg_match('/\$host\s*=\s*["\']([^"\']+)/', $dbcontent, $mH);
        preg_match('/\$dbname\s*=\s*["\']([^"\']+)/', $dbcontent, $mD);
        $dbUser = $mU[1] ?? 'root';
        $dbPass = $mP[1] ?? '';
        $dbHost = $mH[1] ?? 'localhost';
        $dbName = $mD[1] ?? 'stc_event_db';

        // Build SQL dump using mysqli
        try {
            $pdo = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
            if (!$pdo->connect_error) {
                $sql  = "-- Auto backup dump: " . date('Y-m-d H:i:s') . "\n";
                $sql .= "-- Database: {$dbName}\n\n";
                $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
                $tablesRes = $pdo->query("SHOW TABLES");
                while ($row = $tablesRes->fetch_row()) {
                    $tbl = $row[0];
                    // Create table DDL
                    $createRes = $pdo->query("SHOW CREATE TABLE `{$tbl}`");
                    $createRow = $createRes->fetch_row();
                    $sql .= "DROP TABLE IF EXISTS `{$tbl}`;\n";
                    $sql .= $createRow[1] . ";\n\n";
                    // Data
                    $dataRes = $pdo->query("SELECT * FROM `{$tbl}`");
                    while ($dataRow = $dataRes->fetch_row()) {
                        $escaped = array_map(function($v) use ($pdo) {
                            return $v === null ? 'NULL' : "'" . $pdo->real_escape_string($v) . "'";
                        }, $dataRow);
                        $sql .= "INSERT INTO `{$tbl}` VALUES (" . implode(', ', $escaped) . ");\n";
                    }
                    $sql .= "\n";
                }
                $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
                $pdo->close();

                $dumpFile = $backupDir . "/db_dump_{$timestamp}.sql";
                file_put_contents($dumpFile, $sql);
                if ($usesTarGz) $addedFiles[$dumpFile] = "db_dump_{$timestamp}.sql";
                else $zip->addFile($dumpFile, "db_dump_{$timestamp}.sql");
                logMsg("Generated DB dump via mysqli: {$dbName}");
            }
        } catch (Exception $e) {
            logMsg("WARNING: Could not dump database: " . $e->getMessage());
        }
    }
}

// ── 2. Config / API files ─────────────────────────────────────
$configFiles = ['auth.php', '.htaccess', 'index.php'];
foreach ($configFiles as $f) {
    $p = $rootDir . '/' . $f;
    if (!file_exists($p)) continue;
    if ($usesTarGz) $addedFiles[$p] = 'files/' . $f;
    else $zip->addFile($p, 'files/' . $f);
}
logMsg("Added config files.");

$apiDir = $rootDir . '/api';
if (is_dir($apiDir)) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($apiDir));
    foreach ($it as $file) {
        if (!$file->isFile()) continue;
        $local = 'api/' . substr($file->getPathname(), strlen(realpath($apiDir)) + 1);
        if ($usesTarGz) $addedFiles[$file->getPathname()] = $local;
        else $zip->addFile($file->getPathname(), $local);
    }
    logMsg("Added API directory.");
}

// ── 3. Images ─────────────────────────────────────────────────
$imagesDir = $rootDir . '/images';
if (is_dir($imagesDir)) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($imagesDir));
    foreach ($it as $file) {
        if (!$file->isFile()) continue;
        $local = 'images/' . substr($file->getPathname(), strlen(realpath($imagesDir)) + 1);
        if ($usesTarGz) $addedFiles[$file->getPathname()] = $local;
        else $zip->addFile($file->getPathname(), $local);
    }
    logMsg("Added images directory.");
}

// ── Finalize archive ──────────────────────────────────────────
if ($usesTarGz) {
    if (!createTarGz($tempZip, $addedFiles)) {
        logMsg("ERROR: Failed to create tar.gz archive.");
        exit(1);
    }
} else {
    $zip->close();
}

// ── Clean up temp SQL dump if we generated one ────────────────
if (isset($dumpFile) && file_exists($dumpFile)) {
    @unlink($dumpFile);
}

// ── Verify result ─────────────────────────────────────────────
if (!file_exists($tempZip) || filesize($tempZip) === 0) {
    logMsg("ERROR: Archive not created or empty.");
    exit(1);
}

$sizeMb = round(filesize($tempZip) / 1048576, 2);
logMsg("Backup saved: " . basename($tempZip) . " ({$sizeMb} MB)");

// ── Prune old backups — keep only MAX_BACKUPS_TO_KEEP ─────────
$pattern = $backupDir . '/auto_backup_*.{zip,tar.gz}';
$existing = [];
foreach (glob($backupDir . '/auto_backup_*.zip') ?: [] as $f)    $existing[$f] = filemtime($f);
foreach (glob($backupDir . '/auto_backup_*.tar.gz') ?: [] as $f) $existing[$f] = filemtime($f);
arsort($existing);  // newest first

$kept    = 0;
$deleted = 0;
foreach ($existing as $file => $mtime) {
    if (++$kept > MAX_BACKUPS_TO_KEEP) {
        @unlink($file);
        logMsg("Pruned old backup: " . basename($file));
        $deleted++;
    }
}
if ($deleted) logMsg("Pruned {$deleted} old backup(s), keeping last " . MAX_BACKUPS_TO_KEEP . ".");

logMsg("=== Auto backup finished successfully ===\n");

// HTTP response
if (!$isCli) {
    header('Content-Type: application/json');
    echo json_encode([
        'success'  => true,
        'file'     => basename($tempZip),
        'size_mb'  => $sizeMb,
        'kept'     => min($kept, MAX_BACKUPS_TO_KEEP),
        'pruned'   => $deleted,
    ]);
}
exit(0);
