<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="View your Sheepy order history and access your confirmed receipts.">
    <title>Order History | Sheepy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;1,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/storefront.css">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/orders.css">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/cart.css">
    <script src="<?= $escape($basePath) ?>/assets/js/storefront.js" defer></script>
    <script src="<?= $escape($basePath) ?>/assets/js/cart.js" defer></script>
</head>
<body class="storefront-page orders-page">
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <?php $activeNavigation = 'orders'; ?>
    <?php require dirname(__DIR__) . '/storefront/_header.php'; ?>

    <main id="main-content" class="orders-main section-shell">

        <header class="orders-masthead">
            <p class="orders-eyebrow">Your account</p>
            <h1>Order history</h1>
            <p>Every confirmed order is saved here. Click any order to view its receipt.</p>
        </header>

        <?php if ($orders === []): ?>
            <div class="orders-empty" style="margin-top:2.5rem;">
                <h2>Nothing here yet.</h2>
                <p>Once you place your first order it will appear here.</p>
                <a href="<?= $escape($productsUrl) ?>">Browse the collection</a>
            </div>

        <?php else: ?>
            <div class="orders-list" aria-label="Your past orders">
                <?php foreach ($orders as $order): ?>
                    <?php
                    $itemLabel = count($order->items) === 1 ? '1 item' : count($order->items) . ' items';
                    $placed    = date('M j, Y', strtotime($order->placedAt));
                    $badgeClass = strtolower($order->status) === 'confirmed' ? 'is-confirmed' : 'is-cancelled';
                    ?>
                    <a
                        class="order-card"
                        href="<?= $escape($ordersUrl . '/' . $order->orderNumber) ?>"
                        aria-label="Order <?= $escape($order->orderNumber) ?>, placed <?= $escape($placed) ?>, total $<?= $escape($order->grandTotal) ?>"
                    >
                        <h2 class="order-card-number"><?= $escape($order->orderNumber) ?></h2>
                        <p class="order-card-meta">
                            <span><?= $escape($placed) ?></span>
                            <span><?= $escape($itemLabel) ?></span>
                            <span class="order-status-badge <?= $escape($badgeClass) ?>">
                                <?= $escape(ucfirst($order->status)) ?>
                            </span>
                        </p>
                        <span class="order-card-total">$<?= $escape($order->grandTotal) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

    <?php require dirname(__DIR__) . '/storefront/_footer.php'; ?>
</body>
</html>
