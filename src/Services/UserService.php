<?php

namespace App\Services;

use App\Models\User;

class UserService
{

    // New method to handle user registration logic
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
