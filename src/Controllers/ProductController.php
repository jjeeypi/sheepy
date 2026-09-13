<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Exceptions\NotFoundException;
use App\Services\AuthService;
use App\Services\CatalogService;

final class ProductController extends BaseController
{
    public function __construct(
        View $view,
        private readonly CatalogService $catalog,
        private readonly AuthService $auth,
        private readonly Csrf $csrf
    ) {
        parent::__construct($view);
    }

    public function index(Request $request): Response
    {
        return $this->browse($request, null);
    }

    public function category(Request $request, string $slug): Response
    {
        return $this->browse($request, $slug);
    }

    public function show(Request $request, string $slug): Response
    {
        try {
            $result = $this->catalog->product($slug);
        } catch (NotFoundException) {
            return $this->notFoundPage();
        }

        return $this->render('products/show', [
            ...$result,
            'navigation' => $this->catalog->navigation(),
            'user' => $this->auth->currentUser(),
            'csrfToken' => $this->csrf->token(),
            'logoutUrl' => $request->url('/logout'),
            'homeUrl' => $request->url('/home'),
            'productsUrl' => $request->url('/products'),
            'categoriesUrl' => $request->url('/categories'),
            'basePath' => $request->basePath(),
        ]);
    }

    private function browse(Request $request, ?string $categorySlug): Response
    {
        try {
            $result = $this->catalog->browse(
                $categorySlug,
                $request->string('q'),
                $request->integer('page', 1) ?? 1
            );
        } catch (NotFoundException) {
            return $this->notFoundPage();
        }

        $browsePath = $categorySlug === null
            ? '/products'
            : '/categories/' . $categorySlug;
        $pageUrl = function (int $page) use ($request, $browsePath, $result): string {
            $parameters = [];

            if ($result['query'] !== '') {
                $parameters['q'] = $result['query'];
            }

            if ($page > 1) {
                $parameters['page'] = $page;
            }

            $queryString = http_build_query($parameters);

            return $request->url($browsePath) . ($queryString === '' ? '' : '?' . $queryString);
        };

        return $this->render('products/index', [
            ...$result,
            'navigation' => $this->catalog->navigation(),
            'user' => $this->auth->currentUser(),
            'csrfToken' => $this->csrf->token(),
            'logoutUrl' => $request->url('/logout'),
            'homeUrl' => $request->url('/home'),
            'productsUrl' => $request->url('/products'),
            'categoriesUrl' => $request->url('/categories'),
            'browseUrl' => $request->url($browsePath),
            'previousPageUrl' => $result['page'] > 1
                ? $pageUrl($result['page'] - 1)
                : null,
            'nextPageUrl' => $result['page'] < $result['total_pages']
                ? $pageUrl($result['page'] + 1)
                : null,
            'basePath' => $request->basePath(),
        ]);
    }
}
