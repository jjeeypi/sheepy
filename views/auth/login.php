<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in | Sheepy</title>
</head>
<body>
    <main>
        <h1>Log in</h1>

        <?php if (isset($errors['credentials'])): ?>
            <p role="alert"><?= $escape($errors['credentials']) ?></p>
        <?php endif; ?>

        <form method="post" action="<?= $escape($loginUrl) ?>">
            <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">

            <p>
                <label for="identifier">Username or email</label><br>
                <input
                    id="identifier"
                    name="identifier"
                    type="text"
                    value="<?= $escape($old['identifier'] ?? '') ?>"
                    maxlength="191"
                    autocomplete="username"
                    required
                >
                <?php if (isset($errors['identifier'])): ?>
                    <br><span role="alert"><?= $escape($errors['identifier']) ?></span>
                <?php endif; ?>
            </p>

            <p>
                <label for="password">Password</label><br>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                >
                <?php if (isset($errors['password'])): ?>
                    <br><span role="alert"><?= $escape($errors['password']) ?></span>
                <?php endif; ?>
            </p>

            <button type="submit">Log in</button>
        </form>

        <p>New to Sheepy? <a href="<?= $escape($registerUrl) ?>">Create an account</a>.</p>
    </main>
</body>
</html>
