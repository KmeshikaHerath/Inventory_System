<?php

namespace App\Services;

use App\Models\Product;
use App\Requests\ProductRequest;
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
        ProductRequest::validate($data, $file);

        $id = $this->model->create($data);

        if ($file && $file['name']) {
            $path = $this->uploadImage($file, $id);
            $this->model->update($id, array_merge($data, ['image_path' => $path]));
        }
    }

    public function update($id, $data, $file = null)
    {
        ProductRequest::validate($data, $file);

        if ($file && $file['name']) {
            $data['image_path'] = $this->uploadImage($file, $id);
        }

        $this->model->update($id, $data);
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
        $dir = __DIR__ . "/../../public/uploads/products";

        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $ext = 'png';
        $name = $id . "." . $ext;

        $path = $dir . $name;

        move_uploaded_file($file['tmp_name'], $path);

        return "/uploads/products/{$name}";
    }
}
