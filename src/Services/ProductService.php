<?php

namespace App\Services;

use App\Models\Product;
use Exception;

class ProductService
{
    private $model;

    public function __construct()
    {
        $this->model = new Product();
    }


    public function create($data, $file = null)
    {
        $id = $this->model->create($data);

        if ($file && $file['name']) {
            $path = $this->uploadImage($file, $id);
        }
    }


    public function update($id)
    {
        $data = $_POST;
        $file = $_FILES['image'] ?? null;

        $imagePath = __DIR__ . "/../../public/Images/uploads/products/product{$id}.png";

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

        $this->model->update($id, $data);

        return [
            "status" => "success",
            "message" => "Product updated successfully"
        ];
    }


    public function delete($id, $userId)
    {
        return $this->model->delete($id, $userId);
    }


    public function get($id)
    {
        return $this->model->findById($id);
    }


    public function paginate($page, $search, $min, $max, $status, $deleted, $limit)
    {
        return $this->model->paginate($page, $search, $min, $max, $status, $deleted, $limit);
    }


    private function uploadImage($file, $id)
    {

        $dir = __DIR__ . "/../../public/Images/uploads/products";

        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $name = "products" . $id . ".png";

        $path = $dir . '/' . $name;

        move_uploaded_file($file['tmp_name'],"{$dir}{$name}");

        return "/Images/uploads/products/{$name}";
    }
}
