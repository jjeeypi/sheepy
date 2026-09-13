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
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/storefront.css">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/cart.css">
    <script src="<?= $escape($basePath) ?>/assets/js/storefront.js" defer></script>
    <script src="<?= $escape($basePath) ?>/assets/js/cart.js" defer></script>
</head>
<body class="storefront-page home-page">
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <?php $activeNavigation = 'home'; ?>
    <?php require dirname(__DIR__) . '/storefront/_header.php'; ?>

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
                        <?php require dirname(__DIR__) . '/products/_storefront_card.php'; ?>
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

        <!-- Newsletter block -->
        <section class="newsletter-block section-shell" aria-labelledby="newsletter-title">
            <p class="eyebrow">Stay in the loop</p>
            <h2 id="newsletter-title">New pieces, slow stories.</h2>
            <p>Sign up for occasional notes on new arrivals and quiet things worth knowing about.</p>
            <form class="newsletter-form" action="<?= $escape($homeUrl) ?>" method="get" aria-label="Newsletter sign-up">
                <input
                    type="email"
                    name="email"
                    placeholder="your@email.com"
                    autocomplete="email"
                    aria-label="Email address"
                    maxlength="191"
                >
                <button type="submit">Subscribe</button>
            </form>
        </section>
    </main>

    <?php require dirname(__DIR__) . '/storefront/_footer.php'; ?>
</body>
</html>
