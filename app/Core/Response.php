<?php

namespace App\Core;

class Response
{
    public function __construct(
        public int $status = 200,
        public string $content = '',
        public array $headers = []
    ) {
    }

    public static function view(string $template, array $data = [], int $status = 200): self
    {
        return new self($status, View::render($template, $data));
    }

    public static function redirect(string $path, int $status = 302): self
    {
        return new self($status, '', ['Location' => $path]);
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value, true);
        }
        echo $this->content;
    }
}
