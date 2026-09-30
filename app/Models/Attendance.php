<?php
namespace App\Models;

use App\Core\Model;

class Attendance extends Model {
    public function getByEvent(int $eventId): array {
        $sql = "SELECT a.*, s.RFID, s.departments_id as student_dept_id, d.dept_Acronym as department_name 
                FROM attendance_tbl a 
                LEFT JOIN students s ON a.Student_ID = s.ID_Number 
                LEFT JOIN departments d ON s.departments_id = d.id 
                WHERE a.Event_ID = ? 
                ORDER BY a.Attendance_ID DESC";
        return $this->query($sql, [$eventId]);
    }

    public function findStudentAttendance(int $eventId, int|string $studentId): ?array {
        $sql = "SELECT * FROM attendance_tbl WHERE Event_ID = ? AND Student_ID = ? ORDER BY Attendance_ID DESC LIMIT 1";
        return $this->queryOne($sql, [$eventId, $studentId]);
    }

    public function recordTimeIn(int $userId, int $eventId, string $eventName, int|string $studentId, string $studentName, string $course, string $timeIn): int {
        $sql = "INSERT INTO attendance_tbl (user_id, Event_ID, Event_name, Student_ID, Student_name, Course, Time_in, Time_out) 
                VALUES (?, ?, ?, ?, ?, ?, ?, '')";
        return $this->insert($sql, [
            $userId,
            $eventId,
            $eventName,
            $studentId,
            $studentName,
            $course,
            $timeIn
        ]);
    }

    public function recordTimeOut(int $eventId, int|string $studentId, string $timeOut): bool {
        $sql = "UPDATE attendance_tbl 
                SET Time_out = ? 
                WHERE Event_ID = ? AND Student_ID = ? AND (Time_out IS NULL OR Time_out = '')";
        return $this->execute($sql, [$timeOut, $eventId, $studentId]);
    }

    public function deleteRecord(int $attendanceId): bool {
        return $this->execute("DELETE FROM attendance_tbl WHERE Attendance_ID = ?", [$attendanceId]);
    }

    public function countTodayScans(): int {
        $today = date('Y-m-d');
        $res = $this->queryOne("SELECT COUNT(*) as cnt FROM attendance_tbl WHERE DATE(Time_in) = ?", [$today]);
        return (int)($res['cnt'] ?? 0);
    }

    public function getScansLast7Days(): array {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $label = date('D (M j)', strtotime("-$i days"));
            $res = $this->queryOne("SELECT COUNT(*) as cnt FROM attendance_tbl WHERE DATE(Time_in) = ?", [$date]);
            $data[] = [
                'date'  => $date,
                'label' => $label,
                'count' => (int)($res['cnt'] ?? 0)
            ];
        }
        return $data;
    }

    public function getAttendanceByDepartment(int $eventId): array {
        $sql = "SELECT d.dept_Acronym as department, COUNT(a.Attendance_ID) as count 
                FROM attendance_tbl a 
                JOIN students s ON a.Student_ID = s.ID_Number 
                JOIN departments d ON s.departments_id = d.id 
                WHERE a.Event_ID = ? 
                GROUP BY d.id, d.dept_Acronym";
        return $this->query($sql, [$eventId]);
    }

    public function getAbsentees(int $eventId, array $allowedDeptIds): array {
        // Find all active students in allowed departments who do not have an attendance record for this event
        if (empty($allowedDeptIds) || in_array(0, $allowedDeptIds)) {
            // All active students
            $sql = "SELECT s.*, d.dept_Acronym as department_name 
                    FROM students s 
                    LEFT JOIN departments d ON s.departments_id = d.id 
                    WHERE s.status = 'active' 
                      AND s.ID_Number NOT IN (SELECT Student_ID FROM attendance_tbl WHERE Event_ID = ?) 
                    ORDER BY s.Student_name ASC";
            return $this->query($sql, [$eventId]);
        }

        $placeholders = implode(',', array_fill(0, count($allowedDeptIds), '?'));
        $sql = "SELECT s.*, d.dept_Acronym as department_name 
                FROM students s 
                LEFT JOIN departments d ON s.departments_id = d.id 
                WHERE s.status = 'active' 
                  AND s.departments_id IN ($placeholders) 
                  AND s.ID_Number NOT IN (SELECT Student_ID FROM attendance_tbl WHERE Event_ID = ?) 
                ORDER BY s.Student_name ASC";
        
        $params = array_merge($allowedDeptIds, [$eventId]);
        return $this->query($sql, $params);
    }
}
