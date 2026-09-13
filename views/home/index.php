<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Shop Sheepy's considered everyday clothing for men, women, kids, and accessories.">
    <title>Home | Sheepy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/home.css">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/cart.css">
    <script src="<?= $escape($basePath) ?>/assets/js/home.js" defer></script>
    <script src="<?= $escape($basePath) ?>/assets/js/cart.js" defer></script>
</head>
<body class="home-page">
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <header class="site-header" data-site-header>
        <div class="header-shell">
            <button
                class="header-icon-button menu-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="primary-navigation"
                data-menu-toggle
            >
                <svg aria-hidden="true" viewBox="0 0 24 24">
                    <path d="M4 7h16M4 12h16M4 17h16"></path>
                </svg>
                <span class="sr-only">Open navigation</span>
            </button>

            <a class="site-logo" href="<?= $escape($homeUrl) ?>" aria-label="Sheepy home">
                <img src="<?= $escape($basePath) ?>/assets/images/logo/logo.svg" alt="Sheepy" width="116" height="68">
            </a>

            <nav class="primary-navigation" id="primary-navigation" aria-label="Store navigation" data-navigation>
                <a class="is-active" href="<?= $escape($homeUrl) ?>" aria-current="page">Home</a>
                <?php foreach ($navigation as $group): ?>
                    <a href="<?= $escape($categoriesUrl . '/' . $group['department']->slug) ?>">
                        <?= $escape($group['department']->name) ?>
                    </a>
                <?php endforeach; ?>
                <div class="mobile-account-links">
                    <a href="<?= $escape($ordersUrl) ?>">Order history</a>
                    <form method="post" action="<?= $escape($logoutUrl) ?>">
                        <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
                        <button type="submit">Log out</button>
                    </form>
                </div>
            </nav>

            <div class="header-actions">
                <button
                    class="header-icon-button search-toggle"
                    type="button"
                    aria-expanded="false"
                    aria-controls="header-search"
                    data-search-toggle
                >
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="6.5"></circle>
                        <path d="m16 16 4 4"></path>
                    </svg>
                    <span class="sr-only">Search products</span>
                </button>

                <a class="header-icon-button orders-link" href="<?= $escape($ordersUrl) ?>">
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <path d="M7 4h10l2 3v13H5V7l2-3Z"></path>
                        <path d="M5 8h14M9 11v1a3 3 0 0 0 6 0v-1"></path>
                    </svg>
                    <span class="sr-only">Order history</span>
                </a>

                <?php require dirname(__DIR__) . '/products/_cart.php'; ?>

                <details class="account-menu">
                    <summary class="header-icon-button">
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="3.5"></circle>
                            <path d="M5 20c.6-4 3-6 7-6s6.4 2 7 6"></path>
                        </svg>
                        <span class="sr-only">Account menu</span>
                    </summary>
                    <div class="account-popover">
                        <?php if ($user instanceof \App\Models\User): ?>
                            <p class="account-label">Signed in as</p>
                            <strong><?= $escape($user->username) ?></strong>
                        <?php endif; ?>
                        <a href="<?= $escape($ordersUrl) ?>">Order history</a>
                        <form method="post" action="<?= $escape($logoutUrl) ?>">
                            <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
                            <button type="submit">Log out</button>
                        </form>
                    </div>
                </details>
            </div>
        </div>

        <div class="header-search" id="header-search" data-search-panel hidden>
            <form method="get" action="<?= $escape($productsUrl) ?>" role="search">
                <svg aria-hidden="true" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="6.5"></circle>
                    <path d="m16 16 4 4"></path>
                </svg>
                <label class="sr-only" for="home-search">Search products</label>
                <input
                    id="home-search"
                    name="q"
                    type="search"
                    maxlength="100"
                    autocomplete="off"
                    placeholder="Search by product, category, or SKU"
                >
                <button type="submit">Search</button>
            </form>
        </div>
    </header>

    <main id="main-content">
        <section class="hero section-shell" aria-labelledby="hero-title">
            <div class="hero-copy">
                <?php if ($user instanceof \App\Models\User): ?>
                    <p class="eyebrow">Welcome back, <?= $escape($user->username) ?></p>
                <?php else: ?>
                    <p class="eyebrow">The Sheepy collection</p>
                <?php endif; ?>
                <h1 id="hero-title">Considered essentials for <em>every day.</em></h1>
                <p class="hero-description">Easy layers, comfortable shapes, and versatile pieces made for the rhythm of real life.</p>
                <div class="hero-actions">
                    <a class="button button-light" href="<?= $escape($productsUrl) ?>">Shop new arrivals</a>
                    <a class="text-link text-link-light" href="#departments">Explore departments <span aria-hidden="true">&#8594;</span></a>
                </div>
            </div>

            <div class="hero-gallery" aria-label="Featured products">
                <?php if ($latestProducts !== []): ?>
                    <?php foreach (array_reverse(array_slice($latestProducts, 0, 3)) as $index => $featuredProduct): ?>
                        <a
                            class="hero-product hero-product-<?= $escape($index + 1) ?>"
                            href="<?= $escape($productsUrl . '/' . $featuredProduct->slug) ?>"
                            aria-label="View <?= $escape($featuredProduct->name) ?>"
                        >
                            <?php if ($featuredProduct->imageUrl !== null): ?>
                                <img
                                    src="<?= $escape($basePath . $featuredProduct->imageUrl) ?>"
                                    alt="<?= $escape(trim($featuredProduct->imageAlt ?? '') !== '' ? $featuredProduct->imageAlt : $featuredProduct->name) ?>"
                                    <?= $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>
                                >
                            <?php else: ?>
                                <span class="hero-product-placeholder" aria-hidden="true">Sheepy</span>
                            <?php endif; ?>
                            <span><?= $escape($featuredProduct->name) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="hero-empty">
                        <span>New collection arriving soon</span>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="department-section section-shell" id="departments" aria-labelledby="departments-title">
            <div class="section-heading">
                <div>
                    <p class="eyebrow eyebrow-dark">Browse the collection</p>
                    <h2 id="departments-title">Shop by department</h2>
                </div>
                <a class="text-link" href="<?= $escape($productsUrl) ?>">View all products <span aria-hidden="true">&#8594;</span></a>
            </div>

            <div class="department-grid">
                <?php foreach ($navigation as $group): ?>
                    <a class="department-card" href="<?= $escape($categoriesUrl . '/' . $group['department']->slug) ?>">
                        <span class="department-number" aria-hidden="true">
                            <?= $escape(str_pad((string) ($group['department']->id), 2, '0', STR_PAD_LEFT)) ?>
                        </span>
                        <div class="department-copy">
                            <h3><?= $escape($group['department']->name) ?></h3>
                            <?php if ($group['children'] !== []): ?>
                                <p>
                                    <?php foreach (array_slice($group['children'], 0, 3) as $childIndex => $category): ?>
                                        <?= $childIndex > 0 ? ' / ' : '' ?><?= $escape($category->name) ?>
                                    <?php endforeach; ?>
                                </p>
                            <?php else: ?>
                                <p>Explore the collection</p>
                            <?php endif; ?>
                        </div>
                        <span class="department-arrow" aria-hidden="true">&#8599;</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="products-section section-shell" aria-labelledby="latest-products-title">
            <div class="section-heading">
                <div>
                    <p class="eyebrow eyebrow-dark">Freshly added</p>
                    <h2 id="latest-products-title">New arrivals</h2>
                </div>
                <?php if ($latestProducts !== []): ?>
                    <a class="text-link" href="<?= $escape($productsUrl) ?>">See the full collection <span aria-hidden="true">&#8594;</span></a>
                <?php endif; ?>
            </div>

            <?php if ($latestProducts === []): ?>
                <div class="empty-products">
                    <p class="eyebrow eyebrow-dark">Coming soon</p>
                    <h3>Our next collection is being prepared.</h3>
                    <p>Check back shortly for new Sheepy essentials.</p>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($latestProducts as $product): ?>
                        <?php require __DIR__ . '/_product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="collection-story section-shell" aria-labelledby="collection-story-title">
            <div class="story-copy">
                <p class="eyebrow">The everyday edit</p>
                <h2 id="collection-story-title">Build a wardrobe that works together.</h2>
                <p>Start with simple foundations, then add texture and layers as the day changes.</p>
                <a class="button button-light" href="<?= $escape($productsUrl) ?>">Browse all products</a>
            </div>
            <div class="story-mark" aria-hidden="true">
                <span>Sheepy</span>
            </div>
        </section>

        <section class="service-strip section-shell" aria-label="Shopping benefits">
            <article>
                <svg aria-hidden="true" viewBox="0 0 24 24">
                    <path d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"></path>
                    <circle cx="7" cy="18" r="1.5"></circle>
                    <circle cx="18" cy="18" r="1.5"></circle>
                </svg>
                <div><strong>Free shipping</strong><span>On orders of $75 or more</span></div>
            </article>
            <article>
                <svg aria-hidden="true" viewBox="0 0 24 24">
                    <path d="M12 3 5 6v5c0 4.7 2.8 8 7 10 4.2-2 7-5.3 7-10V6l-7-3Z"></path>
                    <path d="m9 12 2 2 4-4"></path>
                </svg>
                <div><strong>Stock rechecked</strong><span>Before every order is confirmed</span></div>
            </article>
            <article>
                <svg aria-hidden="true" viewBox="0 0 24 24">
                    <rect x="4" y="7" width="16" height="12" rx="2"></rect>
                    <path d="M8 7V5a4 4 0 0 1 8 0v2M9 13h6"></path>
                </svg>
                <div><strong>Secure checkout</strong><span>Protected customer sessions</span></div>
            </article>
            <article>
                <svg aria-hidden="true" viewBox="0 0 24 24">
                    <path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"></path>
                    <path d="M9 8h6M9 12h6"></path>
                </svg>
                <div><strong>Order receipts</strong><span>Saved in your order history</span></div>
            </article>
        </section>
    </main>

    <footer class="site-footer">
        <div class="footer-shell section-shell">
            <div class="footer-brand">
                <img src="<?= $escape($basePath) ?>/assets/images/logo/logo.svg" alt="Sheepy" width="116" height="68">
                <p>Comfortable, considered clothing for ordinary days.</p>
            </div>
            <nav aria-label="Footer shop links">
                <strong>Shop</strong>
                <a href="<?= $escape($productsUrl) ?>">All products</a>
                <?php foreach ($navigation as $group): ?>
                    <a href="<?= $escape($categoriesUrl . '/' . $group['department']->slug) ?>">
                        <?= $escape($group['department']->name) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <nav aria-label="Footer account links">
                <strong>Your account</strong>
                <a href="<?= $escape($ordersUrl) ?>">Order history</a>
                <a href="#main-content">Back to top</a>
            </nav>
        </div>
        <div class="footer-bottom section-shell">
            <span>&copy; <?= $escape(date('Y')) ?> Sheepy</span>
            <span>Made for everyday comfort.</span>
        </div>
    </footer>
</body>
</html>
