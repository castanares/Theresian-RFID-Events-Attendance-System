<?php
require_once __DIR__ . '/dbcon.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection not available']);
    exit;
}

// Detect the actual table name to use (plural/singular or any table containing 'semester').
$table = null;
$candidates = ['semesters', 'semester'];
foreach ($candidates as $t) {
    $chk = @$conn->query("SHOW TABLES LIKE '$t'");
    if ($chk && $chk->num_rows > 0) { $table = $t; break; }
}
if (!$table) {
    $chk = @$conn->query("SHOW TABLES");
    if ($chk) {
        while ($row = $chk->fetch_array()) {
            foreach ($row as $val) {
                if (is_string($val) && stripos($val, 'semester') !== false) { $table = $val; break 2; }
            }
        }
    }
}
if (!$table) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'semesters table not found — please verify your database contains a table named like "semesters" or "semester"']);
    exit;
}

function findColumnAny($conn, $table, array $candidates) {
    $res = @$conn->query("SHOW COLUMNS FROM `" . $table . "`");
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

$idCol = findColumnAny($conn, $table, ['id','semester_id','Semester_ID']);
// prefer exact column names you provided: 'semester', 'start', 'end'
$nameCol = findColumnAny($conn, $table, ['semester','name','semester_name','title','label']);
$startCol = findColumnAny($conn, $table, ['start','start_date','date_start','begin']);
$endCol = findColumnAny($conn, $table, ['end','end_date','date_end','finish']);
$archCol = findColumnAny($conn, $table, ['archived','is_archived']);

// Helper: convert month-only (MM) or various date formats into canonical YYYY-MM-DD with year 2000
function canonicalize_month_for_db($val, $isEnd = false) {
    $v = trim((string)$val);
    if ($v === '') return null;
    // MM
    if (preg_match('/^\d{2}$/', $v)) {
        $mm = (int)$v;
        if ($isEnd) {
            $day = date('t', mktime(0,0,0,$mm,1,2000));
        } else {
            $day = 1;
        }
        return sprintf('2000-%02d-%02d', $mm, $day);
    }
    // YYYY-MM
    if (preg_match('/^\d{4}-(\d{2})$/', $v, $m)) {
        $mm = (int)$m[1];
        $day = $isEnd ? date('t', mktime(0,0,0,$mm,1,2000)) : 1;
        return sprintf('2000-%02d-%02d', $mm, $day);
    }
    // YYYY-MM-DD
    if (preg_match('/^\d{4}-(\d{2})-(\d{2})$/', $v, $m)) {
        return sprintf('2000-%02d-%02d', (int)$m[1], (int)$m[2]);
    }
    // fallback: return original
    return $v;
}

// Helper: extract month-only (MM) from stored date or month formats for responses
function month_only_from_stored($val) {
    $v = trim((string)$val);
    if ($v === '') return '';
    if (preg_match('/^(\d{2})$/', $v, $m)) return $m[1];
    if (preg_match('/^\d{4}-(\d{2})-(\d{2})$/', $v, $m)) return $m[1];
    if (preg_match('/^\d{4}-(\d{2})$/', $v, $m)) return $m[1];
    // last resort: try to extract any two-digit month
    if (preg_match('/(\d{2})/', $v, $m)) return $m[1];
    return '';
}

// aliases for flexible input mapping (client may send different param names)
$nameAliases = ['name','semester','semester_name','title','label'];
$startAliases = ['start_date','start','date_start','begin','from_date','from'];
$endAliases = ['end_date','end','date_end','finish','to_date','to'];

function extractFirst(array $aliases, array $source) {
    foreach ($aliases as $a) {
        if (isset($source[$a]) && $source[$a] !== '') return trim($source[$a]);
    }
    return null;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // optional ?id= or ?archived=
    $id = isset($_GET['id']) ? trim($_GET['id']) : '';
    $archived = isset($_GET['archived']) ? (int)$_GET['archived'] : null;

    $selectCols = [];
    if ($idCol) $selectCols[] = "`$idCol`";
    if ($nameCol) $selectCols[] = "`$nameCol`";
    if ($startCol) $selectCols[] = "`$startCol`";
    if ($endCol) $selectCols[] = "`$endCol`";
    if ($archCol) $selectCols[] = "`$archCol`";
    $select = count($selectCols) ? implode(', ', $selectCols) : '*';

    if ($id) {
        // fetch single
        if ($idCol) {
            $sql = "SELECT $select FROM `" . $table . "` WHERE `$idCol` = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $id);
        } else {
            http_response_code(400); echo json_encode(['success'=>false,'error'=>'ID column not found']); exit;
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $r = $res->fetch_assoc();
        if (!$r) { echo json_encode(['success'=>true,'data'=>null]); exit; }
        $out = [];
        if ($idCol && isset($r[$idCol])) $out['id'] = $r[$idCol];
    if ($nameCol && isset($r[$nameCol])) $out['name'] = $r[$nameCol];
    if ($startCol && isset($r[$startCol])) $out['start_date'] = month_only_from_stored($r[$startCol]);
    if ($endCol && isset($r[$endCol])) $out['end_date'] = month_only_from_stored($r[$endCol]);
        if ($archCol && isset($r[$archCol])) $out['archived'] = (int)$r[$archCol];

        // fallback: if name missing, attempt to find a suitable column from the raw row
        if (empty($out['name'])) {
            foreach ($r as $k => $v) {
                if (preg_match('/semester|term|name|title|label/i', $k) && $v !== null && $v !== '') { $out['name'] = $v; break; }
            }
        }
        if (empty($out['name'])) {
            foreach ($r as $k => $v) {
                $low = strtolower($k);
                if ($low === strtolower($idCol) || $low === strtolower($startCol) || $low === strtolower($endCol) || $low === strtolower($archCol)) continue;
                if ($v !== null && $v !== '' && is_string($v)) { $out['name'] = $v; break; }
            }
        }

    echo json_encode(['success'=>true,'data'=>$out]); exit;
    }

    // list
    $where = '';
    $params = [];
    $types = '';
    if ($archived !== null && $archCol) {
        $where = " WHERE `$archCol` = ?";
        $params[] = $archived; $types .= 'i';
    }
    $sql = "SELECT $select FROM `" . $table . "`" . $where;
    if ($idCol) $sql .= " ORDER BY `$idCol` DESC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Prepare failed']); exit; }
    if (count($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $out = [];
        if ($idCol && isset($r[$idCol])) $out['id'] = $r[$idCol];
    if ($nameCol && isset($r[$nameCol])) $out['name'] = $r[$nameCol];
    if ($startCol && isset($r[$startCol])) $out['start_date'] = month_only_from_stored($r[$startCol]);
    if ($endCol && isset($r[$endCol])) $out['end_date'] = month_only_from_stored($r[$endCol]);
        if ($archCol && isset($r[$archCol])) $out['archived'] = (int)$r[$archCol];

        // fallback: if name missing, attempt to find a suitable column from the raw row
        if (empty($out['name'])) {
            foreach ($r as $k => $v) {
                if (preg_match('/semester|term|name|title|label/i', $k) && $v !== null && $v !== '') { $out['name'] = $v; break; }
            }
        }
        if (empty($out['name'])) {
            foreach ($r as $k => $v) {
                $low = strtolower($k);
                if ($low === strtolower($idCol) || $low === strtolower($startCol) || $low === strtolower($endCol) || $low === strtolower($archCol)) continue;
                if ($v !== null && $v !== '' && is_string($v)) { $out['name'] = $v; break; }
            }
        }

    $rows[] = $out;
    }
    echo json_encode(['success'=>true,'data'=>$rows]); exit;
}

// parse raw input for PUT/DELETE
$input = file_get_contents('php://input');
parse_str($input, $putVars);

if ($method === 'POST') {
    // accept flexible param names (map aliases to detected DB columns)
    $name = extractFirst($nameAliases, $_POST);
    $start = extractFirst($startAliases, $_POST);
    $end = extractFirst($endAliases, $_POST);
    if (!$name) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Name required']); exit; }
    if (!$nameCol) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Name column not found']); exit; }

    $cols = "`$nameCol`";
    $placeholders = '?';
    $types = 's';
    $values = [$name];
    if ($start !== null && $startCol) { $cols .= ", `$startCol`"; $placeholders .= ', ?'; $types .= 's'; $values[] = canonicalize_month_for_db($start, false); }
    if ($end !== null && $endCol) { $cols .= ", `$endCol`"; $placeholders .= ', ?'; $types .= 's'; $values[] = canonicalize_month_for_db($end, true); }

    $sql = "INSERT INTO `" . $table . "` ($cols) VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Prepare failed']); exit; }
    $stmt->bind_param($types, ...$values);
    $ok = $stmt->execute();
    if ($ok) echo json_encode(['success'=>true,'id'=>$conn->insert_id]); else echo json_encode(['success'=>false,'error'=>'Insert failed']);
    exit;
}

if ($method === 'PUT') {
    $id = isset($putVars['id']) ? trim($putVars['id']) : '';
    // flexible mapping for update
    $name = extractFirst($nameAliases, $putVars);
    $start = extractFirst($startAliases, $putVars);
    $end = extractFirst($endAliases, $putVars);
    $archived = isset($putVars['archived']) ? (int)$putVars['archived'] : null;

    $setParts = [];
    $params = [];
    $types = '';
    if ($name !== null && $nameCol) { $setParts[] = "`$nameCol` = ?"; $params[] = $name; $types .= 's'; }
    if ($start !== null && $startCol) { $setParts[] = "`$startCol` = ?"; $params[] = canonicalize_month_for_db($start, false); $types .= 's'; }
    if ($end !== null && $endCol) { $setParts[] = "`$endCol` = ?"; $params[] = canonicalize_month_for_db($end, true); $types .= 's'; }
    if ($archived !== null && $archCol) { $setParts[] = "`$archCol` = ?"; $params[] = $archived; $types .= 'i'; }

    if (!count($setParts)) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Nothing to update']); exit; }

    if ($id && $idCol) {
    $sql = "UPDATE `" . $table . "` SET " . implode(', ', $setParts) . " WHERE `$idCol` = ?";
        $params[] = $id; $types .= 's';
        $stmt = $conn->prepare($sql);
        if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Prepare failed']); exit; }
        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok]); exit;
    }

    http_response_code(400); echo json_encode(['success'=>false,'error'=>'Missing id for update']); exit;
}

if ($method === 'DELETE') {
    $id = isset($putVars['id']) ? trim($putVars['id']) : '';
    // archive by setting archived=1 when possible
    if ($archCol) {
        if ($id && $idCol) {
            $sql = "UPDATE `semesters` SET `$archCol` = 1 WHERE `$idCol` = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $id);
            $ok = $stmt->execute();
            echo json_encode(['success' => (bool)$ok]); exit;
        }
    }
    // fallback: attempt to delete row if id column available
    if ($id && $idCol) {
    $sql = "DELETE FROM `" . $table . "` WHERE `$idCol` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $id);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok]); exit;
    }
    http_response_code(400); echo json_encode(['success'=>false,'error'=>'No identifier provided']); exit;
}

http_response_code(405); echo json_encode(['success'=>false,'error'=>'Method not allowed']);

?>
