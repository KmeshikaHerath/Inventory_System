<?php

namespace App\Requests;

/**
 * Class LoginRequest
 *
 * Handles validation for login requests.
 */
class LoginRequest
{
    /**
     * @var array Validation errors
     */
    private array $errors = [];

    /**
     * @var array Validated data
     */
    private array $validatedData = [];

    /**
     * Validate login input
     *
     * @param array $data
     * @return bool
     */
    public function validate(array $data): bool
    {
        // Email validation
        if (empty($data['email'])) {
            $this->errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Invalid email format';
        } else {
            $this->validatedData['email'] = trim($data['email']);
        }

        // Password validation
        if (empty($data['password'])) {
            $this->errors['password'] = 'Password is required';
        } else {
            $this->validatedData['password'] = $data['password'];
        }

        // Remember checkbox
        $this->validatedData['remember'] = isset($data['remember']);

        return empty($this->errors);
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get validated data
     *
     * @return array
     */
    public function validated(): array
    {
        return $this->validatedData;
    }
}