<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Models\Cart;
use App\Repositories\CartRepository;
use App\Validators\CartValidator;

final class CartService
{
    public function __construct(
        private readonly CartRepository $carts,
        private readonly CartValidator $validator
    ) {
    }

    public function get(int $userId): Cart
    {
        $this->assertUserId($userId);

        return $this->carts->cartForUser($userId);
    }

    public function itemCount(int $userId): int
    {
        $this->assertUserId($userId);

        return $this->carts->itemQuantityForUser($userId);
    }

    public function add(int $userId, mixed $productId, mixed $quantity = 1): Cart
    {
        $this->assertUserId($userId);
        $this->carts->addItem(
            $userId,
            $this->validator->productId($productId),
            $this->validator->quantity($quantity)
        );

        return $this->carts->cartForUser($userId);
    }

    public function update(int $userId, int $cartItemId, mixed $quantity): Cart
    {
        $this->assertUserId($userId);

        if ($cartItemId < 1) {
            throw new ValidationException(['cart_item_id' => 'Invalid cart item.']);
        }

        $this->carts->updateItem(
            $userId,
            $cartItemId,
            $this->validator->quantity($quantity)
        );

        return $this->carts->cartForUser($userId);
    }

    public function remove(int $userId, int $cartItemId): Cart
    {
        $this->assertUserId($userId);

        if ($cartItemId < 1) {
            throw new ValidationException(['cart_item_id' => 'Invalid cart item.']);
        }

        $this->carts->removeItem($userId, $cartItemId);

        return $this->carts->cartForUser($userId);
    }

    private function assertUserId(int $userId): void
    {
        if ($userId < 1) {
            throw new ValidationException(['user_id' => 'Invalid cart owner.']);
        }
    }
}
