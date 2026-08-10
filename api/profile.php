<?php
// RESTful profile API for admin (user) and department (event profile)
// Temporary on-boot migrations: add missing columns if they do not exist (remove this block later if desired)
require_once __DIR__ . '/dbcon.php';
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection not available']);
    exit;
}

function show_columns($conn, $table) {
    $res = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($table) . "`");
    $cols = [];
    if ($res) while ($r = $res->fetch_assoc()) $cols[] = $r['Field'];
    return $cols;
}

function table_exists_like($conn, $like) {
    $res = $conn->query("SHOW TABLES");
    if ($res) while ($r = $res->fetch_row()) if (stripos($r[0], $like) !== false) return $r[0];
    return null;
}

function find_col_exact_or_loose($conn, $table, $candidates, $fallbackContains = []) {
    $cols = show_columns($conn, $table);
    foreach ($candidates as $cand) {
        foreach ($cols as $c) if (strcasecmp($c, $cand) === 0) return $c;
    }
    if (!empty($fallbackContains)) {
        foreach ($cols as $c) {
            $lc = strtolower($c);
            foreach ($fallbackContains as $frag) {
                if (strpos($lc, strtolower($frag)) !== false) return $c;
            }
        }
    }
    return null;
}

// BEGIN: Temporary migrations (safe to re-run)
try {
    // user table
    $userTable = table_exists_like($conn, 'user') ?: 'user';
    if ($userTable) {
        $uCols = show_columns($conn, $userTable);
        $toAdd = [];
        if (!in_array('first_name', $uCols, true)) $toAdd[] = 'ADD COLUMN `first_name` varchar(100) NULL';
        if (!in_array('last_name', $uCols, true)) $toAdd[] = 'ADD COLUMN `last_name` varchar(100) NULL';
        if (!in_array('email', $uCols, true)) $toAdd[] = 'ADD COLUMN `email` varchar(255) NULL';
        if (!in_array('phone', $uCols, true)) $toAdd[] = 'ADD COLUMN `phone` varchar(50) NULL';
        if (!in_array('description', $uCols, true)) $toAdd[] = 'ADD COLUMN `description` text NULL';
        if (!in_array('profile_pic', $uCols, true)) $toAdd[] = "ADD COLUMN `profile_pic` varchar(255) NULL";
        if (!in_array('last_login', $uCols, true)) $toAdd[] = "ADD COLUMN `last_login` datetime NULL";
    // toggles for admin settings
    if (!in_array('auto_checkin', $uCols, true)) $toAdd[] = "ADD COLUMN `auto_checkin` tinyint(1) NOT NULL DEFAULT 0";
    if (!in_array('daily_summary', $uCols, true)) $toAdd[] = "ADD COLUMN `daily_summary` tinyint(1) NOT NULL DEFAULT 0";
        if (!empty($toAdd)) {
            $sql = "ALTER TABLE `" . $conn->real_escape_string($userTable) . "` " . implode(', ', $toAdd);
            @$conn->query($sql); // suppress to allow re-run without fatal
        }
    }

    // departments table
    $deptTable = table_exists_like($conn, 'depart');
    if ($deptTable) {
        $dCols = show_columns($conn, $deptTable);
        $toAdd2 = [];
        if (!in_array('contact_email', $dCols, true)) $toAdd2[] = 'ADD COLUMN `contact_email` varchar(255) NULL';
        if (!in_array('contact_phone', $dCols, true)) $toAdd2[] = 'ADD COLUMN `contact_phone` varchar(50) NULL';
        if (!in_array('department_head', $dCols, true)) $toAdd2[] = 'ADD COLUMN `department_head` varchar(255) NULL';
        if (!in_array('office_location', $dCols, true)) $toAdd2[] = 'ADD COLUMN `office_location` varchar(255) NULL';
        if (!in_array('description', $dCols, true)) $toAdd2[] = 'ADD COLUMN `description` text NULL';
        if (!empty($toAdd2)) {
            $sql2 = "ALTER TABLE `" . $conn->real_escape_string($deptTable) . "` " . implode(', ', $toAdd2);
            @$conn->query($sql2);
        }
    }
} catch (Throwable $e) {
    // swallow migration errors; not fatal
}
// END: Temporary migrations

function json_input() {
    $raw = file_get_contents('php://input');
    $j = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($j)) return $j;
    // also support form-encoded fallback
    $vars = [];
    parse_str($raw, $vars);
    return $vars;
}

function resolve_session_user_id($conn) {
    $keys = ['user_id','id','uid','username'];
    foreach ($keys as $k) if (isset($_SESSION[$k])) return $_SESSION[$k];
    return null;
}

function get_user_row($conn, $idOrName) {
    $table = table_exists_like($conn, 'user') ?: 'user';
    $cols = show_columns($conn, $table);
    $idCol = find_col_exact_or_loose($conn, $table, ['id','user_id','UID']);
    $usernameCol = find_col_exact_or_loose($conn, $table, ['username','user_name','user']);
    $acctCol = find_col_exact_or_loose($conn, $table, ['account_type','role','type']);
    if ($idCol && ctype_digit((string)$idOrName)) {
        $stmt = $conn->prepare("SELECT * FROM `".$conn->real_escape_string($table)."` WHERE `$idCol` = ? LIMIT 1");
        if ($stmt) { $i=(int)$idOrName; $stmt->bind_param('i', $i); $stmt->execute(); $r = $stmt->get_result()->fetch_assoc(); if ($r) return $r; }
    }
    if ($usernameCol) {
        $stmt = $conn->prepare("SELECT * FROM `".$conn->real_escape_string($table)."` WHERE `$usernameCol` = ? LIMIT 1");
        if ($stmt) { $s=(string)$idOrName; $stmt->bind_param('s', $s); $stmt->execute(); $r = $stmt->get_result()->fetch_assoc(); if ($r) return $r; }
    }
    return null;
}

function get_department_row($conn, $deptId) {
    $table = table_exists_like($conn, 'depart');
    if (!$table) return null;
    $idCol = find_col_exact_or_loose($conn, $table, ['id','Dept_ID','dept_id','ID']);
    if (!$idCol) return null;
    $stmt = $conn->prepare("SELECT * FROM `".$conn->real_escape_string($table)."` WHERE `$idCol` = ? LIMIT 1");
    if (!$stmt) return null;
    $i = (int)$deptId; $stmt->bind_param('i', $i); $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$method = $_SERVER['REQUEST_METHOD'];
$type = isset($_GET['type']) ? strtolower($_GET['type']) : '';

try {
    if ($method === 'GET') {
        if ($type === 'admin') {
            $userId = resolve_session_user_id($conn);
            if ($userId === null) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Not authenticated']); exit; }

            $userTable = table_exists_like($conn, 'user') ?: 'user';
            $idCol = find_col_exact_or_loose($conn, $userTable, ['id','user_id','UID']);
            $usernameCol = find_col_exact_or_loose($conn, $userTable, ['username','user_name','user']);
            $acctCol = find_col_exact_or_loose($conn, $userTable, ['account_type','role','type']);
            $deptCol = find_col_exact_or_loose($conn, $userTable, ['departments_id','department_id','dept_id'], ['depart']);

            $cols = show_columns($conn, $userTable);
            $firstCol = in_array('first_name',$cols,true) ? 'first_name' : null;
            $lastCol  = in_array('last_name',$cols,true) ? 'last_name'  : null;
            $emailCol = in_array('email',$cols,true) ? 'email' : null;
            $phoneCol = in_array('phone',$cols,true) ? 'phone' : null;
            $descCol  = in_array('description',$cols,true) ? 'description' : null;
            // attempt to detect a last login-like column
            $lastLoginCol = find_col_exact_or_loose($conn, $userTable, [
                'last_login','last_login_at','last_sign_in_at','last_signin_at','last_seen','last_activity','last_access','last_access_at'
            ], ['login','signin','sign_in','seen','access','activity']);

            // Fetch user row
            $whereCol = $idCol ?: $usernameCol;
            $stmt = $conn->prepare("SELECT * FROM `".$conn->real_escape_string($userTable)."` WHERE `$whereCol` = ? LIMIT 1");
            if (!$stmt) throw new Exception('Failed to prepare user select');
            if ($whereCol === $idCol) { $i=(int)$userId; $stmt->bind_param('i', $i); }
            else { $s=(string)$userId; $stmt->bind_param('s', $s); }
            $stmt->execute(); $row = $stmt->get_result()->fetch_assoc();
            if (!$row) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'User not found']); exit; }

            $acct = $acctCol && isset($row[$acctCol]) ? strtolower($row[$acctCol]) : 'user';
            // normalize profile_pic to a web path if present
            $picVal = in_array('profile_pic', $cols, true) ? ($row['profile_pic'] ?? null) : null;
            if ($picVal) {
                if (preg_match('/^https?:\/\//i', $picVal) !== 1 && strpos($picVal, '/') !== 0) {
                    // looks like a relative path stored in DB (e.g., images/profiles/x.jpg). Prefix with /new/
                    $picVal = '/Theresian-RFID-Events-Attendance-System/' . ltrim($picVal, '/');
                }
            }

            $lastLoginVal = null;
            if ($lastLoginCol && isset($row[$lastLoginCol])) {
                $lastLoginVal = $row[$lastLoginCol];
                // cast to string for JSON
                if (is_numeric($lastLoginVal) || is_int($lastLoginVal)) { $lastLoginVal = (string)$lastLoginVal; }
            }

            $data = [
                'id' => $idCol ? $row[$idCol] : null,
                'username' => $usernameCol ? $row[$usernameCol] : null,
                'account_type' => $acct,
                'first_name' => ($acct === 'admin' && $firstCol) ? (string)($row[$firstCol] ?? '') : '',
                'last_name'  => ($acct === 'admin' && $lastCol) ? (string)($row[$lastCol] ?? '') : '',
                'email'      => $emailCol ? (string)($row[$emailCol] ?? '') : '',
                'phone'      => $phoneCol ? (string)($row[$phoneCol] ?? '') : '',
                'department_id' => $deptCol ? $row[$deptCol] : null,
                'description' => $descCol ? (string)($row[$descCol] ?? '') : '',
                'profile_pic' => $picVal,
                'last_login' => $lastLoginVal,
                'auto_checkin' => in_array('auto_checkin', $cols, true) ? (bool)($row['auto_checkin'] ?? false) : false,
                'daily_summary' => in_array('daily_summary', $cols, true) ? (bool)($row['daily_summary'] ?? false) : false,
            ];

            // add department info
            $deptRow = null;
            if (!empty($data['department_id'])) $deptRow = get_department_row($conn, $data['department_id']);
            if ($deptRow) {
                $deptNameCol = find_col_exact_or_loose($conn, table_exists_like($conn,'depart'), ['Dept_name','dept_name','name']);
                $deptAcrCol = find_col_exact_or_loose($conn, table_exists_like($conn,'depart'), ['dept_Acronym','acronym']);
                $data['department'] = [
                    'id' => (int)$data['department_id'],
                    'name' => $deptNameCol && isset($deptRow[$deptNameCol]) ? $deptRow[$deptNameCol] : null,
                    'acronym' => $deptAcrCol && isset($deptRow[$deptAcrCol]) ? $deptRow[$deptAcrCol] : null,
                ];
            } else {
                $data['department'] = null;
            }

            echo json_encode(['success'=>true,'data'=>$data]);
            exit;
        }

        if ($type === 'department' || $type === 'event') {
            // resolve current user's department id from session user
            $userTable = table_exists_like($conn, 'user') ?: 'user';
            $idCol = find_col_exact_or_loose($conn, $userTable, ['id','user_id','UID']);
            $deptCol = find_col_exact_or_loose($conn, $userTable, ['departments_id','department_id','dept_id'], ['depart']);
            $acctCol = find_col_exact_or_loose($conn, $userTable, ['account_type','role','type']);
            $sess = resolve_session_user_id($conn);
            if ($sess === null) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Not authenticated']); exit; }
            $whereCol = $idCol ?: 'id';
            $stmt = $conn->prepare("SELECT * FROM `".$conn->real_escape_string($userTable)."` WHERE `$whereCol` = ? LIMIT 1");
            if ($whereCol === $idCol) { $i=(int)$sess; $stmt->bind_param('i',$i); } else { $s=(string)$sess; $stmt->bind_param('s',$s); }
            $stmt->execute(); $usr = $stmt->get_result()->fetch_assoc();
            if (!$usr) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'User not found']); exit; }
            $deptId = $deptCol && isset($usr[$deptCol]) ? (int)$usr[$deptCol] : 0;
            $acct = $acctCol && isset($usr[$acctCol]) ? strtolower($usr[$acctCol]) : 'user';
            // For admins without a bound department, allow querying a specific department via ?dept_id
            if ((!$deptId || $deptId === 0) && $acct === 'admin') {
                $qDept = isset($_GET['dept_id']) ? (int)$_GET['dept_id'] : 0;
                if ($qDept > 0) $deptId = $qDept;
            }

            $deptTable = table_exists_like($conn, 'depart');
            if (!$deptTable || !$deptId) {
                echo json_encode(['success'=>true,'data'=>null]); exit;
            }
            $idDeptCol = find_col_exact_or_loose($conn, $deptTable, ['id','Dept_ID','dept_id','ID']);
            $nameCol = find_col_exact_or_loose($conn, $deptTable, ['Dept_name','dept_name','name']);
            $acroCol = find_col_exact_or_loose($conn, $deptTable, ['dept_Acronym','acronym']);
            $dcols = show_columns($conn, $deptTable);
            $contactEmailCol = in_array('contact_email',$dcols,true) ? 'contact_email' : null;
            $contactPhoneCol = in_array('contact_phone',$dcols,true) ? 'contact_phone' : null;
            $deptHeadCol     = in_array('department_head',$dcols,true) ? 'department_head' : null;
            $officeLocCol    = in_array('office_location',$dcols,true) ? 'office_location' : null;
            $descCol         = in_array('description',$dcols,true) ? 'description' : null;

            $stmt2 = $conn->prepare("SELECT * FROM `".$conn->real_escape_string($deptTable)."` WHERE `$idDeptCol` = ? LIMIT 1");
            $stmt2->bind_param('i', $deptId);
            $stmt2->execute(); $drow = $stmt2->get_result()->fetch_assoc();
            if (!$drow) { echo json_encode(['success'=>true,'data'=>null]); exit; }

            $data = [
                'id' => $deptId,
                'name' => $nameCol ? (string)$drow[$nameCol] : '',
                'acronym' => $acroCol ? (string)$drow[$acroCol] : '',
                'contact_email' => $contactEmailCol ? (string)($drow[$contactEmailCol] ?? '') : '',
                'contact_phone' => $contactPhoneCol ? (string)($drow[$contactPhoneCol] ?? '') : '',
                'department_head' => $deptHeadCol ? (string)($drow[$deptHeadCol] ?? '') : '',
                'office_location' => $officeLocCol ? (string)($drow[$officeLocCol] ?? '') : '',
                'description' => $descCol ? (string)($drow[$descCol] ?? '') : '',
            ];
            echo json_encode(['success'=>true,'data'=>$data]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>'Missing or invalid type parameter']);
        exit;
    }

    if ($method === 'PUT') {
        $input = json_input();
        if ($type === 'admin') {
            $userTable = table_exists_like($conn, 'user') ?: 'user';
            $idCol = find_col_exact_or_loose($conn, $userTable, ['id','user_id','UID']);
            $acctCol = find_col_exact_or_loose($conn, $userTable, ['account_type','role','type']);
            $cols = show_columns($conn, $userTable);
            $firstCol = in_array('first_name',$cols,true) ? 'first_name' : null;
            $lastCol  = in_array('last_name',$cols,true) ? 'last_name'  : null;
            $emailCol = in_array('email',$cols,true) ? 'email' : null;
            $phoneCol = in_array('phone',$cols,true) ? 'phone' : null;
            $descCol  = in_array('description',$cols,true) ? 'description' : null;

            $userId = resolve_session_user_id($conn);
            if ($userId === null) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Not authenticated']); exit; }

            // Fetch current to determine role
            $stmt = $conn->prepare("SELECT * FROM `".$conn->real_escape_string($userTable)."` WHERE `$idCol` = ? LIMIT 1");
            $i = (int)$userId; $stmt->bind_param('i',$i); $stmt->execute(); $row = $stmt->get_result()->fetch_assoc();
            if (!$row) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'User not found']); exit; }
            $acct = $acctCol && isset($row[$acctCol]) ? strtolower($row[$acctCol]) : 'user';

            $updates = [];
            $types = '';
            $vals = [];
            if ($emailCol && isset($input['email'])) { $updates[] = "`$emailCol` = ?"; $types .= 's'; $vals[] = (string)$input['email']; }
            if ($phoneCol && isset($input['phone'])) { $updates[] = "`$phoneCol` = ?"; $types .= 's'; $vals[] = (string)$input['phone']; }
            if ($descCol && array_key_exists('description',$input)) { $updates[] = "`$descCol` = ?"; $types .= 's'; $vals[] = (string)$input['description']; }
            // toggles
            if (in_array('auto_checkin',$cols,true) && array_key_exists('auto_checkin',$input)) { $updates[] = "`auto_checkin` = ?"; $types .= 'i'; $vals[] = ($input['auto_checkin'] ? 1 : 0); }
            if (in_array('daily_summary',$cols,true) && array_key_exists('daily_summary',$input)) { $updates[] = "`daily_summary` = ?"; $types .= 'i'; $vals[] = ($input['daily_summary'] ? 1 : 0); }
            // Only admins can set first/last name per requirement
            if ($acct === 'admin') {
                if ($firstCol && array_key_exists('first_name',$input)) { $updates[] = "`$firstCol` = ?"; $types .= 's'; $vals[] = (string)$input['first_name']; }
                if ($lastCol && array_key_exists('last_name',$input)) { $updates[] = "`$lastCol` = ?"; $types .= 's'; $vals[] = (string)$input['last_name']; }
            }

            if (empty($updates)) { echo json_encode(['success'=>true,'affected_rows'=>0]); exit; }
            $sql = "UPDATE `".$conn->real_escape_string($userTable)."` SET " . implode(', ', $updates) . " WHERE `$idCol` = ?";
            $stmt2 = $conn->prepare($sql);
            $types .= 'i'; $vals[] = (int)$userId;
            $stmt2->bind_param($types, ...$vals);
            $ok = $stmt2->execute();
            echo json_encode(['success'=>(bool)$ok, 'affected_rows'=>$stmt2->affected_rows]);
            exit;
        }

        if ($type === 'department' || $type === 'event') {
            $userTable = table_exists_like($conn, 'user') ?: 'user';
            $idCol = find_col_exact_or_loose($conn, $userTable, ['id','user_id','UID']);
            $deptIdColOnUser = find_col_exact_or_loose($conn, $userTable, ['departments_id','department_id','dept_id'], ['depart']);
            $acctCol = find_col_exact_or_loose($conn, $userTable, ['account_type','role','type']);
            $sess = resolve_session_user_id($conn);
            if ($sess === null) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Not authenticated']); exit; }
            $stmt = $conn->prepare("SELECT * FROM `".$conn->real_escape_string($userTable)."` WHERE `$idCol` = ? LIMIT 1");
            $i=(int)$sess; $stmt->bind_param('i',$i); $stmt->execute(); $usr = $stmt->get_result()->fetch_assoc();
            if (!$usr) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'User not found']); exit; }
            $deptId = $deptIdColOnUser && isset($usr[$deptIdColOnUser]) ? (int)$usr[$deptIdColOnUser] : 0;
            $acct = $acctCol && isset($usr[$acctCol]) ? strtolower($usr[$acctCol]) : 'user';
            // Allow admin to provide dept_id when none is attached
            if ((!$deptId || $deptId === 0) && $acct === 'admin') {
                $inDept = 0;
                if (isset($input['dept_id'])) $inDept = (int)$input['dept_id'];
                if (!$inDept && isset($_GET['dept_id'])) $inDept = (int)$_GET['dept_id'];
                if ($inDept > 0) $deptId = $inDept;
            }
            if (!$deptId) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'No department associated with current account or provided for admin']); exit; }

            $deptTable = table_exists_like($conn, 'depart');
            $idDeptCol = find_col_exact_or_loose($conn, $deptTable, ['id','Dept_ID','dept_id','ID']);
            $dcols = show_columns($conn, $deptTable);
            $contactEmailCol = in_array('contact_email',$dcols,true) ? 'contact_email' : null;
            $contactPhoneCol = in_array('contact_phone',$dcols,true) ? 'contact_phone' : null;
            $deptHeadCol     = in_array('department_head',$dcols,true) ? 'department_head' : null;
            $officeLocCol    = in_array('office_location',$dcols,true) ? 'office_location' : null;
            $descCol         = in_array('description',$dcols,true) ? 'description' : null;

            $updates = [];
            $types = '';
            $vals = [];
            if ($contactEmailCol && array_key_exists('contact_email',$input)) { $updates[] = "`$contactEmailCol` = ?"; $types.='s'; $vals[]=(string)$input['contact_email']; }
            if ($contactPhoneCol && array_key_exists('contact_phone',$input)) { $updates[] = "`$contactPhoneCol` = ?"; $types.='s'; $vals[]=(string)$input['contact_phone']; }
            if ($deptHeadCol && array_key_exists('department_head',$input)) { $updates[] = "`$deptHeadCol` = ?"; $types.='s'; $vals[]=(string)$input['department_head']; }
            if ($officeLocCol && array_key_exists('office_location',$input)) { $updates[] = "`$officeLocCol` = ?"; $types.='s'; $vals[]=(string)$input['office_location']; }
            if ($descCol && array_key_exists('description',$input)) { $updates[] = "`$descCol` = ?"; $types.='s'; $vals[]=(string)$input['description']; }

            if (empty($updates)) { echo json_encode(['success'=>true,'affected_rows'=>0]); exit; }
            $sql = "UPDATE `".$conn->real_escape_string($deptTable)."` SET " . implode(', ', $updates) . " WHERE `$idDeptCol` = ?";
            $stmt2 = $conn->prepare($sql);
            $types .= 'i'; $vals[] = (int)$deptId;
            $stmt2->bind_param($types, ...$vals);
            $ok = $stmt2->execute();
            echo json_encode(['success'=>(bool)$ok, 'affected_rows'=>$stmt2->affected_rows]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>'Missing or invalid type parameter']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'Method not allowed']);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    exit;
}

?>
