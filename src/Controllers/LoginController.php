<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Exception;
use App\Services\LoginService;
use App\Requests\LoginRequest;
use App\Models\User;
use App\Core\View;
use App\Core\Logger;
use App\Models\Permission;

class LoginController
{
    public static function showLoginForm(): Response
    {
        $rememberedEmail = $_COOKIE['remember_email'] ?? '';
        $html = View::render('login', ['email' => $rememberedEmail], false);
        return new Response($html);
    }

    public static function login(): Response
    {
        try {
            $requestData = $_POST;
            
            Logger::info('Login attempt started', ['email' => $requestData['email'] ?? 'unknown']);

            $validator = new LoginRequest();

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

            // Attempt login - make sure this returns user data
            $result = LoginService::attemptLogin(
                $data['email'],
                $data['password'],
                $data['remember'] ?? false
            );

            if ($result['status'] === 'success') {
                // Start session if not already started
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                // Get user data from login result
                $user = $result['user']; // Make sure LoginService returns user array
                
                // Set session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role_id'] = $user['role_id'];
                
                // Load permissions
                $permissionModel = new Permission();
                $permissions = $permissionModel->getByRole($user['role_id']);             
                $_SESSION['permissions'] = $permissions;
               
                // Debug - log what was stored
                Logger::info('Session data stored', [
                    'user_id' => $_SESSION['user_id'],
                    'role_id' => $_SESSION['role_id'],
                    'permissions' => $permissions
                ]);
                
                return new Response(json_encode([
                    'status' => 'success',
                    'redirect' => '/dashboard'
                ]), 200);
            } else {
                Logger::warning('Login failed', [
                    'email' => $data['email'],
                    'message' => $result['message'] ?? 'Unknown error'
                ]);
                
                return new Response(json_encode([
                    'status' => 'error',
                    'message' => $result['message'] ?? 'Login failed'
                ]), 401);
            }
        } catch (\Exception $e) {
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

    public static function logout(): Response
    {
        try {
            $session = new Session();
            $session->start();

            $user = $session->get('user');

            if ($user) {
                Logger::info("User logout successful", [
                    'user_id' => $user['id'] ?? null,
                    'email' => $user['email'] ?? null
                ]);
            } else {
                Logger::warning("Logout attempted without active session");
            }

            $session->invalidate();
            return new RedirectResponse('/login');
        } catch (Exception $e) {
            Logger::error("Logout failed", [
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return new Response(
                "Something went wrong during logout",
                500
            );
        }
    }
}