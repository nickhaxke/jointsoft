<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=jointasoft;charset=utf8', 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $all = $pdo->query("SELECT * FROM ledgers ORDER BY created_at ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

    $pdo->exec("DELETE FROM ledgers");
    $pdo->exec("ALTER TABLE ledgers AUTO_INCREMENT = 1");

    $insertStmt = $pdo->prepare("INSERT INTO ledgers (user_id, transaction_type, reference_id, description, debit, credit, running_balance, created_by_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $balances = [];

    foreach ($all as $row) {
        $uid = $row['user_id'];
        if (!isset($balances[$uid])) $balances[$uid] = 0.00;
        
        $balances[$uid] = $balances[$uid] + (float)$row['debit'] - (float)$row['credit'];

        $insertStmt->execute([
            $uid,
            $row['transaction_type'],
            $row['reference_id'],
            $row['description'],
            $row['debit'],
            $row['credit'],
            $balances[$uid],
            $row['created_by_id'],
            $row['created_at']
        ]);
    }

    echo "Ledgers table completely rebuilt in correct chronological ID order.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
