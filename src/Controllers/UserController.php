<?php
namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Services\UserService;
use App\Core\View;
use Exception;
use Monolog\Handler\StreamHandler;
use App\Models\User;
use App\Core\Database;
use App\Requests\RegisterRequest;
use App\Core\Logger;

class UserController {

    public static function index(): Response 
{
    // Get roles via model (NOT direct DB)
    $roles = User::getRoles();

    // Pass to view
    $html = View::render('register', ['roles' => $roles]);

    return new Response($html);
}

   
   public static function register(): Response
    {
        try {
        $requestData = $_POST;
        Logger::info('Registration attempt started for email: ' . ($requestData['email'] ?? 'unknown'));     

        $registerRequest = new RegisterRequest();
        
        if (!$registerRequest->validate($requestData)) {
            $errors = $registerRequest->getErrors();
            $errorMessage = implode(', ', $errors);
            
            Logger::warning('Registration validation failed', [
                'errors' => $errors,
                'email' => $requestData['email'] ?? 'unknown'
            ]);
            
            return new Response(json_encode([
                'status' => 'error',
                'message' => $errorMessage,
                'errors' => $errors
            ]), 400);
        }
        

        $validatedData = $registerRequest->getValidatedData();
        
        $message = UserService::registerUser($validatedData); 
            
        Logger::info('User registration successful', ['email' => $validatedData['email'] ?? 'unknown']);
            
        return new Response($message);

        } catch (Exception $exception) { 
            Logger::error('User registration failed', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            return new Response(json_encode([
                'status' => 'error',
                'message' => $exception->getMessage()
            ]), 500);
        }
    }
}
