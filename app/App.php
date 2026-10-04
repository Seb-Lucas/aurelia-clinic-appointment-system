<?php

namespace App;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;

class App
{
    protected array $config;
    protected Router $router;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->router = new Router();
        Session::instance();
    }

    public function bootstrap(): void
    {
        $dbConfig = $this->config['db'];
        $driver = $dbConfig['default'] ?? 'sqlite';
        $connectionConfig = $dbConfig[$driver] ?? $dbConfig['sqlite'];
        $connectionConfig['seed_demo_data'] = $dbConfig['seed_demo_data'] ?? false;
        $connectionConfig['environment'] = $this->config['env'] ?? 'production';
        Database::initialize($connectionConfig);
    }

    public function run(): void
    {
        $this->bootstrap();
        $request = Request::fromGlobals();
        $this->router->load(base_path('routes/web.php'));
        $response = $this->router->dispatch($request);
        if (!$response instanceof Response) {
            $response = new Response(200, (string) $response);
        }
        $response->send();
    }
}
