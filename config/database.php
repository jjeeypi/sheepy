<?php

declare(strict_types=1);

/**
 * Load local database settings without requiring a third-party package.
 * Real environment variables take precedence over values in .env.
 */
$environment = [];
$environmentFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

if (is_readable($environmentFile)) {
    $parsedEnvironment = parse_ini_file($environmentFile, false, INI_SCANNER_RAW);

    if ($parsedEnvironment === false) {
        throw new RuntimeException('The environment configuration could not be read.');
    }

    $environment = $parsedEnvironment;
}

$env = static function (string $key, string $default = '') use ($environment): string {
    $systemValue = getenv($key);

    if ($systemValue !== false) {
        return $systemValue;
    }

    if (array_key_exists($key, $environment)) {
        return (string) $environment[$key];
    }

    return $default;
};

return [
    'driver' => $env('DB_DRIVER', 'mysql'),
    'host' => $env('DB_HOST', '127.0.0.1'),
    'port' => (int) $env('DB_PORT', '3306'),
    'database' => $env('DB_DATABASE', 'sheepy'),
    'username' => $env('DB_USERNAME', 'root'),
    'password' => $env('DB_PASSWORD'),
    'charset' => $env('DB_CHARSET', 'utf8mb4'),
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ],
];
