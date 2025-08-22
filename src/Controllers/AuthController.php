<?php

namespace VendingMachine\Controllers;

use VendingMachine\Models\User;
use VendingMachine\Auth\SessionManager;

class AuthController
{
    private User $userModel;
    private SessionManager $sessionManager;

    public function __construct()
    {
        $this->userModel = new User();
        $this->sessionManager = new SessionManager();
    }

    public function login(array $data): array
    {
        $errors = $this->validateLogin($data);
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        $user = $this->userModel->authenticate($data['username'], $data['password']);
        
        if ($user) {
            $this->sessionManager->login($user);
            return [
                'success' => true,
                'user' => $user,
                'message' => 'Login successful'
            ];
        }

        return [
            'success' => false,
            'errors' => ['credentials' => 'Invalid username or password']
        ];
    }

    public function register(array $data): array
    {
        $errors = $this->validateRegistration($data);
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        // Check if username or email already exists
        if ($this->userModel->findByUsername($data['username'])) {
            return [
                'success' => false,
                'errors' => ['username' => 'Username already exists']
            ];
        }

        if ($this->userModel->findByEmail($data['email'])) {
            return [
                'success' => false,
                'errors' => ['email' => 'Email already exists']
            ];
        }

        try {
            $userId = $this->userModel->create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'user'
            ]);

            $user = $this->userModel->findById($userId);
            $this->sessionManager->login($user);

            return [
                'success' => true,
                'user' => $user,
                'message' => 'Registration successful'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => 'Registration failed: ' . $e->getMessage()]
            ];
        }
    }

    public function logout(): array
    {
        $this->sessionManager->logout();
        return [
            'success' => true,
            'message' => 'Logout successful'
        ];
    }

    public function getCurrentUser(): ?array
    {
        return $this->sessionManager->getCurrentUser();
    }

    private function validateLogin(array $data): array
    {
        $errors = [];

        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        }

        return $errors;
    }

    private function validateRegistration(array $data): array
    {
        $errors = [];

        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters long';
        } elseif (strlen($data['username']) > 100) {
            $errors['username'] = 'Username must not exceed 100 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors['username'] = 'Username can only contain letters, numbers, and underscores';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters long';
        }

        if (empty($data['confirm_password'])) {
            $errors['confirm_password'] = 'Password confirmation is required';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        return $errors;
    }
}
