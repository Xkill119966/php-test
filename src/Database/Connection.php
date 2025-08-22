<?php

namespace VendingMachine\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $instance = null;
    private array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/database.php';
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $connection = new self();
            self::$instance = $connection->createConnection();
        }
        
        return self::$instance;
    }

    private function createConnection(): PDO
    {
        try {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['dbname']}";
                        
            return new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    public static function resetInstance(): void
    {
        self::$instance = null;
    }
}
