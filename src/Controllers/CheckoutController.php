<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\CheckoutItem;
use App\Models\CheckoutSummary;
use App\Models\User;
use App\Services\AuthService;
use App\Services\CheckoutService;

final class CheckoutController
{
    public function __construct(
        private readonly CheckoutService $checkout,
        private readonly AuthService $auth,
        private readonly Csrf $csrf
    ) {
    }

    public function show(Request $request): Response
    {
        $user = $this->auth->currentUser();

        if (!$user instanceof User) {
            return $this->error('Authentication is required.', [], 401);
        }

        return $this->previewResponse($request, $this->checkout->preview($user->id));
    }

    public function confirm(Request $request): Response
    {
        $user = $this->auth->currentUser();

        if (!$user instanceof User) {
            return $this->error('Authentication is required.', [], 401);
        }

        $token = $request->body('_token') ?? $request->header('X-CSRF-Token');

        if (!$this->csrf->validate($token)) {
            return $this->error(
                'Your session token expired. Refresh the page and try again.',
                [],
                403
            );
        }

        try {
            $body = $request->body();
            $order = $this->checkout->confirm($user->id, is_array($body) ? $body : []);
        } catch (ValidationException $exception) {
            return $this->error($exception->getMessage(), $exception->errors(), 422);
        } catch (NotFoundException $exception) {
            return $this->error($exception->getMessage(), [], 404);
        }

        return Response::json([
            'message' => 'Purchased Successfully!',
            'order' => [
                'number' => $order->orderNumber,
                'status' => $order->status,
                'grand_total' => $order->grandTotal,
                'receipt_url' => $request->url('/orders/' . $order->orderNumber),
                'history_url' => $request->url('/orders'),
            ],
            'cart' => [
                'id' => null,
                'items' => [],
                'item_count' => 0,
                'subtotal' => '0.00',
            ],
        ])->withHeader('Cache-Control', 'no-store');
    }

    private function previewResponse(Request $request, CheckoutSummary $summary): Response
    {
        return Response::json([
            'checkout' => [
                'items' => array_map(
                    fn (CheckoutItem $item): array => [
                        'cart_item_id' => $item->cartItemId,
                        'product_id' => $item->productId,
                        'name' => $item->productName,
                        'sku' => $item->productSku,
                        'unit_price' => $item->unitPrice,
                        'quantity' => $item->quantity,
                        'line_total' => $item->lineTotal(),
                        'available' => $item->isAvailable
                            && $item->quantity <= $item->stockQuantity,
                        'stock_quantity' => $item->stockQuantity,
                        'image_url' => $item->imageUrl === null
                            ? null
                            : $request->basePath() . $item->imageUrl,
                        'image_alt' => trim($item->imageAlt ?? '') !== ''
                            ? $item->imageAlt
                            : $item->productName,
                    ],
                    $summary->items
                ),
                'subtotal' => $summary->subtotal,
                'shipping_total' => $summary->shippingTotal,
                'tax_total' => $summary->taxTotal,
                'grand_total' => $summary->grandTotal,
            ],
        ])->withHeader('Cache-Control', 'no-store');
    }

    /** @param array<string, string> $errors */
    private function error(string $message, array $errors, int $status): Response
    {
        return Response::json([
            'error' => $message,
            'errors' => $errors,
        ], $status)->withHeader('Cache-Control', 'no-store');
    }
}
