<?php

declare(strict_types=1);

/**
 * Front Controller
 * 
 * All requests are routed through this file.
 */

// Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

if (isset($_GET['diagnose'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    echo "<h2>Internal Diagnostics</h2>";
    $envFile = dirname(__DIR__) . '/.env';
    if (file_exists($envFile)) {
        echo "<p>✅ .env exists at " . htmlspecialchars($envFile) . "</p>";
        $env = file_get_contents($envFile);
        preg_match('/DB_DATABASE=(.+)/', $env, $db);
        preg_match('/DB_USERNAME=(.+)/', $env, $user);
        preg_match('/DB_PASSWORD=(.+)/', $env, $pass);
        $dbname = trim($db[1] ?? '');
        $dbuser = trim($user[1] ?? '');
        $dbpass = trim($pass[1] ?? '');
        
        try {
            $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            echo "<p style='color:green'>✅ Database connection successful!</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>❌ Database connection FAILED: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ .env NOT found at " . htmlspecialchars($envFile) . "</p>";
    }
    exit;
}

// Bootstrap and run the application
$app = new App\Core\App(dirname(__DIR__));
$app->boot();
