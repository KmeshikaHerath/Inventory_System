<?php

namespace App\Services;

use App\Models\Product;
use Exception;

/**
 * Product business logic
 */
class ProductService
{
    private $product;

    public function __construct()
    {
        $this->product = new Product();
    }

    /**
     * Create product
     */
    public function create(array $data): bool
    {
        return $this->product->create($data);
    }

    /**
     * Update product
     */
    public function update($id, array $data): bool
    {
        return $this->product->update($id, $data);
    }

    /**
     * Get product
     */
    public function get($id)
    {
        return $this->product->findById($id);
    }

    function uploadImage($file, $uploadDir)
{
    // Allowed MIME types
    $allowedMimeTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png'
    ];

    // Max file size (2MB)
    $maxSize = 2 * 1024 * 1024;

    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload error");
    }

    // Validate file size
    if ($file['size'] > $maxSize) {
        throw new Exception("File size exceeds 2MB limit");
    }

    // Validate MIME type (IMPORTANT SECURITY STEP)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMimeTypes)) {
        throw new Exception("Invalid file type. Only JPG, JPEG, PNG allowed");
    }

    // Generate secure unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueName = bin2hex(random_bytes(16)) . '.' . strtolower($extension);

    $destination = $uploadDir . $uniqueName;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Failed to move uploaded file");
    }

    // Return relative path for DB
    return "public/images/" . basename(dirname($uploadDir)) . "/" . basename($uploadDir) . $uniqueName;
}
}
