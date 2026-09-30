<?php
namespace App\Models;

use App\Core\Model;

class Semester extends Model {
    public function getAll(): array {
        return $this->query("SELECT * FROM semester ORDER BY id ASC");
    }

    public function findById(int $id): ?array {
        return $this->queryOne("SELECT * FROM semester WHERE id = ? LIMIT 1", [$id]);
    }

    public function getCurrentSemester(): ?array {
        $currentMonth = (int)date('m');
        $sql = "SELECT * FROM semester 
                WHERE (MONTH(start) <= ? AND MONTH(end) >= ?) 
                   OR (MONTH(start) > MONTH(end) AND (? >= MONTH(start) OR ? <= MONTH(end))) 
                ORDER BY id LIMIT 1";
        $sem = $this->queryOne($sql, [$currentMonth, $currentMonth, $currentMonth, $currentMonth]);
        return $sem ?: $this->findById(1);
    }

    public static function getSchoolYear(string $dateString = 'now'): string {
        $timestamp = strtotime($dateString);
        $year = (int)date('Y', $timestamp);
        $month = (int)date('n', $timestamp);
        if ($month >= 6) {
            return $year . '-' . ($year + 1);
        } else {
            return ($year - 1) . '-' . $year;
        }
    }
}
