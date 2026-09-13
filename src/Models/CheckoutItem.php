<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;

final readonly class CheckoutItem
{
    public function __construct(
        public int $cartItemId,
        public int $productId,
        public string $productName,
        public string $productSku,
        public string $unitPrice,
        public int $quantity,
        public int $stockQuantity,
        public bool $isAvailable,
        public ?string $imageUrl,
        public ?string $imageAlt
    ) {
    }

    public function lineTotalCents(): int
    {
        return Money::toCents($this->unitPrice) * $this->quantity;
    }

    public function lineTotal(): string
    {
        return Money::format($this->lineTotalCents());
    }
}
