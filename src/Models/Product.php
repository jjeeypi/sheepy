<?php

declare(strict_types=1);

namespace App\Models;

final readonly class Product
{
    public function __construct(
        public int $id,
        public ?int $categoryId,
        public ?string $categoryName,
        public string $sku,
        public string $name,
        public string $slug,
        public ?string $description,
        public string $price,
        public int $stockQuantity,
        public bool $isActive,
        public ?int $imageId,
        public ?string $imageUrl,
        public ?string $imageAlt,
        public int $orderCount
    ) {
    }

    public function canBeDeleted(): bool
    {
        return $this->orderCount === 0;
    }
}
