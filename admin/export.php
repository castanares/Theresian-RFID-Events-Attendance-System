<?php
require_once __DIR__ . '/../auth.php';
require_login('../');
if (!is_admin()) { http_response_code(403); echo 'Forbidden'; exit; }

require_once __DIR__ . '/../api/dbcon.php';

// Create a ZIP file with multiple CSVs
$zipFileName = 'dashboard_export_' . date('Ymd_His') . '.zip';

// Check if ZipArchive is available
if (extension_loaded('zip')) {
    // Use ZipArchive for native ZIP
    $tempDir = sys_get_temp_dir();
    $zipPath = $tempDir . '/' . $zipFileName;
    $zip = new ZipArchive();
    
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        http_response_code(500);
        echo 'Failed to create ZIP file';
        exit;
    }
    
    // 1. Export Students
    $studentsCsv = fopen('php://memory', 'r+');
    fprintf($studentsCsv, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    fputcsv($studentsCsv, ['Student ID', 'Student Name', 'Course', 'Department', 'Status']);
    
    $studentsRes = $conn->query("
        SELECT s.ID_Number, s.Student_name, s.Course, d.Dept_name, s.status
        FROM students s
        LEFT JOIN departments d ON s.departments_id = d.id
        WHERE s.status = 'active'
        ORDER BY s.Student_name ASC
    ");
    
    while ($row = $studentsRes->fetch_assoc()) {
        fputcsv($studentsCsv, [
            $row['ID_Number'] ?? '',
            $row['Student_name'] ?? '',
            $row['Course'] ?? '',
            $row['Dept_name'] ?? '',
            $row['status'] ?? ''
        ]);
    }
    rewind($studentsCsv);
    $zip->addFromString('students.csv', stream_get_contents($studentsCsv));
    fclose($studentsCsv);
    
    // 2. Export Events
    $eventsCsv = fopen('php://memory', 'r+');
    fprintf($eventsCsv, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    fputcsv($eventsCsv, ['Event ID', 'Event Name', 'Date', 'Semester', 'Department', 'Status']);
    
    $eventsRes = $conn->query("
        SELECT e.id, e.Event_name, e.date, s.semester, d.Dept_name, e.status
        FROM events e
        LEFT JOIN semester s ON e.semester_id = s.id
        LEFT JOIN departments d ON e.departments_id = d.id
        WHERE e.status = 'active'
        ORDER BY e.date DESC
    ");
    
    while ($row = $eventsRes->fetch_assoc()) {
        fputcsv($eventsCsv, [
            $row['id'] ?? '',
            $row['Event_name'] ?? '',
            $row['date'] ?? '',
            $row['semester'] ?? '',
            $row['Dept_name'] ?? '',
            $row['status'] ?? ''
        ]);
    }
    rewind($eventsCsv);
    $zip->addFromString('events.csv', stream_get_contents($eventsCsv));
    fclose($eventsCsv);
    
    // 3. Export Departments
    $deptsCsv = fopen('php://memory', 'r+');
    fprintf($deptsCsv, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    fputcsv($deptsCsv, ['Department ID', 'Department Name', 'Acronym']);
    
    $deptsRes = $conn->query("
        SELECT id, Dept_name, dept_Acronym
        FROM departments
        WHERE archived = 0
        ORDER BY Dept_name ASC
    ");
    
    while ($row = $deptsRes->fetch_assoc()) {
        fputcsv($deptsCsv, [
            $row['id'] ?? '',
            $row['Dept_name'] ?? '',
            $row['dept_Acronym'] ?? ''
        ]);
    }
    rewind($deptsCsv);
    $zip->addFromString('departments.csv', stream_get_contents($deptsCsv));
    fclose($deptsCsv);
    
    // 4. Export Attendance Summary
    $attendanceSummaryCsv = fopen('php://memory', 'r+');
    fprintf($attendanceSummaryCsv, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    fputcsv($attendanceSummaryCsv, ['Student ID', 'Student Name', 'Department', 'Event Name', 'Time In', 'Time Out', 'Date', 'Status']);
    
    $attendanceRes = $conn->query("
        SELECT 
            s.ID_Number,
            s.Student_name,
            d.Dept_name,
            a.Event_name,
            a.Time_in,
            a.Time_out,
            e.date,
            e.status
        FROM attendance_tbl a
        JOIN students s ON a.Student_ID = s.ID_Number
        JOIN events e ON a.Event_ID = e.id
        JOIN departments d ON s.departments_id = d.id
        WHERE e.status = 'active'
        ORDER BY a.Time_in DESC
        LIMIT 5000
    ");
    
    while ($row = $attendanceRes->fetch_assoc()) {
        fputcsv($attendanceSummaryCsv, [
            $row['ID_Number'] ?? '',
            $row['Student_name'] ?? '',
            $row['Dept_name'] ?? '',
            $row['Event_name'] ?? '',
            substr($row['Time_in'] ?? '', 0, 19),
            substr($row['Time_out'] ?? '', 0, 19),
            $row['date'] ?? '',
            $row['status'] ?? ''
        ]);
    }
    rewind($attendanceSummaryCsv);
    $zip->addFromString('attendance_summary.csv', stream_get_contents($attendanceSummaryCsv));
    fclose($attendanceSummaryCsv);
    
    // 5. Export System Statistics
    $statsCsv = fopen('php://memory', 'r+');
    fprintf($statsCsv, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    fputcsv($statsCsv, ['Metric', 'Value']);
    
    $totalStudents = $conn->query("SELECT COUNT(*) as count FROM students WHERE status = 'active'")->fetch_assoc()['count'];
    $totalEvents = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'active'")->fetch_assoc()['count'];
    $totalDepts = $conn->query("SELECT COUNT(*) as count FROM departments WHERE archived = 0")->fetch_assoc()['count'];
    $totalAttendance = $conn->query("SELECT COUNT(*) as count FROM attendance_tbl")->fetch_assoc()['count'];
    $exportDate = date('Y-m-d H:i:s');
    
    fputcsv($statsCsv, ['Total Active Students', $totalStudents]);
    fputcsv($statsCsv, ['Total Active Events', $totalEvents]);
    fputcsv($statsCsv, ['Total Departments', $totalDepts]);
    fputcsv($statsCsv, ['Total Attendance Records', $totalAttendance]);
    fputcsv($statsCsv, ['Export Date', $exportDate]);
    
    rewind($statsCsv);
    $zip->addFromString('system_statistics.csv', stream_get_contents($statsCsv));
    fclose($statsCsv);
    
    $zip->close();
    
    // Send ZIP to browser
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
    header('Content-Length: ' . filesize($zipPath));
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    
    readfile($zipPath);
    unlink($zipPath);
    exit;
    
} else {
    // Fallback: create tar.gz (no ZipArchive)
    $tarGzFileName = 'dashboard_export_' . date('Ymd_His') . '.tar.gz';
    $tempDir = sys_get_temp_dir();
    $tarPath = $tempDir . '/' . 'dashboard_' . time() . '.tar';
    $gzPath = $tempDir . '/' . $tarGzFileName;
    
    $tarHandle = fopen($tarPath, 'w');
    if (!$tarHandle) {
        http_response_code(500);
        echo 'Failed to create tar file';
        exit;
    }
    
    // Helper function to add file to tar
    function addFileToTar($handle, $filename, $content) {
        $filesize = strlen($content);
        $header = '';
        
        // POSIX.1-1988 (ustar) tar header (512 bytes)
        $header .= str_pad($filename, 100, "\0");                    // 0-99: filename
        $header .= str_pad(decoctPadded(0644, 7), 8, "0", STR_PAD_LEFT);  // 100-107: file mode
        $header .= str_pad(decoctPadded(0, 7), 8, "0", STR_PAD_LEFT);     // 108-115: owner uid
        $header .= str_pad(decoctPadded(0, 7), 8, "0", STR_PAD_LEFT);     // 116-123: group gid
        $header .= str_pad(decoctPadded($filesize, 11), 12, "0", STR_PAD_LEFT); // 124-135: file size
        $header .= str_pad(decoctPadded(time(), 11), 12, "0", STR_PAD_LEFT); // 136-147: modification time
        
        // Checksum placeholder (8 bytes of spaces)
        $header .= '        ';                                        // 148-155: checksum
        
        $header .= '0';                                               // 156: type flag (0 = regular file)
        $header .= str_repeat("\0", 100);                             // 157-256: link name
        $header .= 'ustar' . "\0" . '00';                            // 257-263: ustar indicator
        $header .= str_repeat("\0", 32);                              // 264-295: owner name
        $header .= str_repeat("\0", 32);                              // 296-327: group name
        $header .= str_repeat("\0", 8);                               // 328-335: device major
        $header .= str_repeat("\0", 8);                               // 336-343: device minor
        $header .= str_repeat("\0", 155);                             // 344-498: filename prefix
        $header .= str_repeat("\0", 12);                              // 499-511: padding
        
        // Calculate checksum
        $checksum = 0;
        for ($i = 0; $i < strlen($header); $i++) {
            $checksum += ord($header[$i]);
        }
        
        // Replace checksum
        $header = substr_replace($header, str_pad(decoctPadded($checksum, 7), 7, "0", STR_PAD_LEFT) . "\0", 148, 8);
        
        fwrite($handle, $header, 512);
        fwrite($handle, $content);
        
        // Pad content to 512-byte boundary
        $padding = (512 - ($filesize % 512)) % 512;
        if ($padding > 0) {
            fwrite($handle, str_repeat("\0", $padding));
        }
    }
    
    // Helper to convert integer to octal string
    function decoctPadded($num, $len) {
        $oct = \decoct($num);
        return substr(str_pad($oct, $len, "0", STR_PAD_LEFT), -$len);
    }
    
    // 1. Students CSV
    $studentsCsv = fopen('php://memory', 'r+');
    fprintf($studentsCsv, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($studentsCsv, ['Student ID', 'Student Name', 'Course', 'Department', 'Status']);
    $studentsRes = $conn->query("
        SELECT s.ID_Number, s.Student_name, s.Course, d.Dept_name, s.status
        FROM students s
        LEFT JOIN departments d ON s.departments_id = d.id
        WHERE s.status = 'active'
        ORDER BY s.Student_name ASC
    ");
    while ($row = $studentsRes->fetch_assoc()) {
        fputcsv($studentsCsv, [
            $row['ID_Number'] ?? '',
            $row['Student_name'] ?? '',
            $row['Course'] ?? '',
            $row['Dept_name'] ?? '',
            $row['status'] ?? ''
        ]);
    }
    rewind($studentsCsv);
    $csvContent = stream_get_contents($studentsCsv);
    addFileToTar($tarHandle, 'students.csv', $csvContent);
    fclose($studentsCsv);
    
    // 2. Events CSV
    $eventsCsv = fopen('php://memory', 'r+');
    fprintf($eventsCsv, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($eventsCsv, ['Event ID', 'Event Name', 'Date', 'Semester', 'Department', 'Status']);
    $eventsRes = $conn->query("
        SELECT e.id, e.Event_name, e.date, s.semester, d.Dept_name, e.status
        FROM events e
        LEFT JOIN semester s ON e.semester_id = s.id
        LEFT JOIN departments d ON e.departments_id = d.id
        WHERE e.status = 'active'
        ORDER BY e.date DESC
    ");
    while ($row = $eventsRes->fetch_assoc()) {
        fputcsv($eventsCsv, [
            $row['id'] ?? '',
            $row['Event_name'] ?? '',
            $row['date'] ?? '',
            $row['semester'] ?? '',
            $row['Dept_name'] ?? '',
            $row['status'] ?? ''
        ]);
    }
    rewind($eventsCsv);
    $csvContent = stream_get_contents($eventsCsv);
    addFileToTar($tarHandle, 'events.csv', $csvContent);
    fclose($eventsCsv);
    
    // 3. Departments CSV
    $deptsCsv = fopen('php://memory', 'r+');
    fprintf($deptsCsv, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($deptsCsv, ['Department ID', 'Department Name', 'Acronym']);
    $deptsRes = $conn->query("
        SELECT id, Dept_name, dept_Acronym
        FROM departments
        WHERE archived = 0
        ORDER BY Dept_name ASC
    ");
    while ($row = $deptsRes->fetch_assoc()) {
        fputcsv($deptsCsv, [
            $row['id'] ?? '',
            $row['Dept_name'] ?? '',
            $row['dept_Acronym'] ?? ''
        ]);
    }
    rewind($deptsCsv);
    $csvContent = stream_get_contents($deptsCsv);
    addFileToTar($tarHandle, 'departments.csv', $csvContent);
    fclose($deptsCsv);
    
    // 4. Attendance Summary CSV
    $attendanceSummaryCsv = fopen('php://memory', 'r+');
    fprintf($attendanceSummaryCsv, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($attendanceSummaryCsv, ['Student ID', 'Student Name', 'Department', 'Event Name', 'Time In', 'Time Out', 'Date', 'Status']);
    $attendanceRes = $conn->query("
        SELECT 
            s.ID_Number,
            s.Student_name,
            d.Dept_name,
            a.Event_name,
            a.Time_in,
            a.Time_out,
            e.date,
            e.status
        FROM attendance_tbl a
        JOIN students s ON a.Student_ID = s.ID_Number
        JOIN events e ON a.Event_ID = e.id
        JOIN departments d ON s.departments_id = d.id
        WHERE e.status = 'active'
        ORDER BY a.Time_in DESC
        LIMIT 5000
    ");
    while ($row = $attendanceRes->fetch_assoc()) {
        fputcsv($attendanceSummaryCsv, [
            $row['ID_Number'] ?? '',
            $row['Student_name'] ?? '',
            $row['Dept_name'] ?? '',
            $row['Event_name'] ?? '',
            substr($row['Time_in'] ?? '', 0, 19),
            substr($row['Time_out'] ?? '', 0, 19),
            $row['date'] ?? '',
            $row['status'] ?? ''
        ]);
    }
    rewind($attendanceSummaryCsv);
    $csvContent = stream_get_contents($attendanceSummaryCsv);
    addFileToTar($tarHandle, 'attendance_summary.csv', $csvContent);
    fclose($attendanceSummaryCsv);
    
    // 5. System Statistics CSV
    $statsCsv = fopen('php://memory', 'r+');
    fprintf($statsCsv, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($statsCsv, ['Metric', 'Value']);
    
    $totalStudents = $conn->query("SELECT COUNT(*) as count FROM students WHERE status = 'active'")->fetch_assoc()['count'];
    $totalEvents = $conn->query("SELECT COUNT(*) as count FROM events WHERE status = 'active'")->fetch_assoc()['count'];
    $totalDepts = $conn->query("SELECT COUNT(*) as count FROM departments WHERE archived = 0")->fetch_assoc()['count'];
    $totalAttendance = $conn->query("SELECT COUNT(*) as count FROM attendance_tbl")->fetch_assoc()['count'];
    $exportDate = date('Y-m-d H:i:s');
    
    fputcsv($statsCsv, ['Total Active Students', $totalStudents]);
    fputcsv($statsCsv, ['Total Active Events', $totalEvents]);
    fputcsv($statsCsv, ['Total Departments', $totalDepts]);
    fputcsv($statsCsv, ['Total Attendance Records', $totalAttendance]);
    fputcsv($statsCsv, ['Export Date', $exportDate]);
    
    rewind($statsCsv);
    $csvContent = stream_get_contents($statsCsv);
    addFileToTar($tarHandle, 'system_statistics.csv', $csvContent);
    fclose($statsCsv);
    
    // Add tar end marker (two 512-byte blocks of zeros)
    fwrite($tarHandle, str_repeat("\0", 1024));
    fclose($tarHandle);
    
    // Compress tar to gzip
    $tarContent = file_get_contents($tarPath);
    $gzContent = gzencode($tarContent, 9);
    file_put_contents($gzPath, $gzContent);
    unlink($tarPath);
    
    // Send to browser
    header('Content-Type: application/gzip');
    header('Content-Disposition: attachment; filename="' . $tarGzFileName . '"');
    header('Content-Length: ' . filesize($gzPath));
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    
    readfile($gzPath);
    unlink($gzPath);
    exit;
}
?>
