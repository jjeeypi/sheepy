<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home | Sheepy</title>
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/cart.css">
    <script src="<?= $escape($basePath) ?>/assets/js/cart.js" defer></script>
</head>
<body>
    <header>
        <h1>Home</h1>
        <?php if ($user instanceof \App\Models\User): ?>
            <p>Welcome, <?= $escape($user->username) ?>.</p>
        <?php endif; ?>

        <nav aria-label="Store navigation">
            <a href="<?= $escape($homeUrl) ?>">Home</a>
            <?php foreach ($navigation as $group): ?>
                <a href="<?= $escape($categoriesUrl . '/' . $group['department']->slug) ?>">
                    <?= $escape($group['department']->name) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php require dirname(__DIR__) . '/products/_cart.php'; ?>
        <a href="<?= $escape($ordersUrl) ?>">Orders</a>

        <form method="post" action="<?= $escape($logoutUrl) ?>">
            <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
            <button type="submit">Log out</button>
        </form>
    </header>

    <main>
        <form method="get" action="<?= $escape($productsUrl) ?>" role="search">
            <label for="home-search">Search products</label>
            <input
                id="home-search"
                name="q"
                type="search"
                maxlength="100"
                placeholder="Search products, styles, fabrics"
            >
            <button type="submit">Search</button>
        </form>

        <section aria-labelledby="departments-title">
            <h2 id="departments-title">Shop by Department</h2>
            <?php foreach ($navigation as $group): ?>
                <section>
                    <h3>
                        <a href="<?= $escape($categoriesUrl . '/' . $group['department']->slug) ?>">
                            <?= $escape($group['department']->name) ?>
                        </a>
                    </h3>
                    <?php if ($group['children'] !== []): ?>
                        <ul>
                            <?php foreach ($group['children'] as $category): ?>
                                <li>
                                    <a href="<?= $escape($categoriesUrl . '/' . $category->slug) ?>">
                                        <?= $escape($category->name) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>
        </section>

        <section aria-labelledby="latest-products-title">
            <h2 id="latest-products-title">New Arrivals</h2>
            <?php if ($latestProducts === []): ?>
                <p>No products are available yet.</p>
            <?php else: ?>
                <?php foreach ($latestProducts as $product): ?>
                    <?php require dirname(__DIR__) . '/products/_card.php'; ?>
                <?php endforeach; ?>
                <p><a href="<?= $escape($productsUrl) ?>">View all products</a></p>
            <?php endif; ?>
        </section>

    </main>
</body>
</html>
