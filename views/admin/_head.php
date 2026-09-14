<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $escape($pageTitle ?? 'Admin') ?> | Sheepy Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@1,9..144,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $escape($basePath ?? '') ?>/assets/css/admin.css">
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
            --color-olive-soft: #edefe4;
            --color-tag: #efe9dc;
        }
    </style>
</head>
<body class="adm-page">
<div class="adm-shell">

    <!-- ======================================================
         Sidebar
         ====================================================== -->
    <aside class="adm-sidebar" aria-label="Admin navigation">

        <!-- Logo -->
        <div class="adm-sidebar-logo">
            <a href="<?= $escape($dashboardUrl ?? '/admin') ?>" aria-label="Sheepy Admin home">
                <img src="<?= $escape($basePath ?? '') ?>/assets/images/logo/logo.svg" alt="Sheepy" width="84" height="50">
            </a>
        </div>

        <!-- Nav -->
        <nav class="adm-nav" aria-label="Admin menu">
            <span class="adm-nav-section">Management</span>

            <a href="<?= $escape($dashboardUrl ?? '/admin') ?>"
               class="adm-nav-item <?= ($activeNav ?? '') === 'dashboard' ? 'is-active' : '' ?>"
               <?= ($activeNav ?? '') === 'dashboard' ? 'aria-current="page"' : '' ?>>
                <!-- Grid / dashboard icon -->
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="3" y="3" width="8" height="8" rx="1.5"/>
                    <rect x="13" y="3" width="8" height="8" rx="1.5"/>
                    <rect x="3"  y="13" width="8" height="8" rx="1.5"/>
                    <rect x="13" y="13" width="8" height="8" rx="1.5"/>
                </svg>
                Dashboard
            </a>

            <a href="<?= $escape($manageProductsUrl ?? '/admin/products') ?>"
               class="adm-nav-item <?= ($activeNav ?? '') === 'products' ? 'is-active' : '' ?>"
               <?= ($activeNav ?? '') === 'products' ? 'aria-current="page"' : '' ?>>
                <!-- Tag / products icon -->
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                    <circle cx="7" cy="7" r="1.5"/>
                </svg>
                Products
            </a>
        </nav>

        <!-- Account card -->
        <div class="adm-sidebar-foot">
            <div class="adm-sidebar-divider"></div>
            <div class="adm-account-card">
                <div class="adm-avatar" aria-hidden="true">
                    <?= $escape(mb_strtoupper(mb_substr($user?->username ?? 'A', 0, 1))) ?>
                </div>
                <div class="adm-account-info">
                    <span class="adm-account-name"><?= $escape($user?->username ?? 'Admin') ?></span>
                    <span class="adm-account-role">Administrator</span>
                </div>
                <form method="post" action="<?= $escape($logoutUrl ?? '/logout') ?>" class="adm-logout-form">
                    <input type="hidden" name="_token" value="<?= $escape($csrfToken ?? '') ?>">
                    <button type="submit" class="adm-logout-btn" title="Log out" aria-label="Log out">
                        <!-- Log-out arrow icon -->
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M17 16l4-4m0 0l-4-4m4 4H9"/>
                            <path d="M9 20H6a2 2 0 01-2-2V6a2 2 0 012-2h3"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- ======================================================
         Right panel: topbar + main content
         ====================================================== -->
    <div class="adm-right">

        <!-- Top bar -->
        <header class="adm-topbar">
            <div class="adm-topbar-title">
                <h1><?= $escape($pageTitle ?? 'Dashboard') ?></h1>
                <?php if (!empty($pageSubcopy)): ?>
                    <p><?= $escape($pageSubcopy) ?></p>
                <?php endif; ?>
            </div>

            <div class="adm-topbar-actions">
                <!-- Search -->
                <div class="adm-search" role="search">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="search" placeholder="Search…" aria-label="Search admin">
                </div>

                <!-- Notification bell -->
                <button class="adm-icon-btn" type="button" aria-label="Notifications">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M18 8.5a6 6 0 00-12 0c0 5-3 6.5-3 6.5h18s-3-1.5-3-6.5"/>
                        <path d="M13.73 21a2 2 0 01-3.46 0"/>
                    </svg>
                    <span class="adm-notif-dot" aria-hidden="true"></span>
                </button>

                <!-- Account avatar -->
                <button class="adm-topbar-avatar" type="button" aria-label="Account menu">
                    <span class="adm-avatar adm-avatar-sm" aria-hidden="true">
                        <?= $escape(mb_strtoupper(mb_substr($user?->username ?? 'A', 0, 1))) ?>
                    </span>
                    <svg class="adm-chevron" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </button>
            </div>
        </header>

        <!-- Main content — opened here, closed in _foot.php -->
        <main class="adm-content" id="adm-main">
