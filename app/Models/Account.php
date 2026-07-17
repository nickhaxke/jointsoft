<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Account Model
 * 
 * Manages physical office funds (Cash, Bank, Mobile Money, Petty Cash).
 */
class Account extends Model
{
    protected string $table = 'accounts';

    /**
     * Get total office funds across all accounts.
     */
    public function getTotalFunds(): float
    {
        $sql = "SELECT COALESCE(SUM(balance), 0) as total FROM {$this->table}";
        $result = $this->rawOne($sql);
        return (float) ($result['total'] ?? 0);
    }
}
