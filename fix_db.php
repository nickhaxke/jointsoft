<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=jointasoft;charset=utf8', 'root', 'root');

// Alter the enum
$pdo->exec("ALTER TABLE ledgers MODIFY COLUMN transaction_type ENUM('contribution','expense_reimbursement','receipt_commission','penalty','cash_payment','refund','contribution_due', 'tra_debt', 'campaign_debt') NOT NULL");

// Update existing empty transaction_type where description contains '10% Contribution'
$pdo->exec("UPDATE ledgers SET transaction_type = 'contribution_due' WHERE transaction_type = '' AND description LIKE '%10% Contribution%'");
$pdo->exec("UPDATE ledgers SET transaction_type = 'contribution' WHERE transaction_type = '' AND description LIKE '%Approved payment for Contribution%'");

// We might have others, let's fix them if needed.
echo "Fixed DB ENUM and updated old empty strings";
