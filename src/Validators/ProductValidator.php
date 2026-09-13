<?php

declare(strict_types=1);

namespace App\Validators;

final class ProductValidator
{
    /**
     * @param array<string, mixed> $input
     * @return array{
     *     data: array{
     *         category_id: ?int,
     *         sku: string,
     *         name: string,
     *         description: ?string,
     *         price: string,
     *         stock_quantity: int,
     *         is_active: bool
     *     },
     *     errors: array<string, string>
     * }
     */
    public function product(array $input): array
    {
        $categoryValue = $this->scalar($input['category_id'] ?? null);
        $sku = strtoupper($this->scalar($input['sku'] ?? null));
        $name = $this->scalar($input['name'] ?? null);
        $descriptionValue = $this->scalar($input['description'] ?? null);
        $description = $descriptionValue === '' ? null : $descriptionValue;
        $priceValue = $this->scalar($input['price'] ?? null);
        $stockValue = $this->scalar($input['stock_quantity'] ?? null);
        $isActive = filter_var(
            $input['is_active'] ?? false,
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        ) ?? false;
        $errors = [];
        $categoryId = null;
        $price = $priceValue;
        $stockQuantity = 0;

        if ($categoryValue !== '') {
            if (preg_match('/^[1-9][0-9]*$/', $categoryValue) !== 1) {
                $errors['category_id'] = 'Choose a valid category.';
            } else {
                $categoryId = (int) $categoryValue;
            }
        }

        if ($sku === '') {
            $errors['sku'] = 'SKU is required.';
        } elseif (strlen($sku) > 64 || preg_match('/^[A-Z0-9._-]+$/', $sku) !== 1) {
            $errors['sku'] = 'SKU may contain up to 64 letters, numbers, dots, underscores, and hyphens.';
        }

        if ($name === '') {
            $errors['name'] = 'Product name is required.';
        } elseif (strlen($name) > 200) {
            $errors['name'] = 'Product name may contain up to 200 characters.';
        }

        if ($description !== null && strlen($description) > 5000) {
            $errors['description'] = 'Description may contain up to 5,000 characters.';
        }

        if (preg_match('/^[0-9]{1,8}(?:\.[0-9]{1,2})?$/', $priceValue) !== 1) {
            $errors['price'] = 'Enter a price from 0.01 to 99,999,999.99 with at most two decimals.';
        } else {
            [$whole, $fraction] = array_pad(explode('.', $priceValue, 2), 2, '');
            $whole = ltrim($whole, '0');
            $whole = $whole === '' ? '0' : $whole;
            $fraction = str_pad($fraction, 2, '0');
            $price = $whole . '.' . $fraction;

            if ($price === '0.00') {
                $errors['price'] = 'Price must be at least 0.01.';
            }
        }

        if (preg_match('/^[0-9]{1,10}$/', $stockValue) !== 1) {
            $errors['stock_quantity'] = 'Stock must be a whole number from 0 to 4,294,967,295.';
        } else {
            $stockQuantity = (int) $stockValue;

            if ($stockQuantity > 4294967295) {
                $errors['stock_quantity'] = 'Stock must not exceed 4,294,967,295.';
            }
        }

        return [
            'data' => [
                'category_id' => $categoryId,
                'sku' => $sku,
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock_quantity' => $stockQuantity,
                'is_active' => $isActive,
            ],
            'errors' => $errors,
        ];
    }

    private function scalar(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}
