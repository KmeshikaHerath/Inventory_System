<?php
namespace App\Services;

use App\Models\User;
use Symfony\Component\HttpFoundation\Request;

class UserService {

    public static function registerUser($request): string
    {
        if ($request instanceof Request) {
            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $role_id = $request->request->get('role_id'); 
        } else {
            $name = $request['name'] ?? null;
            $email = $request['email'] ?? null;
            $password = $request['password'] ?? null;
            $role_id = $request['role_id'] ?? null; 
        }

    
        if (empty($role_id)) {
            throw new \Exception("Role is required");
        }

        (new User())->create($name, $email, $password, $role_id);

        return "User Registered Successfully!";
    }
}