<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Services\AuthService;

final class HomeController extends BaseController
{
    public function __construct(
        View $view,
        private readonly AuthService $auth,
        private readonly Csrf $csrf
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        return $this->render('home/index', [
            'user' => $this->auth->currentUser(),
            'csrfToken' => $this->csrf->token(),
            'logoutUrl' => $request->url('/logout'),
        ]);
    }
}
