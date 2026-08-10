<?php
// Set JSON header FIRST before any includes
header('Content-Type: application/json; charset=utf-8');

// Suppress any output from includes
ob_start();
require_once __DIR__ . '/dbcon.php';
ob_end_clean();

// Accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get input
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

if (!$username || !$otp) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Username and OTP are required']);
    exit;
}

try {
    // Get user ID from username
    $userStmt = $conn->prepare("SELECT id, username, password FROM user WHERE username = ? AND account_type = 'admin' AND status = 'active'");
    if (!$userStmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    
    $userStmt->bind_param('s', $username);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid username']);
        exit;
    }
    
    $user = $userResult->fetch_assoc();
    $userId = $user['id'];
    $userStmt->close();
    
    // Check OTP validity - just get all non-used OTPs for this user
    $otpStmt = $conn->prepare("
        SELECT id, otp, expires_at, used 
        FROM otp_requests 
        WHERE user_id = ? 
        AND otp = ? 
        AND used = 0
        ORDER BY created_at DESC
        LIMIT 1
    ");
    
    if (!$otpStmt) {
        throw new Exception('Database prepare error: ' . $conn->error);
    }
    
    $otpStmt->bind_param('is', $userId, $otp);
    $otpStmt->execute();
    $otpResult = $otpStmt->get_result();
    
    if ($otpResult->num_rows === 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid or expired OTP']);
        exit;
    }
    
    $otpRecord = $otpResult->fetch_assoc();
    $otpRequestId = $otpRecord['id'];
    
    // Check expiration in PHP (timezone-safe)
    $expiresAt = strtotime($otpRecord['expires_at']);
    $nowTime = time();
    
    if ($expiresAt < $nowTime) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'OTP has expired']);
        exit;
    }
    $otpStmt->close();
    
    // Mark OTP as used
    $markUsedStmt = $conn->prepare("UPDATE otp_requests SET used = 1, verified_at = NOW() WHERE id = ?");
    if ($markUsedStmt) {
        $markUsedStmt->bind_param('i', $otpRequestId);
        $markUsedStmt->execute();
        $markUsedStmt->close();
    }
    
    // Start session and set user as logged in
    if (!session_id()) session_start();
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $user['username'];
    $_SESSION['account_type'] = 'admin';
    $_SESSION['login_time'] = time();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'OTP verified successfully. Logging in...',
        'redirect' => 'admin/index.php'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>
