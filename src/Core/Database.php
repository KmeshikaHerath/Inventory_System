<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection;

    // Private constructor to prevent direct instantiation
    private function __construct() 
    {
        try {
            $this->connection = new PDO("mysql:host=localhost;dbname=inventory_db", "root", "");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("DB Connection failed: " . $e->getMessage());
        }
    }

    // Singleton instance retrieval
    public static function getInstance(): Database
     {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Get the PDO connection
    public function getConnection(): PDO
     {
        return $this->connection;
    }

    // Helper method for executing select queries
    public function select(string $query, array $params = []): array
    {
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper method for executing insert/update/delete queries
    public function __call($method, $args): mixed
    {
        return $this->connection->$method(...$args);
    }
}