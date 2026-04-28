<?php

namespace App\Requests;

use Exception;

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

            // NAME
            if (empty($data['name'])) {
                throw new Exception("Product name is required");
            }

            if (strlen($data['name']) < 3) {
                throw new Exception("Product name must be at least 3 characters");
            }

            // PRICE
            if (!isset($data['price']) || $data['price'] <= 0) {
                throw new Exception("Price must be greater than 0");
            }

            // QUANTITY
            if (!isset($data['quantity']) || $data['quantity'] < 0) {
                throw new Exception("Quantity cannot be negative");
            }

            // SKU
            if (empty($data['sku'])) {
                throw new Exception("SKU is required");
            }

            if (strlen($data['sku']) < 3) {
                throw new Exception("SKU must be at least 3 characters");
            }

            // OPTIONAL DESCRIPTION
            if (!empty($data['description']) && strlen($data['description']) > 500) {
                throw new Exception("Description cannot exceed 500 characters");
            }

            //Image Validation
             if ($file && !empty($file['name'])) {

                // Allowed MIME types
                $allowedMime = ['image/jpeg', 'image/png'];

                // Allowed extensions
                $allowedExt = ['jpg', 'jpeg', 'png'];

                // Max size (2MB)
                $maxSize = 2 * 1024 * 1024;

                // Check upload error
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Error uploading image");
                }

                // Check size
                if ($file['size'] > $maxSize) {
                    throw new Exception("Image must be less than 2MB");
                }

                // Check MIME type (REAL validation)
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);

                if (!in_array($mime, $allowedMime)) {
                    throw new Exception("Only JPG, JPEG, PNG images are allowed");
                }

                // Check extension
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowedExt)) {
                    throw new Exception("Invalid image file extension");
                }
            }

            return true;
        } catch (Exception $e) {
            // rethrow so controller can handle it
            throw new Exception($e->getMessage());
        }
    }
}
