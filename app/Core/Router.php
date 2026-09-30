<?php
namespace App\Core;

use App\Config\Config;

class Router {
    private array $routes = [];

    public function get(string $path, array $handler, array $middleware = []): void {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array $handler, array $middleware = []): void {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array $handler, array $middleware = []): void {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, array $handler, array $middleware): void {
        $this->routes[] = [
            'method'     => $method,
            'path'       => '/' . trim($path, '/'),
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(): void {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        
        // Handle method spoofing (PUT, DELETE via POST _method)
        if ($requestMethod === 'POST' && isset($_POST['_method'])) {
            $requestMethod = strtoupper($_POST['_method']);
        }

        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Strip base URL
        $base = Config::BASE_URL;
        if (str_starts_with($requestUri, $base)) {
            $requestUri = substr($requestUri, strlen($base));
        }
        $requestUri = '/' . trim($requestUri, '/');
        if ($requestUri === '//') $requestUri = '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Convert route path to regex (e.g., /events/{id} -> /events/(?P<id>[^/]+))
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $requestUri, $matches)) {
                // Execute middleware chain
                foreach ($route['middleware'] as $mwClass) {
                    $mw = new $mwClass();
                    if (!$mw->handle()) {
                        return; // Middleware aborted request
                    }
                }

                // Extract named parameter arguments
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                [$controllerClass, $method] = $route['handler'];
                if (!class_exists($controllerClass)) {
                    die("Controller not found: " . htmlspecialchars($controllerClass));
                }

                $controller = new $controllerClass();
                if (!method_exists($controller, $method)) {
                    die("Method {$method} not found in controller " . htmlspecialchars($controllerClass));
                }

                call_user_func_array([$controller, $method], array_values($params));
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        if (str_starts_with($requestUri, '/api')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
        } else {
            echo "<h1>404 Not Found</h1><p>The requested URL was not found on this server.</p>";
        }
    }
}
