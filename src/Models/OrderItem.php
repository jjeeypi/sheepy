<?php

declare(strict_types=1);

namespace App\Models;

final readonly class OrderItem
{
    public function __construct(
        public int $id,
        public ?int $productId,
        public string $productName,
        public string $productSku,
        public string $unitPrice,
        public int $quantity,
        public string $lineTotal
    ) {
    }
}
