<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $category === null ? 'Products' : $escape($category->name) ?> | Sheepy</title>
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
        <a href="<?= $escape($ordersUrl) ?>">Orders</a>
        <form method="post" action="<?= $escape($logoutUrl) ?>">
            <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
            <button type="submit">Log out</button>
        </form>
    </header>

    <main>
        <?php if ($breadcrumb !== []): ?>
            <nav aria-label="Breadcrumb">
                <a href="<?= $escape($productsUrl) ?>">Products</a>
                <?php foreach ($breadcrumb as $crumb): ?>
                    / <a href="<?= $escape($categoriesUrl . '/' . $crumb->slug) ?>">
                        <?= $escape($crumb->name) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <h1><?= $category === null ? 'All Products' : $escape($category->name) ?></h1>

        <?php if ($children !== []): ?>
            <nav aria-label="Product types">
                <?php foreach ($children as $child): ?>
                    <a href="<?= $escape($categoriesUrl . '/' . $child->slug) ?>">
                        <?= $escape($child->name) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <form method="get" action="<?= $escape($browseUrl) ?>" role="search">
            <label for="product-search">Search this catalogue</label>
            <input
                id="product-search"
                name="q"
                type="search"
                value="<?= $escape($query) ?>"
                maxlength="100"
                placeholder="Search products, styles, fabrics"
            >
            <button type="submit">Search</button>
        </form>

        <p><?= $escape($total) ?> product<?= $total === 1 ? '' : 's' ?> found.</p>

        <?php if ($products === []): ?>
            <p>No products match your selection.</p>
        <?php else: ?>
            <section aria-label="Product results">
                <?php foreach ($products as $product): ?>
                    <?php require __DIR__ . '/_card.php'; ?>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Pagination">
                <?php if ($previousPageUrl !== null): ?>
                    <a href="<?= $escape($previousPageUrl) ?>">Previous</a>
                <?php endif; ?>
                <span>Page <?= $escape($page) ?> of <?= $escape($total_pages) ?></span>
                <?php if ($nextPageUrl !== null): ?>
                    <a href="<?= $escape($nextPageUrl) ?>">Next</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </main>
</body>
</html>
