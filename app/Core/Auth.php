<?php
namespace App\Core;

class Auth {
    public static function check(): bool {
        return Session::has('user_id');
    }

    public static function id(): ?int {
        return Session::get('user_id');
    }

    public static function user(): ?array {
        return Session::get('user');
    }

    public static function username(): string {
        return Session::get('user')['username'] ?? 'User';
    }

    public static function role(): string {
        return Session::get('user')['account_type'] ?? 'user';
    }

    public static function isAdmin(): bool {
        return strtolower(self::role()) === 'admin';
    }

    public static function departmentId(): ?int {
        return Session::get('user')['departments_id'] ?? null;
    }

    public static function login(array $user): void {
        Session::regenerate();
        Session::set('user_id', (int)$user['id']);
        Session::set('user', [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'account_type' => $user['account_type'] ?? 'user',
            'departments_id' => (int)($user['departments_id'] ?? 0),
            'email' => $user['email'] ?? '',
            'profile_pic' => $user['profile_pic'] ?? null,
        ]);
    }

    public static function logout(): void {
        Session::destroy();
    }
}
