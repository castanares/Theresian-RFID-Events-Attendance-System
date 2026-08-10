<?php
/**
 * RESTful Students API
 * GET    /api/students.php           -> list students
 * GET    /api/students.php?id=...   -> get single by ID_Number
 * POST   /api/students.php           -> create (JSON body with ID_Number, Student_name, departments_id, RFID, Course...)
 * PUT    /api/students.php           -> update (JSON body must include ID_Number)
 * DELETE /api/students.php           -> archive by ID_Number (body JSON {ID_Number: '...'}) or hard-delete if no status column
 *
 * Important: this implementation prefers the `ID_Number` column and will never write to an auto-increment PK.
 */

require_once __DIR__ . '/dbcon.php';
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) session_start();

// ensure output buffering so stray output won't break JSON responses
if (!ob_get_level()) ob_start();

if (!isset($conn) || !($conn instanceof mysqli)) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'DB connection']); exit; }

function respond($code, $payload){
    // clear any buffered output to ensure we emit only clean JSON
    if (ob_get_level()) { while (ob_get_level()) ob_end_clean(); }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function getCols($conn,$tbl){ $cols=[]; $res=$conn->query("SHOW COLUMNS FROM `".$conn->real_escape_string($tbl)."`"); if(!$res) return $cols; while($r=$res->fetch_assoc()) $cols[]=$r; return $cols; }
function findCol($cols,$cands){ foreach($cols as $c){ foreach($cands as $cand) if (strcasecmp($c['Field'],$cand)===0) return $c['Field']; } foreach($cols as $c){ $f=strtolower($c['Field']); foreach($cands as $cand) if (strpos($f,strtolower($cand))!==false) return $c['Field']; } return null; }

$tbl = 'students';
$cols = getCols($conn,$tbl);
$allFields = array_map(function($c){ return $c['Field']; }, $cols);

// detect columns we need
$externalIdCol = findCol($cols,['ID_Number']);
$nameCol = findCol($cols,['Student_name','student_name','name','fullname']);
$deptCol = findCol($cols,['departments_id','department_id','dept_id','department','dept']);
$rfidCol = findCol($cols,['RFID','rfid','rfid_value','tag']);
$courseCol = findCol($cols,['Course','course','program']);
$statusCol = findCol($cols,['status','archived','is_archived']);
$archiveCauseCol = findCol($cols,['archive_cause','cause','archived_reason','reason','archive_reason']);
$archivedStampCol = findCol($cols,['archived_stamp','archived_year','archive_year','archived_at','archived_on']);

// detect auto-increment PK to ensure we never write to it
$pkCol = null; $pkIsAi = false;
foreach($cols as $c) if (!empty($c['Extra']) && stripos($c['Extra'],'auto_increment')!==false){ $pkCol = $c['Field']; $pkIsAi = true; break; }

$method = $_SERVER['REQUEST_METHOD'];

// parse JSON body when present
function getJsonBody(){ $raw = file_get_contents('php://input'); if (!$raw) return []; $decoded = json_decode($raw,true); return is_array($decoded) ? $decoded : []; }

if ($method === 'GET'){
    // optional single fetch by ID_Number
    $id = $_GET['id'] ?? null;
    $rfid = $_GET['rfid'] ?? null;
    // Build select order: prefer exposing internal PK as `id` first (if present),
    // then expose the external ID as `ID_Number`, then other fields.
    $select = [];
    if ($pkCol) {
        $select[] = "`$pkCol` AS id";
    }
    if ($externalIdCol) {
        $select[] = "`$externalIdCol` AS ID_Number";
    } else {
        // fall back: if no explicit external ID column, alias the PK as ID_Number
        $select[] = "`".($pkCol ?? 'id')."` AS ID_Number";
    }
    if ($nameCol) $select[] = "`$nameCol`";
    if ($deptCol) $select[] = "`$deptCol`";
    if ($rfidCol) $select[] = "`$rfidCol`";
    if ($courseCol) $select[] = "`$courseCol`";
    if ($statusCol) $select[] = "`$statusCol`";
    if ($archiveCauseCol) $select[] = "`$archiveCauseCol` AS archive_cause";
    if ($archivedStampCol) $select[] = "`$archivedStampCol` AS archived_stamp";

    $sql = 'SELECT '.implode(', ',$select).' FROM `'. $conn->real_escape_string($tbl) .'`';
    $where = [];
    $params = [];

    // filter by ID_Number or internal PK when requested
    if ($id){
        if ($externalIdCol) { $where[] = '`'.$externalIdCol.'` = ?'; $params[] = $id; }
        else { $where[] = '`'.$pkCol.'` = ?'; $params[] = $id; }
    }

    // filter by RFID
    if ($rfid && $rfidCol){
        $where[] = '`'.$rfidCol.'` = ?';
        $params[] = $rfid;
    }

    // optional department filter: only apply if table has a department column
    $deptFilter = $_GET['departments_id'] ?? null;
    if ($deptFilter !== null && $deptFilter !== '' && $deptCol) {
        $where[] = '`'.$deptCol.'` = ?';
        $params[] = $deptFilter;
    }

    // optional status filter: 'active' = not archived, 'archived' = archived only
    $statusFilter = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : null;
    if ($statusFilter && $statusCol) {
        if ($statusFilter === 'archived') {
            $where[] = '`'.$statusCol.'` = ?';
            $params[] = 'archived';
        } elseif ($statusFilter === 'active') {
            // active = records where status is null or not 'archived'
            // add a SQL fragment; this doesn't need a bound parameter
            $where[] = "(`$statusCol` IS NULL OR LOWER(`$statusCol`) <> 'archived')";
        }
    }

    if (count($where)) {
        $sql .= ' WHERE '.implode(' AND ',$where);
        // if requesting a single id or rfid, keep LIMIT 1
        if ($id || $rfid) $sql .= ' LIMIT 1';
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) respond(500,['success'=>false,'error'=>'prepare failed','sql'=>$sql,'dberr'=>$conn->error]);
    if (count($params)) $stmt->bind_param(str_repeat('s',count($params)), ...$params);
    $stmt->execute();
    $res = $stmt->get_result(); $out=[]; while($r=$res->fetch_assoc()) $out[]=$r;
    respond(200,['success'=>true,'data'=>$out]);
}

if ($method === 'POST'){
    $data = getJsonBody();
    // require external ID and name
    $incoming = $data['ID_Number'] ?? null;
    $incomingName = $data['Student_name'] ?? $data['student_name'] ?? null;
    if (!$incoming || !$incomingName) respond(400,['success'=>false,'error'=>'ID_Number and Student_name required']);

    if (!$externalIdCol) respond(400,['success'=>false,'error'=>'schema missing external ID column (ID_Number) - insert denied']);

    // build insertable fields only from allowed columns (never include auto-increment PK)
    $insertCols = [];$place = [];$vals=[];$types='';
    $insertCols[] = $externalIdCol; $place[]='?'; $vals[] = $incoming; $types.='s';
    if ($nameCol && ($incomingName !== null)){ $insertCols[]=$nameCol; $place[]='?'; $vals[]=$incomingName; $types.='s'; }
    if ($deptCol && isset($data['departments_id'])){ $insertCols[]=$deptCol;$place[]='?';$vals[]=$data['departments_id']; $types .= is_numeric($data['departments_id']) ? 'i' : 's'; }
    if ($rfidCol && isset($data['RFID'])){ $insertCols[]=$rfidCol;$place[]='?';$vals[]=$data['RFID']; $types.='s'; }
    if ($courseCol && isset($data['Course'])){ $insertCols[]=$courseCol;$place[]='?';$vals[]=$data['Course']; $types.='s'; }

    // Server-side: prevent duplicate RFID on insert
    if ($rfidCol && isset($data['RFID']) && $data['RFID'] !== ''){
        $checkSql = "SELECT 1 FROM `".$conn->real_escape_string($tbl)."` WHERE `".$conn->real_escape_string($rfidCol)."` = ? LIMIT 1";
        $chk = $conn->prepare($checkSql);
        if ($chk){
            $chk->bind_param('s', $data['RFID']);
            $chk->execute();
            $cres = $chk->get_result();
            if ($cres && $cres->num_rows > 0){
                respond(409, ['success'=>false, 'error'=>'RFID already registered']);
            }
            $chk->close();
        }
    }

    if (!count($insertCols)) respond(400,['success'=>false,'error'=>'no writable columns available']);

    $sql = "INSERT INTO `$tbl` (`".implode('`,`',$insertCols)."`) VALUES (".implode(',',$place).")";
    $stmt = $conn->prepare($sql);
    if (!$stmt) respond(500,['success'=>false,'error'=>'prepare failed','dberr'=>$conn->error,'sql'=>$sql]);
    $stmt->bind_param($types, ...$vals);
    $ok = $stmt->execute();
    if (!$ok) respond(500,['success'=>false,'error'=>$stmt->error]);
    respond(201,['success'=>true,'insert_id'=>$stmt->insert_id]);
}

if ($method === 'PUT'){
    $data = getJsonBody();
    // Support both ID_Number (for backwards compatibility) and old_ID_Number (for identity during update)
    $whereId = $data['old_ID_Number'] ?? $data['ID_Number'] ?? null;
    if (!$whereId) respond(400,['success'=>false,'error'=>'ID_Number or old_ID_Number required for update']);
    if (!$externalIdCol) respond(400,['success'=>false,'error'=>'schema missing external ID column (ID_Number) - update denied']);

    // Build UPDATE set from allowed updatable columns (not PK)
    $sets = [];
    $vals = [];
    $types = '';

    // helper to add nullable fields: if key exists and is null -> col = NULL, else if exists -> col = ? with value
    $addNullable = function($col, $key, $isNumeric=false) use (&$sets,&$vals,&$types,&$data){
        if (array_key_exists($key, $data)){
            if ($data[$key] === null){
                $sets[] = "`$col` = NULL";
            } else {
                $sets[] = "`$col` = ?";
                $vals[] = $data[$key];
                $types .= $isNumeric ? 'i' : 's';
            }
        }
    };

    // Allow updating ID_Number itself
    if ($externalIdCol && isset($data['ID_Number']) && $data['ID_Number'] !== null) {
        $sets[] = "`$externalIdCol` = ?";
        $vals[] = $data['ID_Number'];
        $types .= 's';
    }
    if ($nameCol) $addNullable($nameCol, 'Student_name');
    if ($deptCol) $addNullable($deptCol, 'departments_id', true);
    if ($rfidCol) $addNullable($rfidCol, 'RFID');
    if ($courseCol) $addNullable($courseCol, 'Course');
    if ($statusCol) $addNullable($statusCol, 'status');
    if ($archiveCauseCol) $addNullable($archiveCauseCol, 'cause');
    if ($archivedStampCol) $addNullable($archivedStampCol, 'year');

    if (!count($sets)) respond(400,['success'=>false,'error'=>'no updatable fields provided']);

    // Server-side: prevent duplicate RFID on update (exclude current record)
    if ($rfidCol && array_key_exists('RFID', $data) && $data['RFID'] !== null && $data['RFID'] !== ''){
        $chkSql = "SELECT `" . ($externalIdCol ? $externalIdCol : $pkCol) . "` AS exid FROM `".$conn->real_escape_string($tbl)."` WHERE `".$conn->real_escape_string($rfidCol)."` = ? LIMIT 1";
        $chk = $conn->prepare($chkSql);
        if ($chk){
            $chk->bind_param('s', $data['RFID']);
            $chk->execute();
            $cres = $chk->get_result();
            if ($cres && ($r = $cres->fetch_assoc())){
                $found = $r['exid'] ?? null;
                if ($found !== null && (string)$found !== (string)$whereId){
                    respond(409, ['success'=>false, 'error'=>'RFID already registered']);
                }
            }
            $chk->close();
        }
    }

    $sql = "UPDATE `$tbl` SET " . implode(', ', $sets) . " WHERE `$externalIdCol` = ? LIMIT 1";
    // bind the ID at the end
    $vals[] = $whereId; $types .= 's';

    $stmt = $conn->prepare($sql);
    if (!$stmt) respond(500,['success'=>false,'error'=>'prepare failed','dberr'=>$conn->error,'sql'=>$sql]);

    if (strlen($types)){
        $bindParams = array_merge([$types], $vals);
        $refs = [];
        foreach($bindParams as $k => $v) $refs[$k] = &$bindParams[$k];
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }

    $ok = $stmt->execute();
    if (!$ok) respond(500,['success'=>false,'error'=>$stmt->error]);
    respond(200,['success'=>true,'affected_rows'=>$stmt->affected_rows]);
}

if ($method === 'DELETE'){
    $data = getJsonBody();
    $incoming = $data['ID_Number'] ?? null;
    if (!$incoming) respond(400,['success'=>false,'error'=>'ID_Number required to delete/archive']);
    if (!$externalIdCol) respond(400,['success'=>false,'error'=>'schema missing external ID column (ID_Number) - delete denied']);

    // prefer archival if status column exists
    if ($statusCol){
        // build SET clauses with placeholders and corresponding bind params
        $sets = [];
        $params = [];
        $types = '';

        // set status = 'archived'
        $sets[] = "`$statusCol` = ?";
        $params[] = 'archived'; $types .= 's';

        // optional cause
        if ($archiveCauseCol && isset($data['cause'])){ $sets[] = "`$archiveCauseCol` = ?"; $params[] = $data['cause']; $types .= 's'; }

        // archived stamp/year
        $year = isset($data['year']) ? (string)$data['year'] : (string)date('Y');
        if ($archivedStampCol){ $sets[] = "`$archivedStampCol` = ?"; $params[] = $year; $types .= 's'; }

        $sql = "UPDATE `$tbl` SET " . implode(', ', $sets) . " WHERE `$externalIdCol` = ? LIMIT 1";
        $params[] = $incoming; $types .= 's';

        $stmt = $conn->prepare($sql);
        if (!$stmt) respond(500,['success'=>false,'error'=>'prepare failed','dberr'=>$conn->error,'sql'=>$sql]);

        // bind params (mysqli requires references)
        $bindParams = array_merge([$types], $params);
        $refs = [];
        foreach($bindParams as $k => $v) $refs[$k] = &$bindParams[$k];
        call_user_func_array([$stmt, 'bind_param'], $refs);

        $ok = $stmt->execute();
        if (!$ok) respond(500,['success'=>false,'error'=>$stmt->error]);
        respond(200,['success'=>true,'archived'=>true,'year'=>$year]);
    }

    // fallback hard delete
    $sql = "DELETE FROM `$tbl` WHERE `$externalIdCol` = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) respond(500,['success'=>false,'error'=>'prepare failed','dberr'=>$conn->error]);
    $stmt->bind_param('s',$incoming);
    $ok = $stmt->execute();
    if (!$ok) respond(500,['success'=>false,'error'=>$stmt->error]);
    respond(200,['success'=>true,'deleted'=>true]);
}

respond(405,['success'=>false,'error'=>'Method not allowed']);

?>
