<?php

return [
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => $_ENV['APP_DEBUG'] ?? true,
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'jwt_secret' => $_ENV['JWT_SECRET'] ?? 'your-super-secret-jwt-key-change-this-in-production',
    'session' => [
        'secure' => $_ENV['SESSION_SECURE'] ?? false,
        'http_only' => $_ENV['SESSION_HTTP_ONLY'] ?? true,
    ]
];
