<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Comment Model
 */
class Comment extends Model
{
    protected string $table = 'comments';

    /**
     * Get comments for a sale with user details.
     */
    public function getBySaleId(int $saleId): array
    {
        $sql = "SELECT c.*, u.name as user_name, u.avatar as user_avatar, u.role as user_role
                FROM {$this->table} c
                JOIN users u ON c.user_id = u.id
                WHERE c.sale_id = ?
                ORDER BY c.created_at ASC";
        return $this->db->fetchAll($sql, [$saleId]);
    }
}
