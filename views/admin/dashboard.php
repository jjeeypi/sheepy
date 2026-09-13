<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | Sheepy</title>
</head>
<body>
    <main>
        <h1>Dashboard</h1>
        <?php if ($user instanceof \App\Models\User): ?>
            <p>Welcome, <?= $escape($user->username) ?>.</p>
        <?php endif; ?>
        <form method="post" action="<?= $escape($logoutUrl) ?>">
            <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
            <button type="submit">Log out</button>
        </form>
    </main>
</body>
</html>
