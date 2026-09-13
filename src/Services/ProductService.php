<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Product;
use App\Repositories\ProductRepository;
use PDOException;
use Throwable;

final class ProductService
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly ProductImageService $images
    ) {
    }

    /** @return list<Product> */
    public function all(): array
    {
        return $this->products->all();
    }

    public function findOrFail(int $productId): Product
    {
        $product = $this->products->findById($productId);

        if (!$product instanceof Product) {
            throw new NotFoundException('The requested product was not found.');
        }

        return $product;
    }

    /** @return list<array{id: int, name: string}> */
    public function categories(): array
    {
        return $this->products->categories();
    }

    /**
     * @param array{
     *     category_id: ?int,
     *     sku: string,
     *     name: string,
     *     description: ?string,
     *     price: string,
     *     stock_quantity: int,
     *     is_active: bool
     * } $data
     */
    public function create(array $data, mixed $imageFile): Product
    {
        $this->validateRelationshipsAndSku($data['category_id'], $data['sku']);
        $storedImage = $this->images->store($imageFile, true);

        if ($storedImage === null) {
            throw new ValidationException(['image' => 'Choose a product image.']);
        }

        $data['slug'] = $this->products->uniqueSlug($this->slugify($data['name']));

        try {
            return $this->products->create($data, $storedImage['url']);
        } catch (Throwable $exception) {
            $this->images->removeStoredFile($storedImage['path']);
            $this->convertIntegrityException(
                $exception,
                $data['category_id'],
                $data['sku']
            );
            throw $exception;
        }
    }

    /**
     * @param array{
     *     category_id: ?int,
     *     sku: string,
     *     name: string,
     *     description: ?string,
     *     price: string,
     *     stock_quantity: int,
     *     is_active: bool
     * } $data
     */
    public function update(int $productId, array $data, mixed $imageFile): Product
    {
        $this->findOrFail($productId);
        $this->validateRelationshipsAndSku($data['category_id'], $data['sku'], $productId);
        $storedImage = $this->images->store($imageFile, false);
        $data['slug'] = $this->products->uniqueSlug(
            $this->slugify($data['name']),
            $productId
        );

        try {
            $oldImageUrls = $this->products->update(
                $productId,
                $data,
                $storedImage['url'] ?? null
            );
        } catch (Throwable $exception) {
            if ($storedImage !== null) {
                $this->images->removeStoredFile($storedImage['path']);
            }

            $this->convertIntegrityException(
                $exception,
                $data['category_id'],
                $data['sku'],
                $productId
            );
            throw $exception;
        }

        foreach ($oldImageUrls as $oldImageUrl) {
            $this->images->removeByUrl($oldImageUrl);
        }

        return $this->findOrFail($productId);
    }

    public function delete(int $productId): bool
    {
        $imageUrls = $this->products->deleteIfUnordered($productId);

        if ($imageUrls === null) {
            return false;
        }

        foreach ($imageUrls as $imageUrl) {
            $this->images->removeByUrl($imageUrl);
        }

        return true;
    }

    private function validateRelationshipsAndSku(
        ?int $categoryId,
        string $sku,
        ?int $excludingProductId = null
    ): void {
        $errors = [];

        if ($categoryId !== null && !$this->products->categoryExists($categoryId)) {
            $errors['category_id'] = 'The selected category no longer exists.';
        }

        if ($this->products->skuExists($sku, $excludingProductId)) {
            $errors['sku'] = 'This SKU is already assigned to another product.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }

    private function slugify(string $name): string
    {
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name);
        $value = strtolower(is_string($transliterated) ? $transliterated : $name);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value === '' ? 'product' : $value;
    }

    private function convertIntegrityException(
        Throwable $exception,
        ?int $categoryId,
        string $sku,
        ?int $excludingProductId = null
    ): void {
        if (!$exception instanceof PDOException || (string) $exception->getCode() !== '23000') {
            return;
        }

        if ($categoryId !== null && !$this->products->categoryExists($categoryId)) {
            throw new ValidationException([
                'category_id' => 'The selected category no longer exists.',
            ]);
        }

        if ($this->products->skuExists($sku, $excludingProductId)) {
            throw new ValidationException([
                'sku' => 'This SKU is already assigned to another product.',
            ]);
        }

        throw new ValidationException([
            'name' => 'A product URL conflict occurred. Please try again.',
        ]);
    }
}
