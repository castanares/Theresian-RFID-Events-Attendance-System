<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Semester;
use App\Config\Database;

class SemesterController extends Controller {
    private Semester $semesterModel;

    public function __construct() {
        $this->semesterModel = new Semester();
    }

    public function index(): void {
        $semesters = $this->semesterModel->getAll();
        $current = $this->semesterModel->getCurrentSemester();

        $this->view('admin.semesters.index', [
            'title' => 'Academic Semesters & Terms',
            'semesters' => $semesters,
            'current' => $current
        ]);
    }

    public function store(): void {
        $this->validateCsrf();
        $name  = trim($_POST['semester'] ?? '');
        $start = trim($_POST['start'] ?? '');
        $end   = trim($_POST['end'] ?? '');

        if (!$name || !$start || !$end) {
            Session::flash('error', 'All semester fields are required.');
            $this->redirect('/admin/semesters');
        }

        $db = Database::getConnection();
        $db->prepare("INSERT INTO semester (semester, start, end) VALUES (?, ?, ?)")
           ->execute([$name, $start, $end]);

        Session::flash('success', "Semester '{$name}' created.");
        $this->redirect('/admin/semesters');
    }

    public function update(string $id): void {
        $semId = (int)$id;
        $this->validateCsrf();
        $name  = trim($_POST['semester'] ?? '');
        $start = trim($_POST['start'] ?? '');
        $end   = trim($_POST['end'] ?? '');

        if (!$name || !$start || !$end) {
            Session::flash('error', 'All fields are required.');
            $this->redirect('/admin/semesters');
        }

        $db = Database::getConnection();
        $db->prepare("UPDATE semester SET semester = ?, start = ?, end = ? WHERE id = ?")
           ->execute([$name, $start, $end, $semId]);

        Session::flash('success', "Semester updated.");
        $this->redirect('/admin/semesters');
    }
}
