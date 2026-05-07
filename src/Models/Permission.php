<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Handles permission retrieval
 */
class Permission
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Get permissions by role
     */
    public function getByRole(int $roleId): array
    {
        $stmt = $this->conn->prepare("
            SELECT p.name
            FROM permissions p
            JOIN roles_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
        ");

        $stmt->execute([$roleId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
