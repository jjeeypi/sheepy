<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\AuthService;
use App\Services\CatalogService;

final class HomeController extends BaseController
{
    public function __construct(
        View $view,
        private readonly AuthService $auth,
        private readonly Csrf $csrf,
        private readonly CatalogService $catalog
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        return $this->render('home/index', [
            'user' => $this->auth->currentUser(),
            'csrfToken' => $this->csrf->token(),
            'logoutUrl' => $request->url('/logout'),
            'homeUrl' => $request->url('/home'),
            'productsUrl' => $request->url('/products'),
            'categoriesUrl' => $request->url('/categories'),
            'basePath' => $request->basePath(),
            'navigation' => $this->catalog->navigation(),
            'latestProducts' => $this->catalog->latest(),
        ]);
    }
}
