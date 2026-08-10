<?php
// RESTful users API: create user (POST) and reset password (PUT)
require_once __DIR__ . '/dbcon.php';

header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection not available']);
    exit;
}

// helper: get columns of a table and find first matching candidate
function findColumnFromTable($conn, $table, array $candidates) {
    $res = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($table) . "`");
    if (!$res) return null;
    $cols = [];
    while ($r = $res->fetch_assoc()) $cols[] = $r['Field'];
    foreach ($candidates as $c) {
        foreach ($cols as $existing) {
            if (strcasecmp($existing, $c) === 0) return $existing;
        }
    }
    return null;
}

// looser detection: try exact candidates first, then look for any column containing keywords
function findColumnLoose($conn, $table, array $candidates) {
    $col = findColumnFromTable($conn, $table, $candidates);
    if ($col) return $col;
    $res = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($table) . "`");
    if (!$res) return null;
    $cols = [];
    while ($r = $res->fetch_assoc()) $cols[] = $r['Field'];
    // try common patterns
    foreach ($cols as $c) {
        $lower = strtolower($c);
        if (strpos($lower, 'depart') !== false || strpos($lower, 'dept') !== false) return $c;
        if (preg_match('/departments?_?id/i', $c)) return $c;
    }
    return null;
}

// try to resolve department to an id (if departments table has an id column)
function resolveDepartment($conn, $identifier) {
    if ($identifier === '' || $identifier === null) return null;
    // if numeric, return as int (may be Dept_ID)
    if (is_numeric($identifier)) return (int)$identifier;

    // find department id and name columns
    $idCol = findColumnFromTable($conn, 'departments', ['Dept_ID','id','ID']);
    $nameCol = findColumnFromTable($conn, 'departments', ['Dept_name','dept_name','name']);
    $acroCol = findColumnFromTable($conn, 'departments', ['dept_Acronym','dept_acronym','acronym']);

    if (!$nameCol && !$acroCol) return $identifier; // fallback: return raw name

    // try match by exact name
    if ($nameCol) {
        $sql = "SELECT " . ($idCol ? "`$idCol`" : "`$nameCol`") . " FROM `departments` WHERE `$nameCol` = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('s', $identifier);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($r = $res->fetch_row()) {
                return $r[0];
            }
        }
    }

    // try match by acronym
    if ($acroCol) {
        $sql = "SELECT " . ($idCol ? "`$idCol`" : "`$acroCol`") . " FROM `departments` WHERE `$acroCol` = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('s', $identifier);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($r = $res->fetch_row()) {
                return $r[0];
            }
        }
    }

    // no match, return raw identifier so caller can decide
    return $identifier;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // return list of users (normalize keys)
    $userTable = 'user';
    $idCol = findColumnFromTable($conn, $userTable, ['id','user_id','UID']);
    $usernameCol = findColumnFromTable($conn, $userTable, ['username','user_name','user']);
    $accountCol = findColumnFromTable($conn, $userTable, ['account_type','role','type']);
    $deptCol = findColumnLoose($conn, $userTable, ['department_id','dept_id','department','dept','department_name','departments_id']);
    $statusCol = findColumnFromTable($conn, $userTable, ['status','archived','state']);
    $emailCol = findColumnFromTable($conn, $userTable, ['email','Email','e-mail']);

    $select = [];
    if ($idCol) $select[] = "`$idCol`";
    if ($usernameCol) $select[] = "`$usernameCol`";
    if ($accountCol) $select[] = "`$accountCol`";
    if ($deptCol) $select[] = "`$deptCol`";
    if ($statusCol) $select[] = "`$statusCol`";
    if ($emailCol) $select[] = "`$emailCol`";
    if (!count($select)) $select = ['*'];

    $sql = 'SELECT ' . implode(', ', $select) . ' FROM `'.$conn->real_escape_string($userTable).'`';
    $res = $conn->query($sql);
    if (!$res) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Query failed']); exit; }
    $out = [];
    while ($r = $res->fetch_assoc()) {
        $row = [];
        if ($idCol && isset($r[$idCol])) $row['id'] = $r[$idCol];
        if ($usernameCol && isset($r[$usernameCol])) $row['username'] = $r[$usernameCol];
        if ($accountCol && isset($r[$accountCol])) $row['account_type'] = $r[$accountCol];
        if ($deptCol && isset($r[$deptCol])) $row['department'] = $r[$deptCol];
    if ($statusCol && isset($r[$statusCol])) $row['status'] = $r[$statusCol];
    if ($emailCol && isset($r[$emailCol])) $row['email'] = $r[$emailCol];
        if (!count($row)) $row = $r;
        $out[] = $row;
    }
    echo json_encode(['success'=>true,'data'=>$out]);
    exit;
}

if ($method === 'POST') {
    // Create user
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $account_type = isset($_POST['account_type']) ? trim($_POST['account_type']) : 'user';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';

    if ($username === '' || $password === '') {
        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>'username and password are required']);
        exit;
    }

    // detect user table columns
    $userTable = 'user';
    $usernameCol = findColumnFromTable($conn, $userTable, ['username','user_name','user']);
    $passwordCol = findColumnFromTable($conn, $userTable, ['password','passwd','pass']);
    $accountCol = findColumnFromTable($conn, $userTable, ['account_type','role','type']);
    $deptCol = findColumnLoose($conn, $userTable, ['department_id','dept_id','department','dept','department_name','departments_id']);

    if (!$usernameCol || !$passwordCol) {
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Required user columns not found']);
        exit;
    }

    // check for existing username
    $sqlCheck = "SELECT COUNT(*) FROM `$userTable` WHERE `$usernameCol` = ?";
    $stmt = $conn->prepare($sqlCheck);
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_row();
        if ($row && $row[0] > 0) {
            http_response_code(409);
            echo json_encode(['success'=>false,'error'=>'Username already exists']);
            exit;
        }
    }

    // prepare insert
    $cols = ["`$usernameCol`"=>null, "`$passwordCol`"=>null];
    $params = [];
    $types = '';

    // hashed password
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $params[] = $username; $types .= 's';
    $params[] = $hash; $types .= 's';

    if ($accountCol) {
        $cols["`$accountCol`"] = null;
        $params[] = $account_type; $types .= 's';
    }

    if ($deptCol && $department !== '') {
        // normalize department value
        $resolved = resolveDepartment($conn, $department);
        $cols["`$deptCol`"] = null;
        // determine if resolved is numeric and deptCol likely numeric
        $params[] = $resolved; $types .= is_int($resolved) ? 'i' : 's';
    }

    $colNames = implode(', ', array_keys($cols));
    $placeholders = rtrim(str_repeat('?, ', count($params)), ', ');
    $sql = "INSERT INTO `$userTable` ($colNames) VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Prepare failed: '.$conn->error]); exit; }
    // bind params dynamically
    $bind_names[] = $types;
    for ($i=0; $i<count($params); $i++) { $bind_name = 'bind' . $i; $$bind_name = $params[$i]; $bind_names[] = &$$bind_name; }
    call_user_func_array(array($stmt, 'bind_param'), $bind_names);
    $ok = $stmt->execute();
    if ($ok) {
        echo json_encode(['success'=>true,'id'=>$conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['success'=>false,'error'=>'Insert failed: '.$stmt->error]);
    }
    exit;
}

// parse raw input for PUT
$input = file_get_contents('php://input');
parse_str($input, $putVars);

if ($method === 'PUT') {
    // Reset password or update user fields
    $id = isset($putVars['id']) ? (int)$putVars['id'] : 0;
    $username = isset($putVars['username']) ? trim($putVars['username']) : '';
    $newpass = isset($putVars['new_password']) ? $putVars['new_password'] : '';

    // detect user table columns
    $userTable = 'user';
    $idCol = findColumnFromTable($conn, $userTable, ['id','user_id','UID']);
    $usernameCol = findColumnFromTable($conn, $userTable, ['username','user_name','user']);
    $passwordCol = findColumnFromTable($conn, $userTable, ['password','passwd','pass']);
    $accountCol = findColumnFromTable($conn, $userTable, ['account_type','role','type']);
    $deptCol = findColumnLoose($conn, $userTable, ['department_id','dept_id','department','dept','department_name','departments_id']);
    $statusCol = findColumnFromTable($conn, $userTable, ['status','archived','state']);

    // If a new password is provided, handle password reset first
    if ($newpass !== '') {
        if (!$passwordCol) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Password column not found']); exit; }

        // current_password optional field for verification
        $currentPassword = isset($putVars['current_password']) ? $putVars['current_password'] : '';

        // determine target user (by id or username). If none provided try session user
        $targetId = $id ? $id : 0;
        $targetUsername = $username ? $username : '';

        if (!$targetId && !$targetUsername) {
            // try session
            $sessId = null;
            foreach (['user_id','id','uid'] as $k) if (isset($_SESSION[$k])) { $sessId = $_SESSION[$k]; break; }
            if ($sessId) $targetId = (int)$sessId;
        }

        if (!$targetId && !$targetUsername) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'No identifier provided to update']); exit; }

        // fetch target user's current password hash and id/username/account_type
        $whereSql = '';
        $stmtSel = null;
        if ($targetId && $idCol) {
            $whereSql = "`$idCol` = ?";
            $stmtSel = $conn->prepare("SELECT * FROM `$userTable` WHERE $whereSql LIMIT 1");
            $iid = (int)$targetId; $stmtSel->bind_param('i', $iid);
        } elseif ($targetUsername && $usernameCol) {
            $whereSql = "`$usernameCol` = ?";
            $stmtSel = $conn->prepare("SELECT * FROM `$userTable` WHERE $whereSql LIMIT 1");
            $sname = (string)$targetUsername; $stmtSel->bind_param('s', $sname);
        }

        if (!$stmtSel) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Unable to resolve target user']); exit; }
        $stmtSel->execute(); $targetRow = $stmtSel->get_result()->fetch_assoc();
        if (!$targetRow) { http_response_code(404); echo json_encode(['success'=>false,'error'=>'Target user not found']); exit; }

        $storedHash = isset($targetRow[$passwordCol]) ? $targetRow[$passwordCol] : '';

        // determine if session user is admin (so they can reset other users without current password)
        $sessionIsAdmin = false;
        $sessionUserId = null;
        $sessionUsername = null;
        foreach (['user_id','id','uid'] as $k) if (isset($_SESSION[$k])) { $sessionUserId = $_SESSION[$k]; break; }
        foreach (['username','user_name','user'] as $k) if (isset($_SESSION[$k])) { $sessionUsername = $_SESSION[$k]; break; }
        if ($sessionUserId || $sessionUsername) {
            // fetch session user's account_type by whichever identifier we have
            $acctColTmp = findColumnFromTable($conn, $userTable, ['account_type','role','type']);
            if ($acctColTmp) {
                if ($sessionUserId && ctype_digit((string)$sessionUserId) && $idCol) {
                    $stmtA = $conn->prepare("SELECT `$acctColTmp` FROM `$userTable` WHERE `$idCol` = ? LIMIT 1");
                    if ($stmtA) { $iid2 = (int)$sessionUserId; $stmtA->bind_param('i', $iid2); $stmtA->execute(); $ar = $stmtA->get_result()->fetch_row(); if ($ar && isset($ar[0]) && strtolower($ar[0]) === 'admin') $sessionIsAdmin = true; }
                } elseif ($sessionUsername && $usernameCol) {
                    $stmtA = $conn->prepare("SELECT `$acctColTmp` FROM `$userTable` WHERE `$usernameCol` = ? LIMIT 1");
                    if ($stmtA) { $sname2 = (string)$sessionUsername; $stmtA->bind_param('s', $sname2); $stmtA->execute(); $ar = $stmtA->get_result()->fetch_row(); if ($ar && isset($ar[0]) && strtolower($ar[0]) === 'admin') $sessionIsAdmin = true; }
                }
            }
        }

        // Determine if session user is the same as the target (self-change).
        // We have the fetched $targetRow; compare its id/username with the session values for robust detection.
        $isSelfChange = false;
        if ($sessionUserId && $idCol && isset($targetRow[$idCol]) && (int)$sessionUserId === (int)$targetRow[$idCol]) {
            $isSelfChange = true;
        }
        if (!$isSelfChange && $sessionUsername && $usernameCol && isset($targetRow[$usernameCol]) && (string)$sessionUsername === (string)$targetRow[$usernameCol]) {
            $isSelfChange = true;
        }

        // If we don't have any session identifier (no id/username in session), be conservative and require current password
        $forceRequireCurrent = false;
        if (!$sessionUserId && !$sessionUsername) $forceRequireCurrent = true;

        // Require current password when:
        // - session user is not admin, OR
        // - session user is changing their own password (even if admin)
        if ($forceRequireCurrent || !$sessionIsAdmin || $isSelfChange) {
            if ($currentPassword === '') { http_response_code(403); echo json_encode(['success'=>false,'error'=>'Current password required']); exit; }
            if ($storedHash === '' || !password_verify($currentPassword, $storedHash)) { http_response_code(403); echo json_encode(['success'=>false,'error'=>'Current password incorrect']); exit; }
        }

        // All good - update password
        $hash = password_hash($newpass, PASSWORD_DEFAULT);
        if ($targetId && $idCol) {
            $sql = "UPDATE `$userTable` SET `$passwordCol` = ? WHERE `$idCol` = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Prepare failed']); exit; }
            $stmt->bind_param('si', $hash, $targetId);
            $ok = $stmt->execute();
            echo json_encode(['success'=>(bool)$ok]);
            exit;
        }

        if ($targetUsername && $usernameCol) {
            $sql = "UPDATE `$userTable` SET `$passwordCol` = ? WHERE `$usernameCol` = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Prepare failed']); exit; }
            $stmt->bind_param('ss', $hash, $targetUsername);
            $ok = $stmt->execute();
            echo json_encode(['success'=>(bool)$ok]);
            exit;
        }

        http_response_code(500); echo json_encode(['success'=>false,'error'=>'Unable to update password']); exit;
    }

    // Otherwise, handle updates to other fields (account_type, department)
    $updates = [];
    $params = [];
    $types = '';

    // support renaming username
    $newUsername = isset($putVars['new_username']) ? trim($putVars['new_username']) : '';
    if ($newUsername !== '') {
        // check unique
        $uCol = $usernameCol;
        if ($uCol) {
            $sqlChk = "SELECT COUNT(*) FROM `$userTable` WHERE `$uCol` = ?";
            $s = $conn->prepare($sqlChk);
            if ($s) { $s->bind_param('s', $newUsername); $s->execute(); $r = $s->get_result()->fetch_row(); if ($r && $r[0] > 0) { http_response_code(409); echo json_encode(['success'=>false,'error'=>'Username already exists']); exit; } }
        }
        $updates[] = "`$usernameCol` = ?";
        $params[] = $newUsername; $types .= 's';
    }

    // support setting a new password (if provided) together with other updates
    $newPassword = isset($putVars['new_password']) ? $putVars['new_password'] : '';
    if ($newPassword !== '') {
        if (!$passwordCol) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Password column not found']); exit; }
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updates[] = "`$passwordCol` = ?";
        $params[] = $hash; $types .= 's';
    }

    if (isset($putVars['department']) && $deptCol) {
        $rawDept = trim($putVars['department']);
        if ($rawDept !== '') {
            $resolved = resolveDepartment($conn, $rawDept);
            $updates[] = "`$deptCol` = ?";
            if (is_int($resolved)) { $params[] = $resolved; $types .= 'i'; }
            else { $params[] = $resolved; $types .= 's'; }
        }
    }

    // support updating account status (e.g., 'active', 'archived' or numeric flags)
    if (isset($putVars['status']) && $statusCol) {
        $rawStatus = trim($putVars['status']);
        if ($rawStatus !== '') {
            $updates[] = "`$statusCol` = ?";
            $params[] = $rawStatus; $types .= 's';
        }
    }

    if (!count($updates)) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'No updatable fields provided or columns missing']); exit; }

    // Build WHERE clause
    if ($id && $idCol) {
        $whereSql = "`$idCol` = ?"; $whereParam = $id; $whereType = 'i';
    } elseif ($username && $usernameCol) {
        $whereSql = "`$usernameCol` = ?"; $whereParam = $username; $whereType = 's';
    } else {
        http_response_code(400); echo json_encode(['success'=>false,'error'=>'No identifier provided to update']); exit;
    }

    $sql = "UPDATE `$userTable` SET " . implode(', ', $updates) . " WHERE " . $whereSql;
    $stmt = $conn->prepare($sql);
    if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Prepare failed: '.$conn->error]); exit; }

    // bind params + where param
    $bind_names = [];
    $bind_names[] = $types . $whereType;
    // create bind variables for params
    for ($i=0; $i<count($params); $i++) { $bind_name = 'b' . $i; $$bind_name = $params[$i]; $bind_names[] = &$$bind_name; }
    // where param at end
    $bind_where = 'bw'; $$bind_where = $whereParam; $bind_names[] = &$$bind_where;

    call_user_func_array(array($stmt, 'bind_param'), $bind_names);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    echo json_encode(['success' => (bool)$ok, 'affected_rows' => $affected]);
    exit;
}

if ($method === 'DELETE') {
    // parse raw input
    $input = file_get_contents('php://input');
    parse_str($input, $vars);
    $id = isset($vars['id']) ? (int)$vars['id'] : 0;
    $username = isset($vars['username']) ? trim($vars['username']) : '';
    $userTable = 'user';
    $idCol = findColumnFromTable($conn, $userTable, ['id','user_id','UID']);
    $usernameCol = findColumnFromTable($conn, $userTable, ['username','user_name','user']);
    if ($id && $idCol) {
        $sql = "DELETE FROM `$userTable` WHERE `$idCol` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok]);
        exit;
    }
    if ($username && $usernameCol) {
        $sql = "DELETE FROM `$userTable` WHERE `$usernameCol` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $username);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok]);
        exit;
    }
    http_response_code(400); echo json_encode(['success'=>false,'error'=>'No identifier provided for delete']); exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'error'=>'Method not allowed']);

?>
