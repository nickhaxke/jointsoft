<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Audit Log Model
 */
class AuditLog extends Model
{
    protected string $table = 'audit_logs';

    /**
     * Get recent activities with user details.
     */
    public function getRecentActivities(int $limit = 5): array
    {
        $sql = "SELECT a.*, u.name as user_name, u.avatar as user_avatar 
                FROM {$this->table} a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.created_at DESC LIMIT ?";
                
        return $this->db->fetchAll($sql, [$limit]);
    }
}
