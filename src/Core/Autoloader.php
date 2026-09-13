<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;

final class Autoloader
{
    /** @var array<string, string> */
    private static array $prefixes = [];

    private static bool $registered = false;

    /**
     * @param array<string, string> $prefixes Namespace prefixes mapped to directories.
     */
    public static function register(array $prefixes = []): void
    {
        foreach ($prefixes as $prefix => $baseDirectory) {
            self::addNamespace($prefix, $baseDirectory);
        }

        if (self::$registered) {
            return;
        }

        spl_autoload_register([self::class, 'load'], true, true);
        self::$registered = true;
    }

    public static function addNamespace(string $prefix, string $baseDirectory): void
    {
        $prefix = trim($prefix, '\\') . '\\';

        if ($prefix === '\\' || preg_match('/^(?:[A-Za-z_][A-Za-z0-9_]*\\\\)+$/', $prefix) !== 1) {
            throw new InvalidArgumentException('The namespace prefix is invalid.');
        }

        $resolvedDirectory = realpath($baseDirectory);

        if ($resolvedDirectory === false || !is_dir($resolvedDirectory)) {
            throw new InvalidArgumentException('The autoload directory does not exist.');
        }

        self::$prefixes[$prefix] = rtrim($resolvedDirectory, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR;

        uksort(
            self::$prefixes,
            static fn (string $left, string $right): int => strlen($right) <=> strlen($left)
        );
    }

    public static function load(string $class): void
    {
        foreach (self::$prefixes as $prefix => $baseDirectory) {
            if (!str_starts_with($class, $prefix)) {
                continue;
            }

            $relativeClass = substr($class, strlen($prefix));

            if ($relativeClass === '' || str_contains($relativeClass, "\0")) {
                return;
            }

            $candidate = $baseDirectory
                . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass)
                . '.php';
            $resolvedFile = realpath($candidate);

            if ($resolvedFile === false || !is_file($resolvedFile)) {
                continue;
            }

            $normalizedBase = str_replace('\\', '/', $baseDirectory);
            $normalizedFile = str_replace('\\', '/', $resolvedFile);

            if (!str_starts_with($normalizedFile, $normalizedBase)) {
                return;
            }

            require_once $resolvedFile;

            return;
        }
    }
}
