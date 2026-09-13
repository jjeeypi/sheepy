<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Autoloader;
use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Repositories\CartRepository;
use App\Services\CartService;
use App\Validators\CartValidator;
use PDO;
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
$repository = new CartRepository($database);
$service = new CartService($repository, new CartValidator());
$suffix = bin2hex(random_bytes(6));
$ownerId = null;
$otherUserId = null;
$productId = null;

$createUser = static function (PDO $connection, string $suffix): int {
    $statement = $connection->prepare(
        "INSERT INTO users (username, email, password_hash, role)
         VALUES (:username, :email, :password_hash, 'customer')"
    );
    $statement->execute([
        'username' => 'cart_test_' . $suffix,
        'email' => 'cart_test_' . $suffix . '@example.test',
        'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
    ]);

    return (int) $connection->lastInsertId();
};

try {
    $ownerId = $createUser($connection, $suffix . '_owner');
    $otherUserId = $createUser($connection, $suffix . '_other');

    $productStatement = $connection->prepare(
        'INSERT INTO products (
            category_id, sku, name, slug, description, price, stock_quantity, is_active
         ) VALUES (
            NULL, :sku, :name, :slug, :description, :price, :stock_quantity, 1
         )'
    );
    $productStatement->execute([
        'sku' => 'CART-' . strtoupper($suffix),
        'name' => 'Cart Test Product ' . $suffix,
        'slug' => 'cart-test-product-' . $suffix,
        'description' => 'Temporary product used by the Phase 5 cart smoke test.',
        'price' => '19.95',
        'stock_quantity' => 3,
    ]);
    $productId = (int) $connection->lastInsertId();

    $imageStatement = $connection->prepare(
        'INSERT INTO product_images (product_id, url, alt_text, sort_order)
         VALUES (:product_id, :url, :alt_text, 0)'
    );
    $imageStatement->execute([
        'product_id' => $productId,
        'url' => '/uploads/products/cart-test.jpg',
        'alt_text' => 'Cart Test Product',
    ]);

    $emptyCart = $service->get($ownerId);
    $check($emptyCart->id === null, 'Reading an empty cart should not create a database row.');
    $check($emptyCart->items === [], 'A new customer cart should be empty.');
    $check($emptyCart->subtotal() === '0.00', 'An empty cart subtotal should be zero.');

    $cart = $service->add($ownerId, $productId, 1);
    $check($cart->id !== null, 'Adding a product did not create an active cart.');
    $check(count($cart->items) === 1, 'The added product is missing from the cart.');
    $check($cart->itemCount() === 1, 'The cart badge quantity is incorrect after add.');
    $check($cart->items[0]->lineSubtotal() === '19.95', 'The initial line subtotal is incorrect.');
    $check($cart->items[0]->imageUrl === '/uploads/products/cart-test.jpg', 'The cart thumbnail was not loaded.');
    $check($cart->items[0]->isAvailable, 'An active in-stock product was marked unavailable.');

    $cart = $service->add($ownerId, (string) $productId, '1');
    $check(count($cart->items) === 1, 'Repeated add created a duplicate cart row.');
    $check($cart->items[0]->quantity === 2, 'Repeated add did not increase quantity.');
    $check($cart->subtotal() === '39.90', 'The subtotal after repeated add is incorrect.');

    $activeCartCount = $connection->prepare(
        "SELECT COUNT(*) FROM carts WHERE user_id = :user_id AND status = 'active'"
    );
    $activeCartCount->execute(['user_id' => $ownerId]);
    $check((int) $activeCartCount->fetchColumn() === 1, 'A customer has multiple active carts.');

    $stockRejected = false;

    try {
        $service->add($ownerId, $productId, 2);
    } catch (ValidationException) {
        $stockRejected = true;
    }

    $check($stockRejected, 'Adding more than available stock was accepted.');
    $check($service->get($ownerId)->items[0]->quantity === 2, 'A rejected add changed the cart.');

    $itemId = $cart->items[0]->id;
    $cart = $service->update($ownerId, $itemId, 3);
    $check($cart->items[0]->quantity === 3, 'Updating cart quantity failed.');
    $check($cart->subtotal() === '59.85', 'The updated cart subtotal is incorrect.');

    $invalidQuantityRejected = false;

    try {
        $service->update($ownerId, $itemId, 0);
    } catch (ValidationException) {
        $invalidQuantityRejected = true;
    }

    $check($invalidQuantityRejected, 'A zero cart quantity was accepted.');

    $crossUserUpdateRejected = false;

    try {
        $service->update($otherUserId, $itemId, 1);
    } catch (NotFoundException) {
        $crossUserUpdateRejected = true;
    }

    $check($crossUserUpdateRejected, 'A customer updated another customer\'s cart item.');

    $crossUserRemoveRejected = false;

    try {
        $service->remove($otherUserId, $itemId);
    } catch (NotFoundException) {
        $crossUserRemoveRejected = true;
    }

    $check($crossUserRemoveRejected, 'A customer removed another customer\'s cart item.');

    $deactivate = $connection->prepare(
        'UPDATE products SET is_active = 0 WHERE product_id = :product_id'
    );
    $deactivate->execute(['product_id' => $productId]);
    $check(!$service->get($ownerId)->items[0]->isAvailable, 'An inactive cart product was marked available.');

    $unavailableUpdateRejected = false;

    try {
        $service->update($ownerId, $itemId, 1);
    } catch (ValidationException) {
        $unavailableUpdateRejected = true;
    }

    $check($unavailableUpdateRejected, 'An unavailable product quantity was updated.');

    $cart = $service->remove($ownerId, $itemId);
    $check($cart->items === [], 'Removing a cart item failed.');
    $check($cart->itemCount() === 0, 'The cart badge quantity did not return to zero.');
    $check($cart->subtotal() === '0.00', 'The cart subtotal did not return to zero.');

    fwrite(STDOUT, 'Phase 5 cart system smoke test passed.' . PHP_EOL);
} finally {
    if ($ownerId !== null || $otherUserId !== null) {
        $deleteUser = $connection->prepare('DELETE FROM users WHERE user_id = :user_id');

        foreach ([$ownerId, $otherUserId] as $userId) {
            if ($userId !== null) {
                $deleteUser->execute(['user_id' => $userId]);
            }
        }
    }

    if ($productId !== null) {
        $deleteProduct = $connection->prepare(
            'DELETE FROM products WHERE product_id = :product_id'
        );
        $deleteProduct->execute(['product_id' => $productId]);
    }
}
