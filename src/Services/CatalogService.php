<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;

final class CatalogService
{
    private const PRODUCTS_PER_PAGE = 12;

    public function __construct(
        private readonly ProductRepository $products,
        private readonly CategoryRepository $categories
    ) {
    }

    /** @return list<array{department: Category, children: list<Category>}> */
    public function navigation(): array
    {
        return $this->categories->navigation();
    }

    /** @return list<Product> */
    public function latest(int $limit = 6): array
    {
        $limit = max(1, min($limit, 12));

        return $this->products->storefrontProducts([], '', $limit, 0);
    }

    /**
     * @return array{
     *     products: list<Product>,
     *     category: ?Category,
     *     children: list<Category>,
     *     breadcrumb: list<Category>,
     *     query: string,
     *     page: int,
     *     total: int,
     *     total_pages: int
     * }
     */
    public function browse(?string $categorySlug, string $query, int $page): array
    {
        $query = trim($query);

        if (strlen($query) > 100) {
            $query = substr($query, 0, 100);
        }

        $category = null;
        $categoryIds = [];
        $children = [];
        $breadcrumb = [];

        if ($categorySlug !== null) {
            $category = $this->categories->findBySlug($categorySlug);

            if (!$category instanceof Category) {
                throw new NotFoundException('The requested category was not found.');
            }

            $categoryIds = $this->categories->selfAndChildIds($category->id);
            $children = $this->categories->children($category->id);
            $breadcrumb = $this->categories->breadcrumb($category);
        }

        $total = $this->products->countStorefrontProducts($categoryIds, $query);
        $totalPages = max(1, (int) ceil($total / self::PRODUCTS_PER_PAGE));
        $page = max(1, min($page, $totalPages));
        $products = $this->products->storefrontProducts(
            $categoryIds,
            $query,
            self::PRODUCTS_PER_PAGE,
            ($page - 1) * self::PRODUCTS_PER_PAGE
        );

        return [
            'products' => $products,
            'category' => $category,
            'children' => $children,
            'breadcrumb' => $breadcrumb,
            'query' => $query,
            'page' => $page,
            'total' => $total,
            'total_pages' => $totalPages,
        ];
    }

    /**
     * @return array{
     *     product: Product,
     *     images: list<ProductImage>,
     *     breadcrumb: list<Category>
     * }
     */
    public function product(string $slug): array
    {
        $product = $this->products->findStorefrontBySlug($slug);

        if (!$product instanceof Product) {
            throw new NotFoundException('The requested product was not found.');
        }

        $breadcrumb = [];

        if ($product->categoryId !== null) {
            $category = $this->categories->findById($product->categoryId);

            if ($category instanceof Category) {
                $breadcrumb = $this->categories->breadcrumb($category);
            }
        }

        return [
            'product' => $product,
            'images' => $this->products->storefrontImages($product->id),
            'breadcrumb' => $breadcrumb,
        ];
    }
}
