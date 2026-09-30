<?php
namespace App\Core;

use App\Config\Config;

abstract class Controller {
    protected function view(string $viewPath, array $data = [], string $layout = 'app'): void {
        extract($data);
        
        // Build path to view file
        $viewFile = dirname(__DIR__) . '/Views/' . str_replace('.', '/', $viewPath) . '.php';
        if (!file_exists($viewFile)) {
            die("View file not found: " . htmlspecialchars($viewFile));
        }

        // Capture view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render with layout
        if ($layout) {
            $layoutFile = dirname(__DIR__) . '/Views/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
                return;
            }
        }
        
        echo $content;
    }

    protected function json(mixed $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $path): void {
        if (!str_starts_with($path, 'http') && !str_starts_with($path, Config::BASE_URL)) {
            $path = Config::BASE_URL . '/' . ltrim($path, '/');
        }
        header('Location: ' . $path);
        exit;
    }

    protected function getJsonInput(): array {
        $raw = file_get_contents('php://input');
        if (empty($raw)) return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    protected function validateCsrf(): void {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Csrf::verify($token)) {
            if ($this->isApiRequest()) {
                $this->json(['success' => false, 'error' => 'Invalid or expired CSRF token'], 403);
            } else {
                Session::flash('error', 'Security token expired. Please try again.');
                $this->redirect($_SERVER['HTTP_REFERER'] ?? '/dashboard');
            }
        }
    }

    protected function isApiRequest(): bool {
        return str_starts_with($_SERVER['REQUEST_URI'] ?? '', Config::BASE_URL . '/api')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }
}
