<?php

declare(strict_types=1);

use App\Core\Autoloader;
use App\Core\Container;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Core\Csrf;
use App\Repositories\UserRepository;
use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Services\AuthService;
use App\Services\CartService;
use App\Services\CatalogService;
use App\Services\CheckoutPricing;
use App\Services\CheckoutService;
use App\Services\ProductService;

$root = dirname(__DIR__);

require_once $root . '/src/Core/Autoloader.php';

Autoloader::register(['App\\' => $root . '/src']);

$appConfig = require $root . '/config/app.php';

date_default_timezone_set($appConfig['timezone']);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', $appConfig['error_log']);

set_error_handler(static function (
    int $severity,
    string $message,
    string $file,
    int $line
): bool {
    if ((error_reporting() & $severity) === 0) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    $databaseConfig = require $root . '/config/database.php';
    $container = new Container();
    $container->instance('config.app', $appConfig);
    $container->instance(Session::class, Session::start($appConfig['session'] ?? []));
    $container->singleton(Csrf::class);
    $container->singleton(View::class);
    $container->singleton(
        Database::class,
        static fn (): Database => new Database($databaseConfig)
    );
    $container->singleton(UserRepository::class);
    $container->singleton(AuthService::class);
    $container->singleton(ProductRepository::class);
    $container->singleton(CategoryRepository::class);
    $container->singleton(CartRepository::class);
    $container->singleton(OrderRepository::class);
    $container->singleton(ProductService::class);
    $container->singleton(CatalogService::class);
    $container->singleton(CartService::class);
    $container->singleton(CheckoutPricing::class);
    $container->singleton(CheckoutService::class);

    $router = new Router($container);
    $registerRoutes = require $root . '/config/routes.php';

    if (!is_callable($registerRoutes)) {
        throw new RuntimeException('The route configuration must return a callable.');
    }

    $registerRoutes($router);

    $request = Request::capture();
    $response = $router->dispatch($request);
    $response->send(!$request->isMethod('HEAD'));
} catch (UnexpectedValueException $exception) {
    Response::json(['error' => 'The request body is invalid.'], 400)->send();
} catch (Throwable $exception) {
    error_log(sprintf(
        '[%s] %s in %s:%d',
        $appConfig['name'],
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    ));

    Response::json(
        ['error' => 'An unexpected error occurred. Please try again later.'],
        500
    )->send();
}
