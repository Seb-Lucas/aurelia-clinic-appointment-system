<?php

use App\Config;

function base_path(string $path = ''): string
{
    return rtrim(__DIR__ . '/..', DIRECTORY_SEPARATOR) . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
}

function public_path(string $path = ''): string
{
    return base_path('public') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
}

function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    if ($value === false && array_key_exists($key, $_ENV)) {
        $value = (string) $_ENV[$key];
    }
    if ($value === false && array_key_exists($key, $_SERVER)) {
        $value = (string) $_SERVER[$key];
    }

    if ($value === false) {
        static $fileValues = null;
        if ($fileValues === null) {
            $fileValues = [];
            $envFile = base_path('.env');
            if (is_file($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines ?: [] as $line) {
                    $trimmed = trim($line);
                    if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                        continue;
                    }
                    [$name, $fileValue] = array_pad(explode('=', $trimmed, 2), 2, '');
                    $fileValues[trim($name)] = trim($fileValue, " \t\n\r\0\x0B\"'");
                }
            }
        }
        $value = $fileValues[$key] ?? false;
    }

    if ($value === false) {
        return $default;
    }
    if ($value === 'true') {
        return true;
    }
    if ($value === 'false') {
        return false;
    }
    if ($value === 'null') {
        return null;
    }

    return $value;
}

function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): App\Core\Response
{
    return App\Core\Response::redirect($path);
}

function current_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . ($_SERVER['REQUEST_URI'] ?? '/');
}

function csrf_token(): string
{
    $token = App\Core\Session::get('_csrf_token');
    if (!$token) {
        $token = bin2hex(random_bytes(32));
        App\Core\Session::set('_csrf_token', $token);
    }
    return $token;
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
}

function session(): App\Core\Session
{
    return App\Core\Session::instance();
}

function flash(string $key, mixed $default = null): mixed
{
    return App\Core\Session::flash($key, $default);
}

function set_flash(string $key, mixed $value): void
{
    App\Core\Session::setFlash($key, $value);
}

function abort(int $code = 500, string $message = 'Something went wrong.'): never
{
    http_response_code($code);
    echo $message;
    exit;
}
