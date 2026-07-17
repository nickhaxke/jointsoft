<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Authentication Guard
 * 
 * Handles user authentication state and role-based access control.
 */
class Auth
{
    private const SESSION_USER_KEY = '_auth_user';

    /**
     * Log in a user by storing their data in the session.
     */
    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set(self::SESSION_USER_KEY, [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar' => $user['avatar'] ?? null,
        ]);
        Session::set('_login_time', time());
    }

    /**
     * Log out the current user.
     */
    public static function logout(): void
    {
        Session::destroy();
    }

    /**
     * Check if a user is authenticated.
     */
    public static function check(): bool
    {
        return Session::has(self::SESSION_USER_KEY);
    }

    /**
     * Get the authenticated user data.
     */
    public static function user(): ?array
    {
        return Session::get(self::SESSION_USER_KEY);
    }

    /**
     * Get the authenticated user's ID.
     */
    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    /**
     * Get the authenticated user's role.
     */
    public static function role(): ?string
    {
        $user = self::user();
        return $user ? $user['role'] : null;
    }

    /**
     * Check if the authenticated user is an admin.
     */
    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    /**
     * Check if the authenticated user is staff.
     */
    public static function isStaff(): bool
    {
        return self::role() === 'staff';
    }

    /**
     * Require authentication — redirect to login if not authenticated.
     */
    public static function requireAuth(): void
    {
        if (!self::check()) {
            Session::flash('error', 'Please log in to continue.');
            redirect('/login');
        }
    }

    /**
     * Require admin role — show 403 if not admin.
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!self::isAdmin()) {
            http_response_code(403);
            Session::flash('error', 'You do not have permission to access this page.');
            redirect('/dashboard');
        }
    }
}
