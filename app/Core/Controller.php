<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base Controller
 * 
 * Provides view rendering, redirects, and JSON responses.
 */
abstract class Controller
{
    /**
     * Render a view with the given layout.
     */
    protected function view(string $view, array $data = [], string $layout = 'app'): void
    {
        $basePath = dirname(__DIR__, 2);
        $viewFile = $basePath . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            Logger::error("View not found: {$viewFile}");
            throw new \RuntimeException("View not found: {$view}");
        }

        // Extract data variables for the view
        extract($data);

        // Capture view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render within layout
        $layoutFile = $basePath . '/views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Render a view without a layout.
     */
    protected function viewOnly(string $view, array $data = []): void
    {
        $basePath = dirname(__DIR__, 2);
        $viewFile = $basePath . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        extract($data);
        require $viewFile;
    }

    /**
     * Send a JSON response.
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirect to a URL.
     */
    protected function redirect(string $path): void
    {
        redirect($path);
    }

    /**
     * Redirect back to the previous page.
     */
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/dashboard');
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Get POST data with optional sanitization.
     */
    protected function input(string $key, mixed $default = ''): mixed
    {
        $value = $_POST[$key] ?? $default;
        if (is_string($value)) {
            return trim($value);
        }
        return $value;
    }

    /**
     * Get all POST data.
     */
    protected function allInput(): array
    {
        return array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $_POST);
    }

    /**
     * Get a query parameter.
     */
    protected function query(string $key, mixed $default = ''): mixed
    {
        $value = $_GET[$key] ?? $default;
        if (is_string($value)) {
            return trim($value);
        }
        return $value;
    }

    /**
     * Validate CSRF token for POST requests.
     */
    protected function validateCsrf(): void
    {
        CSRF::validateOrFail();
    }
}
