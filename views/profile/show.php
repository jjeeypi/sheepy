<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="View your Sheepy account details and order history.">
    <title>Profile | Sheepy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;1,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/storefront.css">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/profile.css">
    <link rel="stylesheet" href="<?= $escape($basePath) ?>/assets/css/cart.css">
    <script src="<?= $escape($basePath) ?>/assets/js/storefront.js" defer></script>
    <script src="<?= $escape($basePath) ?>/assets/js/cart.js" defer></script>
</head>
<body class="storefront-page profile-page">
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <?php $activeNavigation = 'profile'; ?>
    <?php require dirname(__DIR__) . '/storefront/_header.php'; ?>

    <main id="main-content" class="profile-main section-shell">
        <header class="profile-masthead">
            <p class="profile-eyebrow">Your account</p>
            <h1>Profile</h1>
            <p>See your account details and return to your Sheepy orders whenever you need them.</p>
        </header>

        <div class="profile-layout">
            <section class="profile-card profile-details" aria-labelledby="details-title">
                <div class="profile-card-heading">
                    <div class="profile-avatar" aria-hidden="true">
                        <svg viewBox="0 0 48 48">
                            <circle cx="24" cy="17" r="8"></circle>
                            <path d="M9 42c1.4-10 6.4-15 15-15s13.6 5 15 15"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="profile-kicker">Personal details</p>
                        <h2 id="details-title"><?= $escape($user->username) ?></h2>
                    </div>
                </div>

                <dl class="profile-detail-list">
                    <div>
                        <dt>Username</dt>
                        <dd><?= $escape($user->username) ?></dd>
                    </div>
                    <div>
                        <dt>Email address</dt>
                        <dd><?= $escape($user->email) ?></dd>
                    </div>
                    <div>
                        <dt>Phone number</dt>
                        <dd><?= $escape($user->phone === null || trim($user->phone) === '' ? 'Not provided' : $user->phone) ?></dd>
                    </div>
                </dl>

                <p class="profile-note">These details come from the information saved when your account was created.</p>
            </section>

            <aside class="profile-sidebar" aria-label="Account actions">
                <a class="profile-action-card" href="<?= $escape($ordersUrl) ?>">
                    <span class="profile-action-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M6 3h12v18H6zM9 8h6M9 12h6M9 16h4"></path></svg>
                    </span>
                    <span>
                        <strong>Order history</strong>
                        <small>View confirmed orders and receipts</small>
                    </span>
                    <span class="profile-action-arrow" aria-hidden="true">&#8594;</span>
                </a>

                <a class="profile-action-card" href="<?= $escape($productsUrl) ?>">
                    <span class="profile-action-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3 3h2l2.2 10.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 1.9-1.4L21 6H6"></path><circle cx="10" cy="20" r="1"></circle><circle cx="18" cy="20" r="1"></circle></svg>
                    </span>
                    <span>
                        <strong>Continue shopping</strong>
                        <small>Browse the complete collection</small>
                    </span>
                    <span class="profile-action-arrow" aria-hidden="true">&#8594;</span>
                </a>

                <section class="profile-security" aria-labelledby="security-title">
                    <p class="profile-kicker">Account security</p>
                    <h2 id="security-title">Finished for now?</h2>
                    <p>Sign out when you are using a shared device. Your password and private session details are never displayed here.</p>
                    <form method="post" action="<?= $escape($logoutUrl) ?>">
                        <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
                        <button type="submit">Log out</button>
                    </form>
                </section>
            </aside>
        </div>
    </main>

    <?php require dirname(__DIR__) . '/storefront/_footer.php'; ?>
</body>
</html>
