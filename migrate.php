<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=jointasoft', 'root', 'root');
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    $pdo->exec("ALTER TABLE `$table` ENGINE=InnoDB");
    echo "Altered $table to InnoDB\n";
}

$sql = file_get_contents('C:\wamp64\www\jointasoft\database\migrations\006_create_contribution_payments_table.sql');
$pdo->exec($sql);
echo "Migration successful.\n";
