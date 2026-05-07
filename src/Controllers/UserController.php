<?php
namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use App\Services\UserService;
use App\Core\View;
use Exception;
use App\Models\User;
use App\Requests\RegisterRequest;
use App\Core\Logger;

class UserController {

    public static function index(): Response 
    {
        
            $userModel = new User();
            $roles = $userModel->getRoles();
            $html = View::render('register', ['roles' => $roles], false);

            return new Response($html);
    }

   // New method to handle user registration
   public static function register(): Response
    {
        try {
            //get the data from the request
        $requestData = $_POST;

        // Log the registration attempt with email if available
        Logger::info('Registration attempt started for email: ' . ($requestData['email'] ?? 'unknown'));     

        // Validate the request data using the RegisterRequest class
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
