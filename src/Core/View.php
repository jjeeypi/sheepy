<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class View
{
    private readonly string $basePath;

    public function __construct(?string $basePath = null)
    {
        $resolvedPath = realpath($basePath ?? dirname(__DIR__, 2) . '/views');

        if ($resolvedPath === false || !is_dir($resolvedPath)) {
            throw new InvalidArgumentException('The view directory does not exist.');
        }

        $this->basePath = rtrim($resolvedPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /** @param array<string, mixed> $data */
    public function render(string $view, array $data = []): string
    {
        if (preg_match('#^[A-Za-z0-9_/-]+$#', $view) !== 1) {
            throw new InvalidArgumentException('The view name is invalid.');
        }

        $candidate = $this->basePath
            . str_replace('/', DIRECTORY_SEPARATOR, trim($view, '/'))
            . '.php';
        $resolvedView = realpath($candidate);

        if ($resolvedView === false || !is_file($resolvedView)) {
            throw new RuntimeException(sprintf('View "%s" was not found.', $view));
        }

        $normalizedBase = str_replace('\\', '/', $this->basePath);
        $normalizedView = str_replace('\\', '/', $resolvedView);

        if (!str_starts_with($normalizedView, $normalizedBase)) {
            throw new RuntimeException('The resolved view is outside the view directory.');
        }

        $escape = static fn (mixed $value): string => htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        extract($data, EXTR_SKIP);
        ob_start();

        try {
            require $resolvedView;

            return (string) ob_get_clean();
        } catch (Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }
    }
}
