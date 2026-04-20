<?php
namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Services\LoginService;
use App\Core\View;
use Exception;
use Monolog\Handler\StreamHandler;
use App\Models\User;
use App\Core\Database;

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