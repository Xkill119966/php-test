<?php

namespace VendingMachine\Models;

use VendingMachine\Database\BaseRepository;
use PDO;

class Product extends BaseRepository
{
    protected string $table = 'products';

    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (name, price, quantity_available) VALUES (:name, :price, :quantity_available)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':price', $data['price']);
        $stmt->bindValue(':quantity_available', $data['quantity_available'] ?? 0, PDO::PARAM_INT);
        
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (in_array($key, ['name', 'price', 'quantity_available'])) {
                $fields[] = "{$key} = :{$key}";
                $params[":{$key}"] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }

    public function updateQuantity(int $id, int $quantity): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET quantity_available = :quantity WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':quantity', $quantity, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function decreaseQuantity(int $id, int $amount): bool
    {
        // Use explicit parameter names to avoid any binding issues
        $sql = "UPDATE {$this->table} SET quantity_available = quantity_available - :decrease_amount WHERE id = :product_id AND quantity_available >= :min_amount";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':product_id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':decrease_amount', $amount, PDO::PARAM_INT);
        $stmt->bindValue(':min_amount', $amount, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function findAvailable(?int $limit = null, int $offset = 0, string $orderBy = 'name', string $orderDirection = 'ASC'): array
    {
        // Show all products, not just those with stock > 0
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

    public function search(string $query, ?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE name LIKE :query ORDER BY name ASC";
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', "%{$query}%");
        
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findInStock(?int $limit = null, int $offset = 0, string $orderBy = 'name', string $orderDirection = 'ASC'): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE quantity_available > 0 ORDER BY {$orderBy} {$orderDirection}";
        
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
}
