<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($product->name) ?> | Sheepy</title>
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/cart.css">
    <script src="<?= $escape($basePath) ?>/assets/js/cart.js" defer></script>
</head>
<body>
    <header>
        <strong>Sheepy</strong>
        <nav aria-label="Departments">
            <a href="<?= $escape($homeUrl) ?>">Home</a>
            <?php foreach ($navigation as $group): ?>
                <a href="<?= $escape($categoriesUrl . '/' . $group['department']->slug) ?>">
                    <?= $escape($group['department']->name) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <?php require __DIR__ . '/_cart.php'; ?>
        <form method="post" action="<?= $escape($logoutUrl) ?>">
            <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
            <button type="submit">Log out</button>
        </form>
    </header>

    <main>
        <nav aria-label="Breadcrumb">
            <a href="<?= $escape($productsUrl) ?>">Products</a>
            <?php foreach ($breadcrumb as $crumb): ?>
                / <a href="<?= $escape($categoriesUrl . '/' . $crumb->slug) ?>">
                    <?= $escape($crumb->name) ?>
                </a>
            <?php endforeach; ?>
            / <span aria-current="page"><?= $escape($product->name) ?></span>
        </nav>

        <?php if ($images === []): ?>
            <p>No image available.</p>
        <?php else: ?>
            <section aria-label="Product images">
                <?php foreach ($images as $image): ?>
                    <img
                        src="<?= $escape($basePath . $image->url) ?>"
                        alt="<?= $escape($image->altText !== '' ? $image->altText : $product->name) ?>"
                        width="360"
                    >
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <?php if ($product->categoryName !== null): ?>
            <p><?= $escape($product->categoryName) ?></p>
        <?php endif; ?>
        <h1><?= $escape($product->name) ?></h1>
        <p>$<?= $escape($product->price) ?></p>
        <p>SKU: <?= $escape($product->sku) ?></p>
        <p>
            <?= $product->stockQuantity > 0
                ? $escape($product->stockQuantity) . ' in stock'
                : 'Out of stock' ?>
        </p>

        <div class="product-cart-controls">
            <label for="product-quantity">Quantity</label>
            <input
                id="product-quantity"
                type="number"
                min="1"
                max="<?= $escape(max(1, $product->stockQuantity)) ?>"
                value="1"
                inputmode="numeric"
                <?= $product->stockQuantity < 1 ? 'disabled' : '' ?>
            >
            <button
                type="button"
                data-add-to-cart
                data-product-id="<?= $escape($product->id) ?>"
                data-quantity-input="product-quantity"
                <?= $product->stockQuantity < 1 ? 'disabled' : '' ?>
            >
                <?= $product->stockQuantity > 0 ? 'Add to cart' : 'Out of stock' ?>
            </button>
        </div>

        <section aria-labelledby="description-title">
            <h2 id="description-title">Description</h2>
            <?php if ($product->description === null): ?>
                <p>No description provided.</p>
            <?php else: ?>
                <p><?= nl2br($escape($product->description)) ?></p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
