<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class User {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function create($name, $email, $password) {
        try {
            $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
            $stmt = $this->conn->prepare($sql);

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            return $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);
        } catch (Exception $e) {
            throw new Exception("User creation failed: " . $e->getMessage());
        }
    }
}