<?php

declare(strict_types=1);

namespace App\Models;

final readonly class CartItem
{
    public function __construct(
        public int $id,
        public int $productId,
        public string $productSlug,
        public string $productName,
        public ?string $description,
        public string $unitPrice,
        public int $quantity,
        public int $stockQuantity,
        public bool $isAvailable,
        public ?string $imageUrl,
        public ?string $imageAlt
    ) {
    }

    public function lineSubtotalCents(): int
    {
        return self::priceToCents($this->unitPrice) * $this->quantity;
    }

    public function lineSubtotal(): string
    {
        return self::formatCents($this->lineSubtotalCents());
    }

    public static function formatCents(int $cents): string
    {
        return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }

    private static function priceToCents(string $price): int
    {
        [$whole, $fraction] = array_pad(explode('.', $price, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }
}
