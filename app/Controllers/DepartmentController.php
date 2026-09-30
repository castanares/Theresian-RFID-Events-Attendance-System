<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Department;

class DepartmentController extends Controller {
    private Department $departmentModel;

    public function __construct() {
        $this->departmentModel = new Department();
    }

    public function index(): void {
        $includeArchived = isset($_GET['archived']) && $_GET['archived'] === '1';
        $departments = $this->departmentModel->getAll($includeArchived);

        $this->view('departments.index', [
            'title' => 'Academic Departments - STC Attendance',
            'departments' => $departments,
            'includeArchived' => $includeArchived
        ]);
    }

    public function store(): void {
        $data = $_POST;
        $this->validateCsrf();

        $name    = trim($data['Dept_name'] ?? '');
        $acronym = trim($data['dept_Acronym'] ?? '');

        if (!$name || !$acronym) {
            Session::flash('error', 'Department name and acronym are required.');
            $this->redirect('/departments');
        }

        $this->departmentModel->create([
            'Dept_name' => $name,
            'dept_Acronym' => $acronym,
            'contact_email' => trim($data['contact_email'] ?? '') ?: null,
            'contact_phone' => trim($data['contact_phone'] ?? '') ?: null,
            'department_head' => trim($data['department_head'] ?? '') ?: null,
            'office_location' => trim($data['office_location'] ?? '') ?: null,
            'description' => trim($data['description'] ?? '') ?: null
        ]);

        Session::flash('success', "Department '{$name}' created successfully!");
        $this->redirect('/departments');
    }

    public function update(string $id): void {
        $deptId = (int)$id;
        $data = $_POST;
        $this->validateCsrf();

        $name    = trim($data['Dept_name'] ?? '');
        $acronym = trim($data['dept_Acronym'] ?? '');

        if (!$name || !$acronym) {
            Session::flash('error', 'Department name and acronym are required.');
            $this->redirect('/departments');
        }

        $this->departmentModel->update($deptId, [
            'Dept_name' => $name,
            'dept_Acronym' => $acronym,
            'contact_email' => trim($data['contact_email'] ?? '') ?: null,
            'contact_phone' => trim($data['contact_phone'] ?? '') ?: null,
            'department_head' => trim($data['department_head'] ?? '') ?: null,
            'office_location' => trim($data['office_location'] ?? '') ?: null,
            'description' => trim($data['description'] ?? '') ?: null
        ]);

        Session::flash('success', "Department updated successfully!");
        $this->redirect('/departments');
    }

    public function archive(string $id): void {
        $deptId = (int)$id;
        $this->departmentModel->archive($deptId);
        Session::flash('success', 'Department archived.');
        $this->redirect('/departments');
    }

    public function restore(string $id): void {
        $deptId = (int)$id;
        $this->departmentModel->restore($deptId);
        Session::flash('success', 'Department restored.');
        $this->redirect('/departments?archived=1');
    }
}
