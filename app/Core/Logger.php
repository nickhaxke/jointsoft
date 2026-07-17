<?php

declare(strict_types=1);

namespace App\Core;

/**
 * File-based Logger
 * 
 * Simple logging with severity levels and daily rotation.
 */
class Logger
{
    private static ?string $logPath = null;

    private const LEVELS = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4,
    ];

    /**
     * Initialize the logger with the log directory path.
     */
    public static function init(string $logPath): void
    {
        self::$logPath = rtrim($logPath, '/\\');
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
    }

    /**
     * Write a log entry.
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        if (self::$logPath === null) {
            return;
        }

        $configLevel = Config::get('log_level', 'debug');
        if ((self::LEVELS[$level] ?? 0) < (self::LEVELS[$configLevel] ?? 0)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $entry = "[{$timestamp}] [{$levelUpper}] {$message}{$contextStr}" . PHP_EOL;

        $filename = self::$logPath . '/app-' . date('Y-m-d') . '.log';
        file_put_contents($filename, $entry, FILE_APPEND | LOCK_EX);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::log('debug', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        self::log('critical', $message, $context);
    }
}
