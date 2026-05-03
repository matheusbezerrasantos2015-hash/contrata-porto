<?php

declare(strict_types=1);

final class Router
{
    private array $routes = [];

    public function add(string $method, string $path, $handler, string $role = 'ANY'): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'path'    => rtrim($path, '/') ?: '/',
            'handler' => $handler,
            'role'    => strtoupper($role),
        ];
    }

    public function get(string $path, $handler, string $role = 'ANY'): void { $this->add('GET', $path, $handler, $role); }
    public function post(string $path, $handler, string $role = 'ANY'): void { $this->add('POST', $path, $handler, $role); }
    public function put(string $path, $handler, string $role = 'ANY'): void { $this->add('PUT', $path, $handler, $role); }
    public function delete(string $path, $handler, string $role = 'ANY'): void { $this->add('DELETE', $path, $handler, $role); }

    public function dispatch(string $method, string $uri): void
    {
        $requestPath = rtrim(parse_url($uri, PHP_URL_PATH) ?: '/', '/') ?: '/';
        $requestMethod = strtoupper($method);

        foreach ($this->routes as $route) {
            $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $requestPath, $matches)) {
                if ($route['method'] === $requestMethod) {
                    
                    // Lógica de Role/Auth básica integrada se não for 'ANY'
                    $session = null;
                    if ($route['role'] !== 'ANY') {
                        require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
                        $session = AuthMiddleware::requireAuth();
                        
                        if ($route['role'] !== 'ANY' && strtoupper($session['role']) !== $route['role']) {
                            Response::json(false, 'Acesso negado: permissão insuficiente.', null, 403);
                            return;
                        }
                    }

                    $params = array_filter($matches, static fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);
                    
                    // Injeta a sessão no controller se o handler for [Controller, Method]
                    $handler = $route['handler'];
                    if (is_array($handler) && count($handler) === 2) {
                        $controller = $handler[0];
                        if (property_exists($controller, 'user')) {
                            $controller->user = $session;
                        }
                    }

                    call_user_func($handler, $params);
                    return;
                }
            }
        }

        Response::json(false, 'Rota não encontrada ou método inválido.', null, 404);
    }
}
