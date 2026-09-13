<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Exceptions\NotFoundException;
use App\Models\User;
use App\Services\AuthService;
use App\Services\CheckoutService;

final class OrderController extends BaseController
{
    public function __construct(
        View $view,
        private readonly CheckoutService $checkout,
        private readonly AuthService $auth,
        private readonly Csrf $csrf
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        $user = $this->auth->currentUser();

        if (!$user instanceof User) {
            return Response::redirect($request->url('/login'));
        }

        return $this->render('orders/index', [
            'orders' => $this->checkout->history($user->id),
            ...$this->viewData($request, $user),
        ]);
    }

    public function show(Request $request, string $orderNumber): Response
    {
        $user = $this->auth->currentUser();

        if (!$user instanceof User) {
            return Response::redirect($request->url('/login'));
        }

        try {
            $order = $this->checkout->receipt($user->id, $orderNumber);
        } catch (NotFoundException) {
            return $this->notFoundPage();
        }

        return $this->render('orders/show', [
            'order' => $order,
            ...$this->viewData($request, $user),
        ]);
    }

    /** @return array<string, mixed> */
    private function viewData(Request $request, User $user): array
    {
        return [
            'user' => $user,
            'csrfToken' => $this->csrf->token(),
            'logoutUrl' => $request->url('/logout'),
            'homeUrl' => $request->url('/home'),
            'productsUrl' => $request->url('/products'),
            'ordersUrl' => $request->url('/orders'),
        ];
    }
}
