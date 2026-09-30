<?php
namespace App\Models;

use App\Core\Model;

class User extends Model {
    public function findByUsername(string $username): ?array {
        return $this->queryOne("SELECT * FROM user WHERE username = ? LIMIT 1", [$username]);
    }

    public function findById(int $id): ?array {
        return $this->queryOne("SELECT * FROM user WHERE id = ? LIMIT 1", [$id]);
    }

    public function getAll(): array {
        return $this->query("SELECT u.*, d.dept_Acronym as department_acronym 
                              FROM user u 
                              LEFT JOIN departments d ON u.departments_id = d.id 
                              ORDER BY u.id DESC");
    }

    public function create(array $data): int {
        $sql = "INSERT INTO user (username, first_name, last_name, password, departments_id, account_type, status, email, phone) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        return $this->insert($sql, [
            $data['username'],
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['departments_id'] ?? 0,
            $data['account_type'] ?? 'user',
            $data['status'] ?? 'active',
            $data['email'] ?? null,
            $data['phone'] ?? null
        ]);
    }

    public function updateProfile(int $id, array $data): bool {
        $sql = "UPDATE user SET first_name = ?, last_name = ?, email = ?, phone = ?, description = ? WHERE id = ?";
        return $this->execute($sql, [
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['description'] ?? null,
            $id
        ]);
    }

    public function updatePassword(int $id, string $plainPassword): bool {
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        return $this->execute("UPDATE user SET password = ? WHERE id = ?", [$hash, $id]);
    }

    public function updateProfilePic(int $id, string $path): bool {
        return $this->execute("UPDATE user SET profile_pic = ? WHERE id = ?", [$path, $id]);
    }

    public function updateLastLogin(int $id): bool {
        return $this->execute("UPDATE user SET last_login = NOW() WHERE id = ?", [$id]);
    }
}
