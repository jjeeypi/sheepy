<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidationException;

final class CheckoutValidator
{
    /**
     * @param array<string, mixed> $input
     * @return array{
     *     shipping_name: string,
     *     shipping_phone: string,
     *     shipping_line1: string,
     *     shipping_line2: ?string,
     *     shipping_city: string,
     *     shipping_state: string,
     *     shipping_postal_code: string,
     *     shipping_country: string
     * }
     */
    public function validate(array $input): array
    {
        $fields = [
            'shipping_name' => ['label' => 'Full name', 'max' => 150, 'required' => true],
            'shipping_phone' => ['label' => 'Phone', 'max' => 30, 'required' => true],
            'shipping_line1' => ['label' => 'Address line 1', 'max' => 255, 'required' => true],
            'shipping_line2' => ['label' => 'Address line 2', 'max' => 255, 'required' => false],
            'shipping_city' => ['label' => 'City', 'max' => 100, 'required' => true],
            'shipping_state' => ['label' => 'State or province', 'max' => 100, 'required' => true],
            'shipping_postal_code' => ['label' => 'Postal code', 'max' => 20, 'required' => true],
            'shipping_country' => ['label' => 'Country', 'max' => 100, 'required' => true],
        ];
        $values = [];
        $errors = [];

        foreach ($fields as $name => $rules) {
            $rawValue = $input[$name] ?? null;
            $value = is_scalar($rawValue) ? trim((string) $rawValue) : '';

            if ($rules['required'] && $value === '') {
                $errors[$name] = $rules['label'] . ' is required.';
            } elseif (strlen($value) > $rules['max']) {
                $errors[$name] = sprintf(
                    '%s must not exceed %d characters.',
                    $rules['label'],
                    $rules['max']
                );
            }

            $values[$name] = $value;
        }

        if (
            $values['shipping_phone'] !== ''
            && preg_match('/^[0-9+().\-\s]{7,30}$/', $values['shipping_phone']) !== 1
        ) {
            $errors['shipping_phone'] = 'Enter a valid phone number.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors, 'Please complete the shipping address.');
        }

        return [
            'shipping_name' => $values['shipping_name'],
            'shipping_phone' => $values['shipping_phone'],
            'shipping_line1' => $values['shipping_line1'],
            'shipping_line2' => $values['shipping_line2'] === ''
                ? null
                : $values['shipping_line2'],
            'shipping_city' => $values['shipping_city'],
            'shipping_state' => $values['shipping_state'],
            'shipping_postal_code' => $values['shipping_postal_code'],
            'shipping_country' => $values['shipping_country'],
        ];
    }
}
