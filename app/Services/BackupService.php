<?php
namespace App\Services;

use App\Config\Database;
use App\Config\Config;
use ZipArchive;

class BackupService {
    private string $storageDir;

    public function __construct() {
        $this->storageDir = dirname(__DIR__, 2) . '/storage/backups';
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    public function createDatabaseDump(): string {
        $pdo = Database::getConnection();
        $timestamp = date('Ymd_His');
        $filename = "db_backup_{$timestamp}.sql";
        $filepath = $this->storageDir . '/' . $filename;

        $sql = "-- STC Attendance Auto-Backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $createTable = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createTable['Create Table'] . ";\n\n";

            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll();
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $escaped = array_map(function($val) use ($pdo) {
                        return $val === null ? 'NULL' : $pdo->quote((string)$val);
                    }, $row);
                    $sql .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $escaped) . ");\n";
                }
                $sql .= "\n";
            }
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($filepath, $sql);
        $this->pruneOldBackups(5);
        return $filename;
    }

    private function pruneOldBackups(int $keepCount): void {
        $files = glob($this->storageDir . '/*.sql') ?: [];
        if (count($files) > $keepCount) {
            usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
            $toDelete = array_slice($files, $keepCount);
            foreach ($toDelete as $f) {
                @unlink($f);
            }
        }
    }
}
