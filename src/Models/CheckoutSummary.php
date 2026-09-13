<?php

declare(strict_types=1);

namespace App\Models;

final readonly class CheckoutSummary
{
    /** @param list<CheckoutItem> $items */
    public function __construct(
        public array $items,
        public string $subtotal,
        public string $shippingTotal,
        public string $taxTotal,
        public string $grandTotal
    ) {
    }
}
