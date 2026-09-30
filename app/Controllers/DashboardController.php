<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Student;
use App\Models\Semester;

class DashboardController extends Controller {
    private Attendance $attendanceModel;
    private Event $eventModel;
    private Student $studentModel;
    private Semester $semesterModel;

    public function __construct() {
        $this->attendanceModel = new Attendance();
        $this->eventModel = new Event();
        $this->studentModel = new Student();
        $this->semesterModel = new Semester();
    }

    public function index(): void {
        $currentSem = $this->semesterModel->getCurrentSemester();
        $schoolYear = Semester::getSchoolYear();

        $userDeptId = Auth::isAdmin() ? null : Auth::departmentId();

        $stats = [
            'total_students' => $this->studentModel->countActive(),
            'today_scans' => $this->attendanceModel->countTodayScans(),
            'current_semester' => $currentSem['semester'] ?? '1st Semester',
            'school_year' => $schoolYear,
        ];

        // Fetch ongoing event for today
        $ongoingEvent = $this->eventModel->getOngoingEvent($userDeptId);

        // Fetch active events today
        $todayEvents = $this->eventModel->getTodayActiveEvents();

        // Chart data
        $scans7Days = $this->attendanceModel->getScansLast7Days();
        
        $deptAttendance = [];
        if ($ongoingEvent) {
            $deptAttendance = $this->attendanceModel->getAttendanceByDepartment((int)$ongoingEvent['id']);
        }

        $this->view('dashboard.index', [
            'title' => 'Dashboard - STC Attendance',
            'stats' => $stats,
            'ongoingEvent' => $ongoingEvent,
            'todayEvents' => $todayEvents,
            'scans7Days' => $scans7Days,
            'deptAttendance' => $deptAttendance,
        ]);
    }
}
