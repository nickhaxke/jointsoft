<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Sale Model
 */
class Sale extends Model
{
    protected string $table = 'sales';

    /**
     * Get dashboard statistics.
     */
    public function getDashboardStats(?int $userId = null): array
    {
        $where = $userId ? "WHERE user_id = " . (int)$userId : "";
        
        $sql = "SELECT 
                    COUNT(*) as total_sales,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_records,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_reviews,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_records,
                    SUM(CASE WHEN status = 'missing_receipt' THEN 1 ELSE 0 END) as missing_receipts,
                    COALESCE(SUM(contribution_amount), 0) as total_contribution
                FROM {$this->table} {$where}";
                
        $result = $this->rawOne($sql);

        // Avoid nulls when table is empty
        return [
            'total_sales' => (int) ($result['total_sales'] ?? 0),
            'approved_records' => (int) ($result['approved_records'] ?? 0),
            'pending_reviews' => (int) ($result['pending_reviews'] ?? 0),
            'rejected_records' => (int) ($result['rejected_records'] ?? 0),
            'missing_receipts' => (int) ($result['missing_receipts'] ?? 0),
            'total_contribution' => (float) ($result['total_contribution'] ?? 0),
        ];
    }
}
