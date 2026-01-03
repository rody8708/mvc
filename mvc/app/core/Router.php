<?php

namespace App\Core;

use App\Core\Logger;

class Router
{
    private array $routes = [];

    public function addRoute($method, $path, $action)
    {
        $path = empty($path) || $path === '/' ? '/' : rtrim($path, '/');

        $this->routes[] = [
            'method' => strtoupper($method),
            'path'   => $path,
            'action' => $action
        ];
    }

    public function dispatch($uri = null)
    {
        // Your MVC uses ?url=/path style routing
        $uri = $uri ?? ($_GET['url'] ?? '/');

        // ✅ IMPORTANT: remove querystring if it was included by rewrite (e.g. /subcription/return?token=... )
        $uri = explode('?', $uri, 2)[0];

        // Normalize slashes
        $uri = '/' . trim($uri, '/');
        $uri = $uri === '//' ? '/' : $uri;

        foreach ($this->routes as $route) {
            if ($route['path'] === $uri && $route['method'] === $_SERVER['REQUEST_METHOD']) {

                if (is_callable($route['action'])) {
                    Logger::info("Executing callable for route '$uri'");
                    call_user_func($route['action']);
                    return;
                }

                if (strpos($route['action'], '@') === false) {
                    Logger::error("Invalid action format: '{$route['action']}'");
                    die("Error: action must be 'Controller@method'.");
                }

                [$controller, $method] = explode('@', $route['action'], 2);

                $controllerClass = "App\\Controllers\\$controller";

                if (!class_exists($controllerClass)) {
                    Logger::error("Controller not found: '$controllerClass'");
                    die("Error: controller does not exist.");
                }

                $controllerInstance = new $controllerClass();

                if (!method_exists($controllerInstance, $method)) {
                    Logger::error("Method '$method' not found in '$controllerClass'");
                    die("Error: method does not exist in controller.");
                }
                
                $excludedApiRoutes = [
                    '/api_invoice',
                    '/api_invoice/app-guide',
                    '/api_invoice/legal',
                    '/api_invoice/prueba',
                    '/api_invoice/version-admin',
                    '/api_invoice/ajax',
                    '/api_invoice/version'
                ];

                // Detect API invoice routes (solo las que reciben payload)
                if (strpos($uri, '/api_invoice') === 0 && !in_array($uri, $excludedApiRoutes)) {
                
                    $raw = file_get_contents('php://input');
                    $payload = json_decode($raw, true) ?: [];
                
                    \App\Core\Functions::$forcedUser =
                        $payload['email']
                        ?? $payload['installation_id']
                        ?? $payload['hardware_id']
                        ?? 'unknown-api-user';
                
                    \App\Core\Functions::$forcedBrowser = $payload['platform'] ?? 'unknown-platform';
                
                    \App\Core\Functions::$forcedOS = $payload['device_model'] ?? 'unknown-device';
                }




                Logger::info("Executing $controllerClass@$method for route '$uri'");
                call_user_func([$controllerInstance, $method]);
                return;
            }
        }

        Logger::warning("404 Not Found - Route '$uri' not defined.");
        http_response_code(404);
        echo "404 Not Found - La ruta '$uri' no está definida.";
    }
}
