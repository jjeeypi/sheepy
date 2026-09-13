<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Cart;
use App\Models\CartItem;
use PDO;

final class CartRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?Cart
    {
        $connection = $this->database->connection();
        $statement = $connection->prepare(
            'SELECT cart_id, user_id FROM carts WHERE cart_id = :cart_id LIMIT 1'
        );
        $statement->execute(['cart_id' => $id]);
        $row = $statement->fetch();

        if (!is_array($row) || $row['user_id'] === null) {
            return null;
        }

        return new Cart(
            (int) $row['cart_id'],
            (int) $row['user_id'],
            $this->items($connection, (int) $row['cart_id'])
        );
    }

    public function cartForUser(int $userId): Cart
    {
        $connection = $this->database->connection();
        $cartId = $this->activeCartId($connection, $userId);

        return new Cart(
            $cartId,
            $userId,
            $cartId === null ? [] : $this->items($connection, $cartId)
        );
    }

    public function itemQuantityForUser(int $userId): int
    {
        $statement = $this->database->connection()->prepare(
            "SELECT COALESCE(SUM(ci.quantity), 0)
             FROM cart_items ci
             INNER JOIN carts c ON c.cart_id = ci.cart_id
             WHERE c.cart_id = (
                 SELECT active.cart_id
                 FROM carts active
                 WHERE active.user_id = :user_id AND active.status = 'active'
                 ORDER BY active.cart_id DESC
                 LIMIT 1
             )"
        );
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public function addItem(int $userId, int $productId, int $quantity): void
    {
        $this->database->transaction(function (PDO $connection) use (
            $userId,
            $productId,
            $quantity
        ): void {
            $this->lockUser($connection, $userId);
            $cartId = $this->activeCartId($connection, $userId);

            if ($cartId === null) {
                $createCart = $connection->prepare(
                    "INSERT INTO carts (user_id, session_token, status)
                     VALUES (:user_id, NULL, 'active')"
                );
                $createCart->execute(['user_id' => $userId]);
                $cartId = (int) $connection->lastInsertId();
            }

            $productQuery = $connection->prepare(
                'SELECT price, stock_quantity
                 FROM products
                 WHERE product_id = :product_id
                   AND is_active = 1
                   AND deleted_at IS NULL
                 FOR UPDATE'
            );
            $productQuery->execute(['product_id' => $productId]);
            $product = $productQuery->fetch();

            if (!is_array($product)) {
                throw new NotFoundException('That product is no longer available.');
            }

            $itemQuery = $connection->prepare(
                'SELECT cart_item_id, quantity
                 FROM cart_items
                 WHERE cart_id = :cart_id AND product_id = :product_id
                 FOR UPDATE'
            );
            $itemQuery->execute([
                'cart_id' => $cartId,
                'product_id' => $productId,
            ]);
            $item = $itemQuery->fetch();
            $newQuantity = $quantity + (is_array($item) ? (int) $item['quantity'] : 0);
            $this->assertStock($newQuantity, (int) $product['stock_quantity']);

            if (is_array($item)) {
                $update = $connection->prepare(
                    'UPDATE cart_items
                     SET quantity = :quantity, unit_price = :unit_price
                     WHERE cart_item_id = :cart_item_id'
                );
                $update->execute([
                    'quantity' => $newQuantity,
                    'unit_price' => (string) $product['price'],
                    'cart_item_id' => (int) $item['cart_item_id'],
                ]);
            } else {
                $insert = $connection->prepare(
                    'INSERT INTO cart_items (cart_id, product_id, quantity, unit_price)
                     VALUES (:cart_id, :product_id, :quantity, :unit_price)'
                );
                $insert->execute([
                    'cart_id' => $cartId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => (string) $product['price'],
                ]);
            }

            $this->touchCart($connection, $cartId);
        });
    }

    public function updateItem(int $userId, int $cartItemId, int $quantity): void
    {
        $this->database->transaction(function (PDO $connection) use (
            $userId,
            $cartItemId,
            $quantity
        ): void {
            $itemQuery = $connection->prepare(
                "SELECT ci.cart_id, p.stock_quantity, p.is_active, p.deleted_at
                 FROM cart_items ci
                 INNER JOIN carts c ON c.cart_id = ci.cart_id
                 INNER JOIN products p ON p.product_id = ci.product_id
                 WHERE ci.cart_item_id = :cart_item_id
                   AND c.user_id = :user_id
                   AND c.status = 'active'
                 FOR UPDATE"
            );
            $itemQuery->execute([
                'cart_item_id' => $cartItemId,
                'user_id' => $userId,
            ]);
            $item = $itemQuery->fetch();

            if (!is_array($item)) {
                throw new NotFoundException('That cart item could not be found.');
            }

            if (!(bool) $item['is_active'] || $item['deleted_at'] !== null) {
                throw new ValidationException([
                    'quantity' => 'This product is no longer available. Remove it from your cart.',
                ]);
            }

            $this->assertStock($quantity, (int) $item['stock_quantity']);
            $update = $connection->prepare(
                'UPDATE cart_items SET quantity = :quantity WHERE cart_item_id = :cart_item_id'
            );
            $update->execute([
                'quantity' => $quantity,
                'cart_item_id' => $cartItemId,
            ]);
            $this->touchCart($connection, (int) $item['cart_id']);
        });
    }

    public function removeItem(int $userId, int $cartItemId): void
    {
        $this->database->transaction(function (PDO $connection) use (
            $userId,
            $cartItemId
        ): void {
            $itemQuery = $connection->prepare(
                "SELECT ci.cart_id
                 FROM cart_items ci
                 INNER JOIN carts c ON c.cart_id = ci.cart_id
                 WHERE ci.cart_item_id = :cart_item_id
                   AND c.user_id = :user_id
                   AND c.status = 'active'
                 FOR UPDATE"
            );
            $itemQuery->execute([
                'cart_item_id' => $cartItemId,
                'user_id' => $userId,
            ]);
            $cartId = $itemQuery->fetchColumn();

            if ($cartId === false) {
                throw new NotFoundException('That cart item could not be found.');
            }

            $delete = $connection->prepare(
                'DELETE FROM cart_items WHERE cart_item_id = :cart_item_id'
            );
            $delete->execute(['cart_item_id' => $cartItemId]);
            $this->touchCart($connection, (int) $cartId);
        });
    }

    private function lockUser(PDO $connection, int $userId): void
    {
        $statement = $connection->prepare(
            'SELECT user_id FROM users WHERE user_id = :user_id FOR UPDATE'
        );
        $statement->execute(['user_id' => $userId]);

        if ($statement->fetchColumn() === false) {
            throw new NotFoundException('The cart owner could not be found.');
        }
    }

    private function activeCartId(PDO $connection, int $userId): ?int
    {
        $statement = $connection->prepare(
            "SELECT cart_id
             FROM carts
             WHERE user_id = :user_id AND status = 'active'
             ORDER BY cart_id DESC
             LIMIT 1"
        );
        $statement->execute(['user_id' => $userId]);
        $cartId = $statement->fetchColumn();

        return $cartId === false ? null : (int) $cartId;
    }

    /** @return list<CartItem> */
    private function items(PDO $connection, int $cartId): array
    {
        $statement = $connection->prepare(
            'SELECT
                ci.cart_item_id,
                ci.product_id,
                ci.quantity,
                ci.unit_price,
                p.slug,
                p.name,
                p.description,
                p.stock_quantity,
                p.is_active,
                p.deleted_at,
                pi.url AS image_url,
                pi.alt_text AS image_alt
             FROM cart_items ci
             INNER JOIN products p ON p.product_id = ci.product_id
             LEFT JOIN product_images pi ON pi.product_image_id = (
                 SELECT selected_image.product_image_id
                 FROM product_images selected_image
                 WHERE selected_image.product_id = p.product_id
                 ORDER BY selected_image.sort_order ASC, selected_image.product_image_id ASC
                 LIMIT 1
             )
             WHERE ci.cart_id = :cart_id
             ORDER BY ci.created_at DESC, ci.cart_item_id DESC'
        );
        $statement->execute(['cart_id' => $cartId]);
        $items = [];

        while (($row = $statement->fetch()) !== false) {
            $items[] = new CartItem(
                (int) $row['cart_item_id'],
                (int) $row['product_id'],
                (string) $row['slug'],
                (string) $row['name'],
                $row['description'] === null ? null : (string) $row['description'],
                (string) $row['unit_price'],
                (int) $row['quantity'],
                (int) $row['stock_quantity'],
                (bool) $row['is_active']
                    && $row['deleted_at'] === null
                    && (int) $row['stock_quantity'] > 0,
                $row['image_url'] === null ? null : (string) $row['image_url'],
                $row['image_alt'] === null ? null : (string) $row['image_alt']
            );
        }

        return $items;
    }

    private function assertStock(int $quantity, int $stockQuantity): void
    {
        if ($stockQuantity < 1) {
            throw new ValidationException([
                'quantity' => 'This product is currently out of stock.',
            ]);
        }

        if ($quantity > $stockQuantity) {
            throw new ValidationException([
                'quantity' => sprintf(
                    'Only %d item%s currently available.',
                    $stockQuantity,
                    $stockQuantity === 1 ? ' is' : 's are'
                ),
            ]);
        }
    }

    private function touchCart(PDO $connection, int $cartId): void
    {
        $statement = $connection->prepare(
            'UPDATE carts SET updated_at = CURRENT_TIMESTAMP WHERE cart_id = :cart_id'
        );
        $statement->execute(['cart_id' => $cartId]);
    }
}
