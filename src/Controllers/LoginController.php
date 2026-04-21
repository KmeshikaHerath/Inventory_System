<?php
namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Services\LoginService;  
use App\Core\View;

class LoginController {

    public static function login(): JsonResponse
    {
        return LoginService::execute();
    }

    public static function showLoginForm(): Response
    {
        $html = View::render('login');
        return new Response($html);
    }
}