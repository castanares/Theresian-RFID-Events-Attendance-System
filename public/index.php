<?php
declare(strict_types=1);

// Set default timezone
date_default_timezone_set('Asia/Manila');

// Register PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\Router;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\AttendanceController;
use App\Controllers\EventController;
use App\Controllers\StudentController;
use App\Controllers\DepartmentController;
use App\Controllers\ReportController;
use App\Controllers\ProfileController;

// Initialize secure session
Session::start();

// Initialize Router
$router = new Router();

// ── Public / Auth Routes ───────────────────────────────────────
$router->get('/',                 [AuthController::class, 'showLogin']);
$router->get('/login',            [AuthController::class, 'showLogin']);
$router->post('/api/login',       [AuthController::class, 'login']);
$router->get('/logout',           [AuthController::class, 'logout']);

$router->post('/api/request-otp', [AuthController::class, 'requestOtp']);
$router->post('/api/verify-otp',  [AuthController::class, 'verifyOtp']);
$router->get('/reset-password',   [AuthController::class, 'showResetPassword']);
$router->post('/reset-password',  [AuthController::class, 'resetPassword']);

// ── Authenticated App Routes ───────────────────────────────────
$router->get('/dashboard',        [DashboardController::class, 'index'], [AuthMiddleware::class]);

// Live RFID Scanner & Scan APIs
$router->get('/scan',             [AttendanceController::class, 'scan'], [AuthMiddleware::class]);
$router->post('/api/scan',        [AttendanceController::class, 'processScan'], [AuthMiddleware::class]);
$router->get('/api/attendance-logs/{id}', [AttendanceController::class, 'getLogs'], [AuthMiddleware::class]);
$router->post('/api/manual-checkin', [AttendanceController::class, 'manualCheckin'], [AuthMiddleware::class]);
$router->delete('/api/attendance-logs/{id}', [AttendanceController::class, 'deleteLog'], [AuthMiddleware::class, AdminMiddleware::class]);

// Events Management
$router->get('/events',           [EventController::class, 'index'], [AuthMiddleware::class]);
$router->post('/events/store',    [EventController::class, 'store'], [AuthMiddleware::class]);
$router->post('/events/update/{id}', [EventController::class, 'update'], [AuthMiddleware::class]);
$router->post('/events/archive/{id}', [EventController::class, 'archive'], [AuthMiddleware::class]);
$router->post('/events/restore/{id}', [EventController::class, 'restore'], [AuthMiddleware::class]);

// Students Directory
$router->get('/students',         [StudentController::class, 'index'], [AuthMiddleware::class]);
$router->post('/students/store',   [StudentController::class, 'store'], [AuthMiddleware::class]);
$router->post('/students/update/{id}', [StudentController::class, 'update'], [AuthMiddleware::class]);
$router->post('/students/archive/{id}', [StudentController::class, 'archive'], [AuthMiddleware::class]);
$router->post('/students/restore/{id}', [StudentController::class, 'restore'], [AuthMiddleware::class]);

// Reports & Absentees
$router->get('/reports',          [ReportController::class, 'index'], [AuthMiddleware::class]);
$router->get('/reports/print/{id}', [ReportController::class, 'printView'], [AuthMiddleware::class]);
$router->get('/reports/export-csv/{id}', [ReportController::class, 'exportCsv'], [AuthMiddleware::class]);

// Profile Settings
$router->get('/profile',          [ProfileController::class, 'index'], [AuthMiddleware::class]);
$router->post('/profile/update',  [ProfileController::class, 'update'], [AuthMiddleware::class]);
$router->post('/profile/change-password', [ProfileController::class, 'changePassword'], [AuthMiddleware::class]);
$router->post('/profile/upload-photo', [ProfileController::class, 'uploadPhoto'], [AuthMiddleware::class]);

// ── Admin-Only Routes ──────────────────────────────────────────
$router->get('/departments',      [DepartmentController::class, 'index'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/departments/store', [DepartmentController::class, 'store'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/departments/update/{id}', [DepartmentController::class, 'update'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/departments/archive/{id}', [DepartmentController::class, 'archive'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/departments/restore/{id}', [DepartmentController::class, 'restore'], [AuthMiddleware::class, AdminMiddleware::class]);

// Dispatch incoming request
$router->dispatch();
