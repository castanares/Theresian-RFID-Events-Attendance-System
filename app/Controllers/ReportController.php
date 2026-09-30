<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Department;
use App\Models\Semester;

class ReportController extends Controller {
    private Attendance $attendanceModel;
    private Event $eventModel;
    private Department $departmentModel;
    private Semester $semesterModel;

    public function __construct() {
        $this->attendanceModel = new Attendance();
        $this->eventModel = new Event();
        $this->departmentModel = new Department();
        $this->semesterModel = new Semester();
    }

    public function index(): void {
        $events = $this->eventModel->getAll();
        $departments = $this->departmentModel->getAll();
        $semesters = $this->semesterModel->getAll();

        $selectedEventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : ($events[0]['id'] ?? 0);
        $tab = $_GET['tab'] ?? 'attendees'; // attendees or absentees

        $event = $selectedEventId ? $this->eventModel->findById($selectedEventId) : null;
        $attendees = $selectedEventId ? $this->attendanceModel->getByEvent($selectedEventId) : [];
        
        $absentees = [];
        if ($selectedEventId && $event) {
            $absentees = $this->attendanceModel->getAbsentees($selectedEventId, $event['allowed_department_ids']);
        }

        $this->view('reports.index', [
            'title' => 'Attendance Reports & Analytics',
            'events' => $events,
            'departments' => $departments,
            'semesters' => $semesters,
            'selectedEventId' => $selectedEventId,
            'event' => $event,
            'attendees' => $attendees,
            'absentees' => $absentees,
            'tab' => $tab
        ]);
    }

    public function printView(string $eventId): void {
        $id = (int)$eventId;
        $event = $this->eventModel->findById($id);
        if (!$event) {
            die("Event not found");
        }

        $type = $_GET['type'] ?? 'attendees';
        $attendees = $this->attendanceModel->getByEvent($id);
        $absentees = $this->attendanceModel->getAbsentees($id, $event['allowed_department_ids']);

        // Render printable standalone view (no app layout)
        $this->view('reports.print', [
            'event' => $event,
            'attendees' => $attendees,
            'absentees' => $absentees,
            'type' => $type
        ], '');
    }

    public function exportCsv(string $eventId): void {
        $id = (int)$eventId;
        $event = $this->eventModel->findById($id);
        if (!$event) die("Event not found");

        $type = $_GET['type'] ?? 'attendees';
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $event['event_name']) . "_{$type}_" . date('Ymd') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel compatibility
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        if ($type === 'absentees') {
            fputcsv($out, ['Student ID', 'Student Name', 'Course', 'Department', 'Status']);
            $absentees = $this->attendanceModel->getAbsentees($id, $event['allowed_department_ids']);
            foreach ($absentees as $s) {
                fputcsv($out, [$s['ID_Number'], $s['Student_name'], $s['Course'], $s['department_name'] ?? $s['departments_id'], 'ABSENT']);
            }
        } else {
            fputcsv($out, ['Attendance ID', 'Student ID', 'Student Name', 'Course', 'Time In', 'Time Out']);
            $attendees = $this->attendanceModel->getByEvent($id);
            foreach ($attendees as $a) {
                fputcsv($out, [$a['Attendance_ID'], $a['Student_ID'], $a['Student_name'], $a['Course'], $a['Time_in'], $a['Time_out']]);
            }
        }

        fclose($out);
        exit;
    }
}
