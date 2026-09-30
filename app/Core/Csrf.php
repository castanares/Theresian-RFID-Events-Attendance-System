<?php
namespace App\Core;

class Csrf {
    public static function token(): string {
        if (!Session::has('_csrf_token')) {
            Session::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('_csrf_token');
    }

    public static function field(): string {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(self::token()) . '">';
    }

    public static function verify(?string $token): bool {
        $stored = Session::get('_csrf_token');
        if (!$stored || !$token) {
            return false;
        }
        return hash_equals($stored, $token);
    }
}
