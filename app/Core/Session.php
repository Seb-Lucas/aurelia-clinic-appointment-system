<?php

namespace App\Core;

class Session
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params([
                'lifetime' => config('app.session_lifetime', 120) * 60,
                'path' => '/',
                'secure' => config('app.session_secure', config('app.env') === 'production'),
                'httponly' => true,
                'samesite' => config('app.session_same_site', 'Lax'),
            ]);
            session_start();
        }
    }

    public static function instance(): self
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    public static function set(string $key, mixed $value): void
    {
        self::instance();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::instance();
        return $_SESSION[$key] ?? $default;
    }

    public static function forget(string $key): void
    {
        self::instance();
        unset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::instance();
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SESSION = [];
    }

    public static function flash(string $key, mixed $default = null): mixed
    {
        self::instance();
        $value = $_SESSION['flash'][$key] ?? $default;
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    public static function setFlash(string $key, mixed $value): void
    {
        self::instance();
        $_SESSION['flash'][$key] = $value;
    }
}
