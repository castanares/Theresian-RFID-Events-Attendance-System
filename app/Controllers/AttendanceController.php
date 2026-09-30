<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Attendance;
use App\Models\Event;
use App\Services\AttendanceService;

class AttendanceController extends Controller {
    private Attendance $attendanceModel;
    private Event $eventModel;
    private AttendanceService $attendanceService;

    public function __construct() {
        $this->attendanceModel = new Attendance();
        $this->eventModel = new Event();
        $this->attendanceService = new AttendanceService();
    }

    public function scan(?string $id = null): void {
        $eventId = $id ? (int)$id : null;

        if (!$eventId) {
            $userDeptId = Auth::isAdmin() ? null : Auth::departmentId();
            $ongoing = $this->eventModel->getOngoingEvent($userDeptId);
            if ($ongoing) {
                $eventId = (int)$ongoing['id'];
            }
        }

        $event = $eventId ? $this->eventModel->findById($eventId) : null;
        $attendees = $eventId ? $this->attendanceModel->getByEvent($eventId) : [];

        $this->view('attendance.scan', [
            'title' => 'Live RFID Attendance Scanner',
            'event' => $event,
            'eventId' => $eventId,
            'attendees' => $attendees
        ]);
    }

    public function processScan(): void {
        $data = $this->getJsonInput();
        $eventId = (int)($data['event_id'] ?? 0);
        $rfid = trim($data['rfid'] ?? '');

        if (!$eventId || !$rfid) {
            $this->json(['success' => false, 'message' => 'Event ID and RFID are required.'], 400);
        }

        $userId = Auth::id() ?? 1;
        $result = $this->attendanceService->processScan($eventId, $rfid, $userId);

        $this->json($result);
    }

    public function getLogs(string $eventId): void {
        $id = (int)$eventId;
        $logs = $this->attendanceModel->getByEvent($id);
        $this->json(['success' => true, 'data' => $logs]);
    }

    public function manualCheckin(): void {
        $data = $this->getJsonInput();
        $eventId = (int)($data['event_id'] ?? 0);
        $idNumber = trim($data['id_number'] ?? '');

        if (!$eventId || !$idNumber) {
            $this->json(['success' => false, 'message' => 'Event ID and Student ID number are required.'], 400);
        }

        $userId = Auth::id() ?? 1;
        $result = $this->attendanceService->manualCheckin($eventId, $idNumber, $userId);

        $this->json($result);
    }

    public function deleteLog(string $id): void {
        if (!Auth::isAdmin()) {
            $this->json(['success' => false, 'error' => 'Admin privileges required.'], 403);
        }

        $logId = (int)$id;
        $this->attendanceModel->deleteRecord($logId);
        $this->json(['success' => true, 'message' => 'Log removed successfully.']);
    }
}
