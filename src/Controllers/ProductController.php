<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use App\Core\View;
use App\Services\ProductService;
use App\Services\PermissionService;
use App\Models\Product;
use App\Requests\ProductRequest;
use Exception;

/**
 * Product Controller
 */
class ProductController
{
    /**
     * Show products
     */
    // In App\Controllers\ProductController
    public static function index(): Response
    {

        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Debug: Check if permissions exist
        error_log('ProductController::index - Session ID: ' . session_id());
        error_log('ProductController::index - Permissions: ' . print_r($_SESSION['permissions'] ?? 'NOT SET', true));
        error_log('ProductController::index - Full Session: ' . print_r($_SESSION, true));

        $productModel = new Product();
        $products = $productModel->getAll(10, 0, '', null, null, 'id', 'DESC');

        $permissions = $_SESSION['permissions'] ?? [];

        return new Response(
            View::render('products/productview', compact('products', 'permissions'), true)
        );
    }

    /**
     * Store product (with permission check)
     */
    public static function store(): Response
    {
        header('Content-Type: application/json');
        session_start();

        try {
            // 🔥 TEMP: disable permission (or fix later)
            // if (!PermissionService::can('products_create')) {
            //     return new Response(json_encode(["status"=>"error","message"=>"Forbidden"]), 403);
            // }

            $data = $_POST;

            ProductRequest::validate($data);

            $service = new ProductService();
            $service->create($data);

            return new Response(json_encode([
                "status" => "success"
            ]), 200);
        } catch (Exception $e) {
            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 400);
        }
    }

    /**
     * Delete product
     */
    public static function delete(): Response
    {
        header('Content-Type: application/json');
        session_start();

        try {

            if (!PermissionService::can('products_delete')) {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "Forbidden"
                ]), 403);
            }

            $id = $_POST['id'] ?? null;

            if (!$id) {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "Missing ID"
                ]), 400);
            }

            $product = new Product();
            $product->delete($id); // you must implement this

            return new Response(json_encode([
                "status" => "success"
            ]));
        } catch (Exception $e) {
            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 400);
        }
    }
    public static function paginate(): Response
    {
        header('Content-Type: application/json');

        try {
            $page = $_GET['page'] ?? 1;
            $search = $_GET['search'] ?? '';
            $minPrice = $_GET['minPrice'] ?? null;
            $maxPrice = $_GET['maxPrice'] ?? null;

            $product = new Product();
            $result = $product->paginate($page, $search, $minPrice, $maxPrice);

            return new Response(json_encode($result), 200);
        } catch (Exception $e) {
            return new Response(json_encode([
                "error" => $e->getMessage()
            ]), 500);
        }
    }

    public static function get(): Response
    {
        header('Content-Type: application/json');

        $id = $_GET['id'] ?? null;

        if (!$id) {
            return new Response(json_encode([
                "status" => "error",
                "message" => "Missing ID"
            ]), 400);
        }

        try {
            $product = new Product();
            $data = $product->findById($id); // correct method

            if (!$data) {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "Product not found"
                ]), 404);
            }

            return new Response(json_encode($data), 200);
        } catch (\Exception $e) {
            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 500);
        }
    }

    /**
     * Update product
     */
    public static function update(): Response
    {
        session_start();

        if (!PermissionService::can('products_update')) {
            return new Response(json_encode([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]), 403);
        }

        try {
            $id = $_POST['id'];

            $data = [
                'name' => $_POST['name'],
                'price' => $_POST['price'],
                'quantity' => $_POST['quantity'],
                'sku' => $_POST['sku'],
                'description' => $_POST['description']
            ];

            ProductRequest::validate($data);

            $service = new ProductService();
            $service->update($id, $data);

            return new Response(json_encode([
                'status' => 'success'
            ]), 200);
        } catch (\Exception $e) {
            return new Response(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]), 400);
        }
    }
}
