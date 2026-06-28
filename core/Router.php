<?php
/**
 * Router Class
 * HoanKiem LAB - Simple but powerful router
 */

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private string $groupPrefix = '';
    private array $groupMiddlewares = [];
    private ?string $matchedRoute = null;

    /**
     * Register a GET route
     */
    public function get(string $path, $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route
     */
    public function post(string $path, $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Register route for both GET and POST
     */
    public function any(string $path, $handler): self
    {
        $this->addRoute('GET', $path, $handler);
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Group routes with prefix and middleware
     */
    public function group(string $prefix, array $middlewares, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddlewares = $this->groupMiddlewares;

        $this->groupPrefix = $previousPrefix . $prefix;
        $this->groupMiddlewares = array_merge($previousMiddlewares, $middlewares);

        $callback($this);

        $this->groupPrefix = $previousPrefix;
        $this->groupMiddlewares = $previousMiddlewares;
    }

    /**
     * Add a route
     */
    private function addRoute(string $method, string $path, $handler): self
    {
        $fullPath = $this->groupPrefix . $path;
        $this->routes[] = [
            'method'      => $method,
            'path'        => $fullPath,
            'handler'     => $handler,
            'middlewares'  => $this->groupMiddlewares,
            'pattern'     => $this->pathToPattern($fullPath),
        ];
        return $this;
    }

    /**
     * Convert path to regex pattern
     */
    private function pathToPattern(string $path): string
    {
        // Convert {param} to named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Register middleware
     */
    public function registerMiddleware(string $name, callable $handler): void
    {
        $this->middlewares[$name] = $handler;
    }

    /**
     * Dispatch the request
     */
    public function dispatch(string $method, string $uri): void
    {
        // Remove base URL prefix
        $appConfig = require dirname(__DIR__) . '/config/app.php';
        $baseUrl = $appConfig['base_url'];
        if (strpos($uri, $baseUrl) === 0) {
            $uri = substr($uri, strlen($baseUrl));
        }

        // Clean URI
        $uri = '/' . trim($uri, '/');
        if ($uri === '/') {
            $uri = '/';
        }

        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            if (preg_match($route['pattern'], $uri, $matches)) {
                $this->matchedRoute = $route['path'];

                // Extract params
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middlewares
                foreach ($route['middlewares'] as $middlewareName) {
                    // Handle middleware with params (e.g., "role:admin")
                    $middlewareParts = explode(':', $middlewareName, 2);
                    $name = $middlewareParts[0];
                    $param = $middlewareParts[1] ?? null;

                    if (isset($this->middlewares[$name])) {
                        $result = ($this->middlewares[$name])($param);
                        if ($result === false) {
                            return;
                        }
                    }
                }

                // Call handler
                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        // No route found - 404
        $this->handleNotFound();
    }

    /**
     * Call route handler
     */
    private function callHandler($handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_string($handler)) {
            [$controllerName, $method] = explode('@', $handler);

            // Build controller class path
            $controllerFile = dirname(__DIR__) . '/controllers/' . str_replace('\\', '/', $controllerName) . '.php';

            if (!file_exists($controllerFile)) {
                $this->handleNotFound();
                return;
            }

            require_once $controllerFile;

            // Get the class name (last part)
            $classParts = explode('/', $controllerName);
            $className = end($classParts);

            if (!class_exists($className)) {
                $this->handleNotFound();
                return;
            }

            $controller = new $className();
            if (!method_exists($controller, $method)) {
                $this->handleNotFound();
                return;
            }

            call_user_func_array([$controller, $method], $params);
        }
    }

    /**
     * Handle 404 Not Found
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        if (file_exists(dirname(__DIR__) . '/views/errors/404.php')) {
            require dirname(__DIR__) . '/views/errors/404.php';
        } else {
            echo '<h1>404 - Không tìm thấy trang</h1>';
            echo '<p>Trang bạn yêu cầu không tồn tại.</p>';
            echo '<a href="' . url('/') . '">Về trang chủ</a>';
        }
    }

    /**
     * Get current matched route
     */
    public function getCurrentRoute(): ?string
    {
        return $this->matchedRoute;
    }
}
