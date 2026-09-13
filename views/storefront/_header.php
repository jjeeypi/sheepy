<?php
$activeNavigation = $activeNavigation ?? '';
$searchQuery = isset($query) && is_string($query) ? $query : '';
?>
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
            <a
                class="<?= $activeNavigation === 'home' ? 'is-active' : '' ?>"
                href="<?= $escape($homeUrl) ?>"
                <?= $activeNavigation === 'home' ? 'aria-current="page"' : '' ?>
            >Home</a>
            <?php foreach ($navigation as $group): ?>
                <?php $isActiveDepartment = $activeNavigation === $group['department']->slug; ?>
                <a
                    class="<?= $isActiveDepartment ? 'is-active' : '' ?>"
                    href="<?= $escape($categoriesUrl . '/' . $group['department']->slug) ?>"
                    <?= $isActiveDepartment ? 'aria-current="page"' : '' ?>
                ><?= $escape($group['department']->name) ?></a>
            <?php endforeach; ?>
            <div class="mobile-account-links" data-mobile-account-links>
                <a href="<?= $escape($profileUrl) ?>">Profile</a>
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
                    <a href="<?= $escape($profileUrl) ?>">Profile</a>
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
            <label class="sr-only" for="storefront-search">Search products</label>
            <input
                id="storefront-search"
                name="q"
                type="search"
                maxlength="100"
                autocomplete="off"
                placeholder="Search products"
                value="<?= $escape($searchQuery) ?>"
            >
            <button type="submit">Search</button>
        </form>
    </div>
</header>
