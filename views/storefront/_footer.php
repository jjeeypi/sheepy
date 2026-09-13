<footer class="site-footer">
    <div class="footer-shell section-shell">
        <div class="footer-brand">
            <img src="<?= $escape($basePath) ?>/assets/images/logo/logo.svg" alt="Sheepy" width="116" height="68">
            <p>Considered basics, made to be worn on repeat.</p>
        </div>
        <nav aria-label="Footer shop links">
            <strong>Shop</strong>
            <a href="<?= $escape($productsUrl) ?>">All products</a>
            <a href="<?= $escape($homeUrl) ?>#new-arrivals">New arrivals</a>
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
            <a href="<?= $escape($profileUrl) ?>">Profile</a>
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
    <a
        class="<?= $activeNavigation === 'home' ? 'is-active' : '' ?>"
        href="<?= $escape($homeUrl) ?>"
        <?= $activeNavigation === 'home' ? 'aria-current="page"' : '' ?>
    >
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
    <a
        class="<?= $activeNavigation === 'profile' ? 'is-active' : '' ?>"
        href="<?= $escape($profileUrl) ?>"
        <?= $activeNavigation === 'profile' ? 'aria-current="page"' : '' ?>
        data-bottom-account
    >
        <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"></circle><path d="M5 20c.6-4 3-6 7-6s6.4 2 7 6"></path></svg>
        <span>Profile</span>
    </a>
</nav>
