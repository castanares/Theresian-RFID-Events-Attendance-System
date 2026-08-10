<?php
// Set JSON header FIRST before any includes
header('Content-Type: application/json; charset=utf-8');

// Suppress any output from includes
ob_start();
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/dbcon.php';
ob_end_clean();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

if (!$username || !$email || !$otp) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

try {
    // Verify username and email match to admin account
    $stmt = $conn->prepare("SELECT id FROM user WHERE username = ? AND email = ? AND account_type = 'admin' AND status = 'active'");
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Username and email do not match']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    $userId = $user['id'];
    
    // Set OTP expiry to 15 minutes (matching EmailJS template)
    $expiryTime = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Create otp_requests table if it doesn't exist
    $createTableQuery = "CREATE TABLE IF NOT EXISTS otp_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        otp VARCHAR(6) NOT NULL,
        email VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        used TINYINT DEFAULT 0,
        verified_at DATETIME DEFAULT NULL,
        FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if (!$conn->query($createTableQuery)) {
        throw new Exception('Failed to create otp_requests table: ' . $conn->error);
    }
    
    // Delete any previous unused OTPs for this user
    $deleteStmt = $conn->prepare("DELETE FROM otp_requests WHERE user_id = ? AND used = 0");
    if ($deleteStmt) {
        $deleteStmt->bind_param('i', $userId);
        $deleteStmt->execute();
        $deleteStmt->close();
    }
    
    // Insert new OTP record
    $insertStmt = $conn->prepare("INSERT INTO otp_requests (user_id, otp, email, expires_at) VALUES (?, ?, ?, ?)");
    if (!$insertStmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    
    $insertStmt->bind_param('isss', $userId, $otp, $email, $expiryTime);
    if (!$insertStmt->execute()) {
        throw new Exception('Failed to insert OTP record: ' . $insertStmt->error);
    }
    
    // Get insert ID before closing
    $insertId = $conn->insert_id;
    $insertStmt->close();
    
    // Log OTP
    $logDir = __DIR__ . '/../otp_logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/otp_' . date('Y-m-d') . '.log';
    @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Username: $username, Email: $email, OTP: $otp, Sent: YES (EmailJS)\n", FILE_APPEND);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'OTP saved successfully',
        'otp_id' => $insertId
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
