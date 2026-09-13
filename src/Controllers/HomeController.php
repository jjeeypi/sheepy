<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\AuthService;
use App\Services\CartService;
use App\Services\CatalogService;

final class HomeController extends BaseController
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

    public function index(Request $request): Response
    {
        $user = $this->auth->currentUser();

        return $this->render('home/index', [
            'user' => $user,
            'csrfToken' => $this->csrf->token(),
            'logoutUrl' => $request->url('/logout'),
            'homeUrl' => $request->url('/home'),
            'productsUrl' => $request->url('/products'),
            'categoriesUrl' => $request->url('/categories'),
            'basePath' => $request->basePath(),
            'navigation' => $this->catalog->navigation(),
            'latestProducts' => $this->catalog->latest(),
            'cartUrl' => $request->url('/cart'),
            'cartItemsUrl' => $request->url('/cart/items'),
            'checkoutUrl' => $request->url('/checkout'),
            'checkoutConfirmUrl' => $request->url('/checkout/confirm'),
            'ordersUrl' => $request->url('/orders'),
            'cartItemCount' => $user === null ? 0 : $this->cart->itemCount($user->id),
        ]);
    }
}
