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
            
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (empty($tables)) {
                 echo "<p style='color:red'>❌ TATIZO LINGINE: Database ipo wazi (Haina tables zozote). Tafadhali ingia phpMyAdmin kwenye cPanel yako na u-import faili la <b>database/schema.sql</b> au <b>database.sql</b>.</p>";
            } else {
                 echo "<p style='color:green'>✅ Found " . count($tables) . " tables.</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color:red'>❌ Database connection FAILED: " . $e->getMessage() . "</p>";
        }
        
        $storagePath = dirname(__DIR__) . '/storage';
        if (!is_dir($storagePath)) {
            echo "<p style='color:red'>❌ Folder la <b>storage</b> halipo!</p>";
        } elseif (!is_writable($storagePath)) {
            echo "<p style='color:red'>❌ Folder la <b>storage</b> haliruhusiwi kuandikwa. Tafadhali badilisha permissions zake kule kwenye FileZilla ziwe <b>0755</b> au <b>0777</b>.</p>";
        } else {
            echo "<p style='color:green'>✅ Storage folder permissions are OK.</p>";
        }
    } else {
        echo "<p>❌ .env NOT found at " . htmlspecialchars($envFile) . "</p>";
    }
    exit;
}

// Bootstrap and run the application
$app = new App\Core\App(dirname(__DIR__));
$app->boot();
