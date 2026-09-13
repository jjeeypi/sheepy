<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | Sheepy</title>
</head>
<body>
    <main>
        <h1>Create an account</h1>

        <form method="post" action="<?= $escape($registerUrl) ?>">
            <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">

            <p>
                <label for="username">Username</label><br>
                <input
                    id="username"
                    name="username"
                    type="text"
                    value="<?= $escape($old['username'] ?? '') ?>"
                    minlength="3"
                    maxlength="50"
                    pattern="[A-Za-z0-9._-]+"
                    autocomplete="username"
                    required
                >
                <?php if (isset($errors['username'])): ?>
                    <br><span role="alert"><?= $escape($errors['username']) ?></span>
                <?php endif; ?>
            </p>

            <p>
                <label for="email">Email</label><br>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="<?= $escape($old['email'] ?? '') ?>"
                    maxlength="191"
                    autocomplete="email"
                    required
                >
                <?php if (isset($errors['email'])): ?>
                    <br><span role="alert"><?= $escape($errors['email']) ?></span>
                <?php endif; ?>
            </p>

            <p>
                <label for="phone">Phone (optional)</label><br>
                <input
                    id="phone"
                    name="phone"
                    type="tel"
                    value="<?= $escape($old['phone'] ?? '') ?>"
                    minlength="7"
                    maxlength="30"
                    pattern="[0-9+(). -]+"
                    autocomplete="tel"
                >
                <?php if (isset($errors['phone'])): ?>
                    <br><span role="alert"><?= $escape($errors['phone']) ?></span>
                <?php endif; ?>
            </p>

            <p>
                <label for="password">Password</label><br>
                <input
                    id="password"
                    name="password"
                    type="password"
                    minlength="8"
                    maxlength="72"
                    pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}"
                    title="Use at least 8 characters with uppercase, lowercase, and a number."
                    autocomplete="new-password"
                    required
                >
                <?php if (isset($errors['password'])): ?>
                    <br><span role="alert"><?= $escape($errors['password']) ?></span>
                <?php endif; ?>
            </p>

            <p>
                <label for="password_confirmation">Confirm password</label><br>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    minlength="8"
                    maxlength="72"
                    autocomplete="new-password"
                    required
                >
                <?php if (isset($errors['password_confirmation'])): ?>
                    <br><span role="alert"><?= $escape($errors['password_confirmation']) ?></span>
                <?php endif; ?>
            </p>

            <button type="submit">Register</button>
        </form>

        <p>Already registered? <a href="<?= $escape($loginUrl) ?>">Log in</a>.</p>
    </main>
</body>
</html>
