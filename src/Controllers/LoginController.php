<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Exception;
use App\Services\LoginService;
use App\Requests\LoginRequest;
use App\Models\User;
use App\Core\View;
use App\Core\Logger;

/**
 * Login Controller
 * Handles HTTP requests only (NO business logic here)
 */

class LoginController
{

    /**
     * Handle login request (web form)
     * @return Response
     */
    public static function showLoginForm(): Response
    {
        $userModel = new User();

        // Optional: remember email from cookie
        $rememberedEmail = $_COOKIE['remember_email'] ?? '';

        $html = View::render('login', ['email' => $rememberedEmail]);

        return new Response($html);
    }

    public static function login(): Response
    {
        try {
            $requestData = $_POST;

            // Log incoming request (safe data only)
            Logger::info('Login attempt started', ['email' => $requestData['email'] ?? 'unknown']);

            $validator = new LoginRequest();

            // Validation check
            if (!$validator->validate($requestData)) {
                $errors = $validator->errors();

                Logger::warning('Login validation failed', ['email' => $requestData['email'] ?? 'unknown', 'errors' => $errors]);

                return new Response(json_encode([
                    'status' => 'error',
                    'message' => implode(', ', $errors),
                    'errors' => $errors
                ]), 422);
            }

            $data = $validator->validated();

            // Attempt login
            $result = LoginService::attemptLogin(
                $data['email'],
                $data['password'],
                $data['remember'] ?? false
            );

            // Log result based on status
            if ($result['status'] === 'success') {
                Logger::info('Login successful', [
                    'email' => $data['email']
                ]);
            } else {
                Logger::warning('Login failed', [
                    'email' => $data['email'],
                    'message' => $result['message'] ?? 'Unknown error'
                ]);
            }

            return new Response(json_encode([
                'status' => 'success',
                'redirect' => '/dashboard'
            ]), 200);

        } catch (\Exception $e) {

            // Log full exception details
            Logger::error('Login exception occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return new Response(json_encode([
                'status' => 'error',
                'message' => 'Internal Server Error'
            ]), 500);
        }
    }
}
