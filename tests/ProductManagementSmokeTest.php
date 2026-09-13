<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Autoloader;
use App\Exceptions\ValidationException;
use App\Models\Product;
use App\Services\ProductImageService;
use App\Validators\ProductValidator;
use RuntimeException;

require dirname(__DIR__) . '/src/Core/Autoloader.php';

Autoloader::register(['App\\' => dirname(__DIR__) . '/src']);

$check = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$validator = new ProductValidator();
$valid = $validator->product([
    'category_id' => '',
    'sku' => ' sheep-001 ',
    'name' => 'Merino Cardigan',
    'description' => 'Soft wool cardigan.',
    'price' => '001299.5',
    'stock_quantity' => '12',
    'is_active' => '1',
]);
$check($valid['errors'] === [], 'Valid product data was rejected.');
$check($valid['data']['sku'] === 'SHEEP-001', 'SKU normalization failed.');
$check($valid['data']['price'] === '1299.50', 'Price normalization failed.');
$check($valid['data']['category_id'] === null, 'Optional category handling failed.');
$check($valid['data']['is_active'] === true, 'Visibility parsing failed.');

$invalid = $validator->product([
    'category_id' => '0',
    'sku' => 'invalid sku!',
    'name' => '',
    'description' => str_repeat('x', 5001),
    'price' => '0',
    'stock_quantity' => '-1',
]);
$check(
    array_keys($invalid['errors']) === [
        'category_id', 'sku', 'name', 'description', 'price', 'stock_quantity',
    ],
    'Invalid product fields were not all rejected.'
);

$unordered = new Product(
    1,
    null,
    null,
    'SHEEP-001',
    'Merino Cardigan',
    'merino-cardigan',
    null,
    '1299.50',
    12,
    true,
    1,
    '/uploads/products/example.jpg',
    'Merino Cardigan',
    0
);
$ordered = new Product(
    2,
    null,
    null,
    'SHEEP-002',
    'Wool Coat',
    'wool-coat',
    null,
    '2499.00',
    4,
    true,
    2,
    '/uploads/products/example-two.jpg',
    'Wool Coat',
    1
);
$check($unordered->canBeDeleted(), 'Unordered product was incorrectly protected.');
$check(!$ordered->canBeDeleted(), 'Ordered product was incorrectly deletable.');

$imageService = new ProductImageService();
$spoofRejected = false;

try {
    $imageService->store([
        'error' => UPLOAD_ERR_OK,
        'tmp_name' => __FILE__,
        'size' => filesize(__FILE__),
        'name' => 'fake.png',
        'type' => 'image/png',
    ], true);
} catch (ValidationException) {
    $spoofRejected = true;
}

$check($spoofRejected, 'A non-HTTP file was accepted as an uploaded image.');

fwrite(STDOUT, 'Admin product management smoke test passed.' . PHP_EOL);
