<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Ledger Model
 * 
 * Manages the double-entry accounting ledger for members.
 */
class Ledger extends Model
{
    protected string $table = 'ledgers';

    /**
     * Record a new ledger transaction.
     * Automatically calculates the running balance for the user.
     * 
     * @param int $userId Member ID
     * @param string $type Transaction type
     * @param string $description Transaction description
     * @param float $debit Amount member owes
     * @param float $credit Amount member paid / office owes member
     * @param int|null $refId Optional reference ID
     * @param int|null $createdById Admin/User who recorded this
     */
    public function recordTransaction(
        int $userId, 
        string $type, 
        string $description, 
        float $debit = 0.00, 
        float $credit = 0.00, 
        ?int $refId = null,
        ?int $createdById = null
    ): int {
        $db = $this->getDb();
        
        // Ensure atomic operation if not already in a transaction
        $inTransaction = $db->inTransaction();
        if (!$inTransaction) {
            $db->beginTransaction();
        }

        try {
            // Get current running balance (lock for update to prevent race conditions)
            $sql = "SELECT running_balance FROM {$this->table} WHERE user_id = :uid ORDER BY id DESC LIMIT 1 FOR UPDATE";
            $last = $db->fetch($sql, ['uid' => $userId]);
            
            $currentBalance = $last ? (float)$last['running_balance'] : 0.00;
            
            // New balance = Current + Debit - Credit
            // Positive balance means member OWES the office.
            // Negative balance means office OWES the member.
            $newBalance = $currentBalance + $debit - $credit;

            $id = $this->create([
                'user_id' => $userId,
                'transaction_type' => $type,
                'reference_id' => $refId,
                'description' => $description,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $newBalance,
                'created_by_id' => $createdById
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
