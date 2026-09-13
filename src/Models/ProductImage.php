<?php

declare(strict_types=1);

namespace App\Models;

final readonly class ProductImage
{
    public function __construct(
        public int $id,
        public string $url,
        public string $altText,
        public int $sortOrder
    ) {
    }
}
