<?php

namespace App\Services;

use App\Models\Product;
use Exception;

/**
 * Class ProductService
 *
 * Handles business logic related to products.
 * Acts as an intermediate layer between Controller and Model.
 */
class ProductService
{
    /**
     * @var Product
     */
    private $model;

    /**
     * ProductService constructor.
     * Initializes Product model.
     */
    public function __construct()
    {
        $this->model = new Product();
    }

    /**
     * Create a new product
     *
     * @param array $data Product data
     * @param array|null $file Uploaded image file
     * @return void
     */
    public function create($data, $file = null)
    {
        $id = $this->model->create($data);

        if ($file && $file['name']) {
            $path = $this->uploadImage($file, $id);
        }
    }

    /**
     * Update an existing product
     *
     * Handles:
     * - Image validation
     * - Image replacement
     * - Data update
     *
     * @param int|string $id Product ID
     * @return array Response status and message
     * @throws Exception If update fails
     */
    public function update($id)
    {
        $data = $_POST;
        $file = $_FILES['image'] ?? null;

        $imagePath = __DIR__ . "/../../public/Images/uploads/products/products{$id}.png";

        // Check if old image exists
        $hasOldImage = file_exists($imagePath);

        //  No new image uploaded
        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {

            if (!$hasOldImage) {
                return [
                    "status" => "error",
                    "message" => "Image is required"
                ];
            }
        }

        // New image uploaded
        else {
            // delete old image if exists
            if ($hasOldImage) {
                unlink($imagePath);
            }

            // upload new image
            $this->uploadImage($file, $id);
        }

        $result = $this->model->update($id, $data);

        if (!$result) {
            throw new Exception("Update failed");
        }

        return [
            "status" => "success",
            "message" => "Product updated successfully"
        ];
    }

    /**
     * Soft delete a product
     *
     * @param int|string $id Product ID
     * @param int|string $userId User performing delete
     * @return bool
     */
    public function delete($id, $userId)
    {
        return $this->model->delete($id, $userId);
    }

      /**
     * Get product by ID
     *
     * @param int|string $id Product ID
     * @return array|null Product data or null if not found
     */
    public function get($id)
    {
        return $this->model->findById($id);
    }

    /**
     * Paginate products list
     *
     * @param int $page Current page
     * @param string|null $search Search keyword
     * @param float|null $min Minimum price
     * @param float|null $max Maximum price
     * @param string|null $status Product status
     * @param bool $deleted Include deleted records
     * @param int $limit Items per page
     * @return array Paginated result set
     */
    public function paginate($page, $search, $min, $max, $status, $deleted, $limit)
    {
        return $this->model->paginate($page, $search, $min, $max, $status, $deleted, $limit);
    }

    /**
     * Upload product image
     *
     * Stores image in:
     * /public/Images/uploads/products/
     *
     * @param array $file Uploaded file
     * @param int|string $id Product ID
     * @return string Image path
     */
    private function uploadImage($file, $id)
    {

        $dir = __DIR__ . "/../../public/Images/uploads/products";

        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $name = "products{$id}.png";

        move_uploaded_file($file['tmp_name'], "{$dir}/{$name}");

        return "/Images/uploads/products/{$name}";
    }
}
