<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Event;
use App\Models\Department;
use App\Models\Semester;

class EventController extends Controller {
    private Event $eventModel;
    private Department $departmentModel;
    private Semester $semesterModel;

    public function __construct() {
        $this->eventModel = new Event();
        $this->departmentModel = new Department();
        $this->semesterModel = new Semester();
    }

    public function index(): void {
        $status = $_GET['status'] ?? 'active';
        $semesterId = $_GET['semester_id'] ?? null;
        $deptId = Auth::isAdmin() ? ($_GET['department_id'] ?? null) : Auth::departmentId();

        $filters = ['status' => $status];
        if ($semesterId) $filters['semester_id'] = $semesterId;
        if ($deptId !== null) $filters['department_id'] = $deptId;

        $events = $this->eventModel->getAll($filters);
        $departments = $this->departmentModel->getAll();
        $semesters = $this->semesterModel->getAll();

        $this->view('events.index', [
            'title' => 'Events Management - STC Attendance',
            'events' => $events,
            'departments' => $departments,
            'semesters' => $semesters,
            'currentFilter' => $filters
        ]);
    }

    public function store(): void {
        $data = $_POST;
        $this->validateCsrf();

        $eventName   = trim($data['event_name'] ?? '');
        $date        = trim($data['date'] ?? '');
        $semesterId  = (int)($data['semester_id'] ?? 1);
        $hostDeptId  = (int)($data['departments_id'] ?? 0);
        $timeInStart = trim($data['TimeIn_start'] ?? '');
        $timeInEnd   = trim($data['TimeIn_end'] ?? '');
        $timeOutIn   = trim($data['Time_out'] ?? '');
        $timeOutEnd  = trim($data['Timeout_out'] ?? '');
        $otherDepts  = $data['other_departments'] ?? [];

        if (!$eventName || !$date || !$timeInStart || !$timeInEnd || !$timeOutIn || !$timeOutEnd) {
            Session::flash('error', 'All event schedule and time fields are required.');
            $this->redirect('/events');
        }

        $userId = Auth::id() ?? 1;

        $this->eventModel->create([
            'user_id' => $userId,
            'event_name' => $eventName,
            'date' => $date,
            'semester_id' => $semesterId,
            'departments_id' => $hostDeptId,
            'TimeIn_start' => $timeInStart,
            'TimeIn_end' => $timeInEnd,
            'Time_out' => $timeOutIn,
            'Timeout_out' => $timeOutEnd,
        ], $otherDepts);

        Session::flash('success', "Event '{$eventName}' created successfully!");
        $this->redirect('/events');
    }

    public function update(string $id): void {
        $eventId = (int)$id;
        $data = $_POST;
        $this->validateCsrf();

        $eventName   = trim($data['event_name'] ?? '');
        $date        = trim($data['date'] ?? '');
        $semesterId  = (int)($data['semester_id'] ?? 1);
        $hostDeptId  = (int)($data['departments_id'] ?? 0);
        $timeInStart = trim($data['TimeIn_start'] ?? '');
        $timeInEnd   = trim($data['TimeIn_end'] ?? '');
        $timeOutIn   = trim($data['Time_out'] ?? '');
        $timeOutEnd  = trim($data['Timeout_out'] ?? '');
        $otherDepts  = $data['other_departments'] ?? [];

        if (!$eventName || !$date) {
            Session::flash('error', 'Event name and date are required.');
            $this->redirect('/events');
        }

        $this->eventModel->update($eventId, [
            'event_name' => $eventName,
            'date' => $date,
            'semester_id' => $semesterId,
            'departments_id' => $hostDeptId,
            'TimeIn_start' => $timeInStart,
            'TimeIn_end' => $timeInEnd,
            'Time_out' => $timeOutIn,
            'Timeout_out' => $timeOutEnd,
        ], $otherDepts);

        Session::flash('success', "Event updated successfully!");
        $this->redirect('/events');
    }

    public function archive(string $id): void {
        $eventId = (int)$id;
        $this->eventModel->archive($eventId);
        Session::flash('success', 'Event moved to archive.');
        $this->redirect('/events');
    }

    public function restore(string $id): void {
        $eventId = (int)$id;
        $this->eventModel->restore($eventId);
        Session::flash('success', 'Event restored to active.');
        $this->redirect('/events?status=archived');
    }
}
