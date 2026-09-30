<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Services\BackupService;

class BackupController extends Controller {
    private BackupService $backupService;
    private string $backupDir;

    public function __construct() {
        $this->backupService = new BackupService();
        $this->backupDir = dirname(__DIR__, 2) . '/storage/backups';
    }

    public function index(): void {
        $files = [];
        $rawFiles = glob($this->backupDir . '/*.{sql,zip,tar.gz}', GLOB_BRACE) ?: [];
        
        foreach ($rawFiles as $filepath) {
            $files[] = [
                'name' => basename($filepath),
                'path' => $filepath,
                'size_mb' => round(filesize($filepath) / 1048576, 2),
                'modified' => date('M j, Y h:i A', filemtime($filepath)),
                'timestamp' => filemtime($filepath)
            ];
        }

        usort($files, fn($a, $b) => $b['timestamp'] - $a['timestamp']);

        $this->view('admin.backup.index', [
            'title' => 'Database Backup & System Maintenance',
            'files' => $files
        ]);
    }

    public function create(): void {
        $this->validateCsrf();
        try {
            $filename = $this->backupService->createDatabaseDump();
            Session::flash('success', "Database snapshot '{$filename}' created successfully!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Failed to generate backup: ' . $e->getMessage());
        }
        $this->redirect('/admin/backup');
    }

    public function download(string $filename): void {
        $clean = basename($filename);
        $filepath = $this->backupDir . '/' . $clean;

        if (!file_exists($filepath)) {
            Session::flash('error', 'Backup file not found.');
            $this->redirect('/admin/backup');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $clean . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    public function delete(string $filename): void {
        $this->validateCsrf();
        $clean = basename($filename);
        $filepath = $this->backupDir . '/' . $clean;

        if (file_exists($filepath)) {
            @unlink($filepath);
            Session::flash('success', "Backup '{$clean}' deleted.");
        } else {
            Session::flash('error', 'File not found.');
        }
        $this->redirect('/admin/backup');
    }
}
