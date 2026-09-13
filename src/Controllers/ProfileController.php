<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\User;
use App\Services\AuthService;
use App\Services\CartService;
use App\Services\CatalogService;

final class ProfileController extends BaseController
{
    public function __construct(
        View $view,
        private readonly AuthService $auth,
        private readonly Csrf $csrf,
        private readonly CatalogService $catalog,
        private readonly CartService $cart
    ) {
        parent::__construct($view);
    }

    public function show(Request $request): Response
    {
        $user = $this->auth->currentUser();

        if (!$user instanceof User) {
            return Response::redirect($request->url('/login'));
        }

        return $this->render('profile/show', [
            'user' => $user,
            'navigation' => $this->catalog->navigation(),
            'csrfToken' => $this->csrf->token(),
            'logoutUrl' => $request->url('/logout'),
            'homeUrl' => $request->url('/home'),
            'productsUrl' => $request->url('/products'),
            'categoriesUrl' => $request->url('/categories'),
            'profileUrl' => $request->url('/profile'),
            'ordersUrl' => $request->url('/orders'),
            'basePath' => $request->basePath(),
            'cartUrl' => $request->url('/cart'),
            'cartItemsUrl' => $request->url('/cart/items'),
            'checkoutUrl' => $request->url('/checkout'),
            'checkoutConfirmUrl' => $request->url('/checkout/confirm'),
            'cartItemCount' => $this->cart->itemCount($user->id),
        ]);
    }
}
