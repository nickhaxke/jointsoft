<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Receipt Model
 */
class Receipt extends Model
{
    protected string $table = 'receipts';

    /**
     * Get receipts for a sale.
     */
    public function getBySaleId(int $saleId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE sale_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$saleId]);
    }
}
