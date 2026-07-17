<?php

declare(strict_types=1);

/**
 * Global Helper Functions
 * 
 * Available throughout the application via Composer autoloading.
 */

use App\Core\Config;
use App\Core\CSRF;
use App\Core\Auth;
use App\Core\Session;

/**
 * Get an environment variable with optional default.
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false) {
        return $default;
    }

    // Cast boolean-like strings
    return match (strtolower((string) $value)) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'null', '(null)' => null,
        'empty', '(empty)' => '',
        default => $value,
    };
}

/**
 * Generate a full URL path relative to the application base.
 */
function url(string $path = ''): string
{
    $basePath = Config::get('app_base_path', '/jointasoft');
    return rtrim($basePath, '/') . '/' . ltrim($path, '/');
}

/**
 * Generate a URL for static assets.
 */
function asset(string $path): string
{
    return url('public/assets/' . ltrim($path, '/'));
}

/**
 * Escape output for HTML context.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL within the application.
 */
function redirect(string $path): never
{
    $fullUrl = url($path);
    header('Location: ' . $fullUrl);
    exit;
}

/**
 * Get the CSRF token field HTML.
 */
function csrf_field(): string
{
    return CSRF::field();
}

/**
 * Get the CSRF token value.
 */
function csrf_token(): string
{
    return CSRF::token();
}

/**
 * Check if the current user is authenticated.
 */
function auth(): ?array
{
    return Auth::user();
}

/**
 * Check if the current URL matches a path (for active nav highlighting).
 */
function isActive(string $path): bool
{
    $currentUri = $_SERVER['REQUEST_URI'] ?? '';
    $fullPath = url($path);
    return str_starts_with($currentUri, $fullPath);
}

/**
 * Get the active CSS class if the path matches.
 */
function activeClass(string $path, string $activeClass = 'bg-indigo-50 text-indigo-700', string $defaultClass = 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'): string
{
    return isActive($path) ? $activeClass : $defaultClass;
}

/**
 * Format a number as currency (TZS).
 */
function formatMoney(float|int|string|null $amount): string
{
    if ($amount === null) {
        return 'TZS 0.00';
    }
    return 'TZS ' . number_format((float) $amount, 2);
}

/**
 * Format a date string.
 */
function formatDate(?string $date, string $format = 'M d, Y'): string
{
    if (empty($date)) {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Format a date with time.
 */
function formatDateTime(?string $date, string $format = 'M d, Y h:i A'): string
{
    if (empty($date)) {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Get a human-readable time ago string.
 */
function timeAgo(?string $datetime): string
{
    if (empty($datetime)) {
        return '-';
    }

    $now = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->diff($then);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/**
 * Get status badge HTML.
 */
function statusBadge(string $status): string
{
    $config = match ($status) {
        'approved' => ['bg-emerald-100 text-emerald-700', 'Approved'],
        'rejected' => ['bg-red-100 text-red-700', 'Rejected'],
        'pending' => ['bg-amber-100 text-amber-700', 'Pending Review'],
        'missing_receipt' => ['bg-slate-100 text-slate-600', 'Missing Receipt'],
        default => ['bg-slate-100 text-slate-600', ucfirst($status)],
    };

    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $config[0] . '">' . e($config[1]) . '</span>';
}

/**
 * Get contribution rate based on status.
 */
function getContributionRate(string $status): float
{
    return match ($status) {
        'approved' => Config::get('contribution_rate_approved', 3.0),
        default => Config::get('contribution_rate_default', 10.0),
    };
}

/**
 * Calculate contribution amount.
 */
function calculateContribution(float $saleAmount, string $status): float
{
    $rate = getContributionRate($status);
    return round($saleAmount * ($rate / 100), 2);
}

/**
 * Truncate a string to a given length.
 */
function truncate(string $text, int $length = 50, string $suffix = '...'): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Generate a file preview URL.
 */
function fileUrl(string $filename): string
{
    return url('/file/' . urlencode($filename));
}

/**
 * Check if a file is an image based on extension.
 */
function isImage(string $filename): bool
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png'], true);
}

/**
 * Check if a file is a PDF.
 */
function isPdf(string $filename): bool
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return $ext === 'pdf';
}

/**
 * Get old form input value (for repopulating forms after validation failure).
 */
function old(string $key, mixed $default = ''): mixed
{
    return Session::get('_old_input')[$key] ?? $default;
}

/**
 * Store old input in session (for form repopulation).
 */
function flashOldInput(array $data): void
{
    // Remove sensitive fields
    unset($data['password'], $data['password_confirmation'], $data['_csrf_token']);
    Session::set('_old_input', $data);
}

/**
 * Clear old input from session.
 */
function clearOldInput(): void
{
    Session::remove('_old_input');
}

/**
 * Format file size in human-readable form.
 */
function formatFileSize(int $bytes): string
{
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = (int) floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
