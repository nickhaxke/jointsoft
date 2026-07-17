<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Configuration Manager
 * 
 * Loads .env variables and provides access to application configuration.
 * Includes a built-in .env parser (no external dependencies required).
 */
class Config
{
    private static array $config = [];
    private static bool $loaded = false;

    /**
     * Load configuration from .env and config files.
     */
    public static function load(string $basePath): void
    {
        if (self::$loaded) {
            return;
        }

        // Parse .env file
        self::loadEnvFile($basePath . '/.env');

        // Load app config
        $configFile = $basePath . '/config/app.php';
        if (file_exists($configFile)) {
            self::$config = require $configFile;
        }

        self::$loaded = true;
    }

    /**
     * Parse a .env file and set environment variables.
     */
    private static function loadEnvFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Parse KEY=VALUE
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove surrounding quotes
                if (
                    (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))
                ) {
                    $value = substr($value, 1, -1);
                }

                // Set in $_ENV and $_SERVER
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    /**
     * Get a configuration value.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$config[$key] ?? $default;
    }

    /**
     * Get all configuration values.
     */
    public static function all(): array
    {
        return self::$config;
    }
}
