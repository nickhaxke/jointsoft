<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Application Bootstrap
 * 
 * Initializes all core components and starts the application.
 */
class App
{
    private Router $router;
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\');
    }

    /**
     * Boot the application.
     */
    public function boot(): void
    {
        // Load configuration
        Config::load($this->basePath);

        // Initialize logger
        Logger::init(Config::get('log_path', $this->basePath . '/storage/logs'));

        // Ensure storage directories exist
        $this->ensureDirectories();

        // Start session
        Session::start();

        // Set up error handling
        $this->setupErrorHandling();

        // Set up router
        $this->router = new Router(Config::get('app_base_path', '/jointasoft'));
        $this->registerRoutes();

        // Dispatch the request
        $this->router->dispatch();
    }

    /**
     * Register all application routes.
     */
    private function registerRoutes(): void
    {
        // Authentication
        $this->router->get('/login', 'AuthController', 'showLogin');
        $this->router->post('/login', 'AuthController', 'login');
        $this->router->get('/logout', 'AuthController', 'logout');

        // Dashboard
        $this->router->get('/', 'DashboardController', 'index');
        $this->router->get('/dashboard', 'DashboardController', 'index');

        // Sales & Records
        $this->router->get('/sales/create', 'SaleController', 'create');
        $this->router->post('/sales/store', 'SaleController', 'store');
        $this->router->get('/sales/{id}', 'SaleController', 'show');
        $this->router->post('/sales/{id}/comment', 'SaleController', 'addComment');
        $this->router->get('/records', 'RecordController', 'index');
        $this->router->get('/receipts/{id}/view', 'RecordController', 'viewReceipt');



        // Contributions
        $this->router->get('/contributions', 'ContributionController', 'index');
        $this->router->get('/contributions/create', 'ContributionController', 'create');
        $this->router->post('/contributions/store', 'ContributionController', 'store');
        $this->router->get('/contributions/{id}', 'ContributionController', 'show');
        $this->router->post('/contributions/payment/{id}', 'ContributionController', 'updatePayment'); // legacy admin direct update
        $this->router->post('/contributions/submit-payment/{id}', 'ContributionController', 'submitPayment');
        $this->router->post('/contributions/approve-payment/{id}', 'ContributionController', 'approvePayment');

        // Member Ledger
        $this->router->get('/ledger', 'LedgerController', 'index');
        $this->router->get('/ledger/{id}', 'LedgerController', 'show');

        // Office Funds
        $this->router->get('/funds', 'FundController', 'index');
        $this->router->post('/funds/process', 'FundController', 'process');

        // Admin Review
        $this->router->get('/review', 'ReviewController', 'index');
        $this->router->get('/review/{id}', 'ReviewController', 'show');
        $this->router->post('/review/{id}/approve', 'ReviewController', 'approve');
        $this->router->post('/review/{id}/reject', 'ReviewController', 'reject');
        $this->router->post('/review/{id}/comment', 'ReviewController', 'comment');

        // Members
        $this->router->get('/members', 'MemberController', 'index');

        // Reports
        $this->router->get('/reports', 'ReportController', 'index');
        $this->router->get('/reports/export/pdf', 'ReportController', 'exportPdf');
        $this->router->get('/reports/export/excel', 'ReportController', 'exportExcel');

        // Settings
        $this->router->get('/settings', 'SettingController', 'index');
        $this->router->post('/settings/update', 'SettingController', 'update');
        $this->router->get('/settings/users', 'SettingController', 'users');
        $this->router->post('/settings/users/store', 'SettingController', 'storeUser');
        $this->router->post('/settings/users/{id}/update', 'SettingController', 'updateUser');
        $this->router->post('/settings/users/{id}/toggle', 'SettingController', 'toggleUser');
        $this->router->get('/settings/profile', 'SettingController', 'profile');
        $this->router->post('/settings/profile/update', 'SettingController', 'updateProfile');

        // File serving (receipts)
        $this->router->get('/file/{filename}', 'SaleController', 'serveFile');
    }

    /**
     * Ensure required storage directories exist.
     */
    private function ensureDirectories(): void
    {
        $dirs = [
            $this->basePath . '/storage/uploads',
            $this->basePath . '/storage/logs',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Set up error handling based on environment.
     */
    private function setupErrorHandling(): void
    {
        $debug = Config::get('app_debug', false);

        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        set_exception_handler(function (\Throwable $e) use ($debug) {
            Logger::critical($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            http_response_code(500);

            if ($debug) {
                echo '<pre style="padding:20px;font-family:monospace;background:#1a1a2e;color:#e94560;">';
                echo '<h2>Error: ' . htmlspecialchars($e->getMessage()) . '</h2>';
                echo '<p>File: ' . $e->getFile() . ':' . $e->getLine() . '</p>';
                echo '<h3>Stack Trace:</h3>';
                echo htmlspecialchars($e->getTraceAsString());
                echo '</pre>';
            } else {
                echo '<div style="text-align:center;padding:50px;font-family:sans-serif;">';
                echo '<h1>500</h1><p>Something went wrong. Please try again later.</p>';
                echo '</div>';
            }
        });
    }
}
