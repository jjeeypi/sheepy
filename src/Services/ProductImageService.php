<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use RuntimeException;

final class ProductImageService
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;
    private const MAX_PIXEL_COUNT = 20_000_000;

    /** @var array<string, string> */
    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    private readonly string $directory;

    public function __construct(?string $directory = null)
    {
        $directory ??= dirname(__DIR__, 2) . '/public/uploads/products';

        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            throw new RuntimeException('The product image directory could not be created.');
        }

        $resolved = realpath($directory);

        if ($resolved === false || !is_writable($resolved)) {
            throw new RuntimeException('The product image directory is not writable.');
        }

        $this->directory = rtrim($resolved, DIRECTORY_SEPARATOR);
    }

    /**
     * @return array{url: string, path: string}|null
     * @throws ValidationException
     */
    public function store(mixed $file, bool $required): ?array
    {
        if (!is_array($file)) {
            if ($required) {
                throw new ValidationException(['image' => 'Choose a product image.']);
            }

            return null;
        }

        $error = isset($file['error']) && is_int($file['error'])
            ? $file['error']
            : UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            if ($required) {
                throw new ValidationException(['image' => 'Choose a product image.']);
            }

            return null;
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new ValidationException(['image' => $this->uploadErrorMessage($error)]);
        }

        $temporaryPath = $file['tmp_name'] ?? null;

        if (!is_string($temporaryPath) || !is_uploaded_file($temporaryPath)) {
            throw new ValidationException(['image' => 'The uploaded image could not be verified.']);
        }

        $size = filesize($temporaryPath);

        if ($size === false || $size < 1 || $size > self::MAX_FILE_SIZE) {
            throw new ValidationException(['image' => 'Image size must be between 1 byte and 5 MB.']);
        }

        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($temporaryPath);
        $extension = is_string($mimeType) ? (self::ALLOWED_TYPES[$mimeType] ?? null) : null;
        $imageInfo = @getimagesize($temporaryPath);

        if (
            $extension === null
            || !is_array($imageInfo)
            || ($imageInfo['mime'] ?? null) !== $mimeType
        ) {
            throw new ValidationException([
                'image' => 'Upload a valid JPG, PNG, WebP, or GIF image.',
            ]);
        }

        $width = (int) ($imageInfo[0] ?? 0);
        $height = (int) ($imageInfo[1] ?? 0);

        if ($width < 1 || $height < 1 || ($width * $height) > self::MAX_PIXEL_COUNT) {
            throw new ValidationException(['image' => 'The image dimensions are too large.']);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $this->directory . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($temporaryPath, $destination)) {
            throw new RuntimeException('The product image could not be saved.');
        }

        @chmod($destination, 0644);

        return [
            'url' => '/uploads/products/' . $filename,
            'path' => $destination,
        ];
    }

    public function removeStoredFile(string $path): void
    {
        $resolvedDirectory = str_replace('\\', '/', $this->directory) . '/';
        $normalizedPath = str_replace('\\', '/', $path);

        if (str_starts_with($normalizedPath, $resolvedDirectory) && is_file($path)) {
            $this->deleteFile($path);
        }
    }

    public function removeByUrl(string $url): void
    {
        if (preg_match('#^/uploads/products/([a-f0-9]{32}\.(?:jpg|png|webp|gif))$#', $url, $matches) !== 1) {
            return;
        }

        $path = $this->directory . DIRECTORY_SEPARATOR . $matches[1];

        if (is_file($path)) {
            $this->deleteFile($path);
        }
    }

    private function deleteFile(string $path): void
    {
        if (!@unlink($path)) {
            error_log('A retired product image could not be removed from private filesystem path: ' . $path);
        }
    }

    private function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The image exceeds the upload size limit.',
            UPLOAD_ERR_PARTIAL => 'The image upload was interrupted. Please try again.',
            default => 'The image could not be uploaded. Please try again.',
        };
    }
}
