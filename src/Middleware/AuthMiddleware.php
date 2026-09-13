<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

final class AuthMiddleware
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        if ($this->auth->currentUser() === null) {
            return Response::redirect($request->url('/login'));
        }

        return $next($request);
    }
}
