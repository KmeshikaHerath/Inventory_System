<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class User {
    private $conn; 

    public function __construct()
    { 

    $this->conn = Database::getInstance()->getConnection(); 
    }

    // Create a new user in the database
    public function create($name, $email, $password, $role_id): bool
    {
        $sql = "INSERT INTO users (name, email, password, role_id, create_at)
                VALUES (:name, :email, :password, :role_id, :create_at)";

        $stmt = $this->conn->prepare($sql);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $create_at = date('Y-m-d H:i:s');

        $success = $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':role_id' => $role_id,
            ':create_at' => $create_at
        ]);

        if (!$success) {
            throw new Exception("Failed to create user.");
        }

        return true;
    }

    // Method to retrieve all roles from the database
    public function getRoles(): array 
    {
        $query = "SELECT id, role_name FROM roles";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Method to find a user by email
    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    } 
}