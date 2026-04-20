<?php
namespace App\Requests;

use App\Models\User;

class RegisterRequest
{
    private array $errors = [];
    private array $validatedData = [];

    public function validate(array $data): bool
    {
        $this->errors = [];
        $this->validatedData = [];

        // Validate name
        if (empty($data['name'])) {
            $this->errors['name'] = 'Name is required';
        } elseif (strlen($data['name']) < 2) {
            $this->errors['name'] = 'Name must be at least 2 characters';
        } elseif (strlen($data['name']) > 100) {
            $this->errors['name'] = 'Name must not exceed 100 characters';
        } elseif (!preg_match('/^[a-zA-Z\s]+$/', $data['name'])) {
            $this->errors['name'] = 'Name can only contain letters and spaces';
        } else {
            $this->validatedData['name'] = htmlspecialchars(trim($data['name']));
        }

        // Validate email
        if (empty($data['email'])) {
             $this->errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Invalid email format';
        } else {
                $this->validatedData['email'] = htmlspecialchars(trim($data['email']));
                $existingUser = User::findByEmail($data['email']);

         if ($existingUser) {
            $this->errors['email'] = 'Email already registered';    
}

        // Validate password
        if (empty($data['password'])) {
            $this->errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $this->errors['password'] = 'Password must be at least 6 characters';
        } elseif (strlen($data['password']) > 10) {
            $this->errors['password'] = 'Password must not exceed 10 characters';
        }  elseif (!preg_match('/[A-Za-z]/', $data['password'])) {
            $this->errors['password'] = 'Password must contain at least one letter';
        } elseif (!preg_match('/[0-9]/', $data['password'])) {
            $this->errors['password'] = 'Password must contain at least one number';
        } elseif (!preg_match('/[@$!%*?&#]/', $data['password'])) {
            $this->errors['password'] = 'Password must contain at least one special character (@$!%*?&#)';
        } else {
            $this->validatedData['password'] = $data['password']; 
        }

    
        if (empty($data['email'])) {
             $this->errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
             $this->errors['email'] = 'Invalid email format';
        } else {
            $email = htmlspecialchars(trim($data['email']));
            $this->validatedData['email'] = $email;

            $existingUser = User::findByEmail($email);

        if ($existingUser) {
            $this->errors['email'] = 'EMAIL_EXISTS';
        }
    }

        // Validate confirm password
        if (empty($data['confirm_password'])) {
            $this->errors['confirm_password'] = 'Please confirm your password';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $this->errors['confirm_password'] = 'Passwords do not match';
        } else {
            $this->validatedData['confirm_password'] = $data['confirm_password'];
        }

        // Validate role_id
        if (empty($data['role_id'])) {
            $this->errors['role_id'] = 'Please select a role';
        } elseif (!is_numeric($data['role_id']) || $data['role_id'] <= 0) {
            $this->errors['role_id'] = 'Invalid role selected';
        } else {
            $this->validatedData['role_id'] = (int)$data['role_id'];
        }

        return empty($this->errors);
    }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getValidatedData(): array
    {
        return $this->validatedData;
    }

    public function getFirstError(): ?string
    {
        return empty($this->errors) ? null : reset($this->errors);
    }

    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }
}
