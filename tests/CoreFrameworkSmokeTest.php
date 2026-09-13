<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Autoloader;
use App\Core\Container;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use InvalidArgumentException;
use RuntimeException;

require dirname(__DIR__) . '/src/Core/Autoloader.php';

Autoloader::register(['App\\' => dirname(__DIR__) . '/src']);

final class SmokeDependency
{
}

final class SmokeService
{
    public function __construct(public readonly SmokeDependency $dependency)
    {
    }
}

final class SmokeController
{
    public function __construct(private readonly SmokeService $service)
    {
    }

    public function show(Request $request, int $id): Response
    {
        return Response::json([
            'id' => $id,
            'route_id' => $request->route('id'),
            'autowired' => $this->service->dependency instanceof SmokeDependency,
        ]);
    }
}

$check = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$container = new Container();
$container->singleton('shared-value', static fn (): object => new \stdClass());

$check(
    $container->get('shared-value') === $container->get('shared-value'),
    'The container did not reuse a singleton.'
);
$check(
    $container->get(SmokeService::class) instanceof SmokeService,
    'Constructor autowiring failed.'
);

$request = new Request(
    'GET',
    '/api/products/42/',
    ['page' => '2', 'source' => 'query'],
    ['source' => 'body'],
    [],
    [],
    [],
    ['Accept' => 'application/json']
);

$check($request->path() === '/api/products/42', 'Request path normalization failed.');
$check($request->integer('page') === 2, 'Integer input parsing failed.');
$check($request->string('source') === 'body', 'Body input did not override query input.');
$check($request->expectsJson(), 'JSON content negotiation failed.');

$router = new Router($container);
$router->use(static function (Request $request, callable $next): Response {
    return $next($request)->withHeader('X-Smoke-Middleware', 'passed');
});
$router->group('/api', static function (Router $router): void {
    $router->get(
        '/products/{id:\d+}',
        static function (Request $request, SmokeService $service, int $id): array {
            return [
                'id' => $id,
                'route_id' => $request->route('id'),
                'autowired' => $service->dependency instanceof SmokeDependency,
            ];
        }
    );
    $router->get('/controller/{id:\d+}', [SmokeController::class, 'show']);
});

$response = $router->dispatch($request);
$payload = json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);

$check($response->status() === 200, 'A valid route did not return HTTP 200.');
$check($payload['id'] === 42, 'A typed route parameter was not injected.');
$check($payload['route_id'] === '42', 'The request did not receive its route parameters.');
$check($payload['autowired'] === true, 'A route dependency was not autowired.');
$check(
    $response->header('X-Smoke-Middleware') === 'passed',
    'The middleware pipeline did not run.'
);

$controllerResponse = $router->dispatch(new Request('GET', '/api/controller/7'));
$controllerPayload = json_decode($controllerResponse->body(), true, 512, JSON_THROW_ON_ERROR);
$check($controllerResponse->status() === 200, 'Controller dispatch failed.');
$check($controllerPayload['id'] === 7, 'A controller route parameter was not injected.');
$check($controllerPayload['autowired'] === true, 'A controller dependency was not autowired.');

$methodNotAllowed = $router->dispatch(new Request('POST', '/api/products/42'));
$check($methodNotAllowed->status() === 405, 'Method-not-allowed handling failed.');
$check(
    str_contains($methodNotAllowed->header('Allow') ?? '', 'HEAD'),
    'The method-not-allowed response omitted HEAD.'
);

$check(
    $router->dispatch(new Request('OPTIONS', '/api/products/42'))->status() === 204,
    'Automatic OPTIONS handling failed.'
);
$check(
    $router->dispatch(new Request('GET', '/missing'))->status() === 404,
    'Not-found handling failed.'
);
$check(
    $router->dispatch(new Request('HEAD', '/api/products/42'))->status() === 200,
    'HEAD-to-GET fallback failed.'
);

$original = Response::text('original');
$modified = $original->withStatus(201)->withHeader('X-Test', 'yes');

$check($original->status() === 200, 'A response instance was mutated in place.');
$check($modified->status() === 201, 'A copied response did not receive its new status.');

$headerInjectionBlocked = false;

try {
    $original->withHeader('X-Test', "safe\r\nInjected: unsafe");
} catch (InvalidArgumentException) {
    $headerInjectionBlocked = true;
}

$check($headerInjectionBlocked, 'Response header injection was not blocked.');

echo "Phase 2 core framework smoke test passed.\n";
