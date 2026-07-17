<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;
use App\Models\AuditLog;

/**
 * Setting Controller
 * 
 * Handles user management and profile settings.
 */
class SettingController extends Controller
{
    private User $userModel;
    private AuditLog $auditLogModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->auditLogModel = new AuditLog();
    }

    /**
     * Redirect to profile since system settings are minimal right now.
     */
    public function index(): void
    {
        $this->redirect('/settings/profile');
    }

    /**
     * Display personal profile settings (Change Password).
     */
    public function profile(): void
    {
        Auth::requireAuth();

        $this->view('settings.profile', [
            'pageTitle' => 'My Profile'
        ]);
    }

    /**
     * Update personal profile (Password).
     */
    public function updateProfile(): void
    {
        Auth::requireAuth();
        $this->validateCsrf();

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|match:new_password'
        ];

        $data = Validator::validate($_POST, $rules);

        if (Validator::hasErrors()) {
            Session::flash('error', 'Please correct the errors in the form.');
            $this->redirect('/settings/profile');
            return;
        }

        $user = $this->userModel->find(Auth::id());

        if (!password_verify($data['current_password'], $user['password'])) {
            Session::flash('error', 'Current password is incorrect.');
            $this->redirect('/settings/profile');
            return;
        }

        $this->userModel->update(Auth::id(), [
            'password' => password_hash($data['new_password'], PASSWORD_BCRYPT, ['cost' => 12])
        ]);

        $this->auditLogModel->create([
            'user_id' => Auth::id(),
            'action' => 'password_changed',
            'entity_type' => 'user',
            'entity_id' => Auth::id(),
            'details' => 'User changed their own password.'
        ]);

        Session::flash('success', 'Password updated successfully.');
        $this->redirect('/settings/profile');
    }

    /**
     * Manage Users (Admin Only).
     */
    public function users(): void
    {
        Auth::requireAuth();
        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized access.');
            return;
        }

        $users = $this->userModel->getDb()->fetchAll("SELECT id, name, email, role, is_active, last_login_at, created_at FROM users ORDER BY created_at DESC");

        $this->view('settings.users', [
            'pageTitle' => 'User Management',
            'users' => $users
        ]);
    }

    /**
     * Create a new user (Admin Only).
     */
    public function storeUser(): void
    {
        Auth::requireAuth();
        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized access.');
            return;
        }

        $this->validateCsrf();

        $rules = [
            'name' => 'required|string',
            'email' => 'required|email',
            'role' => 'required',
            'password' => 'required|min:8'
        ];

        $data = Validator::validate($_POST, $rules);

        if (Validator::hasErrors()) {
            Session::flash('error', 'Validation failed. Check your input.');
            $this->redirect('/settings/users');
            return;
        }

        if (!in_array($data['role'], ['admin', 'staff'])) {
            Session::flash('error', 'Invalid role selected.');
            $this->redirect('/settings/users');
            return;
        }

        // Check if email exists
        if ($this->userModel->findByEmail($data['email'])) {
            Session::flash('error', 'Email is already registered.');
            $this->redirect('/settings/users');
            return;
        }

        $userId = $this->userModel->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'role' => $data['role'],
            'is_active' => true
        ]);

        $this->auditLogModel->create([
            'user_id' => Auth::id(),
            'action' => 'created_user',
            'entity_type' => 'user',
            'entity_id' => $userId,
            'details' => "Created new {$data['role']} user: {$data['email']}"
        ]);

        Session::flash('success', 'User account created successfully.');
        $this->redirect('/settings/users');
    }

    /**
     * Update an existing user's details (Admin Only).
     */
    public function updateUser(string $id): void
    {
        Auth::requireAuth();
        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized access.');
            return;
        }

        $this->validateCsrf();
        $userId = (int) $id;

        $rules = [
            'name' => 'required|string',
            'email' => 'required|email',
            'role' => 'required'
        ];

        $data = Validator::validate($_POST, $rules);

        if (Validator::hasErrors()) {
            Session::flash('error', 'Validation failed.');
            $this->redirect('/settings/users');
            return;
        }

        // Check if email exists for another user
        $existing = $this->userModel->findByEmail($data['email']);
        if ($existing && $existing['id'] !== $userId) {
            Session::flash('error', 'Email is already in use by another user.');
            $this->redirect('/settings/users');
            return;
        }

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role']
        ];

        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 8) {
                Session::flash('error', 'Password must be at least 8 characters.');
                $this->redirect('/settings/users');
                return;
            }
            $updateData['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $this->userModel->update($userId, $updateData);

        $this->auditLogModel->create([
            'user_id' => Auth::id(),
            'action' => 'updated_user',
            'entity_type' => 'user',
            'entity_id' => $userId,
            'details' => "Updated user details for ID: {$userId}"
        ]);

        Session::flash('success', 'User updated successfully.');
        $this->redirect('/settings/users');
    }

    /**
     * Toggle a user's active status (Admin Only).
     */
    public function toggleUser(string $id): void
    {
        Auth::requireAuth();
        if (!Auth::isAdmin()) {
            $this->sendError(403, 'Unauthorized access.');
            return;
        }

        $this->validateCsrf();
        $userId = (int) $id;

        if ($userId === Auth::id()) {
            Session::flash('error', 'You cannot deactivate your own account.');
            $this->redirect('/settings/users');
            return;
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            $this->sendError(404, 'User not found.');
            return;
        }

        $newState = !$user['is_active'];
        $this->userModel->update($userId, ['is_active' => $newState]);

        $this->auditLogModel->create([
            'user_id' => Auth::id(),
            'action' => $newState ? 'activated_user' : 'deactivated_user',
            'entity_type' => 'user',
            'entity_id' => $userId,
            'details' => ($newState ? "Activated" : "Deactivated") . " user account."
        ]);

        Session::flash('success', 'User account status updated.');
        $this->redirect('/settings/users');
    }
}
