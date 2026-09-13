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
            $this->errorPage('Request expired', 'Request expired', 'Your session token has expired. Please go back and try again.', 403),
            403
        )->withHeader('Cache-Control', 'no-store');
    }

    protected function notFoundPage(): Response
    {
        return Response::html(
            $this->errorPage('Page not found | Sheepy', 'Nothing here.', 'The page you\'re looking for doesn\'t exist or has been moved.', 404),
            404
        )->withHeader('Cache-Control', 'no-store');
    }

    private function errorPage(string $title, string $heading, string $message, int $status): string
    {
        $statusLabel = $status === 404 ? '404' : ($status === 403 ? '403' : (string) $status);

        return '<!doctype html><html lang="en"><head>'
            . '<meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>'
            . '<link rel="preconnect" href="https://fonts.googleapis.com">'
            . '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
            . '<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@1,500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">'
            . '<style>'
            . '*,*::before,*::after{box-sizing:border-box}'
            . 'body{background:#f6f3ec;color:#2a2621;font-family:Inter,Arial,sans-serif;margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;}'
            . '.wrap{text-align:center;max-width:32rem;}'
            . '.code{font-family:Fraunces,Georgia,serif;font-style:italic;font-size:clamp(5rem,15vw,9rem);font-weight:500;line-height:1;margin:0;color:#cfc4ac;letter-spacing:-0.04em;}'
            . 'h1{font-family:Fraunces,Georgia,serif;font-style:italic;font-weight:500;font-size:clamp(2rem,5vw,3rem);letter-spacing:-0.03em;margin:0.5rem 0 1rem;}'
            . 'p{color:#7a7266;line-height:1.65;margin:0 0 1.75rem;}'
            . 'a{display:inline-flex;align-items:center;border-radius:999px;background:#2a2621;color:#fcfaf6;font-size:0.85rem;font-weight:600;padding:0.75rem 1.5rem;text-decoration:none;}'
            . 'a:hover{background:#a9663d;}'
            . '</style>'
            . '</head><body><div class="wrap">'
            . '<p class="code">' . $statusLabel . '</p>'
            . '<h1>' . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . '</h1>'
            . '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<a href="javascript:history.back()">Go back</a>'
            . '</div></body></html>';
    }
}
