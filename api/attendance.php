<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/dbcon.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($conn) || !($conn instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>'Database connection not available']);
    exit;
}

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

$table_attendance = 'attendance_tbl';

$table_events = find_table_like($conn, 'events');
if (!$table_events) {
    echo json_encode(['success'=>false,'error'=>'Events table not found']);
    exit;
}
$cols_events = get_table_columns($conn, $table_events);
$colMap_events = [
    'id' => 'id',
    'event_name' => 'event_name',
    'date' => 'date',
    'TimeIN_in' => 'TimeIn_start',
    'TimeIN_out' => 'TimeIn_end',
    'TimeOUT_in' => 'Time_out',
    'TimeOUT_out' => 'Timeout_out',
    'departments_id' => 'departments_id',
    'other_departments' => 'other_dept', // but we'll override with array
];

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $event_id = $_GET['event_id'] ?? null;
    if (!$event_id) {
        echo json_encode(['success' => false, 'error' => 'Missing event_id']);
        exit;
    }
    $sql = "SELECT Attendance_ID, Student_ID as ID_Number, Student_name as student_name, Course, Time_in, Time_out 
            FROM `$table_attendance` 
            WHERE Event_ID = ? 
            ORDER BY Attendance_ID DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $rfid = $input['rfid'] ?? null;
    $event_id = $input['event_id'] ?? null;
    if (!$rfid || !$event_id) {
        echo json_encode(['success' => false, 'error' => 'Missing rfid or event_id']);
        exit;
    }

    date_default_timezone_set('Asia/Manila');
    $currentDate = date('F j, Y');
    $currentTime = strtotime(date('H:i'));

    // Fetch event details
    $selectCols = implode(', ', array_values($colMap_events));
    $sql = "SELECT $selectCols FROM `$table_events` WHERE `{$colMap_events['id']}` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $event_id);
    $stmt->execute();
    $eventRow = $stmt->get_result()->fetch_assoc();
    if (!$eventRow) {
        echo json_encode(['success' => false, 'error' => 'Event not found']);
        exit;
    }
    $event = [];
    foreach ($colMap_events as $key => $col) {
        $event[$key] = $eventRow[$col] ?? null;
    }

    $mapping = find_table_like($conn, 'event_other_depart');
    if (!$mapping) $mapping = find_table_like($conn, 'event_other_departments');
    if ($mapping) {
        $event_id_val = $event['id'];
        $res = $conn->query("SELECT department_id FROM `$mapping` WHERE event_id = $event_id_val");
        $event['other_departments'] = [];
        while ($r = $res->fetch_assoc()) {
            $event['other_departments'][] = $r['department_id'];
        }
    } else {
        $event['other_departments'] = [];
    }

    // Format event date
    $eventDate = date('F j, Y', strtotime($event['date']));

    // Validate event date
    if ($eventDate !== $currentDate) {
        echo json_encode(['success' => false, 'message' => 'Invalid date. Please check the event schedule.', 'type' => 'danger']);
        exit;
    }

    // Convert event times to timestamps
    $timeIN_in = strtotime($event['TimeIN_in']);
    $timeIN_out = strtotime($event['TimeIN_out']);
    $timeOUT_in = strtotime($event['TimeOUT_in']);
    $timeOUT_out = strtotime($event['TimeOUT_out']);

    // Fetch student details
    $studentSql = "SELECT ID_Number, Student_name, Course, departments_id 
                   FROM students 
                   WHERE RFID = ?";
    $stmt = $conn->prepare($studentSql);
    $stmt->bind_param('s', $rfid);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student Not Found', 'type' => 'danger']);
        exit;
    }

    // Check departments
    $allowedDepts = [];
    if ($event['departments_id']) {
        $allowedDepts = array_merge($allowedDepts, explode(',', $event['departments_id']));
    }
    if ($event['other_departments']) {
        if (is_array($event['other_departments'])) {
            $allowedDepts = array_merge($allowedDepts, $event['other_departments']);
        } else {
            $allowedDepts = array_merge($allowedDepts, explode(',', $event['other_departments']));
        }
    }
    $allowedDepts = array_map('trim', $allowedDepts);
    $studentDept = trim($student['departments_id']);
    if (!in_array($studentDept, $allowedDepts)) {
        echo json_encode(['success' => false, 'message' => 'Student department not allowed for this event', 'type' => 'danger']);
        exit;
    }

    // Check existing attendance
    $attSql = "SELECT Time_in, Time_out 
               FROM `$table_attendance` 
               WHERE Event_ID = ? AND Student_ID = ? 
               ORDER BY Attendance_ID DESC LIMIT 1";
    $stmt = $conn->prepare($attSql);
    $stmt->bind_param('ss', $event_id, $student['ID_Number']);
    $stmt->execute();
    $att = $stmt->get_result()->fetch_assoc();
    $hasTimeIN = !empty($att['Time_in']);
    $hasTimeOUT = !empty($att['Time_out']);

    $timeIN = null;
    $timeOUT = null;
    $message = '';
    $type = '';

    if ($currentTime >= $timeIN_in && $currentTime <= $timeIN_out && !$hasTimeIN) {
        $message = "On Time";
        $type = "success";
        $timeIN = date('Y-m-d H:i:s');
    } elseif ($currentTime > $timeIN_out && $currentTime < $timeOUT_in && !$hasTimeIN) {
        $message = "Late";
        $type = "danger";
        $timeIN = date('Y-m-d H:i:s');
    } elseif ($currentTime >= $timeOUT_in && $currentTime <= $timeOUT_out && $hasTimeIN && !$hasTimeOUT) {
        $message = "Timeout Updated";
        $type = "success";
        $timeOUT = date('Y-m-d H:i:s');
    } elseif ($hasTimeIN && $currentTime < $timeOUT_in) {
        $message = "Student Already Time In";
        $type = "danger";
    } elseif ($hasTimeOUT) {
        $message = "Student Already Time Out";
        $type = "danger";
    } elseif ($currentTime >= $timeOUT_in && $currentTime < $timeOUT_out && !$hasTimeIN) {
        $message = "Student Did Not Time IN!";
        $type = "danger";
    } elseif ($currentTime > $timeOUT_out) {
        $message = "Event Attendance Has Ended";
        $type = "danger";
    } else {
        $message = "Invalid operation. Check the event schedule";
        $type = "danger";
    }

    $user_id = 1; // default user

    if ($timeIN) {
        $insSql = "INSERT INTO `$table_attendance` (user_id, Event_ID, Event_name, Student_ID, Student_name, Course, Time_in) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insSql);
        $stmt->bind_param('iisssss', $user_id, $event_id, $event['event_name'], $student['ID_Number'], $student['Student_name'], $student['Course'], $timeIN);
        $stmt->execute();
    }

    if ($timeOUT) {
        $upSql = "UPDATE `$table_attendance` SET Time_out = ? WHERE Event_ID = ? AND Student_ID = ? AND (Time_out IS NULL OR Time_out = '')";
        $stmt = $conn->prepare($upSql);
        $stmt->bind_param('sss', $timeOUT, $event_id, $student['ID_Number']);
        $stmt->execute();
    }

    echo json_encode(['success' => true, 'message' => $message, 'type' => $type]);
    exit;
}
?>
