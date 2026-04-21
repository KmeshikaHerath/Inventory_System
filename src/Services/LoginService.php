<?php

namespace App\Services;  

use App\Models\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class LoginService {

    public static function attemptLogin(string $email, string $password, $remember = false): array {
    
        if (empty($email) || empty($password)) {
            return [
                "status" => "error", 
                "message" => "Email and password are required", 
                "code" => 400
            ];
        }

        $userModel = new User();
        $foundUser = $userModel->findByEmail($email);

        if (!$foundUser) {
            return [
                "status" => "error", 
                "message" => "EMAIL_EXISTS", 
                "errors" => [
                    "email" => "EMAIL_EXISTS"
                ],
                "code" => 404
            ];
        }

        if (!password_verify($password, $foundUser['password'])) {
            return [
                "status" => "error", 
                "message" => "Incorrect password", 
                "code" => 401
            ];
        }

        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user'] = $foundUser['name'];
        $_SESSION['role'] = $foundUser['role_name'] ?? 'user';
        $_SESSION['user_id'] = $foundUser['id'] ?? null;
        $_SESSION['email'] = $foundUser['email'] ?? $email;

        if ($remember) {
            setcookie('remember_email', $email, time() + (30 * 24 * 60 * 60), '/');
        } else {
            if (isset($_COOKIE['remember_email'])) {
                setcookie('remember_email', '', time() - 3600, '/');
            }
        }

        return [
            "status" => "success",
            "message" => "Login successful",
            "role" => $foundUser['role_name'] ?? 'user',
            "code" => 200
        ];
    }
    
    // Optional: Add static execute method if you want to keep controller as is
    
        public static function execute(): JsonResponse {
        $request = Request::createFromGlobals();
        $content = $request->getContent();
        $data = json_decode($content, true);
        
        $email = $data['email'] ?? $request->request->get('email') ?? '';
        $password = $data['password'] ?? $request->request->get('password') ?? '';
        $remember = $data['remember'] ?? $request->request->get('remember') ?? false;
        
        $result = self::attemptLogin($email, $password, $remember);
        
        return new JsonResponse($result, $result['code'] ?? 200);
    }
}