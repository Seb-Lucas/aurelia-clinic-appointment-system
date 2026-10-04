<?php

namespace App\Core;

class Router
{
    /**
     * @var array<int, array{method:string, path:string, handler:callable|array<string,mixed>, middleware?:array<int,callable|string>}>
     */
    protected array $routes = [];

    public function add(string $method, string $path, callable|array $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function load(string $file): void
    {
        $routes = require $file;
        foreach ($routes as $route) {
            $this->add($route[0], $route[1], $route[2], $route[3] ?? []);
        }
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) {
                continue;
            }

            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route['path']);
            $pattern = '#^' . str_replace('/', '\/', $pattern) . '$#';

            if (preg_match($pattern, $request->path, $matches)) {
                array_shift($matches);
                if (!empty($route['middleware'])) {
                    foreach ($route['middleware'] as $middleware) {
                        $middlewareInstance = is_callable($middleware) ? $middleware : new $middleware();
                        $result = is_callable($middlewareInstance) ? $middlewareInstance($request) : $middlewareInstance->handle($request);
                        if ($result instanceof Response) {
                            return $result;
                        }
                    }
                }

                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$controller, $method] = $handler;
                    $instance = new $controller();
                    return $instance->$method($request, ...$matches);
                }

                if (is_callable($handler)) {
                    return $handler($request, ...$matches);
                }

                throw new \RuntimeException('Route handler is not callable.');
            }
        }

        return new Response(404, 'Page not found.');
    }
}
