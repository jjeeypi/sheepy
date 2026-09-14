<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Order <?= $escape($order->orderNumber) ?> receipt — Sheepy.">
    <title>Order <?= $escape($order->orderNumber) ?> | Sheepy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;1,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/storefront.css">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/orders.css">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/cart.css">
    <script src="<?= $escape($basePath) ?>/assets/js/storefront.js" defer></script>
    <script src="<?= $escape($basePath) ?>/assets/js/cart.js" defer></script>
    <!-- Tailwind CSS v4 -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-bone: #f6f3ec;
            --color-paper: #fcfaf6;
            --color-ink: #2a2621;
            --color-ink-soft: #7a7266;
            --color-rust: #a9663d;
            --color-rust-dark: #8f5330;
            --color-stone: #e4dccc;
            --color-stone-dark: #cfc4ac;
            --color-olive: #6e7350;
            --color-tag: #efe9dc;
            --font-serif: 'Fraunces', Georgia, serif;
            --font-sans: 'Inter', Arial, sans-serif;
        }
    </style>
</head>
<body class="storefront-page orders-page">
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <?php $activeNavigation = 'orders'; ?>
    <?php require dirname(__DIR__) . '/storefront/_header.php'; ?>

    <main id="main-content" class="orders-main section-shell">

        <?php
        $placed     = date('F j, Y', strtotime($order->placedAt));
        $time       = date('g:i A', strtotime($order->placedAt));
        $badgeClass = strtolower($order->status) === 'confirmed' ? 'is-confirmed' : 'is-cancelled';
        ?>

        <header class="orders-masthead">
            <a class="receipt-back" href="<?= $escape($ordersUrl) ?>">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                    <path d="M19 12H5M12 5l-7 7 7 7"/>
                </svg>
                Order history
            </a>
            <p class="orders-eyebrow">Receipt</p>
            <h1><?= $escape($order->orderNumber) ?></h1>
            <p>
                Placed <?= $escape($placed) ?> at <?= $escape($time) ?> &nbsp;·&nbsp;
                <span class="order-status-badge <?= $escape($badgeClass) ?>" style="vertical-align:middle;">
                    <?= $escape(ucfirst($order->status)) ?>
                </span>
            </p>
        </header>

        <div class="receipt-layout">

            <!-- Items panel -->
            <div>
                <section class="receipt-panel" aria-labelledby="receipt-items-title">
                    <h2 class="receipt-panel-heading" id="receipt-items-title">Items ordered</h2>
                    <div class="receipt-items">
                        <?php foreach ($order->items as $item): ?>
                            <div class="receipt-item">
                                <span class="receipt-item-name"><?= $escape($item->productName) ?></span>
                                <span class="receipt-item-total">$<?= $escape(number_format((float) $item->lineTotal, 2)) ?></span>
                                <span class="receipt-item-meta">
                                    SKU: <?= $escape($item->productSku) ?>
                                    &nbsp;·&nbsp;
                                    Qty: <?= $escape($item->quantity) ?>
                                    &nbsp;·&nbsp;
                                    $<?= $escape(number_format((float) $item->unitPrice, 2)) ?> each
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <div class="receipt-actions" style="margin-top:1.25rem;">
                    <a class="btn-orders-secondary" href="<?= $escape($ordersUrl) ?>">
                        ← Back to orders
                    </a>
                    <a class="btn-orders-primary" href="<?= $escape($productsUrl) ?>">
                        Continue shopping
                    </a>
                </div>
            </div>

            <!-- Sidebar: totals + address -->
            <aside class="receipt-sidebar" aria-label="Order summary">

                <!-- Totals -->
                <section class="receipt-summary-card" aria-labelledby="receipt-totals-title">
                    <h2 class="receipt-panel-heading" id="receipt-totals-title">Order summary</h2>
                    <div class="receipt-totals" role="list">
                        <div class="receipt-total-row" role="listitem">
                            <span>Subtotal</span>
                            <span>$<?= $escape(number_format((float) $order->subtotal, 2)) ?></span>
                        </div>
                        <div class="receipt-total-row" role="listitem">
                            <span>Shipping</span>
                            <span>$<?= $escape(number_format((float) $order->shippingTotal, 2)) ?></span>
                        </div>
                        <div class="receipt-total-row" role="listitem">
                            <span>Tax</span>
                            <span>$<?= $escape(number_format((float) $order->taxTotal, 2)) ?></span>
                        </div>
                        <div class="receipt-total-row receipt-grand-total" role="listitem">
                            <span>Grand total</span>
                            <span>$<?= $escape(number_format((float) $order->grandTotal, 2)) ?></span>
                        </div>
                    </div>
                </section>

                <!-- Shipping address -->
                <section class="receipt-address-card" aria-labelledby="receipt-address-title">
                    <h2 class="receipt-panel-heading" id="receipt-address-title">Shipping address</h2>
                    <address>
                        <?= $escape($order->shippingName) ?><br>
                        <?php if ($order->shippingPhone !== null && $order->shippingPhone !== ''): ?>
                            <?= $escape($order->shippingPhone) ?><br>
                        <?php endif; ?>
                        <?= $escape($order->shippingLine1) ?><br>
                        <?php if ($order->shippingLine2 !== null && $order->shippingLine2 !== ''): ?>
                            <?= $escape($order->shippingLine2) ?><br>
                        <?php endif; ?>
                        <?= $escape($order->shippingCity) ?>,
                        <?= $escape($order->shippingState ?? '') ?>
                        <?= $escape($order->shippingPostalCode) ?><br>
                        <?= $escape($order->shippingCountry) ?>
                    </address>
                </section>

            </aside>
        </div>

    </main>

    <?php require dirname(__DIR__) . '/storefront/_footer.php'; ?>
</body>
</html>
