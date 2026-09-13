<?php

declare(strict_types=1);

use App\Core\Response;
use App\Core\Router;

return static function (Router $router): void {
    $router->get('/', static fn (): Response => Response::json([
        'application' => 'Sheepy',
        'status' => 'ready',
    ]));
};
