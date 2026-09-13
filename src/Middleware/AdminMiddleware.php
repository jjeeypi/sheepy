<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

final class AdminMiddleware
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $user = $this->auth->currentUser();

        if ($user === null) {
            return Response::redirect($request->url('/login'));
        }

        if (!$user->isAdmin()) {
            return Response::html(
                '<!doctype html><html lang="en"><head><meta charset="utf-8">'
                . '<title>Forbidden</title></head><body><h1>Forbidden</h1>'
                . '<p>You do not have permission to view this page.</p></body></html>',
                403
            )->withHeader('Cache-Control', 'no-store');
        }

        return $next($request);
    }
}
