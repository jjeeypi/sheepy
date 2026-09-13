<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\CheckoutSummary;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Validators\CheckoutValidator;

final class CheckoutService
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly CheckoutValidator $validator
    ) {
    }

    public function preview(int $userId): CheckoutSummary
    {
        $this->assertUserId($userId);

        return $this->orders->checkoutForUser($userId);
    }

    /** @param array<string, mixed> $input */
    public function confirm(int $userId, array $input): Order
    {
        $this->assertUserId($userId);

        return $this->orders->confirmFromActiveCart(
            $userId,
            $this->validator->validate($input)
        );
    }

    /** @return list<Order> */
    public function history(int $userId): array
    {
        $this->assertUserId($userId);

        return $this->orders->ordersForUser($userId);
    }

    public function receipt(int $userId, string $orderNumber): Order
    {
        $this->assertUserId($userId);

        if (preg_match('/^ORD-\d{8}-\d{6}$/', $orderNumber) !== 1) {
            throw new NotFoundException('The requested order was not found.');
        }

        $order = $this->orders->findForUserByNumber($userId, $orderNumber);

        if (!$order instanceof Order) {
            throw new NotFoundException('The requested order was not found.');
        }

        return $order;
    }

    private function assertUserId(int $userId): void
    {
        if ($userId < 1) {
            throw new ValidationException(['user_id' => 'Invalid customer account.']);
        }
    }
}
