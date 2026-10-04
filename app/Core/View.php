<?php

namespace App\Core;

class View
{
    public static function render(string $template, array $data = []): string
    {
        $templatePath = base_path('views/' . ltrim($template, '/') . '.php');
        if (!is_file($templatePath)) {
            throw new \RuntimeException("View not found: {$template}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $templatePath;
        return (string) ob_get_clean();
    }
}
