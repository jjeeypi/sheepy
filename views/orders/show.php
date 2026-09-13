<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($order->orderNumber) ?> | Sheepy</title>
</head>
<body>
    <header>
        <strong>Sheepy</strong>
        <nav aria-label="Account navigation">
            <a href="<?= $escape($homeUrl) ?>">Home</a>
            <a href="<?= $escape($productsUrl) ?>">Products</a>
            <a href="<?= $escape($ordersUrl) ?>">Orders</a>
        </nav>
        <form method="post" action="<?= $escape($logoutUrl) ?>">
            <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
            <button type="submit">Log out</button>
        </form>
    </header>

    <main>
        <p><a href="<?= $escape($ordersUrl) ?>">← Back to order history</a></p>
        <h1>Order <?= $escape($order->orderNumber) ?></h1>
        <p>Status: <?= $escape(ucfirst($order->status)) ?></p>
        <p>Placed: <?= $escape(date('F j, Y g:i A', strtotime($order->placedAt))) ?></p>

        <section aria-labelledby="order-items-title">
            <h2 id="order-items-title">Items</h2>
            <table>
                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">SKU</th>
                        <th scope="col">Unit price</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Line total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order->items as $item): ?>
                        <tr>
                            <td><?= $escape($item->productName) ?></td>
                            <td><?= $escape($item->productSku) ?></td>
                            <td>$<?= $escape($item->unitPrice) ?></td>
                            <td><?= $escape($item->quantity) ?></td>
                            <td>$<?= $escape($item->lineTotal) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section aria-labelledby="order-summary-title">
            <h2 id="order-summary-title">Order Summary</h2>
            <dl>
                <div><dt>Subtotal</dt><dd>$<?= $escape($order->subtotal) ?></dd></div>
                <div><dt>Shipping</dt><dd>$<?= $escape($order->shippingTotal) ?></dd></div>
                <div><dt>Tax</dt><dd>$<?= $escape($order->taxTotal) ?></dd></div>
                <div><dt>Grand total</dt><dd>$<?= $escape($order->grandTotal) ?></dd></div>
            </dl>
        </section>

        <section aria-labelledby="shipping-address-title">
            <h2 id="shipping-address-title">Shipping Address</h2>
            <address>
                <?= $escape($order->shippingName) ?><br>
                <?= $escape($order->shippingPhone ?? '') ?><br>
                <?= $escape($order->shippingLine1) ?><br>
                <?php if ($order->shippingLine2 !== null): ?>
                    <?= $escape($order->shippingLine2) ?><br>
                <?php endif; ?>
                <?= $escape($order->shippingCity) ?>,
                <?= $escape($order->shippingState ?? '') ?>
                <?= $escape($order->shippingPostalCode) ?><br>
                <?= $escape($order->shippingCountry) ?>
            </address>
        </section>
    </main>
</body>
</html>
