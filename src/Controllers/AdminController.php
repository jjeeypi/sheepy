<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\AuthService;
use App\Services\ProductService;

final class AdminController extends BaseController
{
    public function __construct(
        View $view,
        private readonly AuthService $auth,
        private readonly Csrf $csrf,
        private readonly ProductService $products
    ) {
        parent::__construct($view);
    }

    public function dashboard(Request $request): Response
    {
        $allProducts = $this->products->all();
        $productCount = count($allProducts);
        $activeCount = count(array_filter($allProducts, static fn($p) => $p->isActive));
        
        // Sort descending by ID to simulate recent products
        usort($allProducts, static fn($a, $b) => $b->id <=> $a->id);
        $recentProducts = array_slice($allProducts, 0, 5);

        return $this->render('admin/dashboard', [
            'user' => $this->auth->currentUser(),
            'csrfToken' => $this->csrf->token(),
            'logoutUrl' => $request->url('/logout'),
            'addProductUrl' => $request->url('/admin/products/create'),
            'manageProductsUrl' => $request->url('/admin/products'),
            'productCount' => $productCount,
            'activeCount' => $activeCount,
            'recentProducts' => $recentProducts,
            'basePath' => $request->basePath(),
            'activeNav' => 'dashboard',
        ]);
    }
}
