<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

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
     * Get products with filtering, sorting and pagination.
     *
     * @param int $limit Number of records per page
     * @param int $offset Pagination offset
     * @param string $search Search keyword for product name
     * @param float|null $min Minimum price filter
     * @param float|null $max Maximum price filter
     * @param string $sort Column to sort by
     * @param string $order Sort direction (ASC|DESC)
     * @return array List of products
     */

    public function getAll(int $limit, int $offset, string $search, ?float $min, ?float $max, string $sort, string $order): array
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

    /**
     * Create a new product record.
     *
     * @param array $data Product data (name, price, quantity, sku, description, status, category_id)
     * @return bool True on success, false on failure
     */
    public function create(array $data): int
    {
        $stmt = $this->conn->prepare("
        INSERT INTO products (name, price, quantity, sku, description, status, created_by, created_at, category_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)
    ");

        $stmt->execute([
            $data['name'],
            $data['price'],
            $data['quantity'],
            $data['sku'],
            $data['description'],
            $data['status'] ?? 'active',
            $data['created_by'],
            $data['category_id'] ?? null
        ]);

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Paginate products with filters.
     *
     * @param int $page Page number
     * @param string $search Search keyword
     * @param string|null $minPrice Minimum price
     * @param string|null $maxPrice Maximum price
     * @param string|null $status Product status filter
     * @param bool $includeDeleted Include soft-deleted records
     * @return array Paginated result set
     */
    public function paginate(
        int $page = 1,
        string $search = '',
        ?string $minPrice = null,
        ?string $maxPrice = null,
        ?string $status = null,
        bool $includeDeleted = false,
        int $limit = 10
    ) {
        $offset = ($page - 1) * $limit;

        $query = "
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.deleted_at IS NULL ";

        $params = [];

        $params = [];
        $conditions = [];

        $stmt = $this->conn->prepare("SELECT count(*) as total {$query}");
        $stmt->execute();
        $total = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        if (!empty($search)) {
            $conditions[] = "(p.name LIKE :search OR p.sku LIKE :search)";
            $params[':search'] = '%' . trim($search) . '%';
        }

        if ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice)) {
            $conditions[] = "p.price >= :minPrice";
            $params[':minPrice'] = (float)$minPrice;
        }

        if ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice)) {
            $conditions[] = "p.price <= :maxPrice";
            $params[':maxPrice'] = (float)$maxPrice;
        }

        // if ($common !== null && $common !== '') {
        //     $conditions[] = "(p.name LIKE :common OR p.sku LIKE :common)";
        //     $params[':common'] = '%' . trim($common) . '%';
        // }

        $where = !empty($conditions) ? " AND " . implode(' AND ', $conditions) : "";

        $query = "{$query} $where";
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total {$query}");


        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }

        $stmt->execute();
        $filtered = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];


        $where = !empty($conditions) ? " AND " . implode(' AND ', $conditions) : "";
        $query = "{$query}$where";
        $stmt = $this->conn->prepare("SELECT p.*, c.name AS category_name {$query} ORDER BY p.id ASC LIMIT :limit OFFSET :offset");

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "recordsTotal" => $total,
            "recordsFiltered" => $filtered,
            "data" => $data
        ];
    }

    public function findById($id, $includeDeleted = false)
    {
        if (!is_numeric($id)) return null;

        $sql = "SELECT * FROM products WHERE id = :id";

        if (!$includeDeleted) {
            $sql .= " AND deleted_at IS NULL";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function delete($id)
    {

        $stmt = $this->conn->prepare("
        UPDATE products 
        SET deleted_at = NOW() 
        WHERE id = :id AND deleted_at IS NULL
    ");

        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);


        return $stmt->execute();
    }

    /**
     * Update an existing product.
     *
     * @param int $id Product ID
     * @param array $data Updated product data
     * @return bool True on success, false on failure
     */

    public function update($id, $data)
    {
        $data['updated_by'] = $_SESSION['user_id'];

        $stmt = $this->conn->prepare("
        UPDATE products SET
            name = :name,
            price = :price,
            quantity = :quantity,
            sku = :sku,
            description = :description,
            status = :status,
            updated_at = :updated_at,
            updated_by = :updated_by,
            category_id = :category_id
        WHERE id = :id
    ");

        return $stmt->execute([
            ':name' => $data['name'],
            ':price' => $data['price'],
            ':quantity' => $data['quantity'],
            ':sku' => $data['sku'],
            ':description' => $data['description'],
            ':status' => $data['status'],
            ':updated_at' => date('Y-m-d H:i:s'),
            ':updated_by' => $data['updated_by'],
            ':category_id' => $data['category_id'] ?? null,
            ':id' => $id
        ]);
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

    public function getCategory(): array
    {
        $query = "SELECT id, name FROM categories";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
