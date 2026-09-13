<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f3ec">
    <title>Register | Sheepy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@1,9..144,500;1,9..144,600&amp;family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $escape($authCssUrl ?? '/assets/css/auth.css') ?>">
    <script src="<?= $escape($authJsUrl ?? '/assets/js/auth.js') ?>" defer></script>
</head>
<body class="auth-page auth-page-register">
    <a class="auth-skip-link" href="#auth-main">Skip to registration</a>

    <div class="auth-shell">
        <aside class="auth-story" aria-label="About Sheepy">
            <?php
            $brandUrl = $loginUrl;
            $brandClass = 'auth-story-brand';
            require __DIR__ . '/_brand.php';
            ?>

            <div class="auth-story-copy">
                <p class="auth-eyebrow">Your Sheepy account</p>
                <h2>Good pieces are worth coming back to.</h2>
                <p>Create an account to keep checkout simple and find every confirmed order in one secure place.</p>
            </div>

            <div class="auth-story-note">
                <span aria-hidden="true"></span>
                <p>Secure passwords, protected sessions, and private account access.</p>
            </div>
        </aside>

        <main class="auth-main" id="auth-main">
            <section class="auth-card auth-card-register" aria-labelledby="auth-title">
                <div class="auth-mobile-brand">
                    <?php
                    $brandUrl = $loginUrl;
                    $brandClass = '';
                    require __DIR__ . '/_brand.php';
                    ?>
                </div>

                <header class="auth-card-header">
                    <p class="auth-eyebrow">Join Sheepy</p>
                    <h1 id="auth-title">Create your account</h1>
                    <p>Get ready for a simpler checkout and a clear view of your orders.</p>
                </header>

                <form class="auth-form auth-register-form" method="post" action="<?= $escape($registerUrl) ?>" data-auth-form data-submit-label="Creating account..." data-registration-form>
                    <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">

                    <div class="auth-field<?= isset($errors['username']) ? ' auth-field-invalid' : '' ?>">
                        <label for="username">Username</label>
                        <div class="auth-input-wrap">
                            <svg class="auth-input-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="8" r="3.25" />
                                <path d="M5.5 19c.6-3.5 3-5.25 6.5-5.25S17.9 15.5 18.5 19" />
                            </svg>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                value="<?= $escape($old['username'] ?? '') ?>"
                                placeholder="your.username"
                                minlength="3"
                                maxlength="50"
                                pattern="[A-Za-z0-9._-]+"
                                title="Use 3 to 50 letters, numbers, dots, underscores, or hyphens."
                                autocomplete="username"
                                autocapitalize="none"
                                spellcheck="false"
                                <?= isset($errors['username']) ? 'aria-invalid="true" aria-describedby="username-error"' : '' ?>
                                required
                                autofocus
                            >
                        </div>
                        <?php if (isset($errors['username'])): ?>
                            <p class="auth-field-error" id="username-error" role="alert"><?= $escape($errors['username']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="auth-field<?= isset($errors['email']) ? ' auth-field-invalid' : '' ?>">
                        <label for="email">Email address</label>
                        <div class="auth-input-wrap">
                            <svg class="auth-input-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                <rect x="3.5" y="5.5" width="17" height="13" rx="2.5" />
                                <path d="m5 7 7 5 7-5" />
                            </svg>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="<?= $escape($old['email'] ?? '') ?>"
                                placeholder="you@email.com"
                                maxlength="191"
                                autocomplete="email"
                                autocapitalize="none"
                                spellcheck="false"
                                <?= isset($errors['email']) ? 'aria-invalid="true" aria-describedby="email-error"' : '' ?>
                                required
                            >
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <p class="auth-field-error" id="email-error" role="alert"><?= $escape($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="auth-field auth-field-wide<?= isset($errors['phone']) ? ' auth-field-invalid' : '' ?>">
                        <label for="phone">Phone <span>(optional)</span></label>
                        <div class="auth-input-wrap">
                            <svg class="auth-input-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                <path d="M8.2 3.75 10 8.1 7.7 9.6a14.2 14.2 0 0 0 6.7 6.7l1.5-2.3 4.35 1.8v3a1.7 1.7 0 0 1-1.7 1.7A15.05 15.05 0 0 1 3.5 5.45a1.7 1.7 0 0 1 1.7-1.7h3Z" />
                            </svg>
                            <input
                                id="phone"
                                name="phone"
                                type="tel"
                                value="<?= $escape($old['phone'] ?? '') ?>"
                                placeholder="+63 900 000 0000"
                                minlength="7"
                                maxlength="30"
                                pattern="[0-9+(). -]+"
                                autocomplete="tel"
                                inputmode="tel"
                                <?= isset($errors['phone']) ? 'aria-invalid="true" aria-describedby="phone-error"' : '' ?>
                            >
                        </div>
                        <?php if (isset($errors['phone'])): ?>
                            <p class="auth-field-error" id="phone-error" role="alert"><?= $escape($errors['phone']) ?></p>
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
                                placeholder="Create a strong password"
                                minlength="8"
                                maxlength="72"
                                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}"
                                title="Use 8 to 72 characters with uppercase, lowercase, and a number."
                                autocomplete="new-password"
                                aria-describedby="password-help<?= isset($errors['password']) ? ' password-error' : '' ?>"
                                <?= isset($errors['password']) ? 'aria-invalid="true"' : '' ?>
                                required
                                data-new-password
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
                        <div class="auth-password-help" id="password-help">
                            <div class="auth-password-meter" data-password-meter data-score="0" aria-hidden="true">
                                <span></span><span></span><span></span><span></span>
                            </div>
                            <p data-password-feedback aria-live="polite">Use 8-72 characters with uppercase, lowercase, and a number.</p>
                        </div>
                        <p class="auth-caps-lock" data-caps-lock hidden>Caps Lock is on.</p>
                        <?php if (isset($errors['password'])): ?>
                            <p class="auth-field-error" id="password-error" role="alert"><?= $escape($errors['password']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="auth-field<?= isset($errors['password_confirmation']) ? ' auth-field-invalid' : '' ?>">
                        <label for="password_confirmation">Confirm password</label>
                        <div class="auth-input-wrap auth-password-wrap">
                            <svg class="auth-input-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                <rect x="5.5" y="10" width="13" height="10" rx="2.5" />
                                <path d="M8.5 10V7.5a3.5 3.5 0 0 1 7 0V10" />
                            </svg>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                placeholder="Repeat your password"
                                minlength="8"
                                maxlength="72"
                                autocomplete="new-password"
                                aria-describedby="password-confirmation-feedback<?= isset($errors['password_confirmation']) ? ' password-confirmation-error' : '' ?>"
                                <?= isset($errors['password_confirmation']) ? 'aria-invalid="true"' : '' ?>
                                required
                                data-password-confirmation
                            >
                            <button class="auth-password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Show password confirmation" aria-pressed="false">
                                <svg class="auth-eye-open" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                    <path d="M2.75 12s3.4-5.25 9.25-5.25S21.25 12 21.25 12 17.85 17.25 12 17.25 2.75 12 2.75 12Z" />
                                    <circle cx="12" cy="12" r="2.25" />
                                </svg>
                                <svg class="auth-eye-closed" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                    <path d="m4 4 16 16M10.5 6.9A9.2 9.2 0 0 1 12 6.75c5.85 0 9.25 5.25 9.25 5.25a16 16 0 0 1-2.15 2.65M13.85 13.85A2.6 2.6 0 0 1 10.15 10.15M6.1 8.1A15.4 15.4 0 0 0 2.75 12S6.15 17.25 12 17.25c1.05 0 2-.17 2.85-.45" />
                                </svg>
                            </button>
                        </div>
                        <p class="auth-field-hint" id="password-confirmation-feedback" data-password-match aria-live="polite">Enter the same password again.</p>
                        <p class="auth-caps-lock" data-caps-lock hidden>Caps Lock is on.</p>
                        <?php if (isset($errors['password_confirmation'])): ?>
                            <p class="auth-field-error" id="password-confirmation-error" role="alert"><?= $escape($errors['password_confirmation']) ?></p>
                        <?php endif; ?>
                    </div>

                    <p class="auth-privacy auth-field-wide">
                        Your details are used only to secure your Sheepy account and fulfil your orders.
                    </p>

                    <button class="auth-submit auth-field-wide" type="submit" data-submit-button>
                        <span data-submit-text>Create account</span>
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                            <path d="M5 12h13M14 7l5 5-5 5" />
                        </svg>
                    </button>
                </form>

                <footer class="auth-switch">
                    <p>Already have an account?</p>
                    <a href="<?= $escape($loginUrl) ?>">Sign in</a>
                </footer>
            </section>
        </main>
    </div>
</body>
</html>
