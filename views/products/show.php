<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= $escape($product->description !== null ? mb_substr($product->description, 0, 160) : 'Shop ' . $product->name . ' at Sheepy.') ?>">
    <title><?= $escape($product->name) ?> | Sheepy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;1,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/storefront.css">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/catalog.css">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/cart.css">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/product-detail.css">
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
<body class="storefront-page product-detail-page">
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <?php $activeNavigation = isset($breadcrumb[0]) ? $breadcrumb[0]->slug : ''; ?>
    <?php require dirname(__DIR__) . '/storefront/_header.php'; ?>

    <main id="main-content">
        <!-- Breadcrumb -->
        <div class="section-shell" style="padding-top:1.5rem;">
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="<?= $escape($productsUrl) ?>">All products</a>
                <?php foreach ($breadcrumb as $crumb): ?>
                    <span aria-hidden="true">/</span>
                    <a href="<?= $escape($categoriesUrl . '/' . $crumb->slug) ?>">
                        <?= $escape($crumb->name) ?>
                    </a>
                <?php endforeach; ?>
                <span aria-hidden="true">/</span>
                <span aria-current="page"><?= $escape($product->name) ?></span>
            </nav>
        </div>

        <!-- Two-column product layout -->
        <section class="pd-shell section-shell" aria-labelledby="pd-title">
            <!-- Gallery -->
            <div class="pd-gallery" aria-label="Product images">
                <?php if ($images === []): ?>
                    <div class="pd-gallery-placeholder">
                        <svg aria-hidden="true" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="6" y="6" width="36" height="36" rx="6"/>
                            <circle cx="18" cy="19" r="4"/>
                            <path d="M6 36l10-10 6 6 7-9 13 13"/>
                        </svg>
                        <span>Image coming soon</span>
                    </div>
                <?php else: ?>
                    <?php $primary = $images[0]; ?>
                    <div class="pd-gallery-main">
                        <img
                            src="<?= $escape($basePath . $primary->url) ?>"
                            alt="<?= $escape($primary->altText !== '' ? $primary->altText : $product->name) ?>"
                            id="pd-main-image"
                            width="600"
                            height="750"
                            fetchpriority="high"
                        >
                    </div>
                    <?php if (count($images) > 1): ?>
                        <div class="pd-gallery-thumbs" role="list" aria-label="Product image thumbnails">
                            <?php foreach ($images as $i => $image): ?>
                                <button
                                    class="pd-thumb<?= $i === 0 ? ' is-active' : '' ?>"
                                    type="button"
                                    data-pd-thumb
                                    data-src="<?= $escape($basePath . $image->url) ?>"
                                    data-alt="<?= $escape($image->altText !== '' ? $image->altText : $product->name) ?>"
                                    aria-label="View image <?= $i + 1 ?>"
                                    aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>"
                                    role="listitem"
                                >
                                    <img
                                        src="<?= $escape($basePath . $image->url) ?>"
                                        alt=""
                                        loading="lazy"
                                        width="100"
                                        height="100"
                                    >
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Product info -->
            <div class="pd-info">
                <?php if ($product->categoryName !== null): ?>
                    <p class="pd-category"><?= $escape($product->categoryName) ?></p>
                <?php endif; ?>

                <h1 class="pd-title" id="pd-title"><?= $escape($product->name) ?></h1>
                <p class="pd-price">$<?= $escape(number_format((float) $product->price, 2)) ?></p>

                <?php if ($product->stockQuantity > 0): ?>
                    <p class="pd-stock pd-stock--in">
                        <svg aria-hidden="true" viewBox="0 0 16 16"><circle cx="8" cy="8" r="3" fill="currentColor"/></svg>
                        In stock
                    </p>
                <?php else: ?>
                    <p class="pd-stock pd-stock--out">
                        <svg aria-hidden="true" viewBox="0 0 16 16"><circle cx="8" cy="8" r="3" fill="currentColor"/></svg>
                        Out of stock
                    </p>
                <?php endif; ?>

                <!-- Add to cart -->
                <div class="pd-atc">
                    <div class="pd-quantity-wrap">
                        <label class="pd-quantity-label" for="pd-quantity">Quantity</label>
                        <div class="cart-quantity-controls">
                            <button type="button" id="pd-qty-dec" aria-label="Decrease quantity" <?= $product->stockQuantity < 1 ? 'disabled' : '' ?>>−</button>
                            <input
                                id="pd-quantity"
                                type="number"
                                min="1"
                                max="<?= $escape(max(1, $product->stockQuantity)) ?>"
                                value="1"
                                inputmode="numeric"
                                <?= $product->stockQuantity < 1 ? 'disabled' : '' ?>
                            >
                            <button type="button" id="pd-qty-inc" aria-label="Increase quantity" <?= $product->stockQuantity < 1 ? 'disabled' : '' ?>>+</button>
                        </div>
                    </div>

                    <button
                        class="pd-atc-button cart-primary-button"
                        type="button"
                        data-add-to-cart
                        data-product-id="<?= $escape($product->id) ?>"
                        data-quantity-input="pd-quantity"
                        <?= $product->stockQuantity < 1 ? 'disabled' : '' ?>
                        aria-label="<?= $escape($product->stockQuantity > 0 ? 'Add ' . $product->name . ' to cart' : 'Out of stock') ?>"
                    >
                        <?php if ($product->stockQuantity > 0): ?>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                                <path d="M3 3h2l2.2 10.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 1.9-1.4L21 6H6"/>
                                <circle cx="10" cy="20" r="1"/>
                                <circle cx="18" cy="20" r="1"/>
                            </svg>
                            Add to cart
                        <?php else: ?>
                            Out of stock
                        <?php endif; ?>
                    </button>
                </div>

                <!-- Description -->
                <?php if ($product->description !== null): ?>
                    <div class="pd-description">
                        <h2 class="pd-description-title">About this piece</h2>
                        <p><?= nl2br($escape($product->description)) ?></p>
                    </div>
                <?php endif; ?>

                <!-- SKU -->
                <p class="pd-sku">SKU: <?= $escape($product->sku) ?></p>
            </div>
        </section>
    </main>

    <?php require dirname(__DIR__) . '/storefront/_footer.php'; ?>

    <script>
    (() => {
        const mainImg = document.getElementById('pd-main-image');
        document.querySelectorAll('[data-pd-thumb]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!mainImg) return;
                mainImg.src = btn.dataset.src;
                mainImg.alt = btn.dataset.alt;
                document.querySelectorAll('[data-pd-thumb]').forEach(b => {
                    b.classList.remove('is-active');
                    b.setAttribute('aria-pressed', 'false');
                });
                btn.classList.add('is-active');
                btn.setAttribute('aria-pressed', 'true');
            });
        });

        const qtyInput = document.getElementById('pd-quantity');
        const dec = document.getElementById('pd-qty-dec');
        const inc = document.getElementById('pd-qty-inc');
        if (qtyInput && dec && inc) {
            dec.addEventListener('click', () => {
                const v = parseInt(qtyInput.value, 10);
                if (v > 1) qtyInput.value = v - 1;
            });
            inc.addEventListener('click', () => {
                const v = parseInt(qtyInput.value, 10);
                const max = parseInt(qtyInput.max, 10);
                if (v < max) qtyInput.value = v + 1;
            });
        }
    })();
    </script>
</body>
</html>
