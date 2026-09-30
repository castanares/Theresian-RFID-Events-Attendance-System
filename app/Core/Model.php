<?php
namespace App\Core;

use App\Config\Database;
use PDO;

abstract class Model {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    protected function query(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    protected function queryOne(string $sql, array $params = []): ?array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    protected function execute(string $sql, array $params = []): bool {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    protected function insert(string $sql, array $params = []): int {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$this->db->lastInsertId();
    }
}
