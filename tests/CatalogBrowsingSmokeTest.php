<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Autoloader;
use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Models\ProductImage;
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
$categories = new CategoryRepository($database);
$products = new ProductRepository($database);
$catalog = new CatalogService($products, $categories);
$navigation = $catalog->navigation();
$departmentNames = array_map(
    static fn (array $group): string => $group['department']->name,
    $navigation
);

$check(
    $departmentNames === ['Men', 'Women', 'Kids', 'Accessories'],
    'The storefront departments are unavailable or incorrectly ordered.'
);

foreach ($navigation as $group) {
    $check($group['children'] !== [], 'A storefront department has no product types.');

    foreach ($group['children'] as $child) {
        $check(
            $child->parentId === $group['department']->id,
            'A product type is attached to the wrong department.'
        );
    }
}

$all = $catalog->browse(null, '', 1);
$men = $catalog->browse('men', '', 1);
$menCategoryIds = $categories->selfAndChildIds(
    $categories->findBySlug('men')?->id ?? 0
);

foreach ($all['products'] as $product) {
    $check($product->isActive, 'An inactive product appeared in the storefront.');
}

foreach ($men['products'] as $product) {
    $check(
        $product->categoryId !== null && in_array($product->categoryId, $menCategoryIds, true),
        'A product outside Men appeared in the Men catalogue.'
    );
}

if ($all['products'] !== []) {
    $detail = $catalog->product($all['products'][0]->slug);
    $check($detail['product']->isActive, 'An inactive product detail was returned.');

    foreach ($detail['images'] as $image) {
        $check($image instanceof ProductImage, 'Product image hydration failed.');
        $check(str_starts_with($image->url, '/uploads/products/'), 'Product image URL is invalid.');
    }
}

$missingCategoryRejected = false;

try {
    $catalog->browse('category-that-does-not-exist', '', 1);
} catch (NotFoundException) {
    $missingCategoryRejected = true;
}

$check($missingCategoryRejected, 'A nonexistent category was accepted.');

fwrite(STDOUT, 'Phase 4 catalogue browsing smoke test passed.' . PHP_EOL);
