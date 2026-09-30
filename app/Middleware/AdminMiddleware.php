<?php
namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;
use App\Config\Config;

class AdminMiddleware {
    public function handle(): bool {
        if (!Auth::check() || !Auth::isAdmin()) {
            if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', Config::BASE_URL . '/api')) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Forbidden. Admin privileges required.']);
            } else {
                Session::flash('error', 'Access restricted. Administrator privileges required.');
                header('Location: ' . Config::BASE_URL . '/dashboard');
            }
            exit;
        }
        return true;
    }
}
