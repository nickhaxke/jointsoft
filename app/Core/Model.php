<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base Model
 * 
 * Provides common database operations for all models.
 */
abstract class Model
{
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get the database instance.
     */
    public function getDb(): Database
    {
        return $this->db;
    }

    /**
     * Find a record by its primary key.
     */
    public function find(int|string $id): array|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Get all records.
     */
    public function all(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}";
        return $this->db->fetchAll($sql);
    }

    /**
     * Find records by a column value.
     */
    public function findBy(string $column, mixed $value): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        return $this->db->fetchAll($sql, [$value]);
    }

    /**
     * Find a single record by a column value.
     */
    public function findOneBy(string $column, mixed $value): array|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ? LIMIT 1";
        return $this->db->fetch($sql, [$value]);
    }

    /**
     * Insert a new record.
     */
    public function create(array $data): string
    {
        return $this->db->insert($this->table, $data);
    }

    /**
     * Update a record by its primary key.
     */
    public function update(int|string $id, array $data): int
    {
        return $this->db->update($this->table, $data, "{$this->primaryKey} = ?", [$id]);
    }

    /**
     * Delete a record by its primary key.
     */
    public function delete(int|string $id): int
    {
        return $this->db->delete($this->table, "{$this->primaryKey} = ?", [$id]);
    }

    /**
     * Count all records.
     */
    public function count(string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$where}";
        return (int) $this->db->fetchColumn($sql, $params);
    }

    /**
     * Paginate records.
     */
    public function paginate(int $page = 1, int $perPage = 15, string $where = '1=1', array $params = [], string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $total = $this->count($where, $params);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy} {$direction} LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->db->fetchAll($sql, $params);

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
        ];
    }

    /**
     * Execute a raw query.
     */
    public function raw(string $sql, array $params = []): array
    {
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Execute a raw query and return a single row.
     */
    public function rawOne(string $sql, array $params = []): array|false
    {
        return $this->db->fetch($sql, $params);
    }
}
