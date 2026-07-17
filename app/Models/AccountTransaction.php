<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Account Transaction Model
 * 
 * Manages deposits and withdrawals for office funds.
 */
class AccountTransaction extends Model
{
    protected string $table = 'account_transactions';

    /**
     * Record a transaction and update the account balance securely.
     */
    public function recordTransaction(
        int $accountId, 
        string $type, 
        float $amount, 
        string $description, 
        ?int $refId = null, 
        ?int $createdById = null
    ): int {
        $db = $this->getDb();
        
        $inTransaction = $db->inTransaction();
        if (!$inTransaction) {
            $db->beginTransaction();
        }

        try {
            // Insert transaction
            $id = $this->create([
                'account_id' => $accountId,
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
                'reference_id' => $refId,
                'created_by_id' => $createdById
            ]);

            // Update account balance
            $operator = $type === 'deposit' ? '+' : '-';
            // Securely lock and update the balance
            $sql = "UPDATE accounts SET balance = balance {$operator} :amount WHERE id = :id";
            $db->query($sql, [
                'amount' => $amount,
                'id' => $accountId
            ]);

            if (!$inTransaction) {
                $db->commit();
            }

            return (int)$id;
        } catch (\Exception $e) {
            if (!$inTransaction) {
                $db->rollback();
            }
            throw $e;
        }
    }
}
