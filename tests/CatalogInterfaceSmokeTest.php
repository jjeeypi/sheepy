<?php

declare(strict_types=1);

namespace Tests;

use App\Controllers\ProductController;
use App\Core\Autoloader;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\User;
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
$catalog = new CatalogService(
    new ProductRepository($database),
    new CategoryRepository($database)
);
$session = Session::start([
    'name' => 'sheepy_catalog_interface_test',
    'secure' => false,
]);
$session->forget('auth_user_id');
$controller = new ProductController(
    new View($root . '/views'),
    $catalog,
    new AuthService(new UserRepository($database), $session),
    new Csrf($session),
    new CartService(new CartRepository($database), new CartValidator())
);
$controllerResponse = $controller->category(
    new Request(
        originalMethod: 'GET',
        requestPath: '/categories/men',
        applicationBasePath: '/sheepy/public'
    ),
    'men'
);

$check(
    $controllerResponse->status() === 200
        && str_contains($controllerResponse->body(), '<h1 id="catalog-title">Men</h1>'),
    'The ProductController failed to assemble the Men catalogue response.'
);

$result = $catalog->browse('men', '', 1);
$navigation = $catalog->navigation();
$activeDepartment = $result['breadcrumb'][0] ?? null;
$categoryNavigation = [];

foreach ($navigation as $group) {
    if ($activeDepartment !== null && $group['department']->id === $activeDepartment->id) {
        $categoryNavigation = $group['children'];
        break;
    }
}

$html = (new View($root . '/views'))->render('products/index', [
    ...$result,
    'query' => '<script>alert("catalogue")</script>',
    'navigation' => $navigation,
    'activeNavigation' => 'men',
    'activeDepartment' => $activeDepartment,
    'categoryNavigation' => $categoryNavigation,
    'user' => new User(1, 'catalog_customer', 'catalog@example.test', '', null, 'customer'),
    'csrfToken' => 'test-csrf-token',
    'logoutUrl' => '/sheepy/public/logout',
    'homeUrl' => '/sheepy/public/home',
    'productsUrl' => '/sheepy/public/products',
    'categoriesUrl' => '/sheepy/public/categories',
    'profileUrl' => '/sheepy/public/profile',
    'browseUrl' => '/sheepy/public/categories/men',
    'previousPageUrl' => null,
    'nextPageUrl' => null,
    'basePath' => '/sheepy/public',
    'cartUrl' => '/sheepy/public/cart',
    'cartItemsUrl' => '/sheepy/public/cart/items',
    'checkoutUrl' => '/sheepy/public/checkout',
    'checkoutConfirmUrl' => '/sheepy/public/checkout/confirm',
    'ordersUrl' => '/sheepy/public/orders',
    'cartItemCount' => 0,
]);

$check(
    str_contains($html, '<h1 id="catalog-title">Men</h1>')
        && str_contains($html, 'Men collection'),
    'The Men catalogue heading is missing.'
);
$check(
    str_contains($html, '/assets/css/storefront.css')
        && str_contains($html, '/assets/css/catalog.css')
        && str_contains($html, '/assets/js/storefront.js'),
    'The catalogue interface assets are not loaded.'
);
$check(
    str_contains($html, 'href="/sheepy/public/categories/men"')
        && str_contains($html, 'aria-current="page"'),
    'The active Men navigation state is missing.'
);

foreach ($categoryNavigation as $child) {
    $check(
        str_contains($html, '/categories/' . $child->slug),
        sprintf('The %s Men filter is missing.', $child->name)
    );
}

$check(
    !str_contains($html, '<script>alert("catalogue")</script>')
        && str_contains($html, '&lt;script&gt;alert(&quot;catalogue&quot;)&lt;/script&gt;'),
    'The catalogue search query was not safely escaped.'
);
$check(
    str_contains($html, 'data-add-to-cart') || str_contains($html, 'No pieces found'),
    'The catalogue has neither product actions nor its empty state.'
);
$check(
    str_contains($html, 'class="mobile-tabbar"')
        && str_contains($html, 'data-bottom-cart')
        && str_contains($html, 'data-bottom-account'),
    'The mobile catalogue shortcuts are missing.'
);
$check(
    is_file($root . '/public/assets/css/catalog.css')
        && str_contains(
            (string) file_get_contents($root . '/public/assets/css/catalog.css'),
            '@media (max-width: 40rem)'
        ),
    'The responsive catalogue stylesheet is missing.'
);

fwrite(STDOUT, 'Responsive catalogue interface smoke test passed.' . PHP_EOL);
