<?php

define('BASE_PATH', dirname(__DIR__));

ini_set('display_errors', '0');
ini_set('log_errors', '1');

if (PHP_SAPI === 'cli-server') {
	$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
	$publicPath = realpath(__DIR__ . $requestPath);
	$publicRoot = realpath(__DIR__);

	if ($publicPath !== false
		&& $publicRoot !== false
		&& $publicPath !== $publicRoot
		&& str_starts_with($publicPath, $publicRoot . DIRECTORY_SEPARATOR)
		&& is_file($publicPath)) {
		return false;
	}
}

try {
	$app = require BASE_PATH . '/app/bootstrap.php';
	$app->run();
} catch (Throwable $exception) {
	error_log((string) $exception);
	http_response_code(500);
	echo 'An unexpected error occurred.';
}
