<?php
namespace App\Services;

use App\Config\Database;
use App\Config\Config;
use PDO;

class OtpService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->ensureTableExists();
    }

    private function ensureTableExists(): void {
        $sql = "CREATE TABLE IF NOT EXISTS otp_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            otp VARCHAR(6) NOT NULL,
            email VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            expires_at DATETIME NOT NULL,
            used TINYINT DEFAULT 0,
            verified_at DATETIME DEFAULT NULL,
            attempts INT DEFAULT 0,
            INDEX (user_id),
            INDEX (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        $this->db->exec($sql);
    }

    public function generateOtp(int $userId, string $email): string {
        // Invalidate old unused OTPs
        $stmt = $this->db->prepare("UPDATE otp_requests SET used = 1 WHERE user_id = ? AND used = 0");
        $stmt->execute([$userId]);

        // Cryptographically secure 6-digit OTP
        $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . Config::OTP_EXPIRY_MINUTES . ' minutes'));

        $stmt = $this->db->prepare("INSERT INTO otp_requests (user_id, otp, email, expires_at, used) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$userId, $otp, $email, $expiresAt]);

        return $otp;
    }

    public function verifyOtp(int $userId, string $otp): array {
        $stmt = $this->db->prepare("SELECT * FROM otp_requests 
                                    WHERE user_id = ? AND used = 0 
                                    ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$userId]);
        $record = $stmt->fetch();

        if (!$record) {
            return ['success' => false, 'error' => 'No active OTP request found. Please request a new one.'];
        }

        // Check attempts / rate limiting
        if ((int)$record['attempts'] >= Config::MAX_OTP_ATTEMPTS) {
            // Invalidate record due to brute force
            $this->db->prepare("UPDATE otp_requests SET used = 1 WHERE id = ?")->execute([$record['id']]);
            return ['success' => false, 'error' => 'Too many failed attempts. This OTP has been invalidated.'];
        }

        // Check expiration
        if (strtotime($record['expires_at']) < time()) {
            $this->db->prepare("UPDATE otp_requests SET used = 1 WHERE id = ?")->execute([$record['id']]);
            return ['success' => false, 'error' => 'The OTP has expired. Please request a new one.'];
        }

        // Verify OTP
        if (!hash_equals($record['otp'], trim($otp))) {
            // Increment attempts
            $this->db->prepare("UPDATE otp_requests SET attempts = attempts + 1 WHERE id = ?")->execute([$record['id']]);
            $remaining = Config::MAX_OTP_ATTEMPTS - ((int)$record['attempts'] + 1);
            return ['success' => false, 'error' => "Incorrect OTP code. ({$remaining} attempts remaining)"];
        }

        // Success - Mark as used
        $this->db->prepare("UPDATE otp_requests SET used = 1, verified_at = NOW() WHERE id = ?")->execute([$record['id']]);
        return ['success' => true, 'message' => 'OTP verified successfully'];
    }
}
