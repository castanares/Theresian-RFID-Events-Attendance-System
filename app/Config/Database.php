<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, Config::DB_USER, Config::DB_PASS, $options);
            } catch (PDOException $e) {
                // Return clean JSON if API request, else user-friendly error
                if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api')) {
                    header('Content-Type: application/json', true, 500);
                    echo json_encode(['success' => false, 'error' => 'Database connection error']);
                } else {
                    die("Database connection failed. Please ensure MySQL is running in XAMPP.");
                }
                exit;
            }
        }
        return self::$instance;
    }
}
