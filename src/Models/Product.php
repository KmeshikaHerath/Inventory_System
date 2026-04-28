<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Product Model
 */
class Product
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Get all products
     */
    public function getAll($limit, $offset, $search, $min, $max, $sort, $order)
    {
        $sql = "SELECT * FROM products WHERE deleted_at IS NULL";
        $params = [];

        if ($search) {
            $sql .= " AND name LIKE :search";
            $params[':search'] = "%$search%";
        }

        if ($min !== null) {
            $sql .= " AND price >= :min";
            $params[':min'] = $min;
        }

        if ($max !== null) {
            $sql .= " AND price <= :max";
            $params[':max'] = $max;
        }

        // SAFE SORTING
        $allowedSort = ['id', 'name', 'price', 'quantity'];
        $sort = in_array($sort, $allowedSort) ? $sort : 'id';

        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $sql .= " ORDER BY $sort $order LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function create(array $data): bool
    {
        $stmt = $this->conn->prepare("
        INSERT INTO products (name, price, quantity, sku, description, status, created_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

        $created_at = date('Y-m-d H:i:s');

        $userId = $_SESSION['user_id'] ?? null;

        $status = $data['status'] ?? 'active';

        return $stmt->execute([
            $data['name'],
            $data['price'],
            $data['quantity'],
            $data['sku'],
            $data['description'],
            $status,
            $userId,
            $created_at
        ]);

         $productId = $pdo->lastInsertId();
          $uploadDir = __DIR__ . "/public/images/" . $productId . "/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

    }

    public function paginate($page = 1, $search = '', $minPrice = null, $maxPrice = null, $status = null, $includeDeleted = false)
    {

        $limit = 10;
        $page = max(1, (int)$page);
        $offset = ($page - 1) * $limit;

        $params = [];
        $conditions = [];

        // ONLY SHOW ACTIVE PRODUCTS BY DEFAULT (NOT DELETED)
        if (!$includeDeleted) {
            $conditions[] = "deleted_at IS NULL";
        }

        // SEARCH
        if (!empty($search)) {
            $conditions[] = "(name LIKE :search OR sku LIKE :search)";
            $params[':search'] = '%' . trim($search) . '%';
        }

        // MIN PRICE
        if ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice)) {
            $conditions[] = "price >= :minPrice";
            $params[':minPrice'] = (float)$minPrice;
        }

        // MAX PRICE
        if ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice)) {
            $conditions[] = "price <= :maxPrice";
            $params[':maxPrice'] = (float)$maxPrice;
        }

        // BUILD WHERE CLAUSE
        $where = '';
        if (!empty($conditions)) {
            $where = "WHERE " . implode(' AND ', $conditions);
        }

        // COUNT QUERY
        $countSql = "SELECT COUNT(*) as total FROM products $where";
        $stmt = $this->conn->prepare($countSql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        // STATUS FILTER (IMPORTANT FIX)
        if (!empty($status) && $status !== 'all') {
            $conditions[] = "status = :status";
            $params[':status'] = $status;
        }

        $stmt->execute();
        $total = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // FIX PAGE OVERFLOW
        $totalPages = max(1, ceil($total / $limit));
        if ($page > $totalPages && $total > 0) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        // DATA QUERY
        $sql = "SELECT * FROM products $where ORDER BY id ASC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);

        // Bind dynamic params
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        // Bind limit and offset as integers
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [
            "data" => $data,
            "total" => $total,
            "page" => $page,
            "limit" => $limit
        ];
    }

    public function findById($id, $includeDeleted = false)
    {
        $sql = "SELECT * FROM products WHERE id = :id";

        if (!$includeDeleted) {
            $sql .= " AND deleted_at IS NULL";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function delete($id, $userId = null)
    {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $stmt = $this->conn->prepare("
        UPDATE products 
        SET deleted_at = NOW() 
        WHERE id = :id AND deleted_at IS NULL
    ");

        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);


        return $stmt->execute();
    }

    /**
     * Update product
     */
    public function update($id, $data): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;
        $updated_at = date('Y-m-d H:i:s');

        $stmt = $this->conn->prepare("
        UPDATE products 
        SET name = ?, 
            price = ?, 
            quantity = ?, 
            sku = ?, 
            description = ?, 
            updated_by = ?, 
            updated_at = ?
        WHERE id = ?
    ");

        return $stmt->execute([
            $data['name'],
            $data['price'],
            $data['quantity'],
            $data['sku'],
            $data['description'],
            $userId,
            $updated_at,
            $id
        ]);
    }
    public function restore($id)
    {
        $stmt = $this->conn->prepare("
        UPDATE products 
        SET deleted_at = NULL,
            status = 'active'
        WHERE id = :id
    ");

        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getActiveProducts(): array
    {
        $stmt = $this->conn->prepare("
        SELECT * FROM products 
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC
    ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
