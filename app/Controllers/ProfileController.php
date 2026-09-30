<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\User;

class ProfileController extends Controller {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function index(): void {
        $userId = Auth::id();
        $user = $this->userModel->findById($userId);

        $this->view('profile.index', [
            'title' => 'My Account Profile - STC Attendance',
            'user' => $user
        ]);
    }

    public function update(): void {
        $this->validateCsrf();
        $userId = Auth::id();
        $data = $_POST;

        $this->userModel->updateProfile($userId, [
            'first_name' => trim($data['first_name'] ?? ''),
            'last_name' => trim($data['last_name'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'phone' => trim($data['phone'] ?? ''),
            'description' => trim($data['description'] ?? '')
        ]);

        // Refresh session
        $updated = $this->userModel->findById($userId);
        Auth::login($updated);

        Session::flash('success', 'Profile information updated successfully!');
        $this->redirect('/profile');
    }

    public function changePassword(): void {
        $this->validateCsrf();
        $userId = Auth::id();
        $user = $this->userModel->findById($userId);

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPassword, $user['password'])) {
            Session::flash('error', 'Incorrect current password.');
            $this->redirect('/profile');
        }

        if (strlen($newPassword) < 6) {
            Session::flash('error', 'New password must be at least 6 characters long.');
            $this->redirect('/profile');
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'New passwords do not match.');
            $this->redirect('/profile');
        }

        $this->userModel->updatePassword($userId, $newPassword);
        Session::flash('success', 'Password updated successfully!');
        $this->redirect('/profile');
    }

    public function uploadPhoto(): void {
        if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'error' => 'No file uploaded or upload error.'], 400);
        }

        $file = $_FILES['profile_pic'];

        // Validate MIME type
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed)) {
            $this->json(['success' => false, 'error' => 'Invalid image format. Allowed: JPG, PNG, WEBP.'], 400);
        }

        // Verify true image dimensions
        if (!@getimagesize($file['tmp_name'])) {
            $this->json(['success' => false, 'error' => 'Corrupted or fake image file.'], 400);
        }

        // Storage directory
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/profiles';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = ($mime === 'image/png') ? 'png' : (($mime === 'image/webp') ? 'webp' : 'jpg');
        $filename = 'avatar_' . Auth::id() . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $this->json(['success' => false, 'error' => 'Failed to save uploaded image.'], 500);
        }

        $relPath = 'uploads/profiles/' . $filename;
        $this->userModel->updateProfilePic(Auth::id(), $relPath);

        // Update session
        $updated = $this->userModel->findById(Auth::id());
        Auth::login($updated);

        $this->json(['success' => true, 'path' => $relPath, 'message' => 'Profile picture updated!']);
    }
}
