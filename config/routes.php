<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AdminProductController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Middleware\AdminMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Services\AuthService;

return static function (Router $router): void {
    $router->get('/', static function (Request $request, AuthService $auth): Response {
        $user = $auth->currentUser();

        if ($user === null) {
            return Response::redirect($request->url('/login'));
        }

        return Response::redirect(
            $request->url($user->isAdmin() ? '/admin' : '/home')
        );
    });

    $router->group('', static function (Router $router): void {
        $router->get('/login', [AuthController::class, 'showLogin']);
        $router->post('/login', [AuthController::class, 'login']);
        $router->get('/register', [AuthController::class, 'showRegister']);
        $router->post('/register', [AuthController::class, 'register']);
    }, [GuestMiddleware::class]);

    $router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class]);
    $router->get('/home', [HomeController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/products', [ProductController::class, 'index'], [AuthMiddleware::class]);
    $router->get(
        '/products/{slug:[a-z0-9-]+}',
        [ProductController::class, 'show'],
        [AuthMiddleware::class]
    );
    $router->get(
        '/categories/{slug:[a-z0-9-]+}',
        [ProductController::class, 'category'],
        [AuthMiddleware::class]
    );
    $router->get('/admin', [AdminController::class, 'dashboard'], [AdminMiddleware::class]);

    $router->group('/admin/products', static function (Router $router): void {
        $router->get('', [AdminProductController::class, 'index']);
        $router->get('/create', [AdminProductController::class, 'create']);
        $router->post('', [AdminProductController::class, 'store']);
        $router->get('/{id:\d+}/edit', [AdminProductController::class, 'edit']);
        $router->post('/{id:\d+}', [AdminProductController::class, 'update']);
        $router->post('/{id:\d+}/delete', [AdminProductController::class, 'destroy']);
    }, [AdminMiddleware::class]);
};
