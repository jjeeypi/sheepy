<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Category;
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
        $navigation = $this->catalog->navigation();

        return $this->render('home/index', [
            'user' => $user,
            'csrfToken' => $this->csrf->token(),
            'logoutUrl' => $request->url('/logout'),
            'homeUrl' => $request->url('/home'),
            'productsUrl' => $request->url('/products'),
            'categoriesUrl' => $request->url('/categories'),
            'basePath' => $request->basePath(),
            'navigation' => $navigation,
            'featuredCategories' => $this->featuredCategories($navigation),
            'latestProducts' => $this->catalog->latest(),
            'cartUrl' => $request->url('/cart'),
            'cartItemsUrl' => $request->url('/cart/items'),
            'checkoutUrl' => $request->url('/checkout'),
            'checkoutConfirmUrl' => $request->url('/checkout/confirm'),
            'ordersUrl' => $request->url('/orders'),
            'cartItemCount' => $user === null ? 0 : $this->cart->itemCount($user->id),
        ]);
    }

    /**
     * @param list<array{department: Category, children: list<Category>}> $navigation
     * @return list<array{category: Category, label: string, image: string, alt: string}>
     */
    private function featuredCategories(array $navigation): array
    {
        $definitions = [
            'men-shirts' => [
                'label' => 'Shirts',
                'image' => 'category-shirts.jpg',
                'alt' => 'Patterned short-sleeve shirt',
            ],
            'men-jackets' => [
                'label' => 'Outerwear',
                'image' => 'category-outerwear.jpg',
                'alt' => 'Model wearing a relaxed earth-tone top',
            ],
            'men-pants' => [
                'label' => 'Denim',
                'image' => 'category-denim.jpg',
                'alt' => 'Model wearing a neutral T-shirt with denim shorts',
            ],
            'men-t-shirts' => [
                'label' => 'Tees',
                'image' => 'category-tees.jpg',
                'alt' => 'Cream graphic T-shirt',
            ],
        ];
        $categoryLookup = [];

        foreach ($navigation as $group) {
            foreach ($group['children'] as $category) {
                $categoryLookup[$category->slug] = $category;
            }
        }

        $featured = [];

        foreach ($definitions as $slug => $definition) {
            $category = $categoryLookup[$slug] ?? null;

            if (!$category instanceof Category) {
                continue;
            }

            $featured[] = [
                'category' => $category,
                'label' => $definition['label'],
                'image' => $definition['image'],
                'alt' => $definition['alt'],
            ];
        }

        return $featured;
    }
}
