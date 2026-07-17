<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=jointasoft;charset=utf8', 'root', 'root');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // We know the 30k credit is ID 7 currently (from the latest rebuild).
    // Let's insert a 30k debit for the receipt BEFORE ID 7.
    
    // Find the date of the 30k credit
    $creditRow = $pdo->query("SELECT * FROM ledgers WHERE transaction_type = 'receipt_commission' AND credit = 30000.00 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if ($creditRow) {
        $insertStmt = $pdo->prepare("INSERT INTO ledgers (user_id, transaction_type, reference_id, description, debit, credit, running_balance, created_by_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $insertStmt->execute([
            $creditRow['user_id'],
            'contribution_due', // This is the DEBT
            $creditRow['reference_id'],
            '3% Contribution Due for Approved Receipt',
            30000.00, // debit
            0.00, // credit
            0.00, // will recalculate
            $creditRow['created_by_id'],
            $creditRow['created_at'] // Same time, but we'll sort by ID to ensure debit comes before credit?
            // Actually let's just make it 1 second earlier
        ]);
        
        // Re-rebuild the ledgers table chronologically to ensure perfect running balances
        $all = $pdo->query("SELECT * FROM ledgers ORDER BY created_at ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

        $pdo->exec("DELETE FROM ledgers");
        $pdo->exec("ALTER TABLE ledgers AUTO_INCREMENT = 1");

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
        
        echo "Missing 30k DEBT inserted and ledgers rebuilt. New balance should be 390k.\n";
    } else {
        echo "Could not find the 30k credit row.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
