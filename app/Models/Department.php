<?php
namespace App\Models;

use App\Core\Model;

class Department extends Model {
    public function getAll(bool $includeArchived = false): array {
        $sql = "SELECT * FROM departments";
        if (!$includeArchived) {
            $sql .= " WHERE status = 'active' AND archived = 0";
        }
        $sql .= " ORDER BY Dept_name ASC";
        return $this->query($sql);
    }

    public function findById(int $id): ?array {
        return $this->queryOne("SELECT * FROM departments WHERE id = ? LIMIT 1", [$id]);
    }

    public function create(array $data): int {
        $sql = "INSERT INTO departments (Dept_name, dept_Acronym, contact_email, contact_phone, department_head, office_location, description, status, archived) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'active', 0)";
        return $this->insert($sql, [
            $data['Dept_name'],
            $data['dept_Acronym'],
            $data['contact_email'] ?? null,
            $data['contact_phone'] ?? null,
            $data['department_head'] ?? null,
            $data['office_location'] ?? null,
            $data['description'] ?? null
        ]);
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE departments 
                SET Dept_name = ?, dept_Acronym = ?, contact_email = ?, contact_phone = ?, department_head = ?, office_location = ?, description = ? 
                WHERE id = ?";
        return $this->execute($sql, [
            $data['Dept_name'],
            $data['dept_Acronym'],
            $data['contact_email'] ?? null,
            $data['contact_phone'] ?? null,
            $data['department_head'] ?? null,
            $data['office_location'] ?? null,
            $data['description'] ?? null,
            $id
        ]);
    }

    public function archive(int $id): bool {
        return $this->execute("UPDATE departments SET status = 'archived', archived = 1 WHERE id = ?", [$id]);
    }

    public function restore(int $id): bool {
        return $this->execute("UPDATE departments SET status = 'active', archived = 0 WHERE id = ?", [$id]);
    }
}
