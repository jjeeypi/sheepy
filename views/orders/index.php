<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order History | Sheepy</title>
</head>
<body>
    <header>
        <strong>Sheepy</strong>
        <nav aria-label="Account navigation">
            <a href="<?= $escape($homeUrl) ?>">Home</a>
            <a href="<?= $escape($productsUrl) ?>">Products</a>
            <a href="<?= $escape($ordersUrl) ?>" aria-current="page">Orders</a>
        </nav>
        <form method="post" action="<?= $escape($logoutUrl) ?>">
            <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
            <button type="submit">Log out</button>
        </form>
    </header>

    <main>
        <h1>Order History</h1>

        <?php if ($orders === []): ?>
            <p>You have not placed any orders yet.</p>
            <p><a href="<?= $escape($productsUrl) ?>">Browse products</a></p>
        <?php else: ?>
            <div aria-label="Past orders">
                <?php foreach ($orders as $order): ?>
                    <article>
                        <h2>
                            <a href="<?= $escape($ordersUrl . '/' . $order->orderNumber) ?>">
                                <?= $escape($order->orderNumber) ?>
                            </a>
                        </h2>
                        <p>Status: <?= $escape(ucfirst($order->status)) ?></p>
                        <p>Placed: <?= $escape(date('F j, Y g:i A', strtotime($order->placedAt))) ?></p>
                        <p><?= $escape(count($order->items)) ?> product line<?= count($order->items) === 1 ? '' : 's' ?></p>
                        <p>Total: $<?= $escape($order->grandTotal) ?></p>
                        <p>
                            <a href="<?= $escape($ordersUrl . '/' . $order->orderNumber) ?>">
                                View receipt
                            </a>
                        </p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
