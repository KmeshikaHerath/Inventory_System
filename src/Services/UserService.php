<?php
namespace App\Services;

use App\Models\User;
use Symfony\Component\HttpFoundation\Request;

class UserService {

      public static function registerUser(array $data): string
    {
        $name = $data['name'];
        $email = $data['email'];
        $password = $data['password'];
        $role_id = $data['role_id']; 

        // Call the instance method on the User model
        (new User())->create($name, $email, $password, $role_id);

        return "User Registered Successfully!";
    }
}
