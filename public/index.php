<?php

declare(strict_types=1);

use App\Core\Database;

require_once dirname(__DIR__) . '/src/Core/Database.php';

try {
    $database = new Database(require dirname(__DIR__) . '/config/database.php');
    $database->connection()->query('SELECT 1');

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        ['application' => 'Sheepy', 'database' => 'connected'],
        JSON_THROW_ON_ERROR
    );
} catch (Throwable $exception) {
    error_log(sprintf('[Sheepy database] %s', $exception->getMessage()));

    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'application' => 'Sheepy',
        'error' => 'The service is temporarily unavailable. Please try again later.',
    ]);
}
