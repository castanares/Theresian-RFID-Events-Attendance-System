<?php
// Use existing mysqli db connector
require_once __DIR__ . '/dbcon.php';

header('Content-Type: application/json; charset=utf-8');

// $conn should be defined in dbcon.php as a mysqli connection
if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection not available']);
    exit;
}

// ensure archived column exists
function ensureArchivedColumn($conn) {
    $res = $conn->query("SHOW COLUMNS FROM `departments` LIKE 'archived'");
    if (!$res) return; // table might not exist
    if ($res->num_rows === 0) {
        $conn->query("ALTER TABLE `departments` ADD COLUMN `archived` TINYINT(1) NOT NULL DEFAULT 0 AFTER `dept_Acronym`");
    }
}

ensureArchivedColumn($conn);

// helper: find the first existing column from candidates
function findColumn($conn, array $candidates) {
    $res = $conn->query("SHOW COLUMNS FROM `departments`");
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

$idCol = findColumn($conn, ['Dept_ID','id','ID']);
$nameCol = findColumn($conn, ['Dept_name','dept_name','name']);
$acroCol = findColumn($conn, ['dept_Acronym','dept_acronym','acronym']);
$archCol = findColumn($conn, ['archived','is_archived']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $archived = isset($_GET['archived']) ? (int)$_GET['archived'] : 0;
    // build select list dynamically
    $selectCols = [];
    if ($idCol) $selectCols[] = "`$idCol`";
    if ($nameCol) $selectCols[] = "`$nameCol`";
    if ($acroCol) $selectCols[] = "`$acroCol`";
    if ($archCol) $selectCols[] = "`$archCol`";
    $select = count($selectCols) ? implode(', ', $selectCols) : '*';

    if ($archCol) {
        $sql = "SELECT $select FROM `departments` WHERE `$archCol` = ?";
        if ($idCol) $sql .= " ORDER BY `$idCol` ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $archived);
    } else {
        $sql = "SELECT $select FROM `departments`";
        if ($idCol) $sql .= " ORDER BY `$idCol` ASC";
        $stmt = $conn->prepare($sql);
    }

    if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Query prepare failed']); exit; }
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        // normalize keys to id, name, acronym, archived when possible
        $out = [];
        if ($idCol && isset($r[$idCol])) $out['id'] = $r[$idCol];
        if ($nameCol && isset($r[$nameCol])) $out['name'] = $r[$nameCol];
        if ($acroCol && isset($r[$acroCol])) $out['acronym'] = $r[$acroCol];
        if ($archCol && isset($r[$archCol])) $out['archived'] = $r[$archCol];
        // if no normalized keys, just return raw row
        $rows[] = count($out) ? $out : $r;
    }
    echo json_encode(['success' => true, 'data' => $rows]);
    exit;
}

if ($method === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $acronym = isset($_POST['acronym']) ? trim($_POST['acronym']) : '';
    if (!$name) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Name required']); exit; }
    // determine columns to insert
    if (!$nameCol) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Name column not found']); exit; }
    $cols = "`$nameCol`" . ($acroCol ? ", `$acroCol`" : '');
    $placeholders = $acroCol ? '?, ?' : '?';
    $sql = "INSERT INTO `departments` ($cols) VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);
    if ($acroCol) $stmt->bind_param('ss', $name, $acronym); else $stmt->bind_param('s', $name);
    $ok = $stmt->execute();
    if ($ok) {
        echo json_encode(['success'=>true,'id'=>$conn->insert_id]);
    } else {
        echo json_encode(['success'=>false,'error'=>'Insert failed']);
    }
    exit;
}

// parse raw input for PUT/DELETE
$input = file_get_contents('php://input');
parse_str($input, $putVars);

if ($method === 'PUT') {
    // Allow updating name/acronym and archived flag. Accept id or fallback to name/acronym
    $id = isset($putVars['id']) ? (int)$putVars['id'] : 0;
    $name = isset($putVars['name']) ? trim($putVars['name']) : '';
    $acronym = isset($putVars['acronym']) ? trim($putVars['acronym']) : '';
    $archived = isset($putVars['archived']) ? ((int)$putVars['archived']) : null;

    // Build set clause
    $setParts = [];
    $params = [];
    $types = '';
    if ($name !== '' && $nameCol) { $setParts[] = "`$nameCol` = ?"; $params[] = $name; $types .= 's'; }
    if ($acronym !== '' && $acroCol) { $setParts[] = "`$acroCol` = ?"; $params[] = $acronym; $types .= 's'; }
    if ($archived !== null && $archCol) { $setParts[] = "`$archCol` = ?"; $params[] = $archived; $types .= 'i'; }

    if (!count($setParts)) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Nothing to update or missing columns']); exit; }

    if ($id && $idCol) {
        $sql = "UPDATE `departments` SET " . implode(', ', $setParts) . " WHERE `$idCol` = ?";
        $params[] = $id; $types .= 'i';
        $stmt = $conn->prepare($sql);
        if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Prepare failed']); exit; }
        $stmt->bind_param($types, ...$params);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok]);
        exit;
    }

    // fallback: try update by name or acronym
    if ($name && $nameCol) {
        $sql = "UPDATE `departments` SET " . implode(', ', $setParts) . " WHERE `$nameCol` = ?";
        $params2 = $params; $types2 = $types;
        $params2[] = $name; $types2 .= 's';
        $stmt = $conn->prepare($sql);
        if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Prepare failed']); exit; }
        $stmt->bind_param($types2, ...$params2);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok]);
        exit;
    }

    if ($acronym && $acroCol) {
        $sql = "UPDATE `departments` SET " . implode(', ', $setParts) . " WHERE `$acroCol` = ?";
        $params2 = $params; $types2 = $types;
        $params2[] = $acronym; $types2 .= 's';
        $stmt = $conn->prepare($sql);
        if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Prepare failed']); exit; }
        $stmt->bind_param($types2, ...$params2);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok]);
        exit;
    }

    http_response_code(400); echo json_encode(['success'=>false,'error'=>'Could not identify department to update']);
    exit;
}

if ($method === 'DELETE') {
    // Accept id or name/acronym to archive (set archived=1)
    $id = isset($putVars['id']) ? (int)$putVars['id'] : 0;
    $name = isset($putVars['name']) ? trim($putVars['name']) : '';
    $acronym = isset($putVars['acronym']) ? trim($putVars['acronym']) : '';
    if (!$archCol) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'Archived column not found']); exit; }

    if ($id && $idCol) {
        $sql = "UPDATE `departments` SET `$archCol` = 1 WHERE `$idCol` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok]);
        exit;
    }

    if ($name && $nameCol) {
        $sql = "UPDATE `departments` SET `$archCol` = 1 WHERE `$nameCol` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $name);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok]);
        exit;
    }

    if ($acronym && $acroCol) {
        $sql = "UPDATE `departments` SET `$archCol` = 1 WHERE `$acroCol` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $acronym);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok]);
        exit;
    }

    http_response_code(400); echo json_encode(['success'=>false,'error'=>'No identifier provided to archive']);
    exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'error'=>'Method not allowed']);

?>
