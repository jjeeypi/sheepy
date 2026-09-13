<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\AuthService;

final class AdminController extends BaseController
{
    public function __construct(
        View $view,
        private readonly AuthService $auth,
        private readonly Csrf $csrf
    ) {
        parent::__construct($view);
    }

    public function dashboard(Request $request): Response
    {
        return $this->render('admin/dashboard', [
            'user' => $this->auth->currentUser(),
            'csrfToken' => $this->csrf->token(),
            'logoutUrl' => $request->url('/logout'),
            'addProductUrl' => $request->url('/admin/products/create'),
            'manageProductsUrl' => $request->url('/admin/products'),
        ]);
    }
}
