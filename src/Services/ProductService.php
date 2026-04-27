<?php

namespace App\Services;

use App\Models\Product;

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
}
