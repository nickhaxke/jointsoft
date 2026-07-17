<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=jointasoft;charset=utf8', 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $pdo->beginTransaction();

    // 1. Find all member_contributions that do NOT have a corresponding ledger entry
    $mcs = $pdo->query("
        SELECT mc.*, c.title 
        FROM member_contributions mc 
        JOIN contributions c ON c.id = mc.contribution_id
        WHERE NOT EXISTS (
            SELECT 1 FROM ledgers l 
            WHERE l.transaction_type = 'contribution_assigned' 
            AND l.user_id = mc.user_id 
            AND l.reference_id = mc.contribution_id
        )
    ")->fetchAll(PDO::FETCH_ASSOC);

    $insertStmt = $pdo->prepare("INSERT INTO ledgers (user_id, transaction_type, reference_id, description, debit, credit, running_balance, created_by_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($mcs as $mc) {
        // Insert the initial debit
        $insertStmt->execute([
            $mc['user_id'],
            'contribution_assigned',
            $mc['contribution_id'],
            "Assigned Contribution: {$mc['title']}",
            $mc['expected_amount'],
            0.00,
            0.00, // We will recalculate this next
            1,
            $mc['created_at'] // Use the original assignment time
        ]);
    }

    // 2. Recalculate all running balances for all users
    $users = $pdo->query("SELECT DISTINCT user_id FROM ledgers")->fetchAll(PDO::FETCH_ASSOC);
    $fetchTxnStmt = $pdo->prepare("SELECT id, debit, credit FROM ledgers WHERE user_id = ? ORDER BY created_at ASC, id ASC");
    $updateTxnStmt = $pdo->prepare("UPDATE ledgers SET running_balance = ? WHERE id = ?");

    foreach ($users as $u) {
        $userId = $u['user_id'];
        
        $fetchTxnStmt->execute([$userId]);
        $txns = $fetchTxnStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $balance = 0.00;
        foreach ($txns as $txn) {
            $balance = $balance + (float)$txn['debit'] - (float)$txn['credit'];
            $updateTxnStmt->execute([$balance, $txn['id']]);
        }
    }

    $pdo->commit();
    echo "Successfully inserted missing campaign debits and recalculated all running balances.\n";
} catch (\Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
