<?php
namespace App\Core;

use App\Config\Config;

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);
            
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            session_start();
        }

        // Handle auto-timeout (30 mins)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > Config::SESSION_LIFETIME)) {
            self::destroy();
        }
        $_SESSION['last_activity'] = time();
    }

    public static function set(string $key, mixed $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value = null): mixed {
        self::start();
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        
        $msg = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }

    public static function regenerate(): void {
        self::start();
        session_regenerate_id(true);
    }

    public static function destroy(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
