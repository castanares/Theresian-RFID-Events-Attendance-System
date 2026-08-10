<?php
/**
 * Admin Students API
 * GET    /api/admin_students.php           -> list students (includes department acronym)
 * GET    /api/admin_students.php?id=...   -> get single by ID_Number
 * POST   /api/admin_students.php           -> create (JSON body with ID_Number, Student_name, departments_acronym or departments_id, RFID, Course...)
 * PUT    /api/admin_students.php           -> update (JSON body must include ID_Number)
 * DELETE /api/admin_students.php           -> archive by ID_Number (body JSON {ID_Number: '...'})
 *
 * This endpoint helps admin UI by accepting/returning department acronym when available.
 */

require_once __DIR__ . '/dbcon.php';
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($conn) || !($conn instanceof mysqli)) { http_response_code(500); echo json_encode(['success'=>false,'error'=>'DB connection']); exit; }

function respond($code, $payload){
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

// detect auto-increment PK
$pkCol = null; $pkIsAi = false;
foreach($cols as $c) if (!empty($c['Extra']) && stripos($c['Extra'],'auto_increment')!==false){ $pkCol = $c['Field']; $pkIsAi = true; break; }

// helper: resolve department acronym -> id
function getDeptIdByAcronym($conn, $acro){
    $acro = trim($acro);
    if ($acro === '') return null;
    // try dept_Acronym or acronym-like columns
    $res = $conn->query("SHOW COLUMNS FROM `departments`"); if(!$res) return null; $cols = []; while($r=$res->fetch_assoc()) $cols[] = $r['Field'];
    $acroCol = null; foreach($cols as $c){ if (strcasecmp($c,'dept_Acronym')===0 || strcasecmp($c,'dept_acronym')===0 || strcasecmp($c,'acronym')===0) { $acroCol = $c; break; } }
    $idCol = null; foreach($cols as $c){ if (strcasecmp($c,'Dept_ID')===0 || stripos($c,'id')!==false) { $idCol = $c; break; } }
    if (!$acroCol || !$idCol) return null;
    $sql = "SELECT `$idCol` FROM `departments` WHERE `$acroCol` = ? LIMIT 1";
    $stmt = $conn->prepare($sql); if (!$stmt) return null; $stmt->bind_param('s',$acro); $stmt->execute(); $res = $stmt->get_result(); if ($r = $res->fetch_assoc()) return $r[$idCol]; return null;
}

$method = $_SERVER['REQUEST_METHOD'];

function getJsonBody(){ $raw = file_get_contents('php://input'); if (!$raw) return []; $decoded = json_decode($raw,true); return is_array($decoded) ? $decoded : []; }

if ($method === 'GET'){
    $id = $_GET['id'] ?? null;
    $select = [];
    if ($pkCol) $select[] = "`$pkCol` AS id";
    if ($externalIdCol) $select[] = "`$externalIdCol` AS ID_Number"; else $select[] = "`".($pkCol ?? 'id')."` AS ID_Number";
    if ($nameCol) $select[] = "`$nameCol`";
    if ($deptCol) $select[] = "`$deptCol`";
    if ($rfidCol) $select[] = "`$rfidCol`";
    if ($courseCol) $select[] = "`$courseCol`";
    if ($statusCol) $select[] = "`$statusCol`";
    if ($archiveCauseCol) $select[] = "`$archiveCauseCol` AS archive_cause";
    if ($archivedStampCol) $select[] = "`$archivedStampCol` AS archived_stamp";

    $sql = 'SELECT '.implode(', ',$select).' FROM `'. $conn->real_escape_string($tbl) .'`';
    $where=[]; $params=[];

    if ($id){ if ($externalIdCol) { $where[] = '`'.$externalIdCol.'` = ?'; $params[] = $id; } else { $where[] = '`'.$pkCol.'` = ?'; $params[] = $id; } }

    $deptFilter = $_GET['departments_acronym'] ?? null;
    if ($deptFilter !== null && $deptFilter !== '' && $deptCol) {
        // resolve acronym to id
        $did = getDeptIdByAcronym($conn, $deptFilter);
        if ($did !== null) { $where[] = '`'.$deptCol.'` = ?'; $params[] = $did; }
    }

    $statusFilter = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : null;
    if ($statusFilter && $statusCol) {
        if ($statusFilter === 'archived') { $where[] = '`'.$statusCol.'` = ?'; $params[] = 'archived'; }
        elseif ($statusFilter === 'active') { $where[] = "(`$statusCol` IS NULL OR LOWER(`$statusCol`) <> 'archived')"; }
    }

    if (count($where)) { $sql .= ' WHERE '.implode(' AND ',$where); if ($id) $sql .= ' LIMIT 1'; }

    $stmt = $conn->prepare($sql); if (!$stmt) respond(500,['success'=>false,'error'=>'prepare failed','sql'=>$sql,'dberr'=>$conn->error]);
    if (count($params)) $stmt->bind_param(str_repeat('s',count($params)), ...$params);
    $stmt->execute(); $res = $stmt->get_result(); $out=[]; while($r=$res->fetch_assoc()){
        // optionally include department acronym in response
        if ($deptCol && isset($r[$deptCol])){
            $did = $r[$deptCol];
            // find acronym
            $dres = $conn->query("SHOW COLUMNS FROM `departments`"); $dcols=[]; if ($dres) while($dc=$dres->fetch_assoc()) $dcols[]=$dc['Field'];
            $acroCol = null; $idCol = null; foreach($dcols as $c){ if (strcasecmp($c,'dept_Acronym')===0 || strcasecmp($c,'dept_acronym')===0 || strcasecmp($c,'acronym')===0) { $acroCol = $c; break; } }
            foreach($dcols as $c){ if (strcasecmp($c,'Dept_ID')===0 || stripos($c,'id')!==false) { $idCol = $c; break; } }
            if ($acroCol && $idCol){
                $q = $conn->prepare("SELECT `$acroCol` FROM `departments` WHERE `$idCol` = ? LIMIT 1");
                if ($q){
                    $q->bind_param('s',$did);
                    $q->execute();
                    $rr = $q->get_result();
                    if ($rr && ($row=$rr->fetch_row())){
                        $r['departments_acronym'] = $row[0];
                    }
                    $q->close();
                }
            }
        }
        $out[] = $r;
    }
    respond(200,['success'=>true,'data'=>$out]);
}

if ($method === 'POST'){
    $data = getJsonBody();
    $incoming = $data['ID_Number'] ?? null; $incomingName = $data['Student_name'] ?? $data['student_name'] ?? null;
    if (!$incoming || !$incomingName) respond(400,['success'=>false,'error'=>'ID_Number and Student_name required']);
    if (!$externalIdCol) respond(400,['success'=>false,'error'=>'schema missing external ID column (ID_Number) - insert denied']);

    // check duplicate RFID if provided
    if ($rfidCol && isset($data['RFID']) && $data['RFID'] !== null && $data['RFID'] !== ''){
        $checkSql = "SELECT COUNT(*) AS c FROM `$tbl` WHERE `$rfidCol` = ? LIMIT 1";
        $chk = $conn->prepare($checkSql);
        if ($chk){
            $chk->bind_param('s', $data['RFID']);
            $chk->execute();
            $cres = $chk->get_result();
            if ($cres && ($crow = $cres->fetch_assoc()) && intval($crow['c']) > 0){
                respond(409,['success'=>false,'error'=>'RFID already registered']);
            }
            $chk->close();
        }
    }

    // allow departments_acronym or departments_id
    $deptId = null;
    if (isset($data['departments_acronym'])){ $deptId = getDeptIdByAcronym($conn, $data['departments_acronym']); }
    elseif (isset($data['departments_id'])){ $deptId = $data['departments_id']; }

    $insertCols=[];$place=[];$vals=[];$types='';
    $insertCols[] = $externalIdCol; $place[] = '?'; $vals[] = $incoming; $types.='s';
    if ($nameCol){ $insertCols[]=$nameCol; $place[]='?'; $vals[]=$incomingName; $types.='s'; }
    if ($deptCol && $deptId){ $insertCols[]=$deptCol; $place[]='?'; $vals[]=$deptId; $types.='s'; }
    if ($rfidCol && isset($data['RFID'])){ $insertCols[]=$rfidCol;$place[]='?';$vals[]=$data['RFID']; $types.='s'; }
    if ($courseCol && isset($data['Course'])){ $insertCols[]=$courseCol;$place[]='?';$vals[]=$data['Course']; $types.='s'; }

    if (!count($insertCols)) respond(400,['success'=>false,'error'=>'no writable columns available']);
    $sql = "INSERT INTO `$tbl` (`".implode('`,`',$insertCols)."`) VALUES (".implode(',',$place).")";
    $stmt = $conn->prepare($sql); if (!$stmt) respond(500,['success'=>false,'error'=>'prepare failed','dberr'=>$conn->error,'sql'=>$sql]);
    $stmt->bind_param($types, ...$vals); $ok = $stmt->execute(); if (!$ok) respond(500,['success'=>false,'error'=>$stmt->error]);
    respond(201,['success'=>true,'insert_id'=>$stmt->insert_id]);
}

if ($method === 'PUT'){
    $data = getJsonBody();
    // allow providing `old_ID_Number` to identify the existing record while `ID_Number` is the new value
    $whereId = $data['old_ID_Number'] ?? $data['ID_Number'] ?? null;
    if (!$whereId) respond(400,['success'=>false,'error'=>'ID_Number or old_ID_Number required for update']);
    if (!$externalIdCol) respond(400,['success'=>false,'error'=>'schema missing external ID column (ID_Number) - update denied']);

    $sets=[];$vals=[];$types='';
    $addNullable = function($col, $key, $isNumeric=false) use (&$sets,&$vals,&$types,&$data){ if (array_key_exists($key,$data)){ if ($data[$key] === null){ $sets[] = "`$col` = NULL"; } else { $sets[] = "`$col` = ?"; $vals[] = $data[$key]; $types .= $isNumeric ? 'i' : 's'; } } };

    // Allow updating the external ID column itself when a new ID_Number is provided
    if ($externalIdCol && array_key_exists('ID_Number',$data) && $data['ID_Number'] !== null){
        $sets[] = "`$externalIdCol` = ?";
        $vals[] = $data['ID_Number'];
        $types .= 's';
    }

    if ($nameCol) $addNullable($nameCol,'Student_name');
    if ($deptCol){
        // accept departments_acronym or departments_id
        if (array_key_exists('departments_acronym',$data)){
            $did = getDeptIdByAcronym($conn, $data['departments_acronym']);
            if ($did !== null){ $sets[] = "`$deptCol` = ?"; $vals[] = $did; $types .= 's'; }
            else { $sets[] = "`$deptCol` = NULL"; }
        } else {
            $addNullable($deptCol,'departments_id', true);
        }
    }
    if ($rfidCol) $addNullable($rfidCol,'RFID');
    if ($courseCol) $addNullable($courseCol,'Course');
    if ($statusCol) $addNullable($statusCol,'status');
    if ($archiveCauseCol) $addNullable($archiveCauseCol,'cause');
    if ($archivedStampCol) $addNullable($archivedStampCol,'year');

    if (!count($sets)) respond(400,['success'=>false,'error'=>'no updatable fields provided']);
    // before running update, ensure RFID uniqueness if RFID is included in payload
    if ($rfidCol && array_key_exists('RFID',$data) && $data['RFID'] !== null && $data['RFID'] !== ''){
        $checkSql = "SELECT COUNT(*) AS c FROM `$tbl` WHERE `$rfidCol` = ? AND `$externalIdCol` <> ? LIMIT 1";
        $chk = $conn->prepare($checkSql);
        if ($chk){
            $chk->bind_param('ss', $data['RFID'], $whereId);
            $chk->execute();
            $cres = $chk->get_result();
            if ($cres && ($crow = $cres->fetch_assoc()) && intval($crow['c']) > 0){
                respond(409,['success'=>false,'error'=>'RFID already registered']);
            }
            $chk->close();
        }
    }
    $sql = "UPDATE `$tbl` SET " . implode(', ', $sets) . " WHERE `$externalIdCol` = ? LIMIT 1";
    // bind the identifier (old ID) at the end
    $vals[] = $whereId; $types .= 's';
    $stmt = $conn->prepare($sql); if (!$stmt) respond(500,['success'=>false,'error'=>'prepare failed','dberr'=>$conn->error,'sql'=>$sql]);
    if (strlen($types)){
        $bindParams = array_merge([$types], $vals); $refs=[]; foreach($bindParams as $k=>$v) $refs[$k] = &$bindParams[$k]; call_user_func_array([$stmt,'bind_param'],$refs);
    }
    $ok = $stmt->execute(); if (!$ok) respond(500,['success'=>false,'error'=>$stmt->error]); respond(200,['success'=>true,'affected_rows'=>$stmt->affected_rows]);
}

if ($method === 'DELETE'){
    $data = getJsonBody();
    $incoming = $data['ID_Number'] ?? null;
    if (!$incoming) respond(400,['success'=>false,'error'=>'ID_Number required to delete/archive']);
    if (!$externalIdCol) respond(400,['success'=>false,'error'=>'schema missing external ID column (ID_Number) - delete denied']);

    if ($statusCol){
        $sets = [];
        $params = [];
        $types = '';

        // mark as archived
        $sets[] = "`$statusCol` = ?";
        $params[] = 'archived';
        $types .= 's';

        // optional archive cause
        if ($archiveCauseCol && isset($data['cause'])){
            $sets[] = "`$archiveCauseCol` = ?";
            $params[] = $data['cause'];
            $types .= 's';
        }

        // archived year / stamp
        $year = isset($data['year']) ? (string)$data['year'] : (string)date('Y');
        if ($archivedStampCol){
            $sets[] = "`$archivedStampCol` = ?";
            $params[] = $year;
            $types .= 's';
        }

        $sql = "UPDATE `$tbl` SET " . implode(', ', $sets) . " WHERE `$externalIdCol` = ? LIMIT 1";
        $params[] = $incoming;
        $types .= 's';

        $stmt = $conn->prepare($sql);
        if (!$stmt) respond(500,['success'=>false,'error'=>'prepare failed','dberr'=>$conn->error,'sql'=>$sql]);

        $bindParams = array_merge([$types], $params);
        $refs = [];
        foreach($bindParams as $k => $v) $refs[$k] = &$bindParams[$k];
        call_user_func_array([$stmt, 'bind_param'], $refs);

        $ok = $stmt->execute();
        if (!$ok) respond(500,['success'=>false,'error'=>$stmt->error]);
        respond(200,['success'=>true,'archived'=>true,'year'=>$year]);
    }

}

respond(405,['success'=>false,'error'=>'Method not allowed']);

?>