<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Autoloader;
use App\Core\Database;
use App\Core\View;
use App\Models\User;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Services\CatalogService;
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
$view = new View($root . '/views');
$navigation = $catalog->navigation();
$categoryLookup = [];

foreach ($navigation as $group) {
    foreach ($group['children'] as $category) {
        $categoryLookup[$category->slug] = $category;
    }
}

$featuredCategories = [];

foreach ([
    'men-shirts' => ['Shirts', 'category-shirts.jpg', 'Patterned shirt'],
    'men-jackets' => ['Outerwear', 'category-outerwear.jpg', 'Relaxed outerwear'],
    'men-pants' => ['Denim', 'category-denim.jpg', 'Denim outfit'],
    'men-t-shirts' => ['Tees', 'category-tees.jpg', 'Graphic T-shirt'],
] as $slug => [$label, $image, $alt]) {
    if (isset($categoryLookup[$slug])) {
        $featuredCategories[] = compact('label', 'image', 'alt') + [
            'category' => $categoryLookup[$slug],
        ];
    }
}

$user = new User(
    1,
    '<script>alert("unsafe")</script>',
    'customer@example.test',
    '',
    null,
    'customer'
);

$html = $view->render('home/index', [
    'user' => $user,
    'csrfToken' => 'test-csrf-token',
    'logoutUrl' => '/sheepy/public/logout',
    'homeUrl' => '/sheepy/public/home',
    'productsUrl' => '/sheepy/public/products',
    'categoriesUrl' => '/sheepy/public/categories',
    'basePath' => '/sheepy/public',
    'navigation' => $navigation,
    'featuredCategories' => $featuredCategories,
    'latestProducts' => $catalog->latest(),
    'cartUrl' => '/sheepy/public/cart',
    'cartItemsUrl' => '/sheepy/public/cart/items',
    'checkoutUrl' => '/sheepy/public/checkout',
    'checkoutConfirmUrl' => '/sheepy/public/checkout/confirm',
    'ordersUrl' => '/sheepy/public/orders',
    'cartItemCount' => 0,
]);

$check(
    str_contains($html, '/assets/css/storefront.css')
        && str_contains($html, '/assets/js/storefront.js'),
    'The dedicated home interface assets are not loaded.'
);
$check(
    is_file($root . '/public/assets/css/storefront.css')
        && is_file($root . '/public/assets/js/storefront.js'),
    'A dedicated home interface asset is missing.'
);
$check(
    str_contains($html, '/assets/images/logo/logo.svg'),
    'The Sheepy logo is missing from the home page.'
);
$check(
    str_contains($html, 'id="primary-navigation"')
        && str_contains($html, 'data-menu-toggle')
        && str_contains($html, 'aria-expanded="false"'),
    'The accessible responsive navigation controls are missing.'
);
$check(
    str_contains($html, 'id="categories"')
        && str_contains($html, 'id="latest-products-title"')
        && str_contains($html, 'id="lookbook-title"')
        && str_contains($html, 'aria-label="Shopping benefits"'),
    'A required home-page content section is missing.'
);

foreach (['Home', 'Men', 'Women', 'Kids', 'Accessories'] as $navigationLabel) {
    $check(
        preg_match(
            '/>\s*' . preg_quote($navigationLabel, '/') . '\s*<\/a>/',
            $html
        ) === 1,
        sprintf('The %s navigation item is missing.', $navigationLabel)
    );
}

$check(
    !str_contains($html, '<script>alert("unsafe")</script>')
        && str_contains($html, '&lt;script&gt;alert(&quot;unsafe&quot;)&lt;/script&gt;'),
    'Dynamic customer content was not safely escaped.'
);
$check(
    str_contains($html, 'data-add-to-cart') || str_contains($html, 'Our next collection is being prepared.'),
    'The home page has neither product actions nor its empty state.'
);
$check(
    str_contains((string) file_get_contents($root . '/public/assets/css/storefront.css'), '@media (max-width: 40rem)')
        && str_contains((string) file_get_contents($root . '/public/assets/css/storefront.css'), 'prefers-reduced-motion')
        && str_contains((string) file_get_contents($root . '/public/assets/css/storefront.css'), '--rust: #a9663d'),
    'Mobile or reduced-motion home styles are missing.'
);
$check(
    str_contains((string) file_get_contents($root . '/public/assets/js/storefront.js'), "event.key === 'Escape'")
        && str_contains($html, 'class="mobile-tabbar"'),
    'Keyboard dismissal or mobile shortcuts are missing from the home interactions.'
);
$check(
    is_file($root . '/public/assets/images/home/hero.jpg')
        && is_file($root . '/public/assets/images/home/lookbook.jpg')
        && count(glob($root . '/public/assets/images/home/category-*.jpg') ?: []) === 4,
    'A mockup-supplied home image is missing.'
);

fwrite(STDOUT, 'Responsive home interface smoke test passed.' . PHP_EOL);
