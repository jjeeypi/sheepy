<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CheckoutItem;
use App\Models\CheckoutSummary;
use App\Support\Money;

final class CheckoutPricing
{
    private const FREE_SHIPPING_THRESHOLD_CENTS = 7500;
    private const STANDARD_SHIPPING_CENTS = 600;
    private const TAX_CENTS = 0;

    /** @param list<CheckoutItem> $items */
    public function summarize(array $items): CheckoutSummary
    {
        $subtotalCents = array_sum(array_map(
            static fn (CheckoutItem $item): int => $item->lineTotalCents(),
            $items
        ));
        $shippingCents = $subtotalCents === 0
            || $subtotalCents >= self::FREE_SHIPPING_THRESHOLD_CENTS
                ? 0
                : self::STANDARD_SHIPPING_CENTS;
        $taxCents = self::TAX_CENTS;

        return new CheckoutSummary(
            $items,
            Money::format($subtotalCents),
            Money::format($shippingCents),
            Money::format($taxCents),
            Money::format($subtotalCents + $shippingCents + $taxCents)
        );
    }
}
