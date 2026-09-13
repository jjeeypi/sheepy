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

$session->invalidate();

fwrite(STDOUT, 'Phase 3 authentication smoke test passed.' . PHP_EOL);
