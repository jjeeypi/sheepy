<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Autoloader;
use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Services\CartService;
use App\Services\CheckoutPricing;
use App\Services\CheckoutService;
use App\Validators\CartValidator;
use App\Validators\CheckoutValidator;
use PDO;
use RuntimeException;

$root = dirname(__DIR__);

require $root . '/src/Core/Autoloader.php';
Autoloader::register(['App\\' => $root . '/src']);
date_default_timezone_set('Asia/Singapore');

$check = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$database = new Database(require $root . '/config/database.php');
$connection = $database->connection();
$pricing = new CheckoutPricing();
$orders = new OrderRepository($database, $pricing);
$checkout = new CheckoutService($orders, new CheckoutValidator());
$cart = new CartService(new CartRepository($database), new CartValidator());
$suffix = bin2hex(random_bytes(6));
$ownerId = null;
$otherUserId = null;
$productIds = [];

$shipping = [
    'shipping_name' => 'Phase Six Customer',
    'shipping_phone' => '+63 912 345 6789',
    'shipping_line1' => '123 Test Street',
    'shipping_line2' => 'Unit 6',
    'shipping_city' => 'Manila',
    'shipping_state' => 'Metro Manila',
    'shipping_postal_code' => '1000',
    'shipping_country' => 'Philippines',
];

$createUser = static function (PDO $connection, string $suffix): int {
    $statement = $connection->prepare(
        "INSERT INTO users (username, email, password_hash, role)
         VALUES (:username, :email, :password_hash, 'customer')"
    );
    $statement->execute([
        'username' => 'checkout_test_' . $suffix,
        'email' => 'checkout_test_' . $suffix . '@example.test',
        'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
    ]);

    return (int) $connection->lastInsertId();
};

$createProduct = static function (
    PDO $connection,
    string $suffix,
    string $label,
    string $price,
    int $stock
): int {
    $statement = $connection->prepare(
        'INSERT INTO products (
            category_id, sku, name, slug, description, price, stock_quantity, is_active
         ) VALUES (
            NULL, :sku, :name, :slug, :description, :price, :stock_quantity, 1
         )'
    );
    $statement->execute([
        'sku' => 'CHECKOUT-' . strtoupper($label) . '-' . strtoupper($suffix),
        'name' => 'Checkout ' . $label . ' ' . $suffix,
        'slug' => 'checkout-' . strtolower($label) . '-' . $suffix,
        'description' => 'Temporary Phase 6 checkout test product.',
        'price' => $price,
        'stock_quantity' => $stock,
    ]);

    return (int) $connection->lastInsertId();
};

try {
    $ownerId = $createUser($connection, $suffix . '_owner');
    $otherUserId = $createUser($connection, $suffix . '_other');
    $firstProductId = $createProduct($connection, $suffix, 'Alpha', '10.00', 5);
    $secondProductId = $createProduct($connection, $suffix, 'Beta', '15.50', 1);
    $productIds = [$firstProductId, $secondProductId];

    $shippingRejected = false;

    try {
        $checkout->confirm($ownerId, []);
    } catch (ValidationException $exception) {
        $shippingRejected = isset($exception->errors()['shipping_name']);
    }

    $check($shippingRejected, 'Incomplete shipping details were accepted.');

    $cart->add($ownerId, $firstProductId, 2);
    $cart->add($ownerId, $secondProductId, 1);
    $preview = $checkout->preview($ownerId);
    $check(count($preview->items) === 2, 'The checkout preview did not include every cart item.');
    $check($preview->subtotal === '35.50', 'The initial checkout subtotal is incorrect.');
    $check($preview->shippingTotal === '6.00', 'The standard shipping total is incorrect.');
    $check($preview->taxTotal === '0.00', 'The configured tax total is incorrect.');
    $check($preview->grandTotal === '41.50', 'The initial grand total is incorrect.');

    $orderCount = $connection->prepare(
        'SELECT COUNT(*) FROM orders WHERE user_id = :user_id'
    );
    $orderCount->execute(['user_id' => $ownerId]);
    $check((int) $orderCount->fetchColumn() === 0, 'Opening or cancelling checkout created an order.');
    $check($cart->get($ownerId)->itemCount() === 3, 'Cancelling checkout changed the cart.');

    $setStock = $connection->prepare(
        'UPDATE products SET stock_quantity = :stock WHERE product_id = :product_id'
    );
    $setStock->execute(['stock' => 0, 'product_id' => $secondProductId]);
    $stockRejected = false;

    try {
        $checkout->confirm($ownerId, $shipping);
    } catch (ValidationException $exception) {
        $stockRejected = isset($exception->errors()['cart']);
    }

    $check($stockRejected, 'Checkout accepted a product without sufficient stock.');
    $orderCount->execute(['user_id' => $ownerId]);
    $check((int) $orderCount->fetchColumn() === 0, 'A rejected checkout left an order row.');
    $check($cart->get($ownerId)->itemCount() === 3, 'A rejected checkout converted the cart.');

    $firstStock = $connection->prepare(
        'SELECT stock_quantity FROM products WHERE product_id = :product_id'
    );
    $firstStock->execute(['product_id' => $firstProductId]);
    $check((int) $firstStock->fetchColumn() === 5, 'A rejected checkout partially reduced stock.');

    $setStock->execute(['stock' => 1, 'product_id' => $secondProductId]);
    $priceChange = $connection->prepare(
        'UPDATE products SET price = :price WHERE product_id = :product_id'
    );
    $priceChange->execute(['price' => '12.00', 'product_id' => $firstProductId]);
    $refreshedPreview = $checkout->preview($ownerId);
    $check($refreshedPreview->subtotal === '39.50', 'Checkout did not refresh current prices.');
    $check($refreshedPreview->grandTotal === '45.50', 'The refreshed grand total is incorrect.');

    $order = $checkout->confirm($ownerId, $shipping);
    $check($order->status === 'confirmed', 'The new order was not confirmed.');
    $check(
        preg_match('/^ORD-\d{8}-\d{6}$/', $order->orderNumber) === 1,
        'The order number format is invalid.'
    );
    $check($order->subtotal === '39.50', 'The saved order subtotal is incorrect.');
    $check($order->shippingTotal === '6.00', 'The saved shipping total is incorrect.');
    $check($order->taxTotal === '0.00', 'The saved tax total is incorrect.');
    $check($order->grandTotal === '45.50', 'The saved grand total is incorrect.');
    $check($order->shippingName === $shipping['shipping_name'], 'Shipping details were not snapshotted.');
    $check(count($order->items) === 2, 'The order item snapshots are incomplete.');

    $itemsByProduct = [];

    foreach ($order->items as $item) {
        $itemsByProduct[$item->productId] = $item;
    }

    $check($itemsByProduct[$firstProductId]->unitPrice === '12.00', 'Current price was not snapshotted.');
    $check($itemsByProduct[$firstProductId]->quantity === 2, 'First item quantity is incorrect.');
    $check($itemsByProduct[$firstProductId]->lineTotal === '24.00', 'First line total is incorrect.');
    $check($itemsByProduct[$secondProductId]->lineTotal === '15.50', 'Second line total is incorrect.');

    $cartStatus = $connection->prepare(
        "SELECT status FROM carts WHERE user_id = :user_id ORDER BY cart_id DESC LIMIT 1"
    );
    $cartStatus->execute(['user_id' => $ownerId]);
    $check($cartStatus->fetchColumn() === 'converted', 'The purchased cart was not converted.');
    $check($cart->get($ownerId)->itemCount() === 0, 'A converted cart remained active.');

    $stockQuery = $connection->prepare(
        'SELECT stock_quantity FROM products WHERE product_id = :product_id'
    );
    $stockQuery->execute(['product_id' => $firstProductId]);
    $check((int) $stockQuery->fetchColumn() === 3, 'First product stock was not decremented.');
    $stockQuery->execute(['product_id' => $secondProductId]);
    $check((int) $stockQuery->fetchColumn() === 0, 'Second product stock was not decremented.');

    $history = $checkout->history($ownerId);
    $check(count($history) === 1, 'The confirmed order is missing from order history.');
    $receipt = $checkout->receipt($ownerId, $order->orderNumber);
    $check($receipt->id === $order->id, 'The owner could not load the order receipt.');

    $crossUserRejected = false;

    try {
        $checkout->receipt($otherUserId, $order->orderNumber);
    } catch (NotFoundException) {
        $crossUserRejected = true;
    }

    $check($crossUserRejected, 'A customer accessed another customer\'s receipt.');

    $originalSnapshotName = $itemsByProduct[$firstProductId]->productName;
    $catalogueChange = $connection->prepare(
        'UPDATE products
         SET name = :name, sku = :sku, price = :price
         WHERE product_id = :product_id'
    );
    $catalogueChange->execute([
        'name' => 'Changed After Purchase',
        'sku' => 'CHANGED-' . strtoupper($suffix),
        'price' => '99.99',
        'product_id' => $firstProductId,
    ]);
    $unchangedReceipt = $checkout->receipt($ownerId, $order->orderNumber);
    $check(
        $unchangedReceipt->items[0]->productName === $originalSnapshotName,
        'A later product edit changed the order snapshot.'
    );
    $check(
        $unchangedReceipt->items[0]->unitPrice === '12.00',
        'A later price edit changed the order snapshot.'
    );

    $modalScript = file_get_contents($root . '/public/assets/js/cart.js');
    $check(is_string($modalScript), 'The checkout modal script could not be read.');
    $check(
        str_contains($modalScript, "cancel.addEventListener('click', () => renderCart(currentCart))"),
        'Checkout Cancel does not return to the unchanged cart.'
    );
    $check(
        str_contains($modalScript, "modalHeader('Purchased Successfully!')"),
        'The purchase success popup is missing.'
    );

    fwrite(STDOUT, 'Phase 6 checkout confirmation smoke test passed.' . PHP_EOL);
} finally {
    if ($ownerId !== null) {
        $deleteOrders = $connection->prepare('DELETE FROM orders WHERE user_id = :user_id');
        $deleteOrders->execute(['user_id' => $ownerId]);
    }

    if ($ownerId !== null || $otherUserId !== null) {
        $deleteUser = $connection->prepare('DELETE FROM users WHERE user_id = :user_id');

        foreach ([$ownerId, $otherUserId] as $userId) {
            if ($userId !== null) {
                $deleteUser->execute(['user_id' => $userId]);
            }
        }
    }

    if ($productIds !== []) {
        $deleteProduct = $connection->prepare(
            'DELETE FROM products WHERE product_id = :product_id'
        );

        foreach ($productIds as $productId) {
            $deleteProduct->execute(['product_id' => $productId]);
        }
    }
}
