<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\CheckoutItem;
use App\Models\CheckoutSummary;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CheckoutPricing;
use PDO;
use RuntimeException;

final class OrderRepository implements RepositoryInterface
{
    private const ORDER_SELECT = <<<'SQL'
        SELECT
            order_id,
            user_id,
            order_number,
            status,
            subtotal,
            shipping_total,
            tax_total,
            grand_total,
            shipping_name,
            shipping_phone,
            shipping_line1,
            shipping_line2,
            shipping_city,
            shipping_state,
            shipping_postal_code,
            shipping_country,
            placed_at
        FROM orders
        SQL;

    public function __construct(
        private readonly Database $database,
        private readonly CheckoutPricing $pricing
    ) {
    }

    public function findById(int $id): ?Order
    {
        return $this->loadOrder(
            $this->database->connection(),
            'order_id = :order_id',
            ['order_id' => $id]
        );
    }

    /** @return list<Order> */
    public function ordersForUser(int $userId): array
    {
        $connection = $this->database->connection();
        $statement = $connection->prepare(
            self::ORDER_SELECT
            . ' WHERE user_id = :user_id ORDER BY placed_at DESC, order_id DESC'
        );
        $statement->execute(['user_id' => $userId]);
        $orders = [];

        while (($row = $statement->fetch()) !== false) {
            $orders[] = $this->hydrateOrder($connection, $row);
        }

        return $orders;
    }

    public function findForUserByNumber(int $userId, string $orderNumber): ?Order
    {
        return $this->loadOrder(
            $this->database->connection(),
            'user_id = :user_id AND order_number = :order_number',
            ['user_id' => $userId, 'order_number' => $orderNumber]
        );
    }

    public function checkoutForUser(int $userId): CheckoutSummary
    {
        $connection = $this->database->connection();
        $cartId = $this->activeCartId($connection, $userId, false);
        $items = $cartId === null ? [] : $this->checkoutItems($connection, $cartId);

        return $this->pricing->summarize($items);
    }

    /**
     * @param array{
     *     shipping_name: string,
     *     shipping_phone: string,
     *     shipping_line1: string,
     *     shipping_line2: ?string,
     *     shipping_city: string,
     *     shipping_state: string,
     *     shipping_postal_code: string,
     *     shipping_country: string
     * } $shipping
     */
    public function confirmFromActiveCart(int $userId, array $shipping): Order
    {
        return $this->database->transaction(function (PDO $connection) use (
            $userId,
            $shipping
        ): Order {
            $this->lockUser($connection, $userId);
            $cartId = $this->activeCartId($connection, $userId, true);

            if ($cartId === null) {
                throw new ValidationException([
                    'cart' => 'Your cart is empty. Add a product before checking out.',
                ], 'Your cart cannot be checked out.');
            }

            $items = $this->lockedCheckoutItems($connection, $cartId);

            if ($items === []) {
                throw new ValidationException([
                    'cart' => 'Your cart is empty. Add a product before checking out.',
                ], 'Your cart cannot be checked out.');
            }

            foreach ($items as $item) {
                if (!$item->isAvailable) {
                    throw new ValidationException([
                        'cart' => sprintf('%s is no longer available.', $item->productName),
                    ], 'Your cart has an unavailable product.');
                }

                if ($item->quantity > $item->stockQuantity) {
                    throw new ValidationException([
                        'cart' => sprintf(
                            '%s only has %d item%s available. Update your cart quantity.',
                            $item->productName,
                            $item->stockQuantity,
                            $item->stockQuantity === 1 ? '' : 's'
                        ),
                    ], 'A product does not have enough stock.');
                }
            }

            $summary = $this->pricing->summarize($items);
            $orderNumber = $this->uniqueOrderNumber($connection);
            $insertOrder = $connection->prepare(
                "INSERT INTO orders (
                    user_id,
                    order_number,
                    status,
                    subtotal,
                    shipping_total,
                    tax_total,
                    grand_total,
                    shipping_name,
                    shipping_phone,
                    shipping_line1,
                    shipping_line2,
                    shipping_city,
                    shipping_state,
                    shipping_postal_code,
                    shipping_country
                 ) VALUES (
                    :user_id,
                    :order_number,
                    'confirmed',
                    :subtotal,
                    :shipping_total,
                    :tax_total,
                    :grand_total,
                    :shipping_name,
                    :shipping_phone,
                    :shipping_line1,
                    :shipping_line2,
                    :shipping_city,
                    :shipping_state,
                    :shipping_postal_code,
                    :shipping_country
                 )"
            );
            $insertOrder->execute([
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'subtotal' => $summary->subtotal,
                'shipping_total' => $summary->shippingTotal,
                'tax_total' => $summary->taxTotal,
                'grand_total' => $summary->grandTotal,
                ...$shipping,
            ]);
            $orderId = (int) $connection->lastInsertId();
            $insertItem = $connection->prepare(
                'INSERT INTO order_items (
                    order_id,
                    product_id,
                    product_name,
                    product_sku,
                    unit_price,
                    quantity,
                    line_total
                 ) VALUES (
                    :order_id,
                    :product_id,
                    :product_name,
                    :product_sku,
                    :unit_price,
                    :quantity,
                    :line_total
                 )'
            );
            $decrementStock = $connection->prepare(
                'UPDATE products
                 SET stock_quantity = stock_quantity - :ordered_quantity
                 WHERE product_id = :product_id AND stock_quantity >= :required_quantity'
            );

            foreach ($items as $item) {
                $insertItem->execute([
                    'order_id' => $orderId,
                    'product_id' => $item->productId,
                    'product_name' => $item->productName,
                    'product_sku' => $item->productSku,
                    'unit_price' => $item->unitPrice,
                    'quantity' => $item->quantity,
                    'line_total' => $item->lineTotal(),
                ]);
                $decrementStock->execute([
                    'ordered_quantity' => $item->quantity,
                    'product_id' => $item->productId,
                    'required_quantity' => $item->quantity,
                ]);

                if ($decrementStock->rowCount() !== 1) {
                    throw new ValidationException([
                        'cart' => sprintf(
                            '%s no longer has enough stock. Nothing was charged or ordered.',
                            $item->productName
                        ),
                    ], 'Stock changed while your order was being confirmed.');
                }
            }

            $convertCart = $connection->prepare(
                "UPDATE carts
                 SET status = 'converted', updated_at = CURRENT_TIMESTAMP
                 WHERE cart_id = :cart_id AND status = 'active'"
            );
            $convertCart->execute(['cart_id' => $cartId]);

            if ($convertCart->rowCount() !== 1) {
                throw new RuntimeException('The completed cart could not be converted.');
            }

            $order = $this->loadOrder(
                $connection,
                'order_id = :order_id',
                ['order_id' => $orderId]
            );

            if (!$order instanceof Order) {
                throw new RuntimeException('The confirmed order could not be loaded.');
            }

            return $order;
        });
    }

    private function activeCartId(PDO $connection, int $userId, bool $lock): ?int
    {
        $statement = $connection->prepare(
            "SELECT cart_id
             FROM carts
             WHERE user_id = :user_id AND status = 'active'
             ORDER BY cart_id DESC
             LIMIT 1"
            . ($lock ? ' FOR UPDATE' : '')
        );
        $statement->execute(['user_id' => $userId]);
        $cartId = $statement->fetchColumn();

        return $cartId === false ? null : (int) $cartId;
    }

    /** @return list<CheckoutItem> */
    private function checkoutItems(PDO $connection, int $cartId): array
    {
        $statement = $connection->prepare(
            'SELECT
                ci.cart_item_id,
                ci.product_id,
                ci.quantity,
                p.name,
                p.sku,
                p.price,
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
             ORDER BY ci.created_at ASC, ci.cart_item_id ASC'
        );
        $statement->execute(['cart_id' => $cartId]);

        return $this->hydrateCheckoutItems($statement->fetchAll());
    }

    /** @return list<CheckoutItem> */
    private function lockedCheckoutItems(PDO $connection, int $cartId): array
    {
        $statement = $connection->prepare(
            'SELECT
                ci.cart_item_id,
                ci.product_id,
                ci.quantity,
                p.name,
                p.sku,
                p.price,
                p.stock_quantity,
                p.is_active,
                p.deleted_at,
                NULL AS image_url,
                NULL AS image_alt
             FROM cart_items ci
             INNER JOIN products p ON p.product_id = ci.product_id
             WHERE ci.cart_id = :cart_id
             ORDER BY ci.cart_item_id ASC
             FOR UPDATE'
        );
        $statement->execute(['cart_id' => $cartId]);

        return $this->hydrateCheckoutItems($statement->fetchAll());
    }

    /** @param list<array<string, mixed>> $rows @return list<CheckoutItem> */
    private function hydrateCheckoutItems(array $rows): array
    {
        return array_map(static fn (array $row): CheckoutItem => new CheckoutItem(
            (int) $row['cart_item_id'],
            (int) $row['product_id'],
            (string) $row['name'],
            (string) $row['sku'],
            (string) $row['price'],
            (int) $row['quantity'],
            (int) $row['stock_quantity'],
            (bool) $row['is_active']
                && $row['deleted_at'] === null
                && (int) $row['stock_quantity'] > 0,
            $row['image_url'] === null ? null : (string) $row['image_url'],
            $row['image_alt'] === null ? null : (string) $row['image_alt']
        ), $rows);
    }

    private function lockUser(PDO $connection, int $userId): void
    {
        $statement = $connection->prepare(
            'SELECT user_id FROM users WHERE user_id = :user_id FOR UPDATE'
        );
        $statement->execute(['user_id' => $userId]);

        if ($statement->fetchColumn() === false) {
            throw new NotFoundException('The customer account could not be found.');
        }
    }

    private function uniqueOrderNumber(PDO $connection): string
    {
        $exists = $connection->prepare(
            'SELECT 1 FROM orders WHERE order_number = :order_number LIMIT 1'
        );

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $candidate = sprintf('ORD-%s-%06d', date('Ymd'), random_int(0, 999999));
            $exists->execute(['order_number' => $candidate]);

            if ($exists->fetchColumn() === false) {
                return $candidate;
            }
        }

        throw new RuntimeException('A unique order number could not be generated.');
    }

    /** @param array<string, int|string> $parameters */
    private function loadOrder(
        PDO $connection,
        string $condition,
        array $parameters
    ): ?Order {
        $statement = $connection->prepare(
            self::ORDER_SELECT . ' WHERE ' . $condition . ' LIMIT 1'
        );
        $statement->execute($parameters);
        $row = $statement->fetch();

        return is_array($row) ? $this->hydrateOrder($connection, $row) : null;
    }

    /** @param array<string, mixed> $row */
    private function hydrateOrder(PDO $connection, array $row): Order
    {
        return new Order(
            (int) $row['order_id'],
            (int) $row['user_id'],
            (string) $row['order_number'],
            (string) $row['status'],
            (string) $row['subtotal'],
            (string) $row['shipping_total'],
            (string) $row['tax_total'],
            (string) $row['grand_total'],
            (string) $row['shipping_name'],
            $row['shipping_phone'] === null ? null : (string) $row['shipping_phone'],
            (string) $row['shipping_line1'],
            $row['shipping_line2'] === null ? null : (string) $row['shipping_line2'],
            (string) $row['shipping_city'],
            $row['shipping_state'] === null ? null : (string) $row['shipping_state'],
            (string) $row['shipping_postal_code'],
            (string) $row['shipping_country'],
            (string) $row['placed_at'],
            $this->orderItems($connection, (int) $row['order_id'])
        );
    }

    /** @return list<OrderItem> */
    private function orderItems(PDO $connection, int $orderId): array
    {
        $statement = $connection->prepare(
            'SELECT
                order_item_id,
                product_id,
                product_name,
                product_sku,
                unit_price,
                quantity,
                line_total
             FROM order_items
             WHERE order_id = :order_id
             ORDER BY order_item_id ASC'
        );
        $statement->execute(['order_id' => $orderId]);
        $items = [];

        while (($row = $statement->fetch()) !== false) {
            $items[] = new OrderItem(
                (int) $row['order_item_id'],
                $row['product_id'] === null ? null : (int) $row['product_id'],
                (string) $row['product_name'],
                (string) $row['product_sku'],
                (string) $row['unit_price'],
                (int) $row['quantity'],
                (string) $row['line_total']
            );
        }

        return $items;
    }
}
