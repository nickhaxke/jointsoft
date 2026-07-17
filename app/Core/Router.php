<?php

declare(strict_types=1);

namespace App\Core;

/**
 * URL Router
 * 
 * Maps URL patterns to controller actions.
 */
class Router
{
    private array $routes = [];
    private string $basePath;

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Register a GET route.
     */
    public function get(string $path, string $controller, string $action): self
    {
        $this->addRoute('GET', $path, $controller, $action);
        return $this;
    }

    /**
     * Register a POST route.
     */
    public function post(string $path, string $controller, string $action): self
    {
        $this->addRoute('POST', $path, $controller, $action);
        return $this;
    }

    /**
     * Add a route to the routing table.
     */
    private function addRoute(string $method, string $path, string $controller, string $action): void
    {
        // Convert route parameters like {id} to regex
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action,
        ];
    }

    /**
     * Dispatch the current request to the appropriate controller.
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $controllerClass = 'App\\Controllers\\' . $route['controller'];
                $action = $route['action'];

                if (!class_exists($controllerClass)) {
                    Logger::error("Controller not found: {$controllerClass}");
                    $this->sendError(404, 'Page not found.');
                    return;
                }

                $controller = new $controllerClass();

                if (!method_exists($controller, $action)) {
                    Logger::error("Action not found: {$controllerClass}::{$action}");
                    $this->sendError(404, 'Page not found.');
                    return;
                }

                try {
                    $controller->$action(...array_values($params));
                } catch (\Throwable $e) {
                    Logger::error("Route dispatch error: " . $e->getMessage(), [
                        'controller' => $controllerClass,
                        'action' => $action,
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);

                    if (Config::get('app_debug', false)) {
                        throw $e;
                    }

                    $this->sendError(500, 'An internal error occurred.');
                }

                return;
            }
        }

        $this->sendError(404, 'Page not found.');
    }

    /**
     * Parse the request URI, removing the base path and query string.
     */
    private function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove query string
        $uri = strtok($uri, '?');

        // Remove base path
        if (!empty($this->basePath) && str_starts_with($uri, $this->basePath)) {
            $uri = substr($uri, strlen($this->basePath));
        }

        // Remove /public prefix if present
        if (str_starts_with($uri, '/public')) {
            $uri = substr($uri, 7);
        }

        // Normalize
        $uri = '/' . trim($uri, '/');

        return $uri;
    }

    /**
     * Send an error response.
     */
    private function sendError(int $code, string $message): void
    {
        http_response_code($code);

        if ($code === 404) {
            // Try to render a nice 404 page
            $viewFile = dirname(__DIR__, 2) . '/views/errors/404.php';
            if (file_exists($viewFile)) {
                include $viewFile;
                return;
            }
        }

        echo '<div style="text-align:center;padding:50px;font-family:sans-serif;">';
        echo '<h1>' . $code . '</h1>';
        echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<a href="' . $this->basePath . '/dashboard">Go to Dashboard</a>';
        echo '</div>';
    }
}
