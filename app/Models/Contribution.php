<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Contribution Model
 * 
 * Manages office-wide contributions created by admins.
 */
class Contribution extends Model
{
    protected string $table = 'contributions';

    /**
     * Get dashboard statistics for contributions.
     */
    public function getSummaryStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_campaigns,
                    COALESCE(SUM(total_expected_amount), 0) as total_expected
                FROM {$this->table}";
                
        $result = $this->rawOne($sql);

        // Calculate paid amount from member_contributions
        $paidSql = "SELECT COALESCE(SUM(paid_amount), 0) as total_collected FROM member_contributions";
        $paidResult = $this->rawOne($paidSql);

        return [
            'total_campaigns' => (int) ($result['total_campaigns'] ?? 0),
            'total_expected' => (float) ($result['total_expected'] ?? 0),
            'total_collected' => (float) ($paidResult['total_collected'] ?? 0),
        ];
    }
}
