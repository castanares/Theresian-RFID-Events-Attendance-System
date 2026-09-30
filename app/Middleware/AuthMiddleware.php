<?php
namespace App\Middleware;

use App\Core\Auth;
use App\Core\Session;
use App\Config\Config;

class AuthMiddleware {
    public function handle(): bool {
        if (!Auth::check()) {
            if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', Config::BASE_URL . '/api')) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in.']);
            } else {
                Session::flash('error', 'Please log in to continue.');
                header('Location: ' . Config::BASE_URL . '/login');
            }
            exit;
        }
        return true;
    }
}
