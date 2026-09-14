<?php
$pageTitle = $category === null ? 'All products' : $category->name;
$pageDescription = match ($activeNavigation) {
    'men' => 'Relaxed layers and everyday essentials designed to work together, season after season.',
    'women' => 'Considered silhouettes and versatile layers for an easy everyday wardrobe.',
    'kids' => 'Comfortable, practical pieces made for movement and everyday play.',
    'accessories' => 'The finishing pieces: useful, understated, and easy to wear.',
    default => 'Explore the complete Sheepy collection of considered everyday clothing.',
};
$pageEyebrow = $activeDepartment === null
    ? 'The complete collection'
    : $activeDepartment->name . ' collection';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= $escape($pageDescription) ?>">
    <title><?= $escape($pageTitle) ?> | Sheepy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;1,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/storefront.css">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/catalog.css">
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
<body class="storefront-page catalog-page">
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <?php require dirname(__DIR__) . '/storefront/_header.php'; ?>

    <main id="main-content">
        <section class="catalog-hero section-shell <?= $activeNavigation === 'men' ? 'has-media' : '' ?>" aria-labelledby="catalog-title">
            <div class="catalog-hero-copy">
                <?php if ($breadcrumb !== []): ?>
                    <nav class="breadcrumb" aria-label="Breadcrumb">
                        <a href="<?= $escape($productsUrl) ?>">All products</a>
                        <?php foreach ($breadcrumb as $crumb): ?>
                            <span aria-hidden="true">/</span>
                            <?php if ($crumb->id === $category?->id): ?>
                                <span aria-current="page"><?= $escape($crumb->name) ?></span>
                            <?php else: ?>
                                <a href="<?= $escape($categoriesUrl . '/' . $crumb->slug) ?>">
                                    <?= $escape($crumb->name) ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>

                <p class="catalog-eyebrow"><?= $escape($pageEyebrow) ?></p>
                <h1 id="catalog-title"><?= $escape($pageTitle) ?></h1>
                <p class="catalog-description"><?= $escape($pageDescription) ?></p>
            </div>

            <?php if ($activeNavigation === 'men'): ?>
                <div class="catalog-hero-media">
                    <img
                        src="<?= $escape($basePath) ?>/assets/images/home/category-outerwear.jpg"
                        alt="Model wearing a relaxed earth-tone top"
                        width="450"
                        height="675"
                        fetchpriority="high"
                    >
                </div>
            <?php endif; ?>
        </section>

        <?php if ($activeDepartment !== null && $categoryNavigation !== []): ?>
            <nav class="category-filters section-shell" aria-label="<?= $escape($activeDepartment->name) ?> product types">
                <a
                    class="<?= $category?->id === $activeDepartment->id ? 'is-active' : '' ?>"
                    href="<?= $escape($categoriesUrl . '/' . $activeDepartment->slug) ?>"
                    <?= $category?->id === $activeDepartment->id ? 'aria-current="page"' : '' ?>
                >All <?= $escape($activeDepartment->name) ?></a>
                <?php foreach ($categoryNavigation as $child): ?>
                    <a
                        class="<?= $category?->id === $child->id ? 'is-active' : '' ?>"
                        href="<?= $escape($categoriesUrl . '/' . $child->slug) ?>"
                        <?= $category?->id === $child->id ? 'aria-current="page"' : '' ?>
                    ><?= $escape($child->name) ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <section class="catalog-results section-shell" aria-labelledby="results-title">
            <div class="catalog-toolbar">
                <div>
                    <h2 id="results-title"><?= $query === '' ? 'The collection' : 'Search results' ?></h2>
                    <p><?= $escape($total) ?> piece<?= $total === 1 ? '' : 's' ?></p>
                </div>

                <form class="catalog-search" method="get" action="<?= $escape($browseUrl) ?>" role="search">
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="6.5"></circle>
                        <path d="m16 16 4 4"></path>
                    </svg>
                    <label class="sr-only" for="catalog-search">Search this catalogue</label>
                    <input
                        id="catalog-search"
                        name="q"
                        type="search"
                        value="<?= $escape($query) ?>"
                        maxlength="100"
                        autocomplete="off"
                        placeholder="Search this collection"
                    >
                    <button type="submit">Search</button>
                </form>
            </div>

            <?php if ($query !== ''): ?>
                <div class="active-search" role="status">
                    <span>Showing results for &ldquo;<?= $escape($query) ?>&rdquo;</span>
                    <a href="<?= $escape($browseUrl) ?>">Clear search</a>
                </div>
            <?php endif; ?>

            <?php if ($products === []): ?>
                <div class="catalog-empty">
                    <h3>No pieces found</h3>
                    <p>Try a different search or return to the complete collection.</p>
                    <a href="<?= $escape($browseUrl) ?>">Clear search</a>
                </div>
            <?php else: ?>
                <div class="product-grid" aria-label="Product results">
                    <?php foreach ($products as $product): ?>
                        <?php require __DIR__ . '/_storefront_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($total_pages > 1): ?>
                <nav class="pagination" aria-label="Pagination">
                    <?php if ($previousPageUrl !== null): ?>
                        <a href="<?= $escape($previousPageUrl) ?>"><span aria-hidden="true">&#8592;</span> Previous</a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                    <span>Page <?= $escape($page) ?> of <?= $escape($total_pages) ?></span>
                    <?php if ($nextPageUrl !== null): ?>
                        <a href="<?= $escape($nextPageUrl) ?>">Next <span aria-hidden="true">&#8594;</span></a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </section>
    </main>

    <?php require dirname(__DIR__) . '/storefront/_footer.php'; ?>
</body>
</html>
