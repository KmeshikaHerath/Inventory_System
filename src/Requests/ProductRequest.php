<?php

namespace App\Requests;

use Exception;
use App\Core\Database;
use PDO;

class ProductRequest
{
    /**
     * Validate product data + image
     *
     * @param array $data
     * @param array|null $file
     * @return bool
     * @throws Exception
     */
    public static function validate(array $data, ?array $file = null): bool
    {
        try {

            // ======================
            // NAME
            // ======================
            if (empty($data['name'])) {
                throw new Exception("Product name is required");
            }

            if (strlen($data['name']) < 3) {
                throw new Exception("Product name must be at least 3 characters");
            }

            // ======================
            // PRICE
            // ======================
            if (!isset($data['price']) || !is_numeric($data['price'])) {
                throw new Exception("Price must be a valid number");
            }

            $price = (float)$data['price'];

            if ($price <= 0) {
                throw new Exception("Price must be greater than 0");
            }

            // ======================
            // QUANTITY
            // ======================
            if (!isset($data['quantity']) || !is_numeric($data['quantity'])) {
                throw new Exception("Quantity must be a valid number");
            }

            $quantity = (int)$data['quantity'];

            if ($quantity < 0) {
                throw new Exception("Quantity cannot be negative");
            }

            // ======================
            // SKU
            // ======================
            if (empty($data['sku'])) {
                throw new Exception("SKU is required");
            }

            if (strlen($data['sku']) < 3) {
                throw new Exception("SKU must be at least 3 characters");
            }

            // ✅ UNIQUE CHECK
            $conn = Database::getInstance()->getConnection();

            // If updating, exclude current record
            $id = $data['id'] ?? null;

            if ($id) {
                $stmt = $conn->prepare("SELECT id FROM products WHERE sku = :sku AND id != :id LIMIT 1");
                $stmt->execute([
                    ':sku' => $data['sku'],
                    ':id'  => $id
                ]);
            } else {
                $stmt = $conn->prepare("SELECT id FROM products WHERE sku = :sku LIMIT 1");
                $stmt->execute([
                    ':sku' => $data['sku']
                ]);
            }

            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("SKU already exists");
            }
            // ======================
            // DESCRIPTION (OPTIONAL)
            // ======================
            if (!empty($data['description']) && strlen($data['description']) > 500) {
                throw new Exception("Description cannot exceed 500 characters");
            }

            // ======================
            // STATUS
            // ======================
            if (!isset($data['status']) || !in_array($data['status'], ['active', 'inactive'])) {
                throw new Exception("Invalid status value");
            }

            // ======================
            // IMAGE VALIDATION
            // ======================
            if ($file && !empty($file['name'])) {

                $allowedMime = ['image/jpeg', 'image/png'];
                $allowedExt  = ['jpg', 'jpeg', 'png'];
                $maxSize     = 2 * 1024 * 1024; // 2MB

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Error uploading image");
                }

                if ($file['size'] > $maxSize) {
                    throw new Exception("Image must be less than 2MB");
                }

                // Real MIME type check
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mime, $allowedMime)) {
                    throw new Exception("Only JPG, JPEG, PNG images are allowed");
                }

                // Extension check
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowedExt)) {
                    throw new Exception("Invalid image file extension");
                }
            }

            return true;
        } catch (Exception $e) {
            // Rethrow for controller handling
            throw new Exception($e->getMessage());
        }
    }
}
