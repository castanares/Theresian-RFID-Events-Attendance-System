<?php
namespace App\Models;

use App\Core\Model;

class Student extends Model {
    public function findByRfid(string $rfid): ?array {
        return $this->queryOne("SELECT s.*, d.Dept_name, d.dept_Acronym 
                                FROM students s 
                                LEFT JOIN departments d ON s.departments_id = d.id 
                                WHERE s.RFID = ? LIMIT 1", [$rfid]);
    }

    public function findByIdNumber(int|string $idNumber): ?array {
        return $this->queryOne("SELECT s.*, d.Dept_name, d.dept_Acronym 
                                FROM students s 
                                LEFT JOIN departments d ON s.departments_id = d.id 
                                WHERE s.ID_Number = ? LIMIT 1", [$idNumber]);
    }

    public function findById(int $id): ?array {
        return $this->queryOne("SELECT * FROM students WHERE id = ? LIMIT 1", [$id]);
    }

    public function getAll(array $filters = []): array {
        $sql = "SELECT s.*, d.dept_Acronym as department_name 
                FROM students s 
                LEFT JOIN departments d ON s.departments_id = d.id 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND s.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND s.departments_id = ?";
            $params[] = $filters['department_id'];
        }

        if (!empty($filters['course'])) {
            $sql .= " AND s.Course = ?";
            $params[] = $filters['course'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (s.Student_name LIKE ? OR s.ID_Number LIKE ? OR s.RFID LIKE ? OR s.Course LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY s.Student_name ASC";
        return $this->query($sql, $params);
    }

    public function getDistinctCourses(): array {
        $rows = $this->query("SELECT DISTINCT Course FROM students WHERE Course IS NOT NULL AND Course != '' ORDER BY Course ASC");
        return array_column($rows, 'Course');
    }

    public function create(array $data): int {
        $sql = "INSERT INTO students (ID_Number, RFID, Student_name, Course, departments_id, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        return $this->insert($sql, [
            $data['ID_Number'],
            $data['RFID'],
            $data['Student_name'],
            $data['Course'],
            $data['departments_id'],
            $data['status'] ?? 'active'
        ]);
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE students 
                SET ID_Number = ?, RFID = ?, Student_name = ?, Course = ?, departments_id = ? 
                WHERE id = ?";
        return $this->execute($sql, [
            $data['ID_Number'],
            $data['RFID'],
            $data['Student_name'],
            $data['Course'],
            $data['departments_id'],
            $id
        ]);
    }

    public function archive(int $id, string $cause = 'Archived', ?string $year = null): bool {
        $year = $year ?: date('Y');
        $sql = "UPDATE students SET status = 'archived', cause = ?, archived_stamp = ? WHERE id = ?";
        return $this->execute($sql, [$cause, $year, $id]);
    }

    public function restore(int $id): bool {
        $sql = "UPDATE students SET status = 'active', cause = '', archived_stamp = '' WHERE id = ?";
        return $this->execute($sql, [$id]);
    }

    public function rfidExists(string $rfid, ?int $excludeId = null): bool {
        if ($excludeId) {
            $row = $this->queryOne("SELECT id FROM students WHERE RFID = ? AND id != ? LIMIT 1", [$rfid, $excludeId]);
        } else {
            $row = $this->queryOne("SELECT id FROM students WHERE RFID = ? LIMIT 1", [$rfid]);
        }
        return $row !== null;
    }

    public function idNumberExists(int|string $idNumber, ?int $excludeId = null): bool {
        if ($excludeId) {
            $row = $this->queryOne("SELECT id FROM students WHERE ID_Number = ? AND id != ? LIMIT 1", [$idNumber, $excludeId]);
        } else {
            $row = $this->queryOne("SELECT id FROM students WHERE ID_Number = ? LIMIT 1", [$idNumber]);
        }
        return $row !== null;
    }

    public function countActive(): int {
        $res = $this->queryOne("SELECT COUNT(*) as cnt FROM students WHERE status = 'active'");
        return (int)($res['cnt'] ?? 0);
    }
}
