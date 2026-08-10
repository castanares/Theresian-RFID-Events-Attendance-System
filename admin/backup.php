<?php
require_once __DIR__ . '/../auth.php';
if (!is_admin()) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

// ensure no accidental output (clear buffers) and disable compression that may corrupt binary stream
@ini_set('zlib.output_compression', '0');
// clear any output buffers
while (ob_get_level()) ob_end_clean();

// Basic backup script: creates a ZIP and streams it. Does not require external libs beyond ZipArchive.
// Accepts POST fields: backupDB (1), backupFiles (1), backupImages (1)

$includeDB = isset($_POST['backupDB']) && ($_POST['backupDB'] === '1' || $_POST['backupDB'] === 'on' || $_POST['backupDB'] === 'true');
$includeFiles = isset($_POST['backupFiles']) && ($_POST['backupFiles'] === '1' || $_POST['backupFiles'] === 'on' || $_POST['backupFiles'] === 'true');
$includeImages = isset($_POST['backupImages']) && ($_POST['backupImages'] === '1' || $_POST['backupImages'] === 'on' || $_POST['backupImages'] === 'true');

$timestamp = date('Ymd_His');
$zipName = "stc_backup_{$timestamp}.zip";
$tempZip = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipName;

$debugPath = __DIR__ . '/backup_debug.log';

// Helper: create a valid tar.gz archive (fallback when ZipArchive unavailable)
function createTarGz($outputPath, array $filesToAdd) {
    // Create a proper POSIX.1-1988 (ustar) tar archive, then compress with gzip
    $tar = '';
    
    foreach ($filesToAdd as $realPath => $arcPath) {
        if (!file_exists($realPath)) continue;
        $contents = file_get_contents($realPath);
        if ($contents === false) continue;
        
        $fileSize = strlen($contents);
        
        // Build 512-byte tar header following POSIX.1-1988 ustar format
        // All numeric fields are octal strings, null-terminated or space-padded
        $header = '';
        $header .= str_pad(substr($arcPath, 0, 100), 100, "\0");  // [0-99] name
        $header .= str_pad('0000644', 8, ' ', STR_PAD_LEFT);  // [100-107] mode (octal, space-padded)
        $header .= str_pad('0000000', 8, ' ', STR_PAD_LEFT);  // [108-115] uid (octal)
        $header .= str_pad('0000000', 8, ' ', STR_PAD_LEFT);  // [116-123] gid (octal)
        $header .= str_pad(decoct($fileSize), 12, '0', STR_PAD_LEFT);  // [124-135] size (octal, left-padded)
        $header .= str_pad(decoct(time()), 12, '0', STR_PAD_LEFT);  // [136-147] mtime (octal)
        $header .= '        ';  // [148-155] checksum placeholder (8 spaces)
        $header .= '0';  // [156] type flag ('0' = regular file)
        $header .= str_pad('', 100, "\0");  // [157-256] link name (unused)
        $header .= "ustar\0";  // [257-262] ustar magic
        $header .= '00';  // [263-264] ustar version
        $header .= str_pad('', 32, "\0");  // [265-296] uname (unused)
        $header .= str_pad('', 32, "\0");  // [297-328] gname (unused)
        $header .= str_pad('0000000', 8, ' ', STR_PAD_LEFT);  // [329-336] devmajor
        $header .= str_pad('0000000', 8, ' ', STR_PAD_LEFT);  // [337-344] devminor
        $header .= str_pad('', 155, "\0");  // [345-499] prefix (unused)
        
        // Ensure header is exactly 512 bytes
        $header = str_pad($header, 512, "\0");
        
        // Calculate checksum (sum of all bytes, treating checksum field as spaces)
        $checksum = 0;
        for ($i = 0; $i < 512; $i++) {
            $checksum += ord($header[$i]);
        }
        
        // Replace checksum field with octal value (6 digits + null + space)
        $checksumStr = str_pad(decoct($checksum), 6, '0', STR_PAD_LEFT) . "\0 ";
        $header = substr_replace($header, $checksumStr, 148, 8);
        
        // Add header and file content (padded to 512-byte boundary)
        $tar .= $header;
        $paddedContent = $contents . str_repeat("\0", (512 - ($fileSize % 512)) % 512);
        $tar .= $paddedContent;
    }
    
    // End of tar: two 512-byte zero blocks
    $tar .= str_repeat("\0", 1024);
    
    // Compress with gzip format (using gzencode for proper .gz format)
    $compressed = gzencode($tar, 9);
    if ($compressed === false) return false;
    
    return file_put_contents($outputPath, $compressed) !== false;
}

// Try ZipArchive first, fall back to tar.gz
$usesTarGz = false;
if (!class_exists('ZipArchive')) {
    // ZipArchive not available; use tar.gz fallback
    $usesTarGz = true;
    $info = [
        'time' => date('c'),
        'msg' => 'using_targz_fallback',
        'reason' => 'ziparchive_missing',
        'php_version' => phpversion(),
    ];
    @file_put_contents($debugPath, json_encode($info, JSON_PRETTY_PRINT) . "\n", FILE_APPEND | LOCK_EX);
    $tempZip = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "stc_backup_{$timestamp}.tar.gz";
} else {
    // Use ZipArchive
    $zip = new ZipArchive();
    if ($zip->open($tempZip, ZipArchive::CREATE) !== TRUE) {
        http_response_code(500);
        echo "Unable to create zip file";
        exit;
    }
}

$addedFiles = [];  // For tar.gz: map of real path => archive path

// include DB: try to use existing SQL file if present, else attempt mysqldump using env from config
$dbIncluded = false;
$sqlFilePath = __DIR__ . '/../stc_event_db.sql';
if ($includeDB) {
    if (file_exists($sqlFilePath)) {
        if ($usesTarGz) {
            $addedFiles[$sqlFilePath] = basename($sqlFilePath);
        } else {
            $zip->addFile($sqlFilePath, basename($sqlFilePath));
        }
        $dbIncluded = true;
    }
}

// include files (configuration) - include common config or .php files from root
if ($includeFiles) {
    // include important config files if present
    $files = ['auth.php','.htaccess','index.php'];
    foreach ($files as $f) {
        $path = __DIR__ . '/../' . $f;
        if (file_exists($path)) {
            if ($usesTarGz) {
                $addedFiles[$path] = 'files/' . $f;
            } else {
                $zip->addFile($path, 'files/' . $f);
            }
        }
    }
    
    // Include entire api directory
    $apiDir = __DIR__ . '/../api';
    if (is_dir($apiDir)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($apiDir));
        foreach ($it as $file) {
            if ($file->isFile()) {
                $localPath = 'api/' . substr($file->getPathname(), strlen(realpath($apiDir)) + 1);
                if ($usesTarGz) {
                    $addedFiles[$file->getPathname()] = $localPath;
                } else {
                    $zip->addFile($file->getPathname(), $localPath);
                }
            }
        }
    }
}

// include images
if ($includeImages) {
    $imagesDir = __DIR__ . '/../images';
    if (is_dir($imagesDir)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($imagesDir));
        foreach ($it as $file) {
            if ($file->isFile()) {
                $localPath = 'images/' . substr($file->getPathname(), strlen(realpath($imagesDir)) + 1);
                if ($usesTarGz) {
                    $addedFiles[$file->getPathname()] = $localPath;
                } else {
                    $zip->addFile($file->getPathname(), $localPath);
                }
            }
        }
    }
}

// Finalize archive
if ($usesTarGz) {
    if (!createTarGz($tempZip, $addedFiles)) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Failed to create tar.gz backup']);
        exit;
    }
} else {
    $zip->close();
}

// Stream the archive to client
if (!file_exists($tempZip)) { http_response_code(500); echo 'Backup not created'; exit; }

// stream with safe headers
$filesize = filesize($tempZip);
if ($filesize === 0) {
    // create debug log
    $info = [
        'time' => date('c'),
        'msg' => 'empty_backup_file',
        'tempZip' => $tempZip,
        'addedFiles' => $addedFiles,
        'filesize' => $filesize,
        'error' => error_get_last()
    ];
    @file_put_contents($debugPath, json_encode($info, JSON_PRETTY_PRINT) . "\n", FILE_APPEND | LOCK_EX);
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Empty backup file created. Check admin/backup_debug.log on the server.']);
    @unlink($tempZip);
    exit;
}

// Determine correct content-type based on filename
$contentType = $usesTarGz ? 'application/gzip' : 'application/zip';
$downloadName = $usesTarGz ? "stc_backup_{$timestamp}.tar.gz" : $zipName;

header('Content-Description: File Transfer');
header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="' . basename($downloadName) . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($tempZip));

$fp = fopen($tempZip, 'rb');
if ($fp) {
    // stream the file in chunks to avoid memory issues
    while (!feof($fp)) {
        echo fread($fp, 1024 * 1024);
        flush();
    }
    fclose($fp);
} else {
    // fallback
    readfile($tempZip);
}

// After streaming, perform a sanity check and if the archive was suspiciously small, write debug log
$minReasonableSize = 64; // bytes
if ($filesize < $minReasonableSize) {
    $startBytes = '';
    $fh = fopen($tempZip, 'rb');
    if ($fh) { $startBytes = base64_encode(fread($fh, 128)); fclose($fh); }
    $info = [
        'time' => date('c'),
        'msg' => 'small_backup_file',
        'tempZip' => $tempZip,
        'format' => $usesTarGz ? 'tar.gz' : 'zip',
        'filesize' => $filesize,
        'start_bytes_b64' => $startBytes,
        'error' => error_get_last()
    ];
    @file_put_contents($debugPath, json_encode($info, JSON_PRETTY_PRINT) . "\n", FILE_APPEND | LOCK_EX);
}
// remove temp file
@unlink($tempZip);
exit;
