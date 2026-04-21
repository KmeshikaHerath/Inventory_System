<?php
namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class User {
    private static $conn;

    public function __construct() {
        self::$conn = Database::getInstance()->getConnection();
    }

     // Ensure connection exists
    private static function init() {
        if (!self::$conn) {
            self::$conn = Database::getInstance()->getConnection();
        }
    }

   public static function create($name, $email, $password, $role_id) {
        self::init();

      $sql = "INSERT INTO users (name, email, password, role_id, create_at)
                VALUES (:name, :email, :password, :role_id, :create_at)";

        $stmt = self::$conn->prepare($sql);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $create_at = date('Y-m-d H:i:s');

        return $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':role_id' => $role_id,
            ':create_at' => $create_at
        ]);

   if(!$success) {
        throw new Exception("Failed to create user.");
    }

    return true;

}

    public static function getRoles(): array {
        self::init();

        $query = "SELECT id, role_name FROM roles";
        $stmt = self::$conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    }


public static function findByEmail($email) {
    self::init();

    $stmt = self::$conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    return $stmt->fetch(\PDO::FETCH_ASSOC);
} 
}

