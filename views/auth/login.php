<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f3ec">
    <title>Log in | Sheepy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@1,9..144,500;1,9..144,600&amp;family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $escape($authCssUrl ?? '/assets/css/auth.css') ?>">
    <script src="<?= $escape($authJsUrl ?? '/assets/js/auth.js') ?>" defer></script>
    <!-- Tailwind CSS v4 -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-bone: #f6f3ec;
            --color-ink: #2a2621;
            --color-ink-soft: #7a7266;
            --color-rust: #a9663d;
            --color-rust-dark: #8f5330;
            --color-stone: #e4dccc;
            --color-tag: #efe9dc;
            --font-serif: 'Fraunces', Georgia, serif;
            --font-sans: 'Inter', Arial, sans-serif;
        }
    </style>
</head>
<body class="auth-page auth-page-login">
    <a class="auth-skip-link" href="#auth-main">Skip to sign in</a>

    <div class="auth-shell">
        <aside class="auth-story" aria-label="About Sheepy">
            <?php
            $brandUrl = $loginUrl;
            $brandClass = 'auth-story-brand';
            require __DIR__ . '/_brand.php';
            ?>

            <div class="auth-story-copy">
                <p class="auth-eyebrow">Quietly considered clothing</p>
                <h2>Made for the rhythm of every day.</h2>
                <p>Discover thoughtful essentials, seasonal layers, and easy pieces designed to stay in rotation.</p>
            </div>

            <div class="auth-story-note">
                <span aria-hidden="true"></span>
                <p>Secure access to your cart, checkout, and order history.</p>
            </div>
        </aside>

        <main class="auth-main" id="auth-main">
            <section class="auth-card" aria-labelledby="auth-title">
                <div class="auth-mobile-brand">
                    <?php
                    $brandUrl = $loginUrl;
                    $brandClass = '';
                    require __DIR__ . '/_brand.php';
                    ?>
                </div>

                <header class="auth-card-header">
                    <p class="auth-eyebrow">Welcome to Sheepy</p>
                    <h1 id="auth-title">Welcome back</h1>
                    <p>Sign in to pick up right where you left off.</p>
                </header>

                <?php if (isset($errors['credentials'])): ?>
                    <div class="auth-alert" role="alert">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7.5v5.25M12 16.5h.01" />
                        </svg>
                        <span><?= $escape($errors['credentials']) ?></span>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="post" action="<?= $escape($loginUrl) ?>" data-auth-form data-submit-label="Signing in...">
                    <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">

                    <div class="auth-field<?= isset($errors['identifier']) ? ' auth-field-invalid' : '' ?>">
                        <label for="identifier">Username or email</label>
                        <div class="auth-input-wrap">
                            <svg class="auth-input-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="8" r="3.25" />
                                <path d="M5.5 19c.6-3.5 3-5.25 6.5-5.25S17.9 15.5 18.5 19" />
                            </svg>
                            <input
                                id="identifier"
                                name="identifier"
                                type="text"
                                value="<?= $escape($old['identifier'] ?? '') ?>"
                                placeholder="you@email.com"
                                maxlength="191"
                                autocomplete="username"
                                autocapitalize="none"
                                spellcheck="false"
                                <?= isset($errors['identifier']) ? 'aria-invalid="true" aria-describedby="identifier-error"' : '' ?>
                                required
                            >
                        </div>
                        <?php if (isset($errors['identifier'])): ?>
                            <p class="auth-field-error" id="identifier-error" role="alert"><?= $escape($errors['identifier']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="auth-field<?= isset($errors['password']) ? ' auth-field-invalid' : '' ?>">
                        <label for="password">Password</label>
                        <div class="auth-input-wrap auth-password-wrap">
                            <svg class="auth-input-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                <rect x="5.5" y="10" width="13" height="10" rx="2.5" />
                                <path d="M8.5 10V7.5a3.5 3.5 0 0 1 7 0V10" />
                            </svg>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                <?= isset($errors['password']) ? 'aria-invalid="true" aria-describedby="password-error"' : '' ?>
                                required
                            >
                            <button class="auth-password-toggle" type="button" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                                <svg class="auth-eye-open" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                    <path d="M2.75 12s3.4-5.25 9.25-5.25S21.25 12 21.25 12 17.85 17.25 12 17.25 2.75 12 2.75 12Z" />
                                    <circle cx="12" cy="12" r="2.25" />
                                </svg>
                                <svg class="auth-eye-closed" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                    <path d="m4 4 16 16M10.5 6.9A9.2 9.2 0 0 1 12 6.75c5.85 0 9.25 5.25 9.25 5.25a16 16 0 0 1-2.15 2.65M13.85 13.85A2.6 2.6 0 0 1 10.15 10.15M6.1 8.1A15.4 15.4 0 0 0 2.75 12S6.15 17.25 12 17.25c1.05 0 2-.17 2.85-.45" />
                                </svg>
                            </button>
                        </div>
                        <p class="auth-caps-lock" data-caps-lock hidden>Caps Lock is on.</p>
                        <?php if (isset($errors['password'])): ?>
                            <p class="auth-field-error" id="password-error" role="alert"><?= $escape($errors['password']) ?></p>
                        <?php endif; ?>
                    </div>

                    <button class="auth-submit" type="submit" data-submit-button>
                        <span data-submit-text>Sign in</span>
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                            <path d="M5 12h13M14 7l5 5-5 5" />
                        </svg>
                    </button>
                </form>

                <footer class="auth-switch">
                    <p>New to Sheepy?</p>
                    <a href="<?= $escape($registerUrl) ?>">Create an account</a>
                </footer>
            </section>
        </main>
    </div>
</body>
</html>
