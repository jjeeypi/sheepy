<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Session
{
    /** @param array<string, mixed> $cookieParameters */
    private function __construct(
        private readonly array $cookieParameters,
        private readonly int $idleTimeout,
        private readonly int $rotationInterval
    ) {
    }

    /** @param array<string, mixed> $options */
    public static function start(array $options = []): self
    {
        if (session_status() === PHP_SESSION_DISABLED) {
            throw new RuntimeException('PHP sessions are disabled.');
        }

        $secure = $options['secure'] ?? self::requestUsesHttps();
        $cookieParameters = [
            'lifetime' => max(0, (int) ($options['lifetime'] ?? 0)),
            'path' => (string) ($options['path'] ?? '/'),
            'domain' => (string) ($options['domain'] ?? ''),
            'secure' => (bool) $secure,
            'httponly' => true,
            'samesite' => (string) ($options['samesite'] ?? 'Lax'),
        ];

        if (session_status() !== PHP_SESSION_ACTIVE) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_trans_sid', '0');
            session_name((string) ($options['name'] ?? 'sheepy_session'));
            session_set_cookie_params($cookieParameters);

            if (!session_start()) {
                throw new RuntimeException('The session could not be started.');
            }
        }

        $session = new self(
            $cookieParameters,
            max(60, (int) ($options['idle_timeout'] ?? 1800)),
            max(60, (int) ($options['rotation_interval'] ?? 900))
        );
        $session->refreshSecurityMetadata();

        return $session;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->forget($key);

        return $value;
    }

    public function regenerate(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE || !session_regenerate_id(true)) {
            throw new RuntimeException('The session identifier could not be regenerated.');
        }

        $_SESSION['_session_meta'] = [
            'created_at' => time(),
            'last_activity' => time(),
            'rotated_at' => time(),
        ];
    }

    public function invalidate(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => (string) ($this->cookieParameters['path'] ?? '/'),
                'domain' => (string) ($this->cookieParameters['domain'] ?? ''),
                'secure' => (bool) ($this->cookieParameters['secure'] ?? false),
                'httponly' => true,
                'samesite' => (string) ($this->cookieParameters['samesite'] ?? 'Lax'),
            ]);
        }

        session_destroy();
    }

    private function refreshSecurityMetadata(): void
    {
        $now = time();
        $metadata = $_SESSION['_session_meta'] ?? [];
        $lastActivity = (int) ($metadata['last_activity'] ?? $now);

        if ($now - $lastActivity > $this->idleTimeout) {
            $_SESSION = [];
            $this->regenerate();
            $metadata = $_SESSION['_session_meta'];
        }

        $rotatedAt = (int) ($metadata['rotated_at'] ?? $now);

        if ($now - $rotatedAt >= $this->rotationInterval) {
            $this->regenerate();
            $metadata = $_SESSION['_session_meta'];
        }

        $_SESSION['_session_meta'] = [
            'created_at' => (int) ($metadata['created_at'] ?? $now),
            'last_activity' => $now,
            'rotated_at' => (int) ($metadata['rotated_at'] ?? $now),
        ];
    }

    private static function requestUsesHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
            || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
    }
}
