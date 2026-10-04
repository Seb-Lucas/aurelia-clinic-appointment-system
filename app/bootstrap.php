<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/helpers.php';

use App\App;
use App\Config;
use App\Core\Database;

Config::load();

$dbConfig = config('app.db');
$defaultDriver = $dbConfig['default'] ?? 'sqlite';
$connectionConfig = $dbConfig[$defaultDriver] ?? $dbConfig['sqlite'];
$connectionConfig['seed_demo_data'] = $dbConfig['seed_demo_data'] ?? false;
$connectionConfig['environment'] = config('app.env', 'production');
Database::initialize($connectionConfig);

$app = new App(config('app'));
return $app;
