<?php

namespace VendingMachine\Models;

use VendingMachine\Database\BaseRepository;
use PDO;

class Transaction extends BaseRepository
{
    protected string $table = 'transactions';

    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (user_id, product_id, quantity, unit_price, total_amount) VALUES (:user_id, :product_id, :quantity, :unit_price, :total_amount)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':product_id', $data['product_id'], PDO::PARAM_INT);
        $stmt->bindValue(':quantity', $data['quantity'], PDO::PARAM_INT);
        $stmt->bindValue(':unit_price', $data['unit_price']);
        $stmt->bindValue(':total_amount', $data['total_amount']);
        
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function findByUserId(int $userId, ?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT t.*, p.name as product_name FROM {$this->table} t 
                JOIN products p ON t.product_id = p.id 
                WHERE t.user_id = :user_id 
                ORDER BY t.transaction_date DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByProductId(int $productId, ?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT t.*, u.username FROM {$this->table} t 
                JOIN users u ON t.user_id = u.id 
                WHERE t.product_id = :product_id 
                ORDER BY t.transaction_date DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':product_id', $productId, PDO::PARAM_INT);
        
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalSales(): float
    {
        $stmt = $this->db->query("SELECT SUM(total_amount) FROM {$this->table}");
        return (float) $stmt->fetchColumn();
    }

    public function getSalesByDateRange(string $startDate, string $endDate): array
    {
        $sql = "SELECT DATE(transaction_date) as sale_date, SUM(total_amount) as daily_total, COUNT(*) as transaction_count 
                FROM {$this->table} 
                WHERE DATE(transaction_date) BETWEEN :start_date AND :end_date 
                GROUP BY DATE(transaction_date) 
                ORDER BY sale_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':start_date', $startDate);
        $stmt->bindValue(':end_date', $endDate);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function findAllWithDetails(?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT t.*, p.name as product_name, u.username, u.email 
                FROM {$this->table} t 
                JOIN products p ON t.product_id = p.id 
                JOIN users u ON t.user_id = u.id 
                ORDER BY t.transaction_date DESC";
        
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

    public function countAll(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return (int) $stmt->fetchColumn();
    }

    public function countByUserId(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
