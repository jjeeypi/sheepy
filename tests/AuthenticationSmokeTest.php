<?php

declare(strict_types=1);

namespace Tests;

use App\Core\Autoloader;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\User;
use App\Validators\UserValidator;
use RuntimeException;

require dirname(__DIR__) . '/src/Core/Autoloader.php';

Autoloader::register(['App\\' => dirname(__DIR__) . '/src']);

$check = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$validator = new UserValidator();
$valid = $validator->registration([
    'username' => 'customer.one',
    'email' => 'CUSTOMER@example.test',
    'phone' => '+63 900 000 0000',
    'password' => 'SecurePass9',
    'password_confirmation' => 'SecurePass9',
]);
$check($valid['errors'] === [], 'Valid registration data was rejected.');
$check(
    $valid['data']['email'] === 'customer@example.test',
    'Registration email normalization failed.'
);

$invalid = $validator->registration([
    'username' => '<script>',
    'email' => 'not-an-email',
    'phone' => 'letters',
    'password' => 'weak',
    'password_confirmation' => 'different',
]);
$check(
    array_keys($invalid['errors']) === [
        'username', 'email', 'phone', 'password', 'password_confirmation',
    ],
    'Invalid registration fields were not all rejected.'
);

$hash = password_hash('SecurePass9', PASSWORD_DEFAULT);
$customer = new User(7, 'customer.one', 'customer@example.test', $hash, null, 'customer');
$check($customer->passwordMatches('SecurePass9'), 'Valid password verification failed.');
$check(!$customer->passwordMatches('incorrect'), 'Invalid password was accepted.');
$check(!$customer->isAdmin(), 'Customer was incorrectly treated as an admin.');

$request = new Request('GET', '/login', applicationBasePath: '/sheepy/public');
$check(
    $request->url('/home') === '/sheepy/public/home',
    'Application base-path URL generation failed.'
);

$session = Session::start([
    'name' => 'sheepy_auth_smoke',
    'idle_timeout' => 300,
    'rotation_interval' => 300,
]);
$csrf = new Csrf($session);
$token = $csrf->token();
$check(strlen($token) === 64, 'CSRF token length is invalid.');
$check($csrf->validate($token), 'A valid CSRF token was rejected.');
$check(!$csrf->validate(str_repeat('0', 64)), 'An invalid CSRF token was accepted.');
$check($csrf->rotate() !== $token, 'CSRF token rotation failed.');

$view = new View();
$html = $view->render('auth/login', [
    'errors' => [],
    'old' => ['identifier' => '<script>alert(1)</script>'],
    'csrfToken' => $csrf->token(),
    'loginUrl' => '/login',
    'registerUrl' => '/register',
]);
$check(!str_contains($html, '<script>alert(1)</script>'), 'View output was not escaped.');
$check(
    str_contains($html, '&lt;script&gt;alert(1)&lt;/script&gt;'),
    'Escaped view output was not rendered as expected.'
);
$check(
    str_contains($html, 'assets/css/auth.css')
    && str_contains($html, 'assets/js/auth.js'),
    'Authentication assets were not linked from the login view.'
);
$check(
    str_contains($html, 'data-password-toggle="password"')
    && str_contains($html, 'aria-labelledby="auth-title"'),
    'The accessible login interactions are missing.'
);
$check(
    str_contains($html, 'assets/images/logo/logo.svg')
    && str_contains($html, 'class="auth-brand-logo"'),
    'The shared Sheepy logo is missing from the authentication view.'
);

$registrationHtml = $view->render('auth/register', [
    'errors' => ['email' => 'Enter a valid email address.'],
    'old' => [
        'username' => 'customer.one',
        'email' => 'invalid-email',
        'phone' => '+63 900 000 0000',
    ],
    'csrfToken' => $csrf->token(),
    'loginUrl' => '/login',
    'registerUrl' => '/register',
]);
$check(
    str_contains($registrationHtml, 'data-registration-form')
    && str_contains($registrationHtml, 'data-password-meter')
    && str_contains($registrationHtml, 'data-password-confirmation'),
    'Registration password feedback hooks are missing.'
);
$check(
    str_contains($registrationHtml, 'id="email-error"')
    && str_contains($registrationHtml, 'aria-invalid="true"'),
    'Registration errors are not connected to their fields.'
);

$authCss = file_get_contents(dirname(__DIR__) . '/public/assets/css/auth.css');
$authScript = file_get_contents(dirname(__DIR__) . '/public/assets/js/auth.js');
$check(
    is_string($authCss)
    && str_contains($authCss, '@media (max-width: 640px)')
    && str_contains($authCss, '@media (prefers-reduced-motion: reduce)'),
    'The authentication stylesheet lacks responsive or reduced-motion support.'
);
$check(
    is_string($authScript)
    && str_contains($authScript, 'initializePasswordToggles')
    && str_contains($authScript, 'initializeRegistrationFeedback'),
    'The authentication interaction script is incomplete.'
);

$session->invalidate();

fwrite(STDOUT, 'Phase 3 authentication smoke test passed.' . PHP_EOL);
