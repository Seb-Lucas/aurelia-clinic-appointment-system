<?php

namespace App\Core;

class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query,
        public readonly array $body,
        public readonly array $server,
        public readonly array $files = []
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $query = $_GET ?? [];
        $body = $_POST ?? [];

        return new self($method, $path, $query, $body, $_SERVER, $_FILES ?? []);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($method) === $this->method;
    }
}
