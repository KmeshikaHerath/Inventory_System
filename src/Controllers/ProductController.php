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
     * Show products (only active by default)
     */
    public static function index(): Response
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Debug logging
        error_log('ProductController::index - Session ID: ' . session_id());
        error_log('ProductController::index - Permissions: ' . print_r($_SESSION['permissions'] ?? 'NOT SET', true));

        $productModel = new Product();
        // Only get active products (not deleted) for the main view
        $products = $productModel->getActiveProducts(10, 0, '', null, null, 'id', 'DESC');

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

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            // Check permission
            if (!PermissionService::can('products_create')) {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "Forbidden: You don't have permission to create products"
                ]), 403);
            }

            $data = $_POST;

            // Add created_by from session
            $data['created_by'] = $_SESSION['user_id'] ?? null;

            if (!$data['created_by']) {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "User not authenticated"
                ]), 401);
            }

            // Validate request
            ProductRequest::validate($data);

            // Handle file upload if present
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = self::uploadImage($_FILES['image']);
                if ($imagePath) {
                    $data['image_path'] = $imagePath;
                }
            }

            $service = new ProductService();
            $service->create($data);

            return new Response(json_encode([
                "status" => "success",
                "message" => "Product created successfully"
            ]), 200);
        } catch (Exception $e) {
            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 400);
        }
    }

    /**
     * delete delete product
     */
    public static function delete(): Response
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            // Check permission
            if (!PermissionService::can('products_delete')) {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "Forbidden: You don't have permission to delete products"
                ]), 403);
            }

            // Get JSON input for better handling
            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? $_POST['id'] ?? null;

            if (!$id) {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "Missing product ID"
                ]), 400);
            }

            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "User not authenticated"
                ]), 401);
            }

            $product = new Product();

            // Check if product exists and is not already deleted
            $existingProduct = $product->findById($id, false);

            if (!$existingProduct) {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "Product not found or already deleted"
                ]), 404);
            }

            // Perform delete delete
            $result = $product->delete($id, $userId);

            if ($result) {
                return new Response(json_encode([
                    "status" => "success",
                    "message" => "Product moved to trash"
                ]), 200);
            } else {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "Failed to delete product"
                ]), 500);
            }
        } catch (Exception $e) {
            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 400);
        }
    }

    /**
     * Restore delete deleted product
     */
    public static function restore(): Response
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            // Check permission
            if (!PermissionService::can('products_delete')) {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "Forbidden"
                ]), 403);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;

            if (!$id) {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "Missing product ID"
                ]), 400);
            }

            $product = new Product();
            $result = $product->restore($id);

            if ($result) {
                return new Response(json_encode([
                    "status" => "success",
                    "message" => "Product restored successfully"
                ]), 200);
            } else {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "Failed to restore product"
                ]), 500);
            }
        } catch (Exception $e) {
            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 400);
        }
    }

    /**
     * Paginate products (with delete filter)
     */
    public static function paginate(): Response
    {
        header('Content-Type: application/json');

        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $page = $_GET['page'] ?? 1;
            $search = $_GET['search_custom'] ?? '';
            $minPrice = $_GET['min_Price'] ?? null;
            $maxPrice = $_GET['max_Price'] ?? null;
            $status = $_GET['status'] ?? 'all';
            $includeDeleted = ($_GET['deleted'] ?? 0) == 1;
            
            $product = new Product();
            $result = $product->paginate($page, $search, $minPrice, $maxPrice, $status, $includeDeleted);

            return new Response(json_encode($result), 200);
        } catch (Exception $e) {
            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 500);
        }
    }

    /**
     * Get single product by ID
     */
    public static function get(): Response
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = $_GET['id'] ?? null;

        if (!$id) {
            return new Response(json_encode([
                "status" => "error",
                "message" => "Missing product ID"
            ]), 400);
        }

        try {
            $includeDeleted = filter_var($_GET['include_deleted'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $product = new Product();
            $data = $product->findById($id, $includeDeleted);

            if (!$data) {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "Product not found"
                ]), 404);
            }

            return new Response(json_encode([
                "status" => "success",
                "data" => $data
            ]), 200);
        } catch (Exception $e) {
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
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            // Check permission
            if (!PermissionService::can('products_update')) {
                return new Response(json_encode([
                    'status' => 'error',
                    'message' => 'Unauthorized: You don\'t have permission to update products'
                ]), 403);
            }

            $id = $_POST['id'] ?? null;

            if (!$id) {
                return new Response(json_encode([
                    'status' => 'error',
                    'message' => 'Missing product ID'
                ]), 400);
            }

            $data = [
                'name' => $_POST['name'],
                'price' => $_POST['price'],
                'quantity' => $_POST['quantity'],
                'sku' => $_POST['sku'],
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'] ?? 'active'
            ];

            // Validate request
            ProductRequest::validate($data);

            // Handle file upload if present
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = self::uploadImage($_FILES['image']);
                if ($imagePath) {
                    $data['image_path'] = $imagePath;
                }
            }

            // Add updated_by
            $data['updated_by'] = $_SESSION['user_id'] ?? null;
            $data['updated_at'] = date('Y-m-d H:i:s');

            $service = new ProductService();
            $service->update($id, $data);

            return new Response(json_encode([
                'status' => 'success',
                'message' => 'Product updated successfully'
            ]), 200);
        } catch (Exception $e) {
            return new Response(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]), 400);
        }
    }

    /**
     * Upload image helper
     */
    private static function uploadImage($file): ?string
    {
        try {
            $targetDir = __DIR__ . '/../../public/uploads/products/';

            // Create directory if not exists
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $targetFile = $targetDir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                return '/uploads/products/' . $filename;
            }

            return null;
        } catch (Exception $e) {
            error_log("Image upload failed: " . $e->getMessage());
            return null;
        }
    }
}
