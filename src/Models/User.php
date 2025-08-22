<?php

namespace VendingMachine\Models;

use VendingMachine\Database\BaseRepository;
use PDO;

class User extends BaseRepository
{
    protected string $table = 'users';

    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', $data['username']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':password_hash', password_hash($data['password'], PASSWORD_DEFAULT));
        $stmt->bindValue(':role', $data['role'] ?? 'user');
        
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if ($key === 'password') {
                $fields[] = 'password_hash = :password_hash';
                $params[':password_hash'] = password_hash($value, PASSWORD_DEFAULT);
            } elseif (in_array($key, ['username', 'email', 'role'])) {
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

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = :username");
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email");
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);
        
        if ($user && $this->verifyPassword($password, $user['password_hash'])) {
            // Remove password hash from returned data
            unset($user['password_hash']);
            return $user;
        }
        
        return null;
    }
}
