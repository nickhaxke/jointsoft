<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h2>Diagnostics</h2>";

// 1. Check if .env exists
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "<p style='color:green'>✅ .env file found!</p>";
    $envContents = file_get_contents($envFile);
    if (strpos($envContents, 'APP_DEBUG=true') !== false) {
         echo "<p style='color:green'>✅ APP_DEBUG is set to true in .env</p>";
    } else {
         echo "<p style='color:red'>❌ APP_DEBUG is NOT true in .env</p>";
    }
} else {
    echo "<p style='color:red'>❌ .env file NOT found in " . htmlspecialchars($envFile) . "</p>";
}

// 2. Load config
$config = [];
if (file_exists(__DIR__ . '/config/app.php')) {
     echo "<p style='color:green'>✅ config/app.php found.</p>";
     // Try to load env manually
     if (file_exists($envFile)) {
         $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
         foreach ($lines as $line) {
             $line = trim($line);
             if ($line === '' || str_starts_with($line, '#')) continue;
             if (str_contains($line, '=')) {
                 [$key, $value] = explode('=', $line, 2);
                 $key = trim($key);
                 $value = trim(trim($value, '"'), "'");
                 $_ENV[$key] = $value;
                 $_SERVER[$key] = $value;
             }
         }
     }
     
     // Mock env function if it doesn't exist
     if (!function_exists('env')) {
          require __DIR__ . '/app/Helpers/functions.php';
     }
     $config = require __DIR__ . '/config/app.php';
     echo "<p>App Debug Value in Config: " . ($config['app_debug'] ? 'TRUE' : 'FALSE') . "</p>";
}

// 3. Test Database Connection
echo "<h3>Database Test</h3>";
if (!empty($config)) {
    $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_database']};charset={$config['db_charset']}";
    echo "<p>DSN: $dsn</p>";
    echo "<p>User: {$config['db_username']}</p>";
    try {
        $pdo = new \PDO($dsn, $config['db_username'], $config['db_password'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        echo "<p style='color:green'>✅ Database connection successful!</p>";
        
        // Test tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($tables)) {
             echo "<p style='color:red'>❌ Database is EMPTY. You need to import your tables!</p>";
        } else {
             echo "<p style='color:green'>✅ Found " . count($tables) . " tables in the database.</p>";
        }
    } catch (\PDOException $e) {
        echo "<p style='color:red'>❌ Database connection FAILED: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
