<?php

declare(strict_types=1);

namespace Core;

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => [],
        'OPTIONS' => [],
        'ANY' => [],
    ];
    private array $namedRoutes = [];
    private array $middleware = [];
    private array $groups = [];
    private string $currentGroup = '';
    private ?string $basePath = null;

    public function __construct(string $basePath = '')
    {
        $this->basePath = $basePath;
    }

    public function get(string $uri, callable|array|string $action, array $middleware = []): Route
    {
        return $this->addRoute('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, callable|array|string $action, array $middleware = []): Route
    {
        return $this->addRoute('POST', $uri, $action, $middleware);
    }

    public function put(string $uri, callable|array|string $action, array $middleware = []): Route
    {
        return $this->addRoute('PUT', $uri, $action, $middleware);
    }

    public function patch(string $uri, callable|array|string $action, array $middleware = []): Route
    {
        return $this->addRoute('PATCH', $uri, $action, $middleware);
    }

    public function delete(string $uri, callable|array|string $action, array $middleware = []): Route
    {
        return $this->addRoute('DELETE', $uri, $action, $middleware);
    }

    public function any(string $uri, callable|array|string $action, array $middleware = []): Route
    {
        return $this->addRoute('ANY', $uri, $action, $middleware);
    }

    public function match(array $methods, string $uri, callable|array|string $action, array $middleware = []): Route
    {
        $route = new Route($methods[0], $this->prefix($uri), $action, $middleware);
        foreach ($methods as $method) {
            $this->routes[strtoupper($method)][] = $route;
        }
        return $route;
    }

    public function resource(string $name, string $controller, array $options = []): void
    {
        $only = $options['only'] ?? ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
        $except = $options['except'] ?? [];
        $params = $options['params'] ?? [];

        $actions = [
            'index' => ['GET', $name, 'index'],
            'create' => ['GET', $name . '/create', 'create'],
            'store' => ['POST', $name, 'store'],
            'show' => ['GET', $name . '/{' . ($params['id'] ?? 'id') . '}', 'show'],
            'edit' => ['GET', $name . '/{' . ($params['id'] ?? 'id') . '}/edit', 'edit'],
            'update' => ['PUT', $name . '/{' . ($params['id'] ?? 'id') . '}', 'update'],
            'destroy' => ['DELETE', $name . '/{' . ($params['id'] ?? 'id') . '}', 'destroy'],
        ];

        foreach ($actions as $action => [$method, $uri, $controllerMethod]) {
            if (in_array($action, $except, true) || !in_array($action, $only, true)) {
                continue;
            }
            $this->$method($uri, [$controller, $controllerMethod])->name("{$name}.{$action}");
        }
    }

    public function group(array|string $attributes, callable $callback): void
    {
        $attributes = is_string($attributes) ? ['prefix' => $attributes] : $attributes;

        $previousGroup = $this->currentGroup;
        $previousMiddleware = $this->middleware;

        if (isset($attributes['prefix'])) {
            $this->currentGroup .= rtrim($attributes['prefix'], '/') . '/';
        }

        if (isset($attributes['middleware'])) {
            $this->middleware = array_merge($this->middleware, (array) $attributes['middleware']);
        }

        if (isset($attributes['as'])) {
            $this->groups[] = $attributes['as'];
        }

        $callback($this);

        $this->currentGroup = $previousGroup;
        $this->middleware = $previousMiddleware;
        if (isset($attributes['as'])) {
            array_pop($this->groups);
        }
    }

    public function prefix(string $uri): string
    {
        $uri = ltrim($uri, '/');
        $prefix = rtrim($this->currentGroup, '/');
        return $prefix ? "/{$prefix}/{$uri}" : "/{$uri}";
    }

    private function addRoute(string $method, string $uri, callable|array|string $action, array $middleware): Route
    {
        $uri = $this->prefix($uri);
        $route = new Route($method, $uri, $action, array_merge($this->middleware, $middleware));
        $this->routes[$method][] = $route;
        return $route;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $uri = $request->getPathInfo();

        $routes = array_merge($this->routes[$method] ?? [], $this->routes['ANY'] ?? []);

        foreach ($routes as $route) {
            $params = $route->match($uri);
            if ($params !== false) {
                return $this->runRoute($route, $request, $params);
            }
        }

        return $this->fallback($request);
    }

    private function runRoute(Route $route, Request $request, array $params): Response
    {
        foreach ($route->getMiddleware() as $middleware) {
            $response = $this->runMiddleware($middleware, $request);
            if ($response instanceof Response) {
                return $response;
            }
        }

        $action = $route->getAction();
        $params = array_merge($params, $request->all());

        if (is_callable($action)) {
            return $this->callAction($action, $params);
        }

        if (is_array($action) && count($action) === 2) {
            [$controller, $method] = $action;
            if (class_exists($controller)) {
                $instance = new $controller();
                if (method_exists($instance, $method)) {
                    return $this->callAction([$instance, $method], $params);
                }
            }
        }

        if (is_string($action) && str_contains($action, '@')) {
            [$controller, $method] = explode('@', $action, 2);
            if (class_exists($controller)) {
                $instance = new $controller();
                if (method_exists($instance, $method)) {
                    return $this->callAction([$instance, $method], $params);
                }
            }
        }

        abort(500, "Route action not found: {$action}");
    }

    private function callAction(callable $action, array $params): Response
    {
        $result = $action(...array_values($params));

        if ($result instanceof Response) {
            return $result;
        }

        if (is_string($result)) {
            return Response::create($result);
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        return Response::create((string) $result);
    }

    private function runMiddleware(string $middleware, Request $request): ?Response
    {
        if (class_exists($middleware)) {
            $instance = new $middleware();
            if (method_exists($instance, 'handle')) {
                return $instance->handle($request);
            }
        }
        return null;
    }

    private function fallback(Request $request): Response
    {
        if ($request->expectsJson()) {
            return Response::notFound();
        }
        return Response::view('errors/404', [], 404);
    }

    public function route(string $name, array $params = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route [{$name}] not defined.");
        }

        $route = $this->namedRoutes[$name];
        $uri = $route->getUri();

        foreach ($params as $key => $value) {
            $uri = str_replace("{{$key}}", $value, $uri);
            $uri = str_replace("{$key}", $value, $uri);
        }

        return $uri;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }
}

class Route
{
    private string $method;
    private string $uri;
    private callable|array|string $action;
    private array $middleware = [];
    private string $name = '';
    private array $regex = [];
    private array $compiled = [];

    public function __construct(string $method, string $uri, callable|array|string $action, array $middleware = [])
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->action = $action;
        $this->middleware = $middleware;
        $this->compile();
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getAction(): callable|array|string
    {
        return $this->action;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        Router::getInstance()->namedRoutes[$name] = $this;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function middleware(array|string $middleware): self
    {
        $this->middleware = array_merge($this->middleware, (array) $middleware);
        return $this;
    }

    public function where(string $key, string $pattern): self
    {
        $this->regex[$key] = $pattern;
        $this->compile();
        return $this;
    }

    public function match(string $uri): array|false
    {
        if (!isset($this->compiled['regex'])) {
            return false;
        }

        if (!preg_match($this->compiled['regex'], $uri, $matches)) {
            return false;
        }

        $params = [];
        foreach ($this->compiled['keys'] as $i => $key) {
            if (isset($matches[$i + 1])) {
                $params[$key] = $matches[$i + 1];
            }
        }

        return $params;
    }

    private function compile(): void
    {
        $pattern = $this->uri;
        $keys = [];

        $pattern = preg_replace_callback('/\{(\w+)\}/', function ($matches) use (&$keys) {
            $key = $matches[1];
            $keys[] = $key;
            $regex = $this->regex[$key] ?? '[^/]+';
            return '(' . $regex . ')';
        }, $pattern);

        $pattern = str_replace('/', '\/', $pattern);
        $pattern = '^' . $pattern . '$';

        $this->compiled = [
            'regex' => $pattern,
            'keys' => $keys,
        ];
    }
}