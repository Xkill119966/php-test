<?php

namespace VendingMachine\Router;

class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function get(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, callable $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function addRoute(string $method, string $path, callable $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public function addMiddleware(string $name, callable $middleware): void
    {
        $this->middleware[$name] = $middleware;
    }

    public function dispatch(string $method, string $uri): mixed
    {
        $path = parse_url($uri, PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                $params = $this->extractParams($route['path'], $path);
                
                // Apply middleware
                foreach ($route['middleware'] as $middlewareName) {
                    if (isset($this->middleware[$middlewareName])) {
                        $middlewareResult = call_user_func($this->middleware[$middlewareName]);
                        if ($middlewareResult === false) {
                            return false;
                        }
                    }
                }
                
                return call_user_func_array($route['handler'], $params);
            }
        }
        
        // Route not found
        http_response_code(404);
        return ['error' => 'Route not found'];
    }

    private function matchPath(string $routePath, string $requestPath): bool
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));
        
        if (count($routeParts) !== count($requestParts)) {
            return false;
        }
        
        foreach ($routeParts as $i => $routePart) {
            if (strpos($routePart, '{') === 0 && strpos($routePart, '}') === strlen($routePart) - 1) {
                // This is a parameter placeholder
                continue;
            }
            
            if ($routePart !== $requestParts[$i]) {
                return false;
            }
        }
        
        return true;
    }

    private function extractParams(string $routePath, string $requestPath): array
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));
        $params = [];
        
        foreach ($routeParts as $i => $routePart) {
            if (strpos($routePart, '{') === 0 && strpos($routePart, '}') === strlen($routePart) - 1) {
                $paramName = trim($routePart, '{}');
                $params[$paramName] = $requestParts[$i];
            }
        }
        
        return $params;
    }
}
