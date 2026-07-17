<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Member Contribution Model
 * 
 * Tracks individual member payments towards a specific contribution.
 */
class MemberContribution extends Model
{
    protected string $table = 'member_contributions';

    /**
     * Get a member's specific contribution balance summary.
     */
    public function getMemberStats(int $userId): array
    {
        $sql = "SELECT 
                    COALESCE(SUM(expected_amount), 0) as total_expected,
                    COALESCE(SUM(paid_amount), 0) as total_paid
                FROM {$this->table}
                WHERE user_id = :user_id";
                
        $result = $this->rawOne($sql, ['user_id' => $userId]);

        $expected = (float) ($result['total_expected'] ?? 0);
        $paid = (float) ($result['total_paid'] ?? 0);
        $balance = $expected - $paid;

        return [
            'total_expected' => $expected,
            'total_paid' => $paid,
            'remaining_balance' => $balance > 0 ? $balance : 0,
        ];
    }
}
