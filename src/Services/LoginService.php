<?php
namespace App\Services;

use App\Models\User;
use App\Core\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Exception;
use App\Models\Permission;

class LoginService
{
    /**
     * Attempt login
     */
    public static function attemptLogin(string $email, string $password, bool $remember = false): array
    {
        try {

            Logger::info('Login attempt started', ['email' => $email]);

            // Find user
            $user = (new User())->findByEmail($email);

            if (!$user) {
                Logger::warning('Login failed - user not found', ['email' => $email]);

                throw new Exception("User not found", 404);
            }

            // Password check
            if (!password_verify($password, $user['password'])) {
                Logger::warning('Login failed - incorrect password', ['email' => $email]);

                throw new Exception("Incorrect password", 401);
            }

            // Start session
            self::startSession();

            $_SESSION['user'] = [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role_id']
            ];

             $permissionModel = new Permission();
             $permissions = $permissionModel->getByRole($user['role_id']);
             $_SESSION['permissions'] = $permissions;

            // Remember me
            self::handleRememberMe($email, $remember);

            Logger::info('Login successful', ['user_id' => $user['id']]);

            return [
                "status"  => "success",
                "message" => "Login successful",
                "user"    => $user,
                "role"    => $user['role_id'],
                "code"    => 200
            ];

        } catch (Exception $e) {

            Logger::error('Login error', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return [
                "status"  => "error",
                "message" => $e->getMessage(),
                "code"    => $e->getCode() ?: 500
            ];
        }
    }

    /**
     * API execution
     */
    public static function execute(): JsonResponse
    {
        try {
            $data = self::getRequestData();

            $result = self::attemptLogin(
                $data['email'],
                $data['password'],
                $data['remember']
            );

            return new JsonResponse($result, $result['code']);

        } catch (Exception $e) {

            Logger::error('Login execute failure', [
                'error' => $e->getMessage()
            ]);

            return new JsonResponse([
                "status"  => "error",
                "message" => "Server error"
            ], 500);
        }
    }

    /**
     * Get request data
     */
    private static function getRequestData(): array
    {
        $request = Request::createFromGlobals();
        $data = json_decode($request->getContent(), true);

        return [
            'email'    => $data['email'] ?? $request->request->get('email', ''),
            'password' => $data['password'] ?? $request->request->get('password', ''),
            'remember' => $data['remember'] ?? false
        ];
    }

    /**
     * Start session safely
     */
    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Remember me cookie
     */
    private static function handleRememberMe(string $email, bool $remember): void
    {
        if ($remember) {
            setcookie('remember_email', $email, time() + (30 * 24 * 60 * 60), '/');
        } else {
            setcookie('remember_email', '', time() - 3600, '/');
        }
    }
}