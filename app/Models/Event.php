<?php
namespace App\Models;

use App\Core\Model;

class Event extends Model {
    public function findById(int $id): ?array {
        $event = $this->queryOne("SELECT e.*, d.dept_Acronym as host_department_acronym, s.semester as semester_name 
                                  FROM events e 
                                  LEFT JOIN departments d ON e.departments_id = d.id 
                                  LEFT JOIN semester s ON e.semester_id = s.id 
                                  WHERE e.id = ? LIMIT 1", [$id]);
        if ($event) {
            $event['other_department_ids'] = $this->getOtherDepartmentIds($id);
            $event['allowed_department_ids'] = $this->getAllowedDepartmentIds($event);
        }
        return $event;
    }

    public function getAll(array $filters = []): array {
        $sql = "SELECT e.*, d.dept_Acronym as host_department, s.semester as semester_name,
                       (SELECT COUNT(*) FROM attendance_tbl a WHERE a.Event_ID = e.id) as attendee_count 
                FROM events e 
                LEFT JOIN departments d ON e.departments_id = d.id 
                LEFT JOIN semester s ON e.semester_id = s.id 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND e.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['semester_id'])) {
            $sql .= " AND e.semester_id = ?";
            $params[] = $filters['semester_id'];
        }

        if (isset($filters['department_id']) && $filters['department_id'] !== '') {
            $deptId = (int)$filters['department_id'];
            if ($deptId > 0) {
                $sql .= " AND (e.departments_id = ? OR e.departments_id = 0 OR e.id IN (SELECT event_id FROM event_other_departments WHERE department_id = ?))";
                $params[] = $deptId;
                $params[] = $deptId;
            }
        }

        $sql .= " ORDER BY e.date DESC, e.TimeIn_start DESC";
        return $this->query($sql, $params);
    }

    public function getTodayActiveEvents(): array {
        $today = date('Y-m-d');
        return $this->query("SELECT e.*, d.dept_Acronym as host_department 
                             FROM events e 
                             LEFT JOIN departments d ON e.departments_id = d.id 
                             WHERE e.date = ? AND e.status = 'active' 
                             ORDER BY e.TimeIn_start ASC", [$today]);
    }

    public function getOngoingEvent(?int $departmentId = null): ?array {
        $today = date('Y-m-d');
        $nowTime = date('H:i:s');
        
        $sql = "SELECT e.*, d.dept_Acronym as host_department 
                FROM events e 
                LEFT JOIN departments d ON e.departments_id = d.id 
                WHERE e.date = ? AND e.status = 'active'";
        $params = [$today];

        if ($departmentId !== null && $departmentId > 0) {
            $sql .= " AND (e.departments_id = ? OR e.departments_id = 0 OR e.id IN (SELECT event_id FROM event_other_departments WHERE department_id = ?))";
            $params[] = $departmentId;
            $params[] = $departmentId;
        }

        $events = $this->query($sql, $params);
        foreach ($events as $ev) {
            // Check if current time is within TimeIn_start and Timeout_out
            $start = $ev['TimeIn_start'];
            $end = $ev['Timeout_out'];
            if ($nowTime >= $start && $nowTime <= $end) {
                $ev['other_department_ids'] = $this->getOtherDepartmentIds((int)$ev['id']);
                $ev['allowed_department_ids'] = $this->getAllowedDepartmentIds($ev);
                return $ev;
            }
        }

        // Return first active event today if none currently within exact window
        if (!empty($events)) {
            $ev = $events[0];
            $ev['other_department_ids'] = $this->getOtherDepartmentIds((int)$ev['id']);
            $ev['allowed_department_ids'] = $this->getAllowedDepartmentIds($ev);
            return $ev;
        }

        return null;
    }

    public function create(array $data, array $otherDepartments = []): int {
        $sql = "INSERT INTO events (user_id, event_name, date, semester_id, departments_id, other_dept, TimeIn_start, TimeIn_end, Time_out, Timeout_out, status) 
                VALUES (?, ?, ?, ?, ?, '', ?, ?, ?, ?, 'active')";
        $eventId = $this->insert($sql, [
            $data['user_id'],
            $data['event_name'],
            $data['date'],
            $data['semester_id'],
            $data['departments_id'],
            $data['TimeIn_start'],
            $data['TimeIn_end'],
            $data['Time_out'],
            $data['Timeout_out']
        ]);

        $this->syncOtherDepartments($eventId, $otherDepartments);
        return $eventId;
    }

    public function update(int $id, array $data, array $otherDepartments = []): bool {
        $sql = "UPDATE events 
                SET event_name = ?, date = ?, semester_id = ?, departments_id = ?, TimeIn_start = ?, TimeIn_end = ?, Time_out = ?, Timeout_out = ? 
                WHERE id = ?";
        $res = $this->execute($sql, [
            $data['event_name'],
            $data['date'],
            $data['semester_id'],
            $data['departments_id'],
            $data['TimeIn_start'],
            $data['TimeIn_end'],
            $data['Time_out'],
            $data['Timeout_out'],
            $id
        ]);

        $this->syncOtherDepartments($id, $otherDepartments);
        return $res;
    }

    public function archive(int $id): bool {
        return $this->execute("UPDATE events SET status = 'archived' WHERE id = ?", [$id]);
    }

    public function restore(int $id): bool {
        return $this->execute("UPDATE events SET status = 'active' WHERE id = ?", [$id]);
    }

    public function getOtherDepartmentIds(int $eventId): array {
        $rows = $this->query("SELECT department_id FROM event_other_departments WHERE event_id = ?", [$eventId]);
        return array_column($rows, 'department_id');
    }

    public function getAllowedDepartmentIds(array $event): array {
        $hostDept = (int)($event['departments_id'] ?? 0);
        if ($hostDept === 0) {
            return [0]; // School-wide (all departments allowed)
        }
        $allowed = [$hostDept];
        $others = $this->getOtherDepartmentIds((int)$event['id']);
        return array_unique(array_merge($allowed, $others));
    }

    private function syncOtherDepartments(int $eventId, array $departmentIds): void {
        $this->execute("DELETE FROM event_other_departments WHERE event_id = ?", [$eventId]);
        foreach ($departmentIds as $deptId) {
            $deptId = (int)$deptId;
            if ($deptId > 0) {
                $this->execute("INSERT INTO event_other_departments (event_id, department_id) VALUES (?, ?)", [$eventId, $deptId]);
            }
        }
    }
}
