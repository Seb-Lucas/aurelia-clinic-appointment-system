<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/app/helpers.php';

use App\Config;
use App\Core\Database;

$email = strtolower(trim($argv[1] ?? ''));
$name = trim($argv[2] ?? '');
$password = getenv('INITIAL_ADMIN_PASSWORD');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $name === '') {
    fwrite(STDERR, "Usage: php scripts/create-admin.php <email> <name>\n");
    exit(1);
}

if ($password === false || strlen($password) < 16) {
    fwrite(STDERR, "Set INITIAL_ADMIN_PASSWORD to a unique password of at least 16 characters.\n");
    exit(1);
}

Config::load();
$dbConfig = config('app.db');
$driver = $dbConfig['default'] ?? 'sqlite';
$connectionConfig = $dbConfig[$driver] ?? $dbConfig['sqlite'];
$connectionConfig['seed_demo_data'] = false;
$connectionConfig['environment'] = 'production';
$pdo = Database::initialize($connectionConfig);

if ($pdo->query("SELECT 1 FROM users WHERE role = 'admin' LIMIT 1")->fetchColumn() !== false) {
    fwrite(STDERR, "An administrator already exists; refusing to create another initial administrator.\n");
    exit(1);
}

$existingUser = $pdo->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
$existingUser->execute(['email' => $email]);
if ($existingUser->fetchColumn() !== false) {
    fwrite(STDERR, "A user with that email already exists.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
if ($hash === false) {
    throw new RuntimeException('Unable to hash the initial administrator password.');
}

$insert = $pdo->prepare(
    'INSERT INTO users (name, email, password_hash, role, status) VALUES (:name, :email, :password_hash, :role, :status)'
);
$insert->execute([
    'name' => $name,
    'email' => $email,
    'password_hash' => $hash,
    'role' => 'admin',
    'status' => 'active',
]);

fwrite(STDOUT, "Initial administrator created. Remove INITIAL_ADMIN_PASSWORD from the environment.\n");
