<?php

namespace App\Models;

use App\Core\Database;
use PDO;

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
        $sql = "SELECT * FROM products WHERE 1";
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
            INSERT INTO products (name, price, quantity, sku, description)
            VALUES (?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['name'],
            $data['price'],
            $data['quantity'],
            $data['sku'],
            $data['description']
        ]);
    }

    public function paginate($page = 1, $search = '', $minPrice = null, $maxPrice = null)
    {
        $limit = 10;
        $page = max(1, (int)$page); // prevent invalid pages
        $offset = ($page - 1) * $limit;

        $params = [];
        $conditions = [];

        //  SEARCH
        if (!empty($search)) {
            $conditions[] = "(name LIKE :search OR SKU_Code LIKE :search)";
            $params[':search'] = '%' . trim($search) . '%';
        }

        // MIN PRICE
        if ($minPrice !== null && $minPrice !== '') {
            $conditions[] = "price >= :minPrice";
            $params[':minPrice'] = (float)$minPrice;
        }

        // MAX PRICE
        if ($maxPrice !== null && $maxPrice !== '') {
            $conditions[] = "price <= :maxPrice";
            $params[':maxPrice'] = (float)$maxPrice;
        }

        // WHERE
        $where = '';
        if (!empty($conditions)) {
            $where = "WHERE " . implode(' AND ', $conditions);
        }

        //  COUNT
        $countSql = "SELECT COUNT(*) as total FROM products $where";
        $stmt = $this->conn->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        //  Fix page overflow
        $totalPages = max(1, ceil($total / $limit));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $limit;
        }

        //  DATA QUERY
        $sql = "SELECT * FROM products $where ORDER BY id ASC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // SAFE RESPONSE
        return [
            "data" => $data ?: [],
            "total" => $total,
            "currentPage" => $page,
            "totalPages" => $totalPages
        ];
    }

    public function findById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->bindValue(':id', (int)$id, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Update product
     */
    public function update($id, $data): bool
    {
        $stmt = $this->conn->prepare("
        UPDATE products 
        SET name=?, price=?, quantity=?, sku=?, description=? 
        WHERE id=?
    ");

        return $stmt->execute([
            $data['name'],
            $data['price'],
            $data['quantity'],
            $data['sku'],
            $data['description'],
            $id
        ]);
    }
}
