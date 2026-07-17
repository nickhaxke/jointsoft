<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=jointasoft;charset=utf8', 'root', 'root');

// Alter the enum
$pdo->exec("ALTER TABLE ledgers MODIFY COLUMN transaction_type ENUM('contribution','expense_reimbursement','receipt_commission','penalty','cash_payment','refund','contribution_due','tra_debt','campaign_debt','contribution_assigned','contribution_payment') NOT NULL");

// Update existing empty transaction_type where description contains 'Assigned Contribution'
$pdo->exec("UPDATE ledgers SET transaction_type = 'contribution_assigned' WHERE transaction_type = '' AND description LIKE '%Assigned Contribution:%'");

// Update existing empty or 'contribution' transaction_type where description contains 'Approved payment for Contribution'
$pdo->exec("UPDATE ledgers SET transaction_type = 'contribution_payment' WHERE (transaction_type = '' OR transaction_type = 'contribution') AND description LIKE '%Approved payment for Contribution%'");

echo "Fixed DB ENUM and updated old empty strings for Campaign Debts";
