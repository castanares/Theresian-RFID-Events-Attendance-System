<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\User;
use App\Models\Department;

class UserController extends Controller {
    private User $userModel;
    private Department $departmentModel;

    public function __construct() {
        $this->userModel = new User();
        $this->departmentModel = new Department();
    }

    public function index(): void {
        $users = $this->userModel->getAll();
        $departments = $this->departmentModel->getAll();

        $this->view('admin.users.index', [
            'title' => 'User Accounts Management',
            'users' => $users,
            'departments' => $departments
        ]);
    }

    public function store(): void {
        $this->validateCsrf();
        $data = $_POST;

        $username   = trim($data['username'] ?? '');
        $password   = $data['password'] ?? '';
        $role       = trim($data['account_type'] ?? 'user');
        $deptId     = (int)($data['departments_id'] ?? 0);
        $email      = trim($data['email'] ?? '') ?: null;
        $phone      = trim($data['phone'] ?? '') ?: null;
        $firstName  = trim($data['first_name'] ?? '') ?: null;
        $lastName   = trim($data['last_name'] ?? '') ?: null;

        if (!$username || !$password) {
            Session::flash('error', 'Username and temporary password are required.');
            $this->redirect('/admin/users');
        }

        if ($this->userModel->findByUsername($username)) {
            Session::flash('error', "Username '{$username}' is already taken.");
            $this->redirect('/admin/users');
        }

        $this->userModel->create([
            'username' => $username,
            'password' => $password,
            'account_type' => $role,
            'departments_id' => $deptId,
            'email' => $email,
            'phone' => $phone,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'status' => 'active'
        ]);

        Session::flash('success', "Account '{$username}' created successfully!");
        $this->redirect('/admin/users');
    }

    public function update(string $id): void {
        $userId = (int)$id;
        $this->validateCsrf();
        $data = $_POST;

        $email     = trim($data['email'] ?? '') ?: null;
        $phone     = trim($data['phone'] ?? '') ?: null;
        $firstName = trim($data['first_name'] ?? '') ?: null;
        $lastName  = trim($data['last_name'] ?? '') ?: null;
        $deptId    = (int)($data['departments_id'] ?? 0);
        $role      = trim($data['account_type'] ?? 'user');

        $this->userModel->updateProfile($userId, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'description' => trim($data['description'] ?? '') ?: null
        ]);

        // Update department and role
        $db = \App\Config\Database::getConnection();
        $db->prepare("UPDATE user SET departments_id = ?, account_type = ? WHERE id = ?")
           ->execute([$deptId, $role, $userId]);

        Session::flash('success', 'User account updated successfully!');
        $this->redirect('/admin/users');
    }

    public function resetPassword(string $id): void {
        $userId = (int)$id;
        $this->validateCsrf();
        $newPass = $_POST['new_password'] ?? '';

        if (strlen($newPass) < 6) {
            Session::flash('error', 'Password must be at least 6 characters.');
            $this->redirect('/admin/users');
        }

        $this->userModel->updatePassword($userId, $newPass);
        Session::flash('success', 'User password has been reset successfully.');
        $this->redirect('/admin/users');
    }

    public function toggleStatus(string $id): void {
        $userId = (int)$id;
        $this->validateCsrf();
        $user = $this->userModel->findById($userId);

        if (!$user) {
            Session::flash('error', 'User not found.');
            $this->redirect('/admin/users');
        }

        // Prevent self-deactivation
        if ($userId === Auth::id()) {
            Session::flash('error', 'You cannot deactivate your own active account.');
            $this->redirect('/admin/users');
        }

        $newStatus = ($user['status'] === 'active') ? 'archived' : 'active';
        $db = \App\Config\Database::getConnection();
        $db->prepare("UPDATE user SET status = ? WHERE id = ?")->execute([$newStatus, $userId]);

        Session::flash('success', "User account {$user['username']} is now {$newStatus}.");
        $this->redirect('/admin/users');
    }
}
