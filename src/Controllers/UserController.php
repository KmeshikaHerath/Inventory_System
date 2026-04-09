<?php
namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Services\UserService;
use App\Core\View;
use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Models\User;
use App\Core\Database;

class UserController {

    private static function getLogger(): Logger 
    {
        $logger = new Logger('user_logger');
        $logger->pushHandler(new StreamHandler(__DIR__ . '/../../app.log', Logger::DEBUG));
        return $logger;
    }

    
    public static function index(): Response {
        // Get database connection using singleton pattern
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $roles = [];
        $stmt = $conn->prepare("SELECT id, role_name FROM roles");
        $stmt->execute();
        
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $roles[] = [
                'id' => $row['id'],
                'role_name' => $row['role_name']
            ];
        }
        
        // Pass roles data to the view
        $html = View::render('register', ['roles' => $roles]);
        return new Response($html);
    }

   
    public static function register(): Response
    {

        $requestData = $_POST;
        $logger = self::getLogger();
    
        try {
            $message = UserService::registerUser($requestData);
            
            $logger->info('User registration successful', ['email' => $requestData['email'] ?? 'unknown']);
            
            return new Response($message);

        } catch (Exception $exception) { 
            $logger->error('User registration failed', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);
            
            return new Response("Error: " . $exception->getMessage(), 500);
        }

    }
}
    
