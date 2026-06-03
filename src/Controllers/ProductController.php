<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use App\Core\View;
use App\Services\ProductService;
use App\Services\PermissionService;
use App\Requests\ProductRequest;
use Exception;
use App\Core\Logger;
use App\Models\Product;

/**
 * Class ProductController
 *
 * Handles all product-related HTTP requests such as
 * listing, pagination, create, update, delete, and fetch.
 */
class ProductController
{
    /**
     * Show product listing page
     *
     * @return Response
     */

    public static function index(): Response
    {
        Logger::info("Product index page accessed");

        $ProductModel = new Product();
        $categories = $ProductModel->getCategory();

        return new Response(
            View::render('products/productview', [
                'permissions' => $_SESSION['permissions'] ?? [],
                'categories'  => $categories
            ], true)
        );
    }

    /**
     * Paginate product list (DataTables API)
     *
     * @return Response
     */
    public static function paginate(): Response
    {
        try {
            $draw   = intval($_GET['draw'] ?? 1);
            $start  = intval($_GET['start'] ?? 0);
            $length = intval($_GET['length'] ?? 10);

            $page = ($start / $length) + 1;

            Logger::info("Product pagination requested", $_GET);

            $service = new ProductService();

            $result = $service->paginate(
                $page,
                $_GET['search_custom'] ?? '',
                $_GET['min_price'] ?? null,
                $_GET['max_price'] ?? null,
                $_GET['status'] ?? null,
                isset($_GET['deleted']) && $_GET['deleted'] == 1,
                $length
            );

            return new Response(json_encode([
                "draw" => $draw,
                "recordsTotal" => $result['recordsTotal'],
                "recordsFiltered" => $result['recordsFiltered'],
                "data" => $result['data']
            ]), 200);
        } catch (Exception $e) {

            Logger::error("Pagination failed", ['error' => $e->getMessage()]);

            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 500);
        }
    }

    /**
     * Create new product
     *
     * @return Response
     */
    public static function store(): Response
    {

        ProductRequest::validate($_POST, $_FILES);

        try {
            if (!PermissionService::can('products_create')) {
                throw new Exception("Unauthorized", 403);
            }

            $data = $_POST;
            $data['created_by'] = $_SESSION['user_id'];

            (new ProductService())->create($data, $_FILES['image'] ?? null);

            Logger::info("Product created", $data);

            return new Response(json_encode(["status" => "success"]), 200);
        } catch (Exception $e) {

            Logger::error("Product creation failed", [
                'error' => $e->getMessage(),
                'data' => $_POST
            ]);

            return new Response(
                json_encode([
                    "status" => "error",
                    "message" => $e->getMessage()
                ]),
                400,
            );
        }
    }

    /**
     * Update product
     *
     * @return Response
     */
    public static function update(): Response
    {

        try {
            if (!PermissionService::can('products_update')) {
                throw new Exception("Unauthorized", 403);
            }

            $id = $_POST['id'] ?? null;

            if (!$id) throw new Exception("Missing ID");

            ProductRequest::validate($_POST, $_FILES);

            (new ProductService())->update($id);

            Logger::warning("Product updated", ['id' => $id]);

            return new Response(json_encode(["status" => "success"]), 200);
        } catch (Exception $e) {

            Logger::error("Product update failed", [
                'error' => $e->getMessage(),
                'id' => $_POST['id'] ?? null
            ]);
            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 400);
        }
    }

    /**
     * Delete product
     *
     * @return Response
     */
    public static function delete(): Response
    {

        try {
            if (!PermissionService::can('products_delete')) {
                throw new Exception("Unauthorized", 403);
            }

            $id = $_POST['id'] ?? null;
            if (!$id) throw new Exception("Missing ID");

            (new ProductService())->delete($id, $_SESSION['user_id']);

            Logger::warning("Product deleted", [
                'id' => $id,
                'deleted_by' => $_SESSION['user_id']
            ]);

            return new Response(json_encode(["status" => "success"]), 200);
        } catch (Exception $e) {

            Logger::error("Product update failed", [
                'error' => $e->getMessage(),
                'id' => $_POST['id'] ?? null
            ]);

            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 400);
        }
    }

    /**
     * Get single product by ID
     *
     * @return Response
     */
    public static function get(): Response
    {

        try {
            $id = $_GET['id'] ?? null;

            if (!$id) {
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "ID missing"
                ]), 400);
            }

            $service = new ProductService();
            $product = $service->get($id);

            if (!$product) {

                Logger::warning("Product not found", ['id' => $id]);

                return new Response(json_encode([
                    "status" => "error",
                    "message" => "Product not found"
                ]), 404);
            }

            Logger::info("Product fetched", ['id' => $id]);

            return new Response(json_encode([
                "status" => "success",
                "data" => $product
            ]), 200);
        } catch (Exception $e) {

            Logger::info("Product fetched", ['id' => $id]);

            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 500);
        }
    }

    /**
     * Upload CSV and import products
     *
     * @return Response
     */
    public static function csvUpload(): Response
    {
        try {

            if (!PermissionService::can('products_create')) {
                throw new Exception('Unauthorized', 403);
            }

            $service = new ProductService();

            $result = $service->csvUpload($_FILES['csv_file'] ?? []);

            Logger::info('CSV products imported', [
                'user_id' => $_SESSION['user_id']
            ]);

            return new Response(json_encode([
                "status" => "success",
                "data" => $result
            ]), 200);
        } catch (Exception $e) {

            Logger::error('CSV import failed', [
                'error' => $e->getMessage()
            ]);

            return new Response(
                json_encode([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]),
                400
            );
        }
    }
}
