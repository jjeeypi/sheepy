<?php

declare(strict_types=1);

namespace App\Models;

final readonly class Order
{
    /** @param list<OrderItem> $items */
    public function __construct(
        public int $id,
        public int $userId,
        public string $orderNumber,
        public string $status,
        public string $subtotal,
        public string $shippingTotal,
        public string $taxTotal,
        public string $grandTotal,
        public string $shippingName,
        public ?string $shippingPhone,
        public string $shippingLine1,
        public ?string $shippingLine2,
        public string $shippingCity,
        public ?string $shippingState,
        public string $shippingPostalCode,
        public string $shippingCountry,
        public string $placedAt,
        public array $items
    ) {
    }
}
