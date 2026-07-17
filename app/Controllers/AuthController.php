<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Validator;
use App\Core\Logger;
use App\Models\User;

/**
 * Authentication Controller
 * 
 * Handles login and logout.
 */
class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Show the login form.
     */
    public function showLogin(): void
    {
        // If already logged in, redirect to dashboard
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth.login', [], 'auth');
    }

    /**
     * Handle login form submission.
     */
    public function login(): void
    {
        // Validate CSRF
        $this->validateCsrf();

        $email = $this->input('email');
        $password = $this->input('password');

        // Validate input
        $validator = Validator::make($_POST)
            ->required('email', 'Email')
            ->email('email', 'Email')
            ->required('password', 'Password');

        if ($validator->fails()) {
            Session::flash('error', $validator->allErrors()[0]);
            flashOldInput($_POST);
            $this->redirect('/login');
        }

        // Find user by email
        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            Logger::warning("Failed login attempt for: {$email}", [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ]);
            Session::flash('error', 'Invalid email or password.');
            flashOldInput($_POST);
            $this->redirect('/login');
        }

        // Check if user is active
        if (!$user['is_active']) {
            Session::flash('error', 'Your account has been deactivated. Please contact the administrator.');
            $this->redirect('/login');
        }

        // Log the user in
        Auth::login($user);

        // Update last login
        $this->userModel->updateLastLogin((int) $user['id']);

        Logger::info("User logged in: {$user['email']}", [
            'user_id' => $user['id'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ]);

        clearOldInput();
        Session::flash('success', 'Welcome back, ' . $user['name'] . '!');
        $this->redirect('/dashboard');
    }

    /**
     * Handle logout.
     */
    public function logout(): void
    {
        $user = Auth::user();

        if ($user) {
            Logger::info("User logged out: {$user['email']}", [
                'user_id' => $user['id'],
            ]);
        }

        Auth::logout();

        // Start a new session for flash message
        Session::start();
        Session::flash('success', 'You have been logged out.');
        redirect('/login');
    }
}
