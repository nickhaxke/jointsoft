<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Contribution Payment Model
 */
class ContributionPayment extends Model
{
    protected string $table = 'contribution_payments';

    /**
     * Get pending payments for a specific member contribution
     */
    public function getPendingForMemberContribution(int $mcId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE member_contribution_id = :mcid AND status = 'pending' ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, ['mcid' => $mcId]);
    }

    /**
     * Get all pending payments system-wide
     */
    public function getAllPending(): array
    {
        $sql = "SELECT cp.*, u.name as member_name, c.title as contribution_title
                FROM {$this->table} cp
                JOIN users u ON cp.user_id = u.id
                JOIN member_contributions mc ON cp.member_contribution_id = mc.id
                JOIN contributions c ON mc.contribution_id = c.id
                WHERE cp.status = 'pending'
                ORDER BY cp.created_at ASC";
        return $this->db->fetchAll($sql);
    }
}
