<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidationException;

final class CartValidator
{
    private const MAX_QUANTITY = 999;

    public function productId(mixed $value): int
    {
        $productId = $this->unsignedInteger($value);

        if ($productId === null || $productId < 1) {
            throw new ValidationException([
                'product_id' => 'Please select a valid product.',
            ]);
        }

        return $productId;
    }

    public function quantity(mixed $value): int
    {
        $quantity = $this->unsignedInteger($value);

        if ($quantity === null || $quantity < 1 || $quantity > self::MAX_QUANTITY) {
            throw new ValidationException([
                'quantity' => 'Quantity must be a whole number from 1 to 999.',
            ]);
        }

        return $quantity;
    }

    private function unsignedInteger(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (!is_string($value) || preg_match('/^\d+$/', $value) !== 1) {
            return null;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0],
        ]);

        return is_int($filtered) ? $filtered : null;
    }
}
