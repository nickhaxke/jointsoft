<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=jointasoft', 'root', 'root');
$stmt = $pdo->query("SHOW TABLE STATUS LIKE 'member_contributions'");
$status = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($status);
