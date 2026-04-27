<?php

namespace App\Requests;

use Exception;

class ProductRequest
{
    /**
     * Validate product data
     * @throws Exception
     */
    public static function validate(array $data): bool
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

            return true;
        } catch (Exception $e) {
            // rethrow so controller can handle it
            throw new Exception($e->getMessage());
        }
    }
}
