<?php

namespace App\Service;

use App\Models\User;

class LoginService {

    public function attemptLogin(string $email, string $password, $remember = false): array {

        // Validate input
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
                "message" => "User not found", 
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

        // Start session safely
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user'] = $foundUser['name'];
        $_SESSION['role'] = $foundUser['role_name'] ?? 'user';
        $_SESSION['user_id'] = $foundUser['id'] ?? null;
        $_SESSION['email'] = $foundUser['email'] ?? $email;

        // Handle remember me cookie
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
}