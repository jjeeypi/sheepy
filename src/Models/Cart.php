<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;

final readonly class Cart
{
    /** @param list<CartItem> $items */
    public function __construct(
        public ?int $id,
        public int $userId,
        public array $items
    ) {
    }

    public function itemCount(): int
    {
        return array_sum(array_map(
            static fn (CartItem $item): int => $item->quantity,
            $this->items
        ));
    }

    public function subtotal(): string
    {
        $cents = array_sum(array_map(
            static fn (CartItem $item): int => $item->lineSubtotalCents(),
            $this->items
        ));

        return Money::format($cents);
    }
}
