<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        echo "<pre style='color:red;'>FATAL ERROR: ";
        print_r($error);
        echo "</pre>";
    }
});

echo "<h2>Diagnostics v2</h2>";

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "<p>✅ .env exists</p>";
    $env = file_get_contents($envFile);
    if (strpos($env, 'APP_DEBUG=true') !== false) {
        echo "<p>✅ APP_DEBUG is true</p>";
    } else {
        echo "<p>❌ APP_DEBUG is NOT true</p>";
    }
    
    // Extract DB credentials manually
    preg_match('/DB_DATABASE=(.+)/', $env, $db);
    preg_match('/DB_USERNAME=(.+)/', $env, $user);
    preg_match('/DB_PASSWORD=(.+)/', $env, $pass);
    
    $dbname = trim($db[1] ?? '');
    $dbuser = trim($user[1] ?? '');
    $dbpass = trim($pass[1] ?? '');
    
    echo "<p>DB Name: $dbname</p>";
    echo "<p>DB User: $dbuser</p>";
    
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "<p style='color:green'>✅ Database connection successful!</p>";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($tables)) {
             echo "<p style='color:red'>❌ Database is EMPTY. You need to import your tables via phpMyAdmin!</p>";
        } else {
             echo "<p style='color:green'>✅ Found " . count($tables) . " tables.</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>❌ Database connection FAILED: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ .env NOT found</p>";
}
