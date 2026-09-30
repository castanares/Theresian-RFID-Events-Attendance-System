<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Event;
use App\Models\Student;
use App\Models\Department;
use App\Models\User;
use App\Config\Database;

class ArchiveController extends Controller {
    private Event $eventModel;
    private Student $studentModel;
    private Department $departmentModel;
    private User $userModel;

    public function __construct() {
        $this->eventModel = new Event();
        $this->studentModel = new Student();
        $this->departmentModel = new Department();
        $this->userModel = new User();
    }

    public function index(): void {
        $tab = $_GET['tab'] ?? 'students';

        $archivedStudents = $this->studentModel->getAll(['status' => 'archived']);
        $archivedEvents   = $this->eventModel->getAll(['status' => 'archived']);
        $archivedDepts    = array_filter($this->departmentModel->getAll(true), fn($d) => $d['status'] === 'archived' || $d['archived'] == 1);
        $archivedUsers    = array_filter($this->userModel->getAll(), fn($u) => $u['status'] === 'archived');

        $this->view('admin.archived.index', [
            'title' => 'System Archive Center',
            'tab' => $tab,
            'archivedStudents' => $archivedStudents,
            'archivedEvents' => $archivedEvents,
            'archivedDepts' => $archivedDepts,
            'archivedUsers' => $archivedUsers,
        ]);
    }

    public function permanentDelete(string $type, string $id): void {
        $this->validateCsrf();
        $recordId = (int)$id;
        $db = Database::getConnection();

        switch ($type) {
            case 'student':
                $db->prepare("DELETE FROM students WHERE id = ?")->execute([$recordId]);
                Session::flash('success', 'Student record permanently deleted.');
                break;
            case 'event':
                $db->prepare("DELETE FROM attendance_tbl WHERE Event_ID = ?")->execute([$recordId]);
                $db->prepare("DELETE FROM event_other_departments WHERE event_id = ?")->execute([$recordId]);
                $db->prepare("DELETE FROM events WHERE id = ?")->execute([$recordId]);
                Session::flash('success', 'Event and its attendance logs permanently deleted.');
                break;
            case 'department':
                $db->prepare("DELETE FROM departments WHERE id = ?")->execute([$recordId]);
                Session::flash('success', 'Department permanently deleted.');
                break;
            case 'user':
                $db->prepare("DELETE FROM user WHERE id = ?")->execute([$recordId]);
                Session::flash('success', 'User account permanently deleted.');
                break;
            default:
                Session::flash('error', 'Invalid deletion type.');
        }

        $this->redirect('/admin/archived?tab=' . $type . 's');
    }
}
