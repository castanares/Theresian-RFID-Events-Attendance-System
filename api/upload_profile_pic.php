<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/dbcon.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_FILES['profile_pic'])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'No file uploaded']);
    exit;
}

// resolve user id from session
function table_exists_like($conn, $like) {
    $res = $conn->query("SHOW TABLES");
    if ($res) while ($r = $res->fetch_row()) if (stripos($r[0], $like) !== false) return $r[0];
    return null;
}
function find_col_exact_or_loose($conn, $table, $candidates, $fallbackContains = []) {
    $cols = [];
    $res = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($table) . "`");
    if ($res) while ($r = $res->fetch_assoc()) $cols[] = $r['Field'];
    foreach ($candidates as $cand) {
        foreach ($cols as $c) if (strcasecmp($c, $cand) === 0) return $c;
    }
    if (!empty($fallbackContains)) {
        foreach ($cols as $c) {
            $lc = strtolower($c);
            foreach ($fallbackContains as $frag) if (strpos($lc, strtolower($frag)) !== false) return $c;
        }
    }
    return null;
}

$userId = null;
$keys = ['user_id','id','uid','username'];
foreach ($keys as $k) if (isset($_SESSION[$k])) { $userId = $_SESSION[$k]; break; }
if (!$userId) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Not authenticated']); exit; }

// validate file
$file = $_FILES['profile_pic'];
if ($file['error'] !== UPLOAD_ERR_OK) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Upload error']); exit; }
$allowed = ['image/jpeg','image/png','image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
if (!in_array($mime, $allowed)) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Invalid file type']); exit; }

// ensure directory
$dir = __DIR__ . '/../images/profiles';
if (!is_dir($dir)) mkdir($dir, 0755, true);
// build filename
$ext = ($mime === 'image/png') ? 'png' : (($mime === 'image/gif') ? 'gif' : 'jpg');
$base = 'user_' . preg_replace('/[^a-z0-9_-]/i','', (string)$userId) . '_' . time();
$filename = $base . '.' . $ext;
$dest = $dir . '/' . $filename;
if (!move_uploaded_file($file['tmp_name'], $dest)) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Failed to move uploaded file']); exit; }

// store relative path in DB
$userTable = table_exists_like($conn, 'user') ?: 'user';
$cols = [];
$resCols = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($userTable) . "`");
if ($resCols) while ($r = $resCols->fetch_assoc()) $cols[] = $r['Field'];

// Build both filesystem path (above) and web path for browser
$relPath = 'images/profiles/' . $filename; // relative to /new/
$webPath = '/new/' . $relPath; // adjust if app root differs

$dbUpdated = false;
if (in_array('profile_pic', $cols, true)) {
    $idCol = find_col_exact_or_loose($conn, $userTable, ['id','user_id','UID']);
    if (!$idCol) $idCol = 'id';
    $stmt = $conn->prepare("UPDATE `" . $conn->real_escape_string($userTable) . "` SET `profile_pic` = ? WHERE `$idCol` = ?");
    if ($stmt) {
        if (ctype_digit((string)$userId)) { $i = (int)$userId; $stmt->bind_param('si', $relPath, $i); }
        else { $s = (string)$userId; $stmt->bind_param('ss', $relPath, $s); }
        $stmt->execute();
        $dbUpdated = ($stmt->affected_rows >= 0); // true on success (0 if same value)
    }
}

echo json_encode(['success'=>true,'path'=>$webPath,'db_updated'=>$dbUpdated]);
exit;
?>