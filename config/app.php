<?php

declare(strict_types=1);

/**
 * Application Configuration Constants
 * 
 * Central configuration file for the application.
 * Values are loaded from .env where appropriate.
 */

return [
    // Application
    'app_name' => env('APP_NAME', 'JointaSoft'),
    'app_env' => env('APP_ENV', 'production'),
    'app_debug' => env('APP_DEBUG', false),
    'app_url' => env('APP_URL', 'http://localhost'),
    'app_base_path' => '/jointasoft',

    // Database
    'db_connection' => env('DB_CONNECTION', 'mysql'),
    'db_host' => env('DB_HOST', '127.0.0.1'),
    'db_port' => env('DB_PORT', '3306'),
    'db_database' => env('DB_DATABASE', 'jointasoft'),
    'db_username' => env('DB_USERNAME', 'root'),
    'db_password' => env('DB_PASSWORD', ''),
    'db_charset' => 'utf8mb4',
    'db_collation' => 'utf8mb4_unicode_ci',

    // Session
    'session_lifetime' => (int) env('SESSION_LIFETIME', 120),
    'session_name' => 'jointasoft_session',

    // File Upload
    'upload_max_size' => 10 * 1024 * 1024, // 10 MB
    'upload_allowed_types' => ['jpg', 'jpeg', 'png', 'pdf'],
    'upload_allowed_mimes' => [
        'image/jpeg',
        'image/png',
        'application/pdf',
    ],
    'upload_path' => dirname(__DIR__) . '/storage/uploads',

    // Logging
    'log_path' => dirname(__DIR__) . '/storage/logs',
    'log_level' => env('LOG_LEVEL', 'debug'),

    // Contribution Rates (internal office policy)
    'contribution_rate_approved' => 3.0,
    'contribution_rate_default' => 10.0,

    // Pagination
    'per_page' => 15,

    // User Roles
    'roles' => [
        'admin' => 'Administrator',
        'staff' => 'Staff',
    ],

    // Sale Statuses
    'statuses' => [
        'pending' => 'Pending Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'missing_receipt' => 'Missing Receipt',
    ],
];
