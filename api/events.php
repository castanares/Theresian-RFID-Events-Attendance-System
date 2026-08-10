<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/dbcon.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Database connection not available']);
    exit;
}

$table = 'events';

function get_table_columns($conn, $table) {
    $cols = [];
    $res = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($table) . "`");
    if ($res) while ($r = $res->fetch_assoc()) $cols[] = $r['Field'];
    return $cols;
}

function find_column($cols, $candidates) {
    foreach ($candidates as $c) {
        foreach ($cols as $col) {
            if (strcasecmp($col, $c) === 0) return $col;
        }
    }
    return null;
}

function find_table_like($conn, $like) {
    $likeEsc = $conn->real_escape_string('%' . $like . '%');
    $res = $conn->query("SHOW TABLES LIKE '" . $likeEsc . "'");
    if ($res && $row = $res->fetch_row()) return $row[0];
    // scan
    $res2 = $conn->query("SHOW TABLES");
    if ($res2) while ($r = $res2->fetch_row()) if (stripos($r[0], $like) !== false) return $r[0];
    return null;
}

function lookup_semester_id($conn, $val) {
    if ($val === null || $val === '') return null;
    // numeric id
    if (is_numeric($val)) return (int)$val;
    $table = find_table_like($conn, 'semester');
    if (!$table) return null;
    $cols = get_table_columns($conn, $table);
    $idCol = find_column($cols, ['id','semester_id','Semester_ID']) ?: $cols[0];
    $nameCol = find_column($cols, ['semester','name','term','title']) ?: ($cols[1] ?? $cols[0]);
    // exact
    $sql = "SELECT `$idCol` FROM `$table` WHERE `$nameCol` = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt) { $stmt->bind_param('s', $val); $stmt->execute(); $r = $stmt->get_result()->fetch_row(); if ($r) return $r[0]; }
    // try partial
    $sql2 = "SELECT `$idCol` FROM `$table` WHERE `$nameCol` LIKE ? LIMIT 1";
    $stmt2 = $conn->prepare($sql2);
    if ($stmt2) { $like = "%".$val."%"; $stmt2->bind_param('s',$like); $stmt2->execute(); $r2 = $stmt2->get_result()->fetch_row(); if ($r2) return $r2[0]; }
    return null;
}

function lookup_department_ids($conn, $values) {
    // values: array of strings or numeric ids
    $out = [];
    if (!is_array($values)) return [];
    $table = find_table_like($conn, 'depart');
    // if no departments table, return any numeric ids and ignore others
    if (!$table) {
        foreach ($values as $v) if (is_numeric($v)) $out[] = (int)$v;
        return $out;
    }
    $cols = get_table_columns($conn, $table);
    $idCol = find_column($cols, ['id','Dept_ID','dept_id','ID']) ?: $cols[0];
    $nameCols = [];
    foreach (['dept_acronym','dept_Acronym','acronym','dept_name','Dept_name','name'] as $c) if (in_array($c,$cols)) $nameCols[] = $c;
    if (empty($nameCols)) $nameCols[] = $cols[1] ?? $cols[0];
    foreach ($values as $val) {
        $v = trim($val);
        if ($v === '') continue;
        if (is_numeric($v)) { $out[] = (int)$v; continue; }
        $found = null;
        foreach ($nameCols as $nc) {
            $sql = "SELECT `$idCol` FROM `$table` WHERE LOWER(`$nc`) = LOWER(?) LIMIT 1";
            $stmt = $conn->prepare($sql);
            if (!$stmt) continue;
            $stmt->bind_param('s', $v);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_row(); if ($r) { $found = $r[0]; break; }
        }
        if ($found === null) {
            foreach ($nameCols as $nc) {
                $sql = "SELECT `$idCol` FROM `$table` WHERE LOWER(`$nc`) LIKE LOWER(?) LIMIT 1";
                $stmt = $conn->prepare($sql);
                if (!$stmt) continue;
                $like = "%".$v."%";
                $stmt->bind_param('s', $like);
                $stmt->execute();
                $r = $stmt->get_result()->fetch_row(); if ($r) { $found = $r[0]; break; }
            }
        }
        if ($found !== null) $out[] = $found;
    }
    return array_values(array_unique($out));
}

function resolve_session_user_id($conn) {
    // prefer numeric session values
    $keys = ['user_id','id','uid','userId','username'];
    foreach ($keys as $k) {
        if (isset($_SESSION[$k])) {
            if (is_numeric($_SESSION[$k])) return (int)$_SESSION[$k];
            // if string, try to find numeric id in users table
            $userTable = find_table_like($conn, 'user');
            if (!$userTable) $userTable = 'user';
            $cols = get_table_columns($conn, $userTable);
            $idCol = find_column($cols, ['id','user_id','UID']) ?: ($cols[0] ?? null);
            $nameCol = find_column($cols, ['username','user_name','user','email']) ?: ($cols[1] ?? null);
            if ($idCol && $nameCol) {
                $sql = "SELECT `$idCol` FROM `$userTable` WHERE `$nameCol` = ? LIMIT 1";
                $stmt = $conn->prepare($sql);
                if ($stmt) { $stmt->bind_param('s', $_SESSION[$k]); $stmt->execute(); $r = $stmt->get_result()->fetch_row(); if ($r) return (int)$r[0]; }
            }
        }
    }
    return null;
}

$dbCols = get_table_columns($conn, $table);

// build a column map for common names
$mapCandidates = [
    'id' => ['id','event_id'],
    'user_id' => ['user_id','user','userId'],
    'event_name' => ['event_name','name','title'],
    'date' => ['date','event_date','eventDate'],
    'semester_id' => ['semester_id','semester'],
    'departments_id' => ['departments_id','department_id','dept_id','departmentsid'],
    'TimeIN_in' => ['TimeIn_start','timein_start','time_in_start','time_in'],
    'TimeIN_out' => ['TimeIn_end','timein_end','time_in_end'],
    'TimeOUT_in' => ['Time_out','time_out','timeout'],
    'TimeOUT_out' => ['Timeout_out','timeout_out','time_out2','time_out_end'],
    'status' => ['status','state','archived','is_archived'],
    'timestamp' => ['timestamp','created_at','created','date_created']
];

$colMap = [];
foreach ($mapCandidates as $key => $cands) $colMap[$key] = find_column($dbCols, $cands);

function normalize_row($row, $colMap) {
    global $conn;
    $out = [];
    foreach ($colMap as $k => $c) {
        $out[$k] = ($c && array_key_exists($c, $row)) ? $row[$c] : null;
    }
    foreach ($row as $k => $v) if (!in_array($k, $colMap)) $out[$k] = $v;

    // prefer normalized join table for other_departments
    $out['other_departments'] = [];
    $out['other_departments_info'] = [];
    $eventId = null;
    $idColName = $colMap['id'] ?? null;
    if ($idColName && array_key_exists($idColName, $row)) $eventId = $row[$idColName];
    if ($eventId) {
        $mapping = find_table_like($conn, 'event_other_depart');
        if (!$mapping) $mapping = find_table_like($conn, 'event_other_departments');
        if ($mapping) {
            $mcols = get_table_columns($conn, $mapping);
            $eventCol = find_column($mcols, ['event_id','eventId','evt_id','ev_id']) ?: $mcols[1] ?? $mcols[0];
            $deptCol = find_column($mcols, ['department_id','dept_id','departmentid','deptids']) ?: $mcols[2] ?? $mcols[1] ?? $mcols[0];
            $eid = (int)$eventId;
            $q = $conn->query("SELECT `$deptCol` FROM `" . $conn->real_escape_string($mapping) . "` WHERE `$eventCol` = " . $eid);
            $ids = [];
            if ($q) while ($r = $q->fetch_row()) $ids[] = (int)$r[0];
            $out['other_departments'] = $ids;
            if (!empty($ids)) {
                $deptTable = find_table_like($conn, 'depart');
                if ($deptTable) {
                    $cols = get_table_columns($conn, $deptTable);
                    $idCol2 = find_column($cols, ['id','Dept_ID','dept_id','ID']) ?: $cols[0];
                    $in = implode(',', array_map('intval', $ids));
                    $q2 = $conn->query("SELECT * FROM `" . $conn->real_escape_string($deptTable) . "` WHERE `$idCol2` IN ($in)");
                    $rows = [];
                    if ($q2) while ($r = $q2->fetch_assoc()) $rows[] = $r;
                    $out['other_departments_info'] = $rows;
                }
            }
        }
    }

    // resolve primary department name
    if (isset($out['departments_id']) && $out['departments_id']) {
        $deptTable = find_table_like($conn, 'depart');
        if ($deptTable) {
            $cols = get_table_columns($conn, $deptTable);
            $idCol = find_column($cols, ['id','Dept_ID','dept_id','ID']) ?: $cols[0];
            $acronymCol = find_column($cols, ['dept_Acronym','acronym','Acronym']) ?: $cols[2] ?? $cols[1] ?? $cols[0];
            $stmt = $conn->prepare("SELECT `$acronymCol` FROM `$deptTable` WHERE `$idCol` = ?");
            $stmt->bind_param('i', $out['departments_id']);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $out['department_name'] = $r ? $r[$acronymCol] : 'Unknown';
        } else {
            $out['department_name'] = 'Unknown';
        }
    } else {
        $out['department_name'] = 'Unknown';
    }

    // count participants
    if ($eventId) {
        $attTable = find_table_like($conn, 'attendance');
        if ($attTable) {
            $cols = get_table_columns($conn, $attTable);
            $eventCol = find_column($cols, ['Event_ID','event_id','evt_id']) ?: $cols[1] ?? $cols[0];
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM `$attTable` WHERE `$eventCol` = ?");
            $stmt->bind_param('i', $eventId);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $out['participants'] = $r ? (int)$r['count'] : 0;
        } else {
            $out['participants'] = 0;
        }
    } else {
        $out['participants'] = 0;
    }

    // resolve semester name
    if (isset($out['semester_id']) && $out['semester_id']) {
        $semTable = find_table_like($conn, 'semester');
        if ($semTable) {
            $cols = get_table_columns($conn, $semTable);
            $idCol = find_column($cols, ['id']) ?: 'id';
            $nameCol = find_column($cols, ['semester','name']) ?: 'semester';
            $stmt = $conn->prepare("SELECT `$nameCol` FROM `$semTable` WHERE `$idCol` = ?");
            $stmt->bind_param('i', $out['semester_id']);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $out['semester'] = $r ? $r[$nameCol] : '';
        }
    }

    return $out;
}

$method = $_SERVER['REQUEST_METHOD'];
try {
    if ($method === 'GET') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $idCol = $colMap['id'] ?: 'id';
            $stmt = $conn->prepare("SELECT * FROM `$table` WHERE `$idCol` = ? LIMIT 1");
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            if ($r) {
                $out = normalize_row($r, $colMap);
                // add other departments
                $mapping = find_table_like($conn, 'event_other_depart');
                if (!$mapping) $mapping = find_table_like($conn, 'event_other_departments');
                if ($mapping) {
                    $mcols = get_table_columns($conn, $mapping);
                    $eventCol = find_column($mcols, ['event_id','eventId','evt_id','ev_id']) ?: $mcols[1] ?? $mcols[0];
                    $deptCol = find_column($mcols, ['department_id','dept_id','departmentid','deptids']) ?: $mcols[2] ?? $mcols[1] ?? $mcols[0];
                    $stmt2 = $conn->prepare("SELECT `$deptCol` FROM `" . $conn->real_escape_string($mapping) . "` WHERE `$eventCol` = ?");
                    if ($stmt2) {
                        $stmt2->bind_param('i', $id);
                        $stmt2->execute();
                        $res2 = $stmt2->get_result();
                        $out['other_departments'] = [];
                        while ($r2 = $res2->fetch_assoc()) $out['other_departments'][] = (int)$r2[$deptCol];
                        $stmt2->close();
                    }
                }
                echo json_encode(['success'=>true,'data'=>$out]);
            } else {
                echo json_encode(['success'=>false,'error'=>'Not found']);
            }
            exit;
        }
        // optional: return only events belonging to the current session user
        $mine = isset($_GET['mine']) ? true : false;
        $filterUserId = null;
        if ($mine) {
            $uid = resolve_session_user_id($conn);
            if ($uid === null) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Not authenticated']); exit; }
            $filterUserId = (int)$uid;
            $userCol = $colMap['user_id'] ?? 'user_id';
            $stmt = $conn->prepare("SELECT * FROM `$table` WHERE `$userCol` = ? ORDER BY " . ($colMap['timestamp'] ?: ($colMap['id'] ?: 'id')) . " DESC");
            if (!$stmt) throw new Exception($conn->error);
            $stmt->bind_param('i', $filterUserId);
            $stmt->execute();
            $res = $stmt->get_result();
            $out = [];
            while ($r = $res->fetch_assoc()) $out[] = normalize_row($r,$colMap);
            echo json_encode(['success'=>true,'data'=>$out]); exit;
        }
        // optional status filter
        $statusFilter = isset($_GET['status']) ? $_GET['status'] : null;
        if ($statusFilter) {
            $statusCol = $colMap['status'] ?: 'status';
            $q = $conn->query("SELECT * FROM `$table` WHERE `$statusCol` = '" . $conn->real_escape_string($statusFilter) . "' ORDER BY " . ($colMap['timestamp'] ?: ($colMap['id'] ?: 'id')) . " DESC");
        } else {
            // list all
            $q = $conn->query("SELECT * FROM `$table` ORDER BY " . ($colMap['timestamp'] ?: ($colMap['id'] ?: 'id')) . " DESC");
        }
        if (!$q) throw new Exception($conn->error);
        $out = [];
        while ($r = $q->fetch_assoc()) $out[] = normalize_row($r,$colMap);
        echo json_encode(['success'=>true,'data'=>$out]); exit;
    }

    if ($method === 'POST') {
        // accept JSON body or form
        $input = [];
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) $input = $json; else $input = $_POST;

        $id = $_GET['id'] ?? $input['id'] ?? null;
        if ($id) {
            // UPDATE existing event
            $fields = [];
            // copy known simple mappings
            foreach ($colMap as $key => $col) {
                if (!$col) continue;
                if (isset($input[$key])) $fields[$col] = $input[$key];
            }

            // semester resolution if provided as name
            if (isset($input['semester']) && ($input['semester'] !== '')) {
                $semId = lookup_semester_id($conn, $input['semester']);
                if ($semId !== null) {
                    if ($colMap['semester_id']) $fields[$colMap['semester_id']] = $semId; else $fields['semester_id'] = $semId;
                }
            }

            // departments: accept array or comma-separated string
            $deptInputs = [];
            if (isset($input['departments']) && is_array($input['departments'])) $deptInputs = $input['departments'];
            elseif (isset($input['departments_id']) && $input['departments_id']) $deptInputs = array_map('trim', explode(',', $input['departments_id']));

            $deptIds = [];
            if (!empty($deptInputs)) {
                $deptTable = find_table_like($conn, 'depart');
                if ($deptTable) {
                    $cols = get_table_columns($conn, $deptTable);
                    $idCol = find_column($cols, ['id','Dept_ID','dept_id','ID']) ?: $cols[0];
                    $nameCol = find_column($cols, ['Dept_name','name','dept_name','Name']) ?: $cols[1] ?? $cols[0];
                    $acroCol = find_column($cols, ['dept_Acronym','acronym','acro','Acronym']) ?: $cols[2] ?? $cols[1] ?? $cols[0];
                    foreach ($deptInputs as $d) {
                        $d = trim($d);
                        if (!$d) continue;
                        // if numeric, assume id
                        if (is_numeric($d)) { $deptIds[] = (int)$d; continue; }
                        // else try to find by acronym or name
                        $sql = "SELECT `$idCol` FROM `$deptTable` WHERE `$acroCol` = ? OR `$nameCol` = ? LIMIT 1";
                        $stmt = $conn->prepare($sql);
                        if ($stmt) { $stmt->bind_param('ss', $d, $d); $stmt->execute(); $r = $stmt->get_result()->fetch_assoc(); if ($r) $deptIds[] = (int)$r[$idCol]; $stmt->close(); }
                    }
                }
            }

            // Determine primary department
            $primaryDept = !empty($deptIds) ? $deptIds[0] : null;
            if ($primaryDept !== null && $colMap['departments_id']) {
                $fields[$colMap['departments_id']] = (string)$primaryDept;
            }

            // For mapping, exclude primary
            $otherDeptIds = [];
            if (!empty($deptIds)) {
                foreach ($deptIds as $did) { $d = (int)$did; if ($primaryDept === null || $d !== $primaryDept) $otherDeptIds[] = $d; }
            }

            if (empty($fields) && empty($otherDeptIds)) throw new Exception('No valid fields provided');

            $idCol = $colMap['id'] ?: 'id';
            $setParts = [];
            $types = '';
            $vals = [];
            foreach ($fields as $c => $v) { $setParts[] = "`$c` = ?"; $types .= 's'; $vals[] = (string)$v; }
            $types .= 'i'; $vals[] = (int)$id;
            $sql = "UPDATE `$table` SET " . implode(', ', $setParts) . " WHERE `$idCol` = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception($conn->error);
            $stmt->bind_param($types, ...$vals);
            $ok = $stmt->execute(); if (!$ok) throw new Exception($stmt->error ?: $conn->error);

            // update departments: first clear existing other departments, then insert new
            $mapping = find_table_like($conn, 'event_other_depart');
            if (!$mapping) $mapping = find_table_like($conn, 'event_other_departments');
            if ($mapping) {
                $mcols = get_table_columns($conn, $mapping);
                $eventCol = find_column($mcols, ['event_id','eventId','evt_id','ev_id']) ?: $mcols[1] ?? $mcols[0];
                $deptCol = find_column($mcols, ['department_id','dept_id','departmentid','deptids']) ?: $mcols[2] ?? $mcols[1] ?? $mcols[0];
                // delete existing
                $del = $conn->prepare("DELETE FROM `" . $conn->real_escape_string($mapping) . "` WHERE `$eventCol` = ?");
                if ($del) { $del->bind_param('i', $id); $del->execute(); $del->close(); }
                // insert new
                if (!empty($otherDeptIds)) {
                    $ins = $conn->prepare("INSERT INTO `" . $conn->real_escape_string($mapping) . "` (`$eventCol`,`$deptCol`) VALUES (?,?)");
                    if ($ins) {
                        foreach ($otherDeptIds as $did) { $d = (int)$did; $ins->bind_param('ii', $id, $d); $ins->execute(); }
                        $ins->close();
                    }
                }
            }

            echo json_encode(['success'=>true,'id'=>$id]); exit;
        } else {
            // INSERT new event (existing code)
        // copy known simple mappings
        foreach ($colMap as $key => $col) {
            if (!$col) continue;
            if (isset($input[$key])) $fields[$col] = $input[$key];
        }

        // semester resolution
        if (isset($input['semester']) && ($input['semester'] !== '')) {
            $semId = lookup_semester_id($conn, $input['semester']);
            if ($semId !== null) {
                if ($colMap['semester_id']) $fields[$colMap['semester_id']] = $semId; else $fields['semester_id'] = $semId;
            }
        }

        // departments: accept array or comma-separated string
        $deptInputs = [];
        if (isset($input['departments']) && is_array($input['departments'])) $deptInputs = $input['departments'];
        elseif (isset($input['departments_id']) && $input['departments_id']) $deptInputs = array_map('trim', explode(',', $input['departments_id']));

        // If no department info provided in the request, try to load the current user's department(s)
        if (empty($deptInputs)) {
            // try to resolve session user
            $sess = resolve_session_user_id($conn);
            $userTable = find_table_like($conn, 'user');
            if ($userTable) {
                $cols = get_table_columns($conn, $userTable);
                $userIdCol = find_column($cols, ['id','user_id','uid','ID']);
                $userNameCol = find_column($cols, ['username','user_name','user','email','account_name','name']);
                $userDeptCol = find_column($cols, ['departments_id','department_id','dept_id','department','dept']);
                $foundDeptVal = null;
                if ($sess !== null && $userIdCol) {
                    $sqlu = "SELECT `" . $conn->real_escape_string($userDeptCol) . "` FROM `" . $conn->real_escape_string($userTable) . "` WHERE `" . $conn->real_escape_string($userIdCol) . "` = ? LIMIT 1";
                    $st = $conn->prepare($sqlu);
                    if ($st) { $st->bind_param('i', $sess); $st->execute(); $ru = $st->get_result()->fetch_assoc(); if ($ru && array_key_exists($userDeptCol, $ru)) $foundDeptVal = $ru[$userDeptCol]; $st->close(); }
                }
                // fallback: try using raw session value against username/email
                if ($foundDeptVal === null && isset($_SESSION['user_id']) && is_string($_SESSION['user_id']) && $userNameCol) {
                    $sqlu2 = "SELECT `" . $conn->real_escape_string($userDeptCol) . "` FROM `" . $conn->real_escape_string($userTable) . "` WHERE `" . $conn->real_escape_string($userNameCol) . "` = ? LIMIT 1";
                    $st2 = $conn->prepare($sqlu2);
                    if ($st2) { $st2->bind_param('s', $_SESSION['user_id']); $st2->execute(); $ru2 = $st2->get_result()->fetch_assoc(); if ($ru2 && array_key_exists($userDeptCol, $ru2)) $foundDeptVal = $ru2[$userDeptCol]; $st2->close(); }
                }
                if ($foundDeptVal !== null) {
                    if (is_array($foundDeptVal)) $deptInputs = array_map('strval', $foundDeptVal);
                    else $deptInputs = array_filter(array_map('trim', explode(',', strval($foundDeptVal))), 'strlen');
                }
            }
        }
        $deptIds = [];
        if (!empty($deptInputs)) {
            $deptIds = lookup_department_ids($conn, $deptInputs);
            if (empty($deptIds)) {
                http_response_code(400); echo json_encode(['success'=>false,'error'=>'Could not resolve any department ids']); exit;
            }
        }

        // Determine primary department (owner's department) to store in `departments_id` column.
        $primaryDept = null;
        // Try to resolve from session user first
        $sess = resolve_session_user_id($conn);
        if ($sess !== null) {
            $userTable = find_table_like($conn, 'user');
            if ($userTable) {
                $ucols = get_table_columns($conn, $userTable);
                $userIdCol = find_column($ucols, ['id','user_id','uid','ID']);
                $userDeptCol = find_column($ucols, ['departments_id','department_id','dept_id','department','dept']);
                if ($userIdCol && $userDeptCol) {
                    $sqlu = "SELECT `" . $conn->real_escape_string($userDeptCol) . "` FROM `" . $conn->real_escape_string($userTable) . "` WHERE `" . $conn->real_escape_string($userIdCol) . "` = ? LIMIT 1";
                    $st = $conn->prepare($sqlu);
                    if ($st) { $st->bind_param('i', $sess); $st->execute(); $ru = $st->get_result()->fetch_assoc(); if ($ru && array_key_exists($userDeptCol, $ru)) {
                        $found = $ru[$userDeptCol];
                        $resolved = lookup_department_ids($conn, is_array($found) ? $found : array_filter(array_map('trim', explode(',', strval($found))), 'strlen'));
                        if (!empty($resolved)) $primaryDept = (int)$resolved[0];
                    } $st->close(); }
                }
            }
        }

        // If we still don't have a primary department, try to derive from provided deptIds (use first)
        if ($primaryDept === null && !empty($deptIds)) {
            $primaryDept = (int)$deptIds[0];
        }

        // store primary department as single int (legacy column). If no primary, skip.
        if ($primaryDept !== null) {
            if ($colMap['departments_id']) $fields[$colMap['departments_id']] = (string)$primaryDept; else $fields['departments_id'] = (string)$primaryDept;
        }

        // For normalized mapping, only keep other departments (exclude primary)
        $otherDeptIds = [];
        if (!empty($deptIds)) {
            foreach ($deptIds as $did) { $d = (int)$did; if ($primaryDept === null || $d !== (int)$primaryDept) $otherDeptIds[] = $d; }
        }

        // user id from session
        $sess = resolve_session_user_id($conn);
        if ($sess !== null) {
            if ($colMap['user_id']) $fields[$colMap['user_id']] = $sess; else $fields['user_id'] = $sess;
        }

        if (empty($fields)) throw new Exception('No valid fields provided');

        $cols = array_keys($fields);
        $place = array_fill(0, count($cols), '?');
        $types = '';
        $vals = [];
        foreach ($cols as $c) { $types .= 's'; $vals[] = (string)$fields[$c]; }
        $sql = "INSERT INTO `$table` (`" . implode('`,`',$cols) . "`) VALUES (" . implode(',',$place) . ")";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param($types, ...$vals);
        $ok = $stmt->execute(); if (!$ok) throw new Exception($stmt->error ?: $conn->error);
        $newId = $conn->insert_id;

        // store selected other departments into normalized join table `event_other_departments`
        $stored_other = null;
        if (!isset($deptIds) || !is_array($deptIds)) $deptIds = [];
        if (!empty($deptIds)) {
            $mappingName = 'event_other_departments';
            $createSql = "CREATE TABLE IF NOT EXISTS `" . $conn->real_escape_string($mappingName) . "` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `event_id` INT NOT NULL,
                `department_id` INT NOT NULL,
                INDEX (`event_id`),
                INDEX (`department_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $conn->query($createSql);
            $mapping = find_table_like($conn, 'event_other_depart');
            if (!$mapping) $mapping = find_table_like($conn, 'event_other_departments');
            if ($mapping) {
                $mcols = get_table_columns($conn, $mapping);
                $eventCol = find_column($mcols, ['event_id','eventId','evt_id','ev_id']) ?: $mcols[1] ?? $mcols[0];
                $deptCol = find_column($mcols, ['department_id','dept_id','departmentid','deptids']) ?: $mcols[2] ?? $mcols[1] ?? $mcols[0];
                $ins = $conn->prepare("INSERT IGNORE INTO `" . $conn->real_escape_string($mapping) . "` (`$eventCol`,`$deptCol`) VALUES (?,?)");
                if ($ins) {
                    foreach ($deptIds as $did) { $d = (int)$did; $ins->bind_param('ii', $newId, $d); $ins->execute(); }
                    $stored_other = $deptIds;
                }
            }
        }

        echo json_encode(['success'=>true,'id'=>$newId,'stored_other_departments'=>$stored_other]); exit;
    }
    }

    if ($method === 'PUT') {
        // accept JSON body or form
        $input = [];
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) $input = $json; else parse_str($raw, $input);

        $id = $input['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Missing id']); exit; }

        $fields = [];
        // copy known simple mappings
        foreach ($colMap as $key => $col) {
            if (!$col) continue;
            if (isset($input[$key])) $fields[$col] = $input[$key];
        }

        // semester resolution if provided as name
        if (isset($input['semester']) && ($input['semester'] !== '')) {
            $semId = lookup_semester_id($conn, $input['semester']);
            if ($semId !== null) {
                if ($colMap['semester_id']) $fields[$colMap['semester_id']] = $semId; else $fields['semester_id'] = $semId;
            }
        }

        // departments: accept array or comma-separated string
        $deptInputs = [];
        if (isset($input['departments']) && is_array($input['departments'])) $deptInputs = $input['departments'];
        elseif (isset($input['departments_id']) && $input['departments_id']) $deptInputs = array_map('trim', explode(',', $input['departments_id']));

        $deptIds = [];
        if (!empty($deptInputs)) {
            $deptTable = find_table_like($conn, 'depart');
            if ($deptTable) {
                $cols = get_table_columns($conn, $deptTable);
                $idCol = find_column($cols, ['id','Dept_ID','dept_id','ID']) ?: $cols[0];
                $nameCol = find_column($cols, ['Dept_name','name','dept_name','Name']) ?: $cols[1] ?? $cols[0];
                $acroCol = find_column($cols, ['dept_Acronym','acronym','acro','Acronym']) ?: $cols[2] ?? $cols[1] ?? $cols[0];
                foreach ($deptInputs as $d) {
                    $d = trim($d);
                    if (!$d) continue;
                    // if numeric, assume id
                    if (is_numeric($d)) { $deptIds[] = (int)$d; continue; }
                    // else try to find by acronym or name
                    $sql = "SELECT `$idCol` FROM `$deptTable` WHERE `$acroCol` = ? OR `$nameCol` = ? LIMIT 1";
                    $stmt = $conn->prepare($sql);
                    if ($stmt) { $stmt->bind_param('ss', $d, $d); $stmt->execute(); $r = $stmt->get_result()->fetch_assoc(); if ($r) $deptIds[] = (int)$r[$idCol]; $stmt->close(); }
                }
            }
        }

        if (empty($fields) && empty($deptIds)) throw new Exception('No valid fields provided');

        $idCol = $colMap['id'] ?: 'id';
        $setParts = [];
        $types = '';
        $vals = [];
        foreach ($fields as $c => $v) { $setParts[] = "`$c` = ?"; $types .= 's'; $vals[] = (string)$v; }
        $sql = "UPDATE `$table` SET " . implode(', ', $setParts) . " WHERE `$idCol` = ?";
        $types .= 's';
        $vals[] = (string)$id;
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param($types, ...$vals);
        $ok = $stmt->execute(); if (!$ok) throw new Exception($stmt->error ?: $conn->error);

        // update other_departments: first delete existing, then insert new
        $mapping = find_table_like($conn, 'event_other_depart');
        if (!$mapping) $mapping = find_table_like($conn, 'event_other_departments');
        if ($mapping) {
            $mcols = get_table_columns($conn, $mapping);
            $eventCol = find_column($mcols, ['event_id','eventId','evt_id','ev_id']) ?: $mcols[1] ?? $mcols[0];
            $deptCol = find_column($mcols, ['department_id','dept_id','departmentid','deptids']) ?: $mcols[2] ?? $mcols[1] ?? $mcols[0];
            $conn->query("DELETE FROM `" . $conn->real_escape_string($mapping) . "` WHERE `$eventCol` = " . (int)$id);
            if (!empty($deptIds)) {
                $ins = $conn->prepare("INSERT INTO `" . $conn->real_escape_string($mapping) . "` (`$eventCol`,`$deptCol`) VALUES (?,?)");
                if ($ins) {
                    foreach ($deptIds as $did) { $ins->bind_param('ii', $id, $did); $ins->execute(); }
                }
            }
        }

        echo json_encode(['success'=>true]); exit;
    }

    if ($method === 'DELETE') {
        parse_str(file_get_contents('php://input'), $body);
        $id = $body['id'] ?? null; if (!$id) throw new Exception('Missing id');
        // try to archive via status column
        if ($colMap['status']) { $statusCol = $colMap['status']; $stmt = $conn->prepare("UPDATE `$table` SET `$statusCol` = 'archived' WHERE `" . ($colMap['id'] ?: 'id') . "` = ?"); $stmt->bind_param('s',$id); $stmt->execute(); echo json_encode(['success'=>true,'archived_id'=>$id]); exit; }
        // fallback delete
        $stmt = $conn->prepare("DELETE FROM `$table` WHERE `" . ($colMap['id'] ?: 'id') . "` = ?"); $stmt->bind_param('s',$id); $stmt->execute(); echo json_encode(['success'=>true,'deleted_id'=>$id]); exit;
    }

    http_response_code(405); echo json_encode(['success'=>false,'error'=>'Method not allowed']);

} catch (Exception $e) {
    http_response_code(400); echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}

?>
