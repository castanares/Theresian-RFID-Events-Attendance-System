<?php
require_once __DIR__ . '/../auth.php';
// require_login('/new/');
require_login('../');
if (!is_admin()) { http_response_code(403); echo 'Forbidden'; exit; }

require_once __DIR__ . '/../api/dbcon.php';

// Get filter parameters
$startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
$departmentId = isset($_GET['department_id']) ? trim($_GET['department_id']) : '';
$semesterId = isset($_GET['semester_id']) ? trim($_GET['semester_id']) : '';

// Build query - start simple and check all rows first
$query = "SELECT 
    s.ID_Number as student_id,
    s.Student_name,
    a.Event_name,
    a.Time_in,
    a.Time_out,
    d.Dept_name as department,
    e.date as event_date,
    e.status
FROM attendance_tbl a
JOIN students s ON a.Student_ID = s.ID_Number
JOIN events e ON a.Event_ID = e.id
JOIN departments d ON s.departments_id = d.id
WHERE 1=1";

$params = [];
$types = '';

// Apply filters
if (!empty($startDate)) {
    $query .= " AND DATE(a.Time_in) >= ?";
    $params[] = $startDate;
    $types .= 's';
}

if (!empty($endDate)) {
    $query .= " AND DATE(a.Time_in) <= ?";
    $params[] = $endDate;
    $types .= 's';
}

if (!empty($departmentId) && $departmentId !== 'all') {
    $query .= " AND s.departments_id = ?";
    $params[] = $departmentId;
    $types .= 'i';
}

if (!empty($semesterId) && $semesterId !== 'all') {
    $query .= " AND e.semester_id = ?";
    $params[] = $semesterId;
    $types .= 'i';
}

// Only show active events (not archived)
$query .= " AND e.status = 'active'";

// Order by date and student
$query .= " ORDER BY a.Time_in DESC, s.Student_name ASC";

// Execute query
$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo "Error preparing statement: " . $conn->error;
    exit;
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo "Error executing query: " . $stmt->error;
    exit;
}

$result = $stmt->get_result();

// Generate CSV
$filename = 'attendance_report_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$output = fopen('php://output', 'w');
if (!$output) { http_response_code(500); echo 'Failed to generate report'; exit; }

// Write BOM for Excel UTF-8 compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write header with column widths as comment (Excel will ignore but helps with formatting intent)
fputcsv($output, ['Student ID', 'Student Name', 'Event Name', 'Time In', 'Time Out', 'Department', 'Event Date', 'Status']);

// Write data
$rowCount = 0;
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['student_id'] ?? '',
        $row['Student_name'] ?? '',
        $row['Event_name'] ?? '',
        substr($row['Time_in'] ?? '', 0, 19),  // Format: YYYY-MM-DD HH:MM:SS
        substr($row['Time_out'] ?? '', 0, 19),  // Format: YYYY-MM-DD HH:MM:SS
        $row['department'] ?? '',
        $row['event_date'] ?? '',
        $row['status'] ?? ''
    ]);
    $rowCount++;
}

fclose($output);
$stmt->close();
exit;
