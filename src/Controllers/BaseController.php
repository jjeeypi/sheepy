<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\View;

abstract class BaseController
{
    public function __construct(protected readonly View $view)
    {
    }

    /** @param array<string, mixed> $data */
    protected function render(string $template, array $data = [], int $status = 200): Response
    {
        return Response::html($this->view->render($template, $data), $status)
            ->withHeader('Cache-Control', 'no-store');
    }

    protected function expiredRequest(): Response
    {
        return Response::html(
            '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            . '<title>Request expired</title></head><body><h1>Request expired</h1>'
            . '<p>Please return to the form and try again.</p></body></html>',
            403
        )->withHeader('Cache-Control', 'no-store');
    }
}
