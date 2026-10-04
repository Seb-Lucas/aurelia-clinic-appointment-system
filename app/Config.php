<?php

namespace App;

if (!function_exists('base_path')) {
    require_once __DIR__ . '/helpers.php';
}

class Config
{
    private static array $config = [];

    public static function load(): void
    {
        $files = glob(base_path('config') . DIRECTORY_SEPARATOR . '*.php');
        foreach ($files as $file) {
            $name = basename($file, '.php');
            self::$config[$name] = require $file;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (empty(self::$config)) {
            self::load();
        }

        $segments = explode('.', $key);
        $value = self::$config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value ?? $default;
    }
}
