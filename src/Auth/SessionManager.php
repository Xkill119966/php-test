<?php

namespace VendingMachine\Auth;

class SessionManager
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        
        // Start a new session
        session_start();
        session_regenerate_id(true);
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public function getCurrentUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'role' => $_SESSION['role'] ?? null,
        ];
    }

    public function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public function getUsername(): ?string
    {
        return $_SESSION['username'] ?? null;
    }

    public function getUserRole(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    public function isAdmin(): bool
    {
        return $this->getUserRole() === 'admin';
    }

    public function isUser(): bool
    {
        return $this->getUserRole() === 'user';
    }

    public function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }

    public function requireAdmin(): void
    {
        $this->requireLogin();
        
        if (!$this->isAdmin()) {
            header('HTTP/1.1 403 Forbidden');
            header('Location: /unauthorized');
            exit;
        }
    }
}
