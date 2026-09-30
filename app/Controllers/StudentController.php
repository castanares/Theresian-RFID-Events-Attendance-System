<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Student;
use App\Models\Department;

class StudentController extends Controller {
    private Student $studentModel;
    private Department $departmentModel;

    public function __construct() {
        $this->studentModel = new Student();
        $this->departmentModel = new Department();
    }

    public function index(): void {
        $status = $_GET['status'] ?? 'active';
        $deptId = Auth::isAdmin() ? ($_GET['department_id'] ?? null) : Auth::departmentId();
        $search = trim($_GET['search'] ?? '');

        $filters = ['status' => $status];
        if ($deptId) $filters['department_id'] = $deptId;
        if ($search) $filters['search'] = $search;

        $students = $this->studentModel->getAll($filters);
        $departments = $this->departmentModel->getAll();

        $this->view('students.index', [
            'title' => 'Student Directory - STC Attendance',
            'students' => $students,
            'departments' => $departments,
            'currentFilter' => $filters,
            'search' => $search
        ]);
    }

    public function store(): void {
        $data = $_POST;
        $this->validateCsrf();

        $idNumber    = trim($data['ID_Number'] ?? '');
        $rfid        = trim($data['RFID'] ?? '');
        $studentName = trim($data['Student_name'] ?? '');
        $course      = trim($data['Course'] ?? '');
        $deptId      = trim($data['departments_id'] ?? '');

        if (!$idNumber || !$rfid || !$studentName || !$course || !$deptId) {
            Session::flash('error', 'All student fields are required.');
            $this->redirect('/students');
        }

        if ($this->studentModel->idNumberExists($idNumber)) {
            Session::flash('error', "Student ID '{$idNumber}' is already registered.");
            $this->redirect('/students');
        }

        if ($this->studentModel->rfidExists($rfid)) {
            Session::flash('error', "RFID tag '{$rfid}' is already assigned to another student.");
            $this->redirect('/students');
        }

        $this->studentModel->create([
            'ID_Number' => $idNumber,
            'RFID' => $rfid,
            'Student_name' => $studentName,
            'Course' => $course,
            'departments_id' => $deptId,
            'status' => 'active'
        ]);

        Session::flash('success', "Student '{$studentName}' registered successfully!");
        $this->redirect('/students');
    }

    public function update(string $id): void {
        $studentId = (int)$id;
        $data = $_POST;
        $this->validateCsrf();

        $idNumber    = trim($data['ID_Number'] ?? '');
        $rfid        = trim($data['RFID'] ?? '');
        $studentName = trim($data['Student_name'] ?? '');
        $course      = trim($data['Course'] ?? '');
        $deptId      = trim($data['departments_id'] ?? '');

        if (!$idNumber || !$rfid || !$studentName || !$course || !$deptId) {
            Session::flash('error', 'All student fields are required.');
            $this->redirect('/students');
        }

        if ($this->studentModel->idNumberExists($idNumber, $studentId)) {
            Session::flash('error', "Student ID '{$idNumber}' is already registered to someone else.");
            $this->redirect('/students');
        }

        if ($this->studentModel->rfidExists($rfid, $studentId)) {
            Session::flash('error', "RFID tag '{$rfid}' is already assigned to another student.");
            $this->redirect('/students');
        }

        $this->studentModel->update($studentId, [
            'ID_Number' => $idNumber,
            'RFID' => $rfid,
            'Student_name' => $studentName,
            'Course' => $course,
            'departments_id' => $deptId
        ]);

        Session::flash('success', "Student record updated successfully!");
        $this->redirect('/students');
    }

    public function archive(string $id): void {
        $studentId = (int)$id;
        $cause = trim($_POST['cause'] ?? 'Discontinued');
        $year  = trim($_POST['year'] ?? date('Y'));

        $this->studentModel->archive($studentId, $cause, $year);
        Session::flash('success', 'Student record archived.');
        $this->redirect('/students');
    }

    public function restore(string $id): void {
        $studentId = (int)$id;
        $this->studentModel->restore($studentId);
        Session::flash('success', 'Student record restored to active.');
        $this->redirect('/students?status=archived');
    }
}
