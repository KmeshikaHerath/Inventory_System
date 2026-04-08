<?php
namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Services\UserService;
use App\Core\View; 

class UserController {

    public static function index(): Response
    {
            $html = View::render('register', []);

            return new Response($html);
    }


   // UserController.php line 19
public static function register($request = null)
{

    $request = $request ?? $_POST;
    
        try {
            $message = UserService::registerUser($request);
            return new Response($message);

        } catch (\Exception $e) {
            return new Response("Error: " . $e->getMessage(), 500);
        }
    }
    
}