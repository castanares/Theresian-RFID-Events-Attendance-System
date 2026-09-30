<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\User;
use App\Services\OtpService;

class AuthController extends Controller {
    private User $userModel;
    private OtpService $otpService;

    public function __construct() {
        $this->userModel = new User();
        $this->otpService = new OtpService();
    }

    public function showLogin(): void {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth.login', ['title' => 'Sign In - STC RFID System'], 'auth');
    }

    public function login(): void {
        $data = $this->getJsonInput();
        if (empty($data)) $data = $_POST;

        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if (!$username || !$password) {
            $this->json(['success' => false, 'error' => 'Please provide both username and password.'], 400);
        }

        $user = $this->userModel->findByUsername($username);
        if (!$user) {
            $this->json(['success' => false, 'error' => 'Invalid username or password.'], 401);
        }

        if ($user['status'] === 'archived') {
            $this->json(['success' => false, 'error' => 'This account has been deactivated.'], 403);
        }

        if (!password_verify($password, $user['password'])) {
            $this->json(['success' => false, 'error' => 'Invalid username or password.'], 401);
        }

        // Successful authentication
        Auth::login($user);
        $this->userModel->updateLastLogin((int)$user['id']);

        $this->json([
            'success' => true,
            'account_type' => $user['account_type'],
            'redirect' => '/dashboard'
        ]);
    }

    public function logout(): void {
        Auth::logout();
        Session::flash('success', 'You have been successfully logged out.');
        $this->redirect('/login');
    }

    public function requestOtp(): void {
        $data = $this->getJsonInput();
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');

        if (!$username || !$email) {
            $this->json(['success' => false, 'error' => 'Username and email are required.'], 400);
        }

        $user = $this->userModel->findByUsername($username);
        if (!$user || strcasecmp($user['email'] ?? '', $email) !== 0) {
            $this->json(['success' => false, 'error' => 'No account found matching this username and email.'], 404);
        }

        $otp = $this->otpService->generateOtp((int)$user['id'], $email);

        // Store temporary reset user id in session
        Session::set('password_reset_user_id', (int)$user['id']);
        Session::set('password_reset_email', $email);

        $this->json([
            'success' => true,
            'message' => 'OTP has been generated. Please check your email.',
            'otp' => $otp // Passed to EmailJS template if client-side dispatch is used
        ]);
    }

    public function verifyOtp(): void {
        $data = $this->getJsonInput();
        $otp = trim($data['otp'] ?? '');
        $userId = Session::get('password_reset_user_id');

        if (!$userId || !$otp) {
            $this->json(['success' => false, 'error' => 'Invalid OTP verification request.'], 400);
        }

        $result = $this->otpService->verifyOtp((int)$userId, $otp);
        if ($result['success']) {
            Session::set('otp_verified', true);
            $this->json(['success' => true, 'redirect' => '/reset-password']);
        } else {
            $this->json(['success' => false, 'error' => $result['error']], 400);
        }
    }

    public function showResetPassword(): void {
        if (!Session::get('otp_verified') || !Session::get('password_reset_user_id')) {
            Session::flash('error', 'Please verify your identity first.');
            $this->redirect('/login');
        }

        $this->view('auth.reset_password', ['title' => 'Reset Password - STC RFID System'], 'auth');
    }

    public function resetPassword(): void {
        $userId = Session::get('password_reset_user_id');
        $isVerified = Session::get('otp_verified');

        if (!$userId || !$isVerified) {
            Session::flash('error', 'Session expired. Please try again.');
            $this->redirect('/login');
        }

        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (strlen($password) < 6) {
            Session::flash('error', 'Password must be at least 6 characters long.');
            $this->redirect('/reset-password');
        }

        if ($password !== $confirm) {
            Session::flash('error', 'Passwords do not match.');
            $this->redirect('/reset-password');
        }

        $this->userModel->updatePassword((int)$userId, $password);

        // Clear password reset session flags
        Session::remove('password_reset_user_id');
        Session::remove('password_reset_email');
        Session::remove('otp_verified');

        Session::flash('success', 'Your password has been reset successfully. Please log in.');
        $this->redirect('/login');
    }
}
