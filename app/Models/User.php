<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * User Model
 */
class User extends Model
{
    protected string $table = 'users';

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): array|false
    {
        return $this->findOneBy('email', $email);
    }

    /**
     * Verify a user's password.
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Hash a password.
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(int $userId): void
    {
        $this->update($userId, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Get all active users.
     */
    public function getActive(): array
    {
        return $this->raw(
            "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name ASC"
        );
    }

    /**
     * Get all staff members.
     */
    public function getStaff(): array
    {
        return $this->raw(
            "SELECT * FROM {$this->table} WHERE role = 'staff' AND is_active = 1 ORDER BY name ASC"
        );
    }

    /**
     * Count users by role.
     */
    public function countByRole(string $role): int
    {
        return $this->count('role = ? AND is_active = 1', [$role]);
    }

    /**
     * Check if an email exists (optionally excluding a user ID).
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = ?";
        $params = [$email];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        return (int) $this->db->fetchColumn($sql, $params) > 0;
    }
}
