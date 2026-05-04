<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use App\Core\View;
use App\Services\ProductService;
use App\Services\PermissionService;
use Exception;

class ProductController
{
    private static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function index(): Response
    {

        session_start();

        return new Response(
            View::render('products/productview', [
                'permissions' => $_SESSION['permissions'] ?? []
            ], true)
        );
    }

    public static function paginate(): Response
    {
        header('Content-Type: application/json');
        ob_clean();

        try {
            $draw   = intval($_GET['draw'] ?? 1);
            $start  = intval($_GET['start'] ?? 0);
            $length = intval($_GET['length'] ?? 10);

            $page = ($start / $length) + 1;

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
            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 500);
        }
    }

    public static function store(): Response
    {
        self::startSession();
        header('Content-Type: application/json');

        try {
            if (!PermissionService::can('products_create')) {
                throw new Exception("Unauthorized", 403);
            }

            $data = $_POST;
            $data['created_by'] = $_SESSION['user_id'];

            (new ProductService())->create($data, $_FILES['image'] ?? null);

            return new Response(json_encode(["status" => "success"]), 200);

        } catch (Exception $e) {

            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 
            400, 
        ['Content-Type' => 'application/json']
    );
        }
    }

    public static function update(): Response
    {
        self::startSession();
        header('Content-Type: application/json');

        try {
            if (!PermissionService::can('products_update')) {
                throw new Exception("Unauthorized", 403);
            }

            $id = $_POST['id'] ?? null;
            if (!$id) throw new Exception("Missing ID");

            (new ProductService())->update($id, $_POST, $_FILES['image'] ?? null);

            return new Response(json_encode(["status" => "success"]), 200);
        } catch (Exception $e) {
            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 400);
        }
    }

    public static function delete(): Response
    {
        self::startSession();
        header('Content-Type: application/json');

        try {
            if (!PermissionService::can('products_delete')) {
                throw new Exception("Unauthorized", 403);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;

            (new ProductService())->delete($id, $_SESSION['user_id']);

            return new Response(json_encode(["status" => "success"]), 200);
        } catch (Exception $e) {
            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 400);
        }
    }

    public static function get(): Response
    {
        header('Content-Type: application/json');

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
                return new Response(json_encode([
                    "status" => "error",
                    "message" => "Product not found"
                ]), 404);
            }

            return new Response(json_encode([
                "status" => "success",
                "data" => $product
            ]), 200);
        } catch (Exception $e) {
            return new Response(json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]), 500);
        }
    }
}
