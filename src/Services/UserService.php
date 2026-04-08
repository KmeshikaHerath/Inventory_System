<?php
namespace App\Services;

use App\Models\User;
use Symfony\Component\HttpFoundation\Request;

class UserService {
    

public static function registerUser($request) {  // Remove type hint or make it flexible
    
    // Handle both Request object and array
    if ($request instanceof Request) {
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $password = $request->request->get('password');
    } else {
        // It's an array
        $name = $request['name'] ?? null;
        $email = $request['email'] ?? null;
        $password = $request['password'] ?? null;
    }
    
    $user = new User();
    $user->create($name, $email, $password);
    
    return "User Registered Successfully!";
}
}