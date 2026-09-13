<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Shop Sheepy's considered everyday clothing for men, women, kids, and accessories.">
    <title>Home | Sheepy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;1,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                <div class="mobile-account-links" data-mobile-account-links>
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
                    placeholder="Search products"
                >
                <button type="submit">Search</button>
            </form>
        </div>
    </header>

    <main id="main-content">
        <section class="hero section-shell" aria-labelledby="hero-title">
            <img
                class="hero-image"
                src="<?= $escape($basePath) ?>/assets/images/home/hero.jpg"
                alt="A white sweatshirt styled with grey denim and natural accessories"
                width="900"
                height="676"
                fetchpriority="high"
            >
            <div class="hero-copy">
                <p class="eyebrow">Autumn collection</p>
                <h1 id="hero-title">Considered basics for slower mornings</h1>
                <a class="button button-paper" href="<?= $escape($productsUrl) ?>">Shop new arrivals</a>
            </div>
        </section>

        <section class="category-section section-shell" id="categories" aria-labelledby="categories-title">
            <div class="section-heading">
                <h2 id="categories-title">Shop by category</h2>
                <a class="text-link" href="<?= $escape($productsUrl) ?>">See all <span aria-hidden="true">&#8594;</span></a>
            </div>

            <div class="category-grid">
                <?php foreach ($featuredCategories as $tile): ?>
                    <a class="category-card" href="<?= $escape($categoriesUrl . '/' . $tile['category']->slug) ?>">
                        <img
                            src="<?= $escape($basePath . '/assets/images/home/' . $tile['image']) ?>"
                            alt="<?= $escape($tile['alt']) ?>"
                            width="450"
                            height="675"
                            loading="lazy"
                        >
                        <span><?= $escape($tile['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="products-section section-shell" id="new-arrivals" aria-labelledby="latest-products-title">
            <div class="section-heading">
                <h2 id="latest-products-title">New arrivals</h2>
                <?php if ($latestProducts !== []): ?>
                    <a class="text-link" href="<?= $escape($productsUrl) ?>">See all <span aria-hidden="true">&#8594;</span></a>
                <?php endif; ?>
            </div>

            <?php if ($latestProducts === []): ?>
                <div class="empty-products">
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

        <section class="lookbook section-shell" aria-labelledby="lookbook-title">
            <img
                src="<?= $escape($basePath) ?>/assets/images/home/lookbook.jpg"
                alt="A model walking through a sunlit architectural space"
                width="700"
                height="1049"
                loading="lazy"
            >
            <div class="lookbook-copy">
                <p class="eyebrow">Lookbook</p>
                <h2 id="lookbook-title">Field notes: the autumn edit</h2>
                <a href="<?= $escape($productsUrl) ?>">Read the edit <span aria-hidden="true">&#8594;</span></a>
            </div>
        </section>

        <section class="trust-strip section-shell" aria-label="Shopping benefits">
            <article>
                <svg aria-hidden="true" viewBox="0 0 24 24">
                    <path d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"></path>
                    <circle cx="7" cy="18" r="1.5"></circle>
                    <circle cx="18" cy="18" r="1.5"></circle>
                </svg>
                <div><strong>Free shipping</strong><span>Orders over $75</span></div>
            </article>
            <article>
                <svg aria-hidden="true" viewBox="0 0 24 24">
                    <path d="M12 3 5 6v5c0 4.7 2.8 8 7 10 4.2-2 7-5.3 7-10V6l-7-3Z"></path>
                    <path d="m9 12 2 2 4-4"></path>
                </svg>
                <div><strong>Stock rechecked</strong><span>Before confirmation</span></div>
            </article>
            <article>
                <svg aria-hidden="true" viewBox="0 0 24 24">
                    <rect x="4" y="7" width="16" height="12" rx="2"></rect>
                    <path d="M8 7V5a4 4 0 0 1 8 0v2M9 13h6"></path>
                </svg>
                <div><strong>Secure checkout</strong><span>Protected sessions</span></div>
            </article>
            <article>
                <svg aria-hidden="true" viewBox="0 0 24 24">
                    <path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"></path>
                    <path d="M9 8h6M9 12h6"></path>
                </svg>
                <div><strong>Order receipts</strong><span>Saved to your history</span></div>
            </article>
        </section>
    </main>

    <footer class="site-footer">
        <div class="footer-shell section-shell">
            <div class="footer-brand">
                <img src="<?= $escape($basePath) ?>/assets/images/logo/logo.svg" alt="Sheepy" width="116" height="68">
                <p>Considered basics, made to be worn on repeat.</p>
            </div>
            <nav aria-label="Footer shop links">
                <strong>Shop</strong>
                <a href="<?= $escape($productsUrl) ?>">All products</a>
                <a href="#new-arrivals">New arrivals</a>
            </nav>
            <nav aria-label="Footer department links">
                <strong>Departments</strong>
                <?php foreach ($navigation as $group): ?>
                    <a href="<?= $escape($categoriesUrl . '/' . $group['department']->slug) ?>">
                        <?= $escape($group['department']->name) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <nav aria-label="Footer account links">
                <strong>Account</strong>
                <a href="<?= $escape($ordersUrl) ?>">Order history</a>
                <form method="post" action="<?= $escape($logoutUrl) ?>">
                    <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
                    <button type="submit">Log out</button>
                </form>
            </nav>
        </div>
        <div class="footer-bottom section-shell">
            <span>&copy; <?= $escape(date('Y')) ?> Sheepy</span>
            <a href="#main-content">Back to top</a>
        </div>
    </footer>

    <nav class="mobile-tabbar" aria-label="Mobile shortcuts">
        <a class="is-active" href="<?= $escape($homeUrl) ?>" aria-current="page">
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m4 11 8-7 8 7v9h-6v-6h-4v6H4v-9Z"></path></svg>
            <span>Home</span>
        </a>
        <button type="button" data-bottom-search>
            <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4"></path></svg>
            <span>Search</span>
        </button>
        <button type="button" data-bottom-cart>
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 3h2l2.2 10.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 1.9-1.4L21 6H6"></path><circle cx="10" cy="20" r="1"></circle><circle cx="18" cy="20" r="1"></circle></svg>
            <span>Bag</span>
        </button>
        <button type="button" data-bottom-account>
            <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"></circle><path d="M5 20c.6-4 3-6 7-6s6.4 2 7 6"></path></svg>
            <span>Account</span>
        </button>
    </nav>
</body>
</html>
