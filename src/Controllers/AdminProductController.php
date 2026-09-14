<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Exceptions\NotFoundException;
use App\Exceptions\ProductInUseException;
use App\Exceptions\ValidationException;
use App\Models\Product;
use App\Services\AuthService;
use App\Services\ProductService;
use App\Validators\ProductValidator;

final class AdminProductController extends BaseController
{
    public function __construct(
        View $view,
        private readonly ProductService $products,
        private readonly ProductValidator $validator,
        private readonly Csrf $csrf,
        private readonly AuthService $auth
    ) {
        parent::__construct($view);
    }

    public function create(Request $request): Response
    {
        return $this->productForm($request, [
            'category_id' => '',
            'sku' => '',
            'name' => '',
            'description' => '',
            'price' => '',
            'stock_quantity' => '0',
            'is_active' => true,
        ]);
    }

    public function store(Request $request): Response
    {
        if (!$this->csrf->validate($request->body('_token'))) {
            return $this->expiredRequest();
        }

        $result = $this->validator->product($request->body());
        $old = $this->oldInput($result['data']);

        if ($result['errors'] !== []) {
            return $this->productForm($request, $old, $result['errors'], null, 422);
        }

        try {
            $this->products->create($result['data'], $request->file('image'));
        } catch (ValidationException $exception) {
            return $this->productForm($request, $old, $exception->errors(), null, 422);
        }

        return Response::redirect($request->url('/admin/products?notice=created'), 303);
    }

    public function index(Request $request): Response
    {
        $notices = [
            'created' => 'Product created successfully.',
            'updated' => 'Product updated successfully.',
            'deleted' => 'Product deleted successfully.',
        ];
        $errors = [
            'ordered' => 'This product cannot be deleted because it is included in an order.',
            'not_found' => 'The requested product no longer exists.',
        ];
        $noticeKey = $request->string('notice');
        $errorKey = $request->string('error');

        return $this->render('admin/products/index', [
            'products' => $this->products->all(),
            'notice' => $notices[$noticeKey] ?? null,
            'pageError' => $errors[$errorKey] ?? null,
            'csrfToken' => $this->csrf->token(),
            'basePath' => $request->basePath(),
            'dashboardUrl' => $request->url('/admin'),
            'addProductUrl' => $request->url('/admin/products/create'),
            'manageProductsUrl' => $request->url('/admin/products'),
            'productsUrl' => $request->url('/admin/products'),
            'user' => $this->auth->currentUser(),
            'logoutUrl' => $request->url('/logout'),
            'activeNav' => 'products',
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        try {
            $product = $this->products->findOrFail($id);
        } catch (NotFoundException) {
            return Response::redirect($request->url('/admin/products?error=not_found'), 303);
        }

        return $this->productForm($request, $this->productInput($product), [], $product);
    }

    public function update(Request $request, int $id): Response
    {
        if (!$this->csrf->validate($request->body('_token'))) {
            return $this->expiredRequest();
        }

        try {
            $product = $this->products->findOrFail($id);
        } catch (NotFoundException) {
            return Response::redirect($request->url('/admin/products?error=not_found'), 303);
        }

        $result = $this->validator->product($request->body());
        $old = $this->oldInput($result['data']);

        if ($result['errors'] !== []) {
            return $this->productForm($request, $old, $result['errors'], $product, 422);
        }

        try {
            $this->products->update($id, $result['data'], $request->file('image'));
        } catch (ValidationException $exception) {
            return $this->productForm($request, $old, $exception->errors(), $product, 422);
        } catch (NotFoundException) {
            return Response::redirect($request->url('/admin/products?error=not_found'), 303);
        }

        return Response::redirect($request->url('/admin/products?notice=updated'), 303);
    }

    public function destroy(Request $request, int $id): Response
    {
        if (!$this->csrf->validate($request->body('_token'))) {
            return $this->expiredRequest();
        }

        try {
            $deleted = $this->products->delete($id);
        } catch (ProductInUseException) {
            return Response::redirect($request->url('/admin/products?error=ordered'), 303);
        }

        return Response::redirect(
            $request->url('/admin/products?' . ($deleted ? 'notice=deleted' : 'error=not_found')),
            303
        );
    }

    /**
     * @param array<string, mixed> $old
     * @param array<string, string> $errors
     */
    private function productForm(
        Request $request,
        array $old,
        array $errors = [],
        ?Product $product = null,
        int $status = 200
    ): Response {
        $editing = $product instanceof Product;

        return $this->render('admin/products/form', [
            'editing' => $editing,
            'product' => $product,
            'old' => $old,
            'errors' => $errors,
            'categories' => $this->products->categories(),
            'csrfToken' => $this->csrf->token(),
            'formUrl' => $editing
                ? $request->url('/admin/products/' . $product->id)
                : $request->url('/admin/products'),
            'manageProductsUrl' => $request->url('/admin/products'),
            'dashboardUrl' => $request->url('/admin'),
            'user' => $this->auth->currentUser(),
            'logoutUrl' => $request->url('/logout'),
            'activeNav' => 'products',
            'basePath' => $request->basePath(),
            'currentImageUrl' => $editing && $product->imageUrl !== null
                ? $request->url($product->imageUrl)
                : null,
        ], $status);
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function oldInput(array $data): array
    {
        return [
            'category_id' => $data['category_id'] ?? '',
            'sku' => $data['sku'] ?? '',
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'price' => $data['price'] ?? '',
            'stock_quantity' => $data['stock_quantity'] ?? '0',
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    /** @return array<string, mixed> */
    private function productInput(Product $product): array
    {
        return [
            'category_id' => $product->categoryId ?? '',
            'sku' => $product->sku,
            'name' => $product->name,
            'description' => $product->description ?? '',
            'price' => $product->price,
            'stock_quantity' => $product->stockQuantity,
            'is_active' => $product->isActive,
        ];
    }
}
