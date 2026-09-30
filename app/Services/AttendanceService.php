<?php
namespace App\Services;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Student;

class AttendanceService {
    private Attendance $attendanceModel;
    private Event $eventModel;
    private Student $studentModel;

    public function __construct() {
        $this->attendanceModel = new Attendance();
        $this->eventModel = new Event();
        $this->studentModel = new Student();
    }

    public function processScan(int $eventId, string $rfid, int $userId): array {
        date_default_timezone_set('Asia/Manila');
        $now = date('Y-m-d H:i:s');
        $currentTime = strtotime(date('H:i'));
        $currentDate = date('Y-m-d');

        // 1. Validate Event
        $event = $this->eventModel->findById($eventId);
        if (!$event) {
            return ['success' => false, 'message' => 'Event not found', 'type' => 'error'];
        }

        if ($event['status'] !== 'active') {
            return ['success' => false, 'message' => 'This event is archived', 'type' => 'error'];
        }

        if ($event['date'] !== $currentDate) {
            return ['success' => false, 'message' => 'Event schedule is set for ' . date('M j, Y', strtotime($event['date'])), 'type' => 'warning'];
        }

        // 2. Find Student
        $student = $this->studentModel->findByRfid($rfid);
        if (!$student) {
            return ['success' => false, 'message' => 'Unregistered RFID tag (' . htmlspecialchars($rfid) . ')', 'type' => 'error'];
        }

        if ($student['status'] !== 'active') {
            return ['success' => false, 'message' => "Student {$student['Student_name']} is {$student['status']}", 'type' => 'error'];
        }

        // 3. Validate Department Eligibility
        $allowedDepts = $event['allowed_department_ids'];
        $studentDept = (int)$student['departments_id'];

        if (!in_array(0, $allowedDepts) && !in_array($studentDept, $allowedDepts)) {
            return [
                'success' => false,
                'message' => "Student department ({$student['dept_Acronym']}) is not allowed for this event",
                'type' => 'error',
                'student' => $student
            ];
        }

        // 4. Calculate Time Windows
        $timeInStart  = strtotime($event['TimeIn_start']);
        $timeInEnd    = strtotime($event['TimeIn_end']);
        $timeOutStart = strtotime($event['Time_out']);
        $timeOutEnd   = strtotime($event['Timeout_out']);

        // 5. Check Existing Attendance
        $existing = $this->attendanceModel->findStudentAttendance($eventId, $student['ID_Number']);
        $hasTimeIn  = !empty($existing['Time_in']);
        $hasTimeOut = !empty($existing['Time_out']);

        // 6. Evaluate Action
        if (!$hasTimeIn) {
            if ($currentTime < $timeInStart) {
                return ['success' => false, 'message' => 'Time-In has not started yet. Starts at ' . date('h:i A', $timeInStart), 'type' => 'warning', 'student' => $student];
            }

            if ($currentTime >= $timeInStart && $currentTime <= $timeInEnd) {
                // On-Time In
                $this->attendanceModel->recordTimeIn($userId, $eventId, $event['event_name'], $student['ID_Number'], $student['Student_name'], $student['Course'], $now);
                return [
                    'success' => true,
                    'status' => 'ON-TIME',
                    'action' => 'TIME-IN',
                    'message' => 'Time-In Recorded (On Time)',
                    'type' => 'success',
                    'time' => date('h:i:s A'),
                    'student' => $student
                ];
            }

            if ($currentTime > $timeInEnd && $currentTime < $timeOutStart) {
                // Late In
                $this->attendanceModel->recordTimeIn($userId, $eventId, $event['event_name'], $student['ID_Number'], $student['Student_name'], $student['Course'], $now);
                return [
                    'success' => true,
                    'status' => 'LATE',
                    'action' => 'TIME-IN',
                    'message' => 'Time-In Recorded (Late)',
                    'type' => 'warning',
                    'time' => date('h:i:s A'),
                    'student' => $student
                ];
            }

            if ($currentTime >= $timeOutStart && $currentTime <= $timeOutEnd) {
                return ['success' => false, 'message' => 'Student did not Time-In during arrival window', 'type' => 'error', 'student' => $student];
            }

            if ($currentTime > $timeOutEnd) {
                return ['success' => false, 'message' => 'Event attendance has already ended', 'type' => 'error', 'student' => $student];
            }
        }

        if ($hasTimeIn && !$hasTimeOut) {
            if ($currentTime < $timeOutStart) {
                return [
                    'success' => false,
                    'message' => 'Already Timed-In at ' . date('h:i A', strtotime($existing['Time_in'])) . '. Time-Out starts at ' . date('h:i A', $timeOutStart),
                    'type' => 'info',
                    'student' => $student
                ];
            }

            if ($currentTime >= $timeOutStart && $currentTime <= $timeOutEnd) {
                $this->attendanceModel->recordTimeOut($eventId, $student['ID_Number'], $now);
                return [
                    'success' => true,
                    'status' => 'COMPLETED',
                    'action' => 'TIME-OUT',
                    'message' => 'Time-Out Recorded Successfully',
                    'type' => 'success',
                    'time' => date('h:i:s A'),
                    'student' => $student
                ];
            }

            if ($currentTime > $timeOutEnd) {
                return ['success' => false, 'message' => 'Time-Out window has closed', 'type' => 'error', 'student' => $student];
            }
        }

        if ($hasTimeIn && $hasTimeOut) {
            return [
                'success' => false,
                'message' => 'Attendance already complete (In: ' . date('h:i A', strtotime($existing['Time_in'])) . ', Out: ' . date('h:i A', strtotime($existing['Time_out'])) . ')',
                'type' => 'info',
                'student' => $student
            ];
        }

        return ['success' => false, 'message' => 'Invalid scanning window. Please check the event schedule.', 'type' => 'error'];
    }

    public function manualCheckin(int $eventId, int|string $idNumber, int $userId): array {
        date_default_timezone_set('Asia/Manila');
        $now = date('Y-m-d H:i:s');

        $event = $this->eventModel->findById($eventId);
        if (!$event) return ['success' => false, 'message' => 'Event not found'];

        $student = $this->studentModel->findByIdNumber($idNumber);
        if (!$student) return ['success' => false, 'message' => 'Student ID not found'];

        $existing = $this->attendanceModel->findStudentAttendance($eventId, $student['ID_Number']);
        if (!$existing) {
            $this->attendanceModel->recordTimeIn($userId, $eventId, $event['event_name'], $student['ID_Number'], $student['Student_name'], $student['Course'], $now);
            return ['success' => true, 'message' => "Manual Time-In recorded for {$student['Student_name']}", 'action' => 'TIME-IN'];
        }

        if (empty($existing['Time_out'])) {
            $this->attendanceModel->recordTimeOut($eventId, $student['ID_Number'], $now);
            return ['success' => true, 'message' => "Manual Time-Out recorded for {$student['Student_name']}", 'action' => 'TIME-OUT'];
        }

        return ['success' => false, 'message' => "Student {$student['Student_name']} already completed both In & Out"];
    }
}
