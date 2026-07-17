<?php

declare(strict_types=1);

namespace App\Core;

/**
 * CSRF Token Manager
 * 
 * Generates and validates CSRF tokens for form submissions.
 */
class CSRF
{
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Generate or retrieve the current CSRF token.
     */
    public static function token(): string
    {
        if (!Session::has(self::TOKEN_KEY)) {
            Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
        }
        return Session::get(self::TOKEN_KEY);
    }

    /**
     * Generate an HTML hidden input field with the CSRF token.
     */
    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate the CSRF token from the request.
     */
    public static function validate(?string $token = null): bool
    {
        $token = $token ?? ($_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $sessionToken = Session::get(self::TOKEN_KEY, '');

        if (empty($token) || empty($sessionToken)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Validate and throw an exception if invalid.
     */
    public static function validateOrFail(?string $token = null): void
    {
        if (!self::validate($token)) {
            Logger::warning('CSRF validation failed', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            ]);
            http_response_code(403);
            throw new \RuntimeException('CSRF token validation failed.');
        }
    }

    /**
     * Regenerate the CSRF token.
     */
    public static function regenerate(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set(self::TOKEN_KEY, $token);
        return $token;
    }
}
