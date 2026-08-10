<?php
require_once __DIR__ . '/dbcon.php';
header('Content-Type: application/json; charset=utf-8');
if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'DB connection']);
    exit;
}
// Accept JSON or form
$raw = file_get_contents('php://input');
$data = [];
if (!empty($raw) && stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === 0) {
    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $data = $decoded;
}
// fallback to $_POST
if (empty($data)) $data = $_POST;

$username = isset($data['username']) ? trim($data['username']) : '';
$password = isset($data['password']) ? $data['password'] : '';

if ($username === '' || $password === '') { http_response_code(400); echo json_encode(['success'=>false,'error'=>'username and password required']); exit; }

// find columns
function findCol($conn,$table,$cands){
    $res = $conn->query("SHOW COLUMNS FROM `".$conn->real_escape_string($table)."`");
    if (!$res) return null;
    while ($r=$res->fetch_assoc()){
        foreach($cands as $c) if (strcasecmp($r['Field'],$c)===0) return $r['Field'];
    }
    // fallback: search approximate
    $res->data_seek(0);
    while ($r=$res->fetch_assoc()){
        $f=strtolower($r['Field']);
        foreach($cands as $c) if (strpos($f,strtolower($c))!==false) return $r['Field'];
    }
    return null;
}

$userTable = 'user';
$usernameCol = findCol($conn,$userTable,['username','user_name','user','email']);
$passwordCol = findCol($conn,$userTable,['password','passwd','pass']);
$accountCol = findCol($conn,$userTable,['account_type','role','type']);

if (!$usernameCol || !$passwordCol) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'user table columns missing']); exit; }

$sql = "SELECT * FROM `$userTable` WHERE `$usernameCol` = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'prepare failed']); exit; }
$stmt->bind_param('s',$username);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if (!$row) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'invalid credentials']); exit; }

$hash = $row[$passwordCol];
if (!password_verify($password, $hash)) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'invalid credentials']); exit; }

// Before creating a session, ensure the account and its department(s) are not archived
// Check user-level archived/status
$userArchived = false;
if (array_key_exists('archived', $row)) {
    $val = $row['archived'];
    if ($val === '1' || $val === 1 || strtolower((string)$val) === 'true') $userArchived = true;
}
if (!$userArchived && array_key_exists('status', $row)) {
    $st = strtolower((string)$row['status']);
    if ($st === 'archived' || $st === 'inactive') $userArchived = true;
}
if ($userArchived) { http_response_code(403); echo json_encode(['success'=>false,'error'=>'account archived or inactive']); exit; }

// Check department(s) associated with the user (if any) for archived flag/status
$deptCol = findCol($conn,$userTable,['departments_id','department_id','dept_id','department','dept']);
if ($deptCol && !empty($row[$deptCol])) {
    // find departments table
    $deptTable = null;
    $resT = $conn->query("SHOW TABLES");
    if ($resT) while ($r = $resT->fetch_row()) { if (stripos($r[0],'depart') !== false) { $deptTable = $r[0]; break; } }
    if ($deptTable) {
        $deptVal = $row[$deptCol];
        // tokens could be CSV or single id/name
        $tokens = is_array($deptVal) ? $deptVal : array_filter(array_map('trim', explode(',', (string)$deptVal)), 'strlen');
        foreach ($tokens as $t) {
            if ($t === '') continue;
            // try numeric id lookup
            if (ctype_digit((string)$t)) {
                $q = $conn->prepare("SELECT * FROM `" . $conn->real_escape_string($deptTable) . "` WHERE `id` = ? LIMIT 1");
                if ($q) { $iid = (int)$t; $q->bind_param('i', $iid); $q->execute(); $rD = $q->get_result()->fetch_assoc(); $q->close();
                    if ($rD) {
                        if ((isset($rD['archived']) && ($rD['archived'] === '1' || $rD['archived'] === 1)) || (isset($rD['status']) && strtolower((string)$rD['status']) === 'archived')) {
                            http_response_code(403); echo json_encode(['success'=>false,'error'=>'department archived']); exit;
                        }
                    }
                }
            } else {
                // try name or acronym match
                $nameCol = null; $acrCol = null;
                // detect columns
                $cres = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($deptTable) . "`");
                if ($cres) while ($cr = $cres->fetch_assoc()) {
                    $f = $cr['Field'];
                    if (!$nameCol && stripos($f,'name') !== false) $nameCol = $f;
                    if (!$acrCol && (stripos($f,'acronym') !== false || stripos($f,'acr') !== false)) $acrCol = $f;
                }
                $tLow = mb_strtolower($t);
                if ($nameCol) {
                    $q = $conn->prepare("SELECT * FROM `" . $conn->real_escape_string($deptTable) . "` WHERE LOWER(`" . $conn->real_escape_string($nameCol) . "`) = ? LIMIT 1");
                    if ($q) { $q->bind_param('s', $tLow); $q->execute(); $rD = $q->get_result()->fetch_assoc(); $q->close();
                        if ($rD) {
                            if ((isset($rD['archived']) && ($rD['archived'] === '1' || $rD['archived'] === 1)) || (isset($rD['status']) && strtolower((string)$rD['status']) === 'archived')) {
                                http_response_code(403); echo json_encode(['success'=>false,'error'=>'department archived']); exit;
                            }
                        }
                    }
                }
                if ($acrCol) {
                    $q2 = $conn->prepare("SELECT * FROM `" . $conn->real_escape_string($deptTable) . "` WHERE LOWER(`" . $conn->real_escape_string($acrCol) . "`) = ? LIMIT 1");
                    if ($q2) { $q2->bind_param('s', $tLow); $q2->execute(); $rA = $q2->get_result()->fetch_assoc(); $q2->close();
                        if ($rA) {
                            if ((isset($rA['archived']) && ($rA['archived'] === '1' || $rA['archived'] === 1)) || (isset($rA['status']) && strtolower((string)$rA['status']) === 'archived')) {
                                http_response_code(403); echo json_encode(['success'=>false,'error'=>'department archived']); exit;
                            }
                        }
                    }
                }
            }
        }
    }
}

// start session and store user info
session_start();
// Prefer storing a numeric user id in session. Try to detect the id column in the user table.
$idCol = findCol($conn,$userTable,['id','user_id','uid','UID','ID']);
if ($idCol && isset($row[$idCol]) && is_numeric($row[$idCol])) {
    $_SESSION['user_id'] = (int)$row[$idCol];
} else {
    // fallback: store username value for backward compatibility
    $_SESSION['user_id'] = $row[$usernameCol] ?? null;
}
// also store the readable username for convenience
if (isset($row[$usernameCol])) $_SESSION['username'] = $row[$usernameCol];
$_SESSION['account_type'] = ($accountCol && isset($row[$accountCol])) ? $row[$accountCol] : 'user';

// Ensure a last_login column exists and update it for this user (idempotent)
try {
    $hasLast = false;
    $check = $conn->query("SHOW COLUMNS FROM `".$conn->real_escape_string($userTable)."` LIKE 'last_login'");
    if ($check && $check->num_rows > 0) { $hasLast = true; }
    if (!$hasLast) {
        @$conn->query("ALTER TABLE `".$conn->real_escape_string($userTable)."` ADD COLUMN `last_login` datetime NULL");
    }
    // Update by id if possible, else by username
    if ($idCol && isset($row[$idCol])) {
        $stmtUp = $conn->prepare("UPDATE `".$conn->real_escape_string($userTable)."` SET `last_login` = NOW() WHERE `".$conn->real_escape_string($idCol)."` = ?");
        if ($stmtUp) { $val = $row[$idCol]; if (is_numeric($val)) { $i=(int)$val; $stmtUp->bind_param('i',$i); } else { $s=(string)$val; $stmtUp->bind_param('s',$s); } $stmtUp->execute(); }
    } else {
        $stmtUp = $conn->prepare("UPDATE `".$conn->real_escape_string($userTable)."` SET `last_login` = NOW() WHERE `".$conn->real_escape_string($usernameCol)."` = ?");
        if ($stmtUp) { $s=(string)$username; $stmtUp->bind_param('s',$s); $stmtUp->execute(); }
    }
} catch (Throwable $e) { /* ignore */ }

// return account type and success
$response = ['success'=>true,'account_type'=>$_SESSION['account_type']];

// If admin, also return email for OTP
if ($_SESSION['account_type'] === 'admin') {
    $emailCol = findCol($conn,$userTable,['email','e_mail','mail']);
    if ($emailCol && isset($row[$emailCol])) {
        $response['email'] = $row[$emailCol];
    }
}

echo json_encode($response);
exit;

?>