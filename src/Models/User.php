<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;
use PDOException;

class User {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

   public function create($name, $email, $password, $role_id) {
    try {
        $sql = "INSERT INTO users (name, email, password, role_id, create_at)
                VALUES (:name, :email, :password, :role_id, :create_at)";

        $stmt = $this->conn->prepare($sql);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $create_at = date('Y-m-d H:i:s');

        return $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':role_id' => $role_id,
            ':create_at' => $create_at
        ]);

    } catch (Exception $e) {
        throw new Exception("User creation failed: " . $e->getMessage());
    }
}

    public static function getRoles(): array {
        $db = Database::getInstance();
        $query = "SELECT id, role_name FROM roles";
        return $db->select($query);
    }

   public static function findByEmail($email) {
    try {
        $conn = Database::getInstance()->getConnection();

        $sql = "SELECT u.*, r.role_name 
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE u.email = :email
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);

        return $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
}