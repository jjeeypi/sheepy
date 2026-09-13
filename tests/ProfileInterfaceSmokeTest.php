<?php

declare(strict_types=1);

namespace Tests;

use App\Controllers\ProfileController;
use App\Core\Autoloader;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Repositories\CartRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\CartService;
use App\Services\CatalogService;
use App\Validators\CartValidator;
use RuntimeException;

$root = dirname(__DIR__);

require $root . '/src/Core/Autoloader.php';
Autoloader::register(['App\\' => $root . '/src']);

$check = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$database = new Database(require $root . '/config/database.php');
$connection = $database->connection();
$users = new UserRepository($database);
$session = Session::start([
    'name' => 'sheepy_profile_interface_test',
    'secure' => false,
]);
$session->forget('auth_user_id');
$connection->beginTransaction();

try {
    $suffix = bin2hex(random_bytes(5));
    $user = $users->createCustomer(
        'profile_' . $suffix,
        'profile_' . $suffix . '@example.test',
        password_hash('Profile-test-password-41', PASSWORD_DEFAULT),
        '+63 917 555 0101'
    );
    $session->put('auth_user_id', $user->id);

    $controller = new ProfileController(
        new View($root . '/views'),
        new AuthService($users, $session),
        new Csrf($session),
        new CatalogService(
            new ProductRepository($database),
            new CategoryRepository($database)
        ),
        new CartService(new CartRepository($database), new CartValidator())
    );
    $response = $controller->show(new Request(
        originalMethod: 'GET',
        requestPath: '/profile',
        applicationBasePath: '/sheepy/public'
    ));
    $html = $response->body();

    $check($response->status() === 200, 'The authenticated profile page did not return HTTP 200.');
    $check(
        str_contains($html, '<h1>Profile</h1>')
            && str_contains($html, $user->username)
            && str_contains($html, $user->email)
            && str_contains($html, '+63 917 555 0101'),
        'The profile page is missing stored customer details.'
    );
    $check(
        str_contains($html, '/assets/css/storefront.css')
            && str_contains($html, '/assets/css/profile.css')
            && str_contains($html, '/assets/css/cart.css')
            && str_contains($html, '/assets/js/storefront.js')
            && str_contains($html, '/assets/js/cart.js'),
        'The shared profile, search, or cart assets are missing.'
    );
    $check(
        str_contains($html, 'action="/sheepy/public/products"')
            && str_contains($html, 'data-cart-open')
            && str_contains($html, 'href="/sheepy/public/profile"')
            && str_contains($html, 'data-bottom-account')
            && str_contains($html, 'aria-current="page"'),
        'The profile page is missing its search, cart, or active account navigation.'
    );
    $check(
        str_contains($html, 'href="/sheepy/public/orders"')
            && str_contains($html, 'action="/sheepy/public/logout"')
            && str_contains($html, 'name="_token"'),
        'The profile page is missing its order-history or protected logout action.'
    );
    $check(
        !str_contains($html, 'Profile-test-password-41')
            && !str_contains($html, '$2y$'),
        'Private password information was exposed on the profile page.'
    );

    $session->forget('auth_user_id');
    $guestController = new ProfileController(
        new View($root . '/views'),
        new AuthService($users, $session),
        new Csrf($session),
        new CatalogService(
            new ProductRepository($database),
            new CategoryRepository($database)
        ),
        new CartService(new CartRepository($database), new CartValidator())
    );
    $guestResponse = $guestController->show(new Request(
        originalMethod: 'GET',
        requestPath: '/profile',
        applicationBasePath: '/sheepy/public'
    ));

    $check(
        $guestResponse->status() === 302
            && $guestResponse->header('Location') === '/sheepy/public/login',
        'The profile controller did not reject an unauthenticated request.'
    );

    $routes = (string) file_get_contents($root . '/config/routes.php');
    $profileCss = (string) file_get_contents($root . '/public/assets/css/profile.css');
    $check(
        str_contains($routes, "'/profile'")
            && str_contains($routes, 'ProfileController::class')
            && str_contains($routes, 'AuthMiddleware::class'),
        'The authenticated profile route is missing.'
    );
    $check(
        str_contains($profileCss, '@media (max-width: 56rem)')
            && str_contains($profileCss, '@media (max-width: 40rem)'),
        'The profile page is missing responsive tablet or mobile styles.'
    );
} finally {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }

    $session->forget('auth_user_id');
}

fwrite(STDOUT, 'Responsive profile interface smoke test passed.' . PHP_EOL);
