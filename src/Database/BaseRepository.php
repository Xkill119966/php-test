<?php

namespace VendingMachine\Database;

use PDO;
use PDOException;

abstract class BaseRepository
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function findAll(?int $limit = null, int $offset = 0, string $orderBy = 'id', string $orderDirection = 'ASC'): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$orderDirection}";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);
        
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollback(): bool
    {
        return $this->db->rollback();
    }
}
