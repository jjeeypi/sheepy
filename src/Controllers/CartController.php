<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Services\AuthService;
use App\Services\CartService;

final class CartController
{
    public function __construct(
        private readonly CartService $cart,
        private readonly AuthService $auth,
        private readonly Csrf $csrf
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->auth->currentUser();

        if (!$user instanceof User) {
            return $this->error('Authentication is required.', [], 401);
        }

        return $this->cartResponse($request, $this->cart->get($user->id));
    }

    public function store(Request $request): Response
    {
        return $this->mutate($request, function (User $user) use ($request): Cart {
            return $this->cart->add(
                $user->id,
                $request->body('product_id'),
                $request->body('quantity', 1)
            );
        }, 'Product added to your cart.');
    }

    public function update(Request $request, int $id): Response
    {
        return $this->mutate($request, function (User $user) use ($request, $id): Cart {
            return $this->cart->update($user->id, $id, $request->body('quantity'));
        }, 'Cart quantity updated.');
    }

    public function destroy(Request $request, int $id): Response
    {
        return $this->mutate($request, function (User $user) use ($id): Cart {
            return $this->cart->remove($user->id, $id);
        }, 'Product removed from your cart.');
    }

    /** @param callable(User): Cart $operation */
    private function mutate(Request $request, callable $operation, string $message): Response
    {
        $user = $this->auth->currentUser();

        if (!$user instanceof User) {
            return $this->error('Authentication is required.', [], 401);
        }

        $token = $request->body('_token') ?? $request->header('X-CSRF-Token');

        if (!$this->csrf->validate($token)) {
            return $this->error('Your session token expired. Refresh the page and try again.', [], 403);
        }

        try {
            $cart = $operation($user);
        } catch (ValidationException $exception) {
            return $this->error($exception->getMessage(), $exception->errors(), 422);
        } catch (NotFoundException $exception) {
            return $this->error($exception->getMessage(), [], 404);
        }

        return $this->cartResponse($request, $cart, $message);
    }

    private function cartResponse(Request $request, Cart $cart, ?string $message = null): Response
    {
        $payload = [
            'cart' => [
                'id' => $cart->id,
                'items' => array_map(
                    fn (CartItem $item): array => $this->itemPayload($request, $item),
                    $cart->items
                ),
                'item_count' => $cart->itemCount(),
                'subtotal' => $cart->subtotal(),
            ],
        ];

        if ($message !== null) {
            $payload = ['message' => $message, ...$payload];
        }

        return Response::json($payload)->withHeader('Cache-Control', 'no-store');
    }

    /** @return array<string, bool|int|string|null> */
    private function itemPayload(Request $request, CartItem $item): array
    {
        return [
            'id' => $item->id,
            'product_id' => $item->productId,
            'product_url' => $request->url('/products/' . $item->productSlug),
            'name' => $item->productName,
            'description' => $item->description ?? '',
            'unit_price' => $item->unitPrice,
            'quantity' => $item->quantity,
            'max_quantity' => $item->stockQuantity,
            'available' => $item->isAvailable,
            'image_url' => $item->imageUrl === null
                ? null
                : $request->basePath() . $item->imageUrl,
            'image_alt' => trim($item->imageAlt ?? '') !== ''
                ? $item->imageAlt
                : $item->productName,
            'line_subtotal' => $item->lineSubtotal(),
        ];
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
