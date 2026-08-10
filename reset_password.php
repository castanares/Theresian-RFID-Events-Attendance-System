<?php
// Start session
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if user has verified OTP (session should have user_id and account_type from verify_otp.php)
if (empty($_SESSION['user_id']) || $_SESSION['account_type'] !== 'admin') {
    // Not authenticated through OTP flow - redirect to login
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';
$error_message = '';
$success_message = '';

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    
    // Validation
    if (!$new_password || !$confirm_password) {
        $error_message = 'Both password fields are required.';
    } elseif (strlen($new_password) < 8) {
        $error_message = 'Password must be at least 8 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } else {
        // Update password in database
        require_once 'api/dbcon.php';
        
        try {
            // Hash the new password
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            
            // Update user password
            $stmt = $conn->prepare("UPDATE user SET password = ? WHERE id = ? AND account_type = 'admin'");
            if (!$stmt) {
                throw new Exception('Database error: ' . $conn->error);
            }
            
            $stmt->bind_param('si', $hashed, $user_id);
            if (!$stmt->execute()) {
                throw new Exception('Failed to update password: ' . $stmt->error);
            }
            $stmt->close();
            
            $success_message = 'Password reset successfully! Redirecting to login...';
            
            // Clear session
            session_destroy();
            
            // Redirect to login after 2 seconds
            header('Refresh: 2; url=index.php');
        } catch (Exception $e) {
            $error_message = 'Error updating password: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - STC RFID Attendance</title>
    <link rel="stylesheet" href="styles/dash_styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --maroon: #6b0f0f; }
        body { 
            background: linear-gradient(180deg, #faf8f8, #f2eded); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
        }
        .reset-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 1rem;
        }
        .reset-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
            padding: 2rem;
        }
        .reset-header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--maroon);
            padding-bottom: 1rem;
        }
        .reset-header h2 {
            color: var(--maroon);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .reset-header p {
            color: #666;
            font-size: 0.95rem;
            margin: 0;
        }
        .form-label {
            font-weight: 600;
            color: var(--maroon);
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 0.75rem;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: var(--maroon);
            box-shadow: 0 0 0 0.2rem rgba(107, 15, 15, 0.1);
        }
        .password-requirements {
            background: #f8f9fa;
            border-left: 4px solid var(--maroon);
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .password-requirements ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        .password-requirements li {
            margin: 0.3rem 0;
            color: #555;
        }
        .btn-reset {
            background: var(--maroon);
            color: #fff;
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 6px;
            width: 100%;
            margin-top: 1rem;
            transition: background 0.3s;
        }
        .btn-reset:hover {
            background: #540a0a;
            color: #fff;
        }
        .btn-reset:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .alert {
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        .security-badge {
            display: inline-block;
            background: #e8f5e9;
            color: #2e7d32;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }
        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-right: 0;
        }
        .input-group .form-control {
            border-left: 0;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <h2><i class="bi bi-shield-lock me-2"></i>Reset Your Password</h2>
                <p>Create a new secure password for your account</p>
            </div>

            <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success_message); ?>
            </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if (!$success_message): ?>
            <div class="security-badge">
                <i class="bi bi-shield-check"></i> Secure Password Reset
            </div>

            <form method="POST" action="" id="resetForm">
                <input type="hidden" name="reset_password" value="1">

                <div class="mb-3">
                    <label for="newPassword" class="form-label">New Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="newPassword" 
                            name="new_password" 
                            placeholder="Enter new password" 
                            required
                            minlength="8"
                        >
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword1" onclick="togglePassword('newPassword')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="confirmPassword" 
                            name="confirm_password" 
                            placeholder="Confirm new password" 
                            required
                            minlength="8"
                        >
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword2" onclick="togglePassword('confirmPassword')">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2" id="passwordMatch" style="display: none;">✓ Passwords match</small>
                    <small class="text-danger d-block mt-2" id="passwordMismatch" style="display: none;">✗ Passwords do not match</small>
                </div>

                <div class="password-requirements">
                    <strong>Password Requirements:</strong>
                    <ul>
                        <li id="req-length">At least 8 characters</li>
                        <li>Use a mix of uppercase, lowercase, numbers, and symbols for better security</li>
                    </ul>
                </div>

                <button type="submit" class="btn-reset" id="resetBtn">
                    <i class="bi bi-shield-check me-2"></i>Reset Password
                </button>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>Your password will be securely updated
                    </small>
                </div>
            </form>
            <?php endif; ?>

            <?php if ($success_message): ?>
            <div class="text-center mt-4">
                <p class="text-muted">Redirecting to login in 2 seconds...</p>
                <div class="spinner-border text-maroon" role="status" style="color: var(--maroon);">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const isPassword = field.type === 'password';
            field.type = isPassword ? 'text' : 'password';
        }

        // Real-time password validation
        const newPasswordInput = document.getElementById('newPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const passwordMatch = document.getElementById('passwordMatch');
        const passwordMismatch = document.getElementById('passwordMismatch');
        const resetBtn = document.getElementById('resetBtn');
        const reqLength = document.getElementById('req-length');

        function validatePasswords() {
            const newPwd = newPasswordInput.value;
            const confirmPwd = confirmPasswordInput.value;

            // Check length
            if (newPwd.length >= 8) {
                reqLength.style.color = '#2e7d32';
                reqLength.innerHTML = '✓ At least 8 characters';
            } else {
                reqLength.style.color = '#d32f2f';
                reqLength.innerHTML = '✗ At least 8 characters';
            }

            // Check if passwords match
            if (confirmPwd.length > 0) {
                if (newPwd === confirmPwd) {
                    passwordMatch.style.display = 'block';
                    passwordMismatch.style.display = 'none';
                } else {
                    passwordMatch.style.display = 'none';
                    passwordMismatch.style.display = 'block';
                }
            } else {
                passwordMatch.style.display = 'none';
                passwordMismatch.style.display = 'none';
            }

            // Enable/disable reset button
            resetBtn.disabled = newPwd.length < 8 || confirmPwd.length === 0 || newPwd !== confirmPwd;
        }

        newPasswordInput?.addEventListener('input', validatePasswords);
        confirmPasswordInput?.addEventListener('input', validatePasswords);

        // Initial validation
        validatePasswords();

        // Form submission
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            const newPwd = newPasswordInput.value;
            const confirmPwd = confirmPasswordInput.value;

            if (newPwd.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return false;
            }

            if (newPwd !== confirmPwd) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }

            // Show loading state
            resetBtn.disabled = true;
            resetBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Resetting Password...';
        });
    </script>
</body>
</html>
