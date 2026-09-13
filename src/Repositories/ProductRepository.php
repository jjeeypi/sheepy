<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Exceptions\NotFoundException;
use App\Exceptions\ProductInUseException;
use App\Models\Product;
use PDO;

final class ProductRepository implements RepositoryInterface
{
    private const PRODUCT_SELECT = <<<'SQL'
        SELECT
            p.product_id,
            p.category_id,
            c.name AS category_name,
            p.sku,
            p.name,
            p.slug,
            p.description,
            p.price,
            p.stock_quantity,
            p.is_active,
            pi.product_image_id,
            pi.url AS image_url,
            pi.alt_text AS image_alt,
            (SELECT COUNT(*) FROM order_items oi WHERE oi.product_id = p.product_id) AS order_count
        FROM products p
        LEFT JOIN categories c ON c.category_id = p.category_id
        LEFT JOIN product_images pi ON pi.product_image_id = (
            SELECT image.product_image_id
            FROM product_images image
            WHERE image.product_id = p.product_id
            ORDER BY image.sort_order ASC, image.product_image_id ASC
            LIMIT 1
        )
        SQL;

    public function __construct(private readonly Database $database)
    {
    }

    /** @return list<Product> */
    public function all(): array
    {
        $statement = $this->database->connection()->query(
            self::PRODUCT_SELECT . ' ORDER BY p.created_at DESC, p.product_id DESC'
        );
        $products = [];

        while (($row = $statement->fetch()) !== false) {
            $products[] = $this->hydrate($row);
        }

        return $products;
    }

    public function findById(int $id): ?Product
    {
        $statement = $this->database->connection()->prepare(
            self::PRODUCT_SELECT . ' WHERE p.product_id = :product_id LIMIT 1'
        );
        $statement->execute(['product_id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? $this->hydrate($row) : null;
    }

    /** @return list<array{id: int, name: string}> */
    public function categories(): array
    {
        $statement = $this->database->connection()->query(
            "SELECT
                child.category_id,
                CASE
                    WHEN parent.category_id IS NULL THEN child.name
                    ELSE CONCAT(parent.name, ' — ', child.name)
                END AS display_name,
                COALESCE(parent.name, child.name) AS department_name,
                child.parent_category_id
             FROM categories child
             LEFT JOIN categories parent ON parent.category_id = child.parent_category_id
             ORDER BY
                department_name ASC,
                child.parent_category_id IS NOT NULL ASC,
                child.name ASC,
                child.category_id ASC"
        );
        $categories = [];

        while (($row = $statement->fetch()) !== false) {
            $categories[] = [
                'id' => (int) $row['category_id'],
                'name' => (string) $row['display_name'],
            ];
        }

        return $categories;
    }

    public function categoryExists(int $categoryId): bool
    {
        $statement = $this->database->connection()->prepare(
            'SELECT 1 FROM categories WHERE category_id = :category_id LIMIT 1'
        );
        $statement->execute(['category_id' => $categoryId]);

        return $statement->fetchColumn() !== false;
    }

    public function skuExists(string $sku, ?int $excludingProductId = null): bool
    {
        $sql = 'SELECT 1 FROM products WHERE sku = :sku';
        $parameters = ['sku' => $sku];

        if ($excludingProductId !== null) {
            $sql .= ' AND product_id <> :product_id';
            $parameters['product_id'] = $excludingProductId;
        }

        $statement = $this->database->connection()->prepare($sql . ' LIMIT 1');
        $statement->execute($parameters);

        return $statement->fetchColumn() !== false;
    }

    public function uniqueSlug(string $baseSlug, ?int $excludingProductId = null): string
    {
        $baseSlug = substr($baseSlug, 0, 200);

        for ($suffix = 1; $suffix <= 1000; $suffix++) {
            $candidate = $suffix === 1
                ? $baseSlug
                : substr($baseSlug, 0, 200 - strlen((string) $suffix)) . '-' . $suffix;
            $sql = 'SELECT 1 FROM products WHERE slug = :slug';
            $parameters = ['slug' => $candidate];

            if ($excludingProductId !== null) {
                $sql .= ' AND product_id <> :product_id';
                $parameters['product_id'] = $excludingProductId;
            }

            $statement = $this->database->connection()->prepare($sql . ' LIMIT 1');
            $statement->execute($parameters);

            if ($statement->fetchColumn() === false) {
                return $candidate;
            }
        }

        return substr($baseSlug, 0, 183) . '-' . bin2hex(random_bytes(8));
    }

    /**
     * @param array{
     *     category_id: ?int,
     *     sku: string,
     *     name: string,
     *     description: ?string,
     *     price: string,
     *     stock_quantity: int,
     *     is_active: bool,
     *     slug: string
     * } $data
     */
    public function create(array $data, string $imageUrl): Product
    {
        $productId = $this->database->transaction(static function (PDO $connection) use (
            $data,
            $imageUrl
        ): int {
            $productStatement = $connection->prepare(
                'INSERT INTO products (
                    category_id, sku, name, slug, description, price, stock_quantity, is_active
                 ) VALUES (
                    :category_id, :sku, :name, :slug, :description, :price, :stock_quantity, :is_active
                 )'
            );
            $productStatement->execute([
                'category_id' => $data['category_id'],
                'sku' => $data['sku'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'price' => $data['price'],
                'stock_quantity' => $data['stock_quantity'],
                'is_active' => $data['is_active'] ? 1 : 0,
            ]);
            $productId = (int) $connection->lastInsertId();
            $imageStatement = $connection->prepare(
                'INSERT INTO product_images (product_id, url, alt_text, sort_order)
                 VALUES (:product_id, :url, :alt_text, 0)'
            );
            $imageStatement->execute([
                'product_id' => $productId,
                'url' => $imageUrl,
                'alt_text' => $data['name'],
            ]);

            return $productId;
        });
        $product = $this->findById($productId);

        if (!$product instanceof Product) {
            throw new \RuntimeException('The new product could not be loaded.');
        }

        return $product;
    }

    /**
     * @param array{
     *     category_id: ?int,
     *     sku: string,
     *     name: string,
     *     description: ?string,
     *     price: string,
     *     stock_quantity: int,
     *     is_active: bool,
     *     slug: string
     * } $data
     * @return list<string> URLs of images replaced by the update.
     */
    public function update(int $productId, array $data, ?string $newImageUrl): array
    {
        return $this->database->transaction(static function (PDO $connection) use (
            $productId,
            $data,
            $newImageUrl
        ): array {
            $lock = $connection->prepare(
                'SELECT product_id FROM products WHERE product_id = :product_id FOR UPDATE'
            );
            $lock->execute(['product_id' => $productId]);

            if ($lock->fetchColumn() === false) {
                throw new NotFoundException('The product no longer exists.');
            }

            $statement = $connection->prepare(
                'UPDATE products SET
                    category_id = :category_id,
                    sku = :sku,
                    name = :name,
                    slug = :slug,
                    description = :description,
                    price = :price,
                    stock_quantity = :stock_quantity,
                    is_active = :is_active
                 WHERE product_id = :product_id'
            );
            $statement->execute([
                'category_id' => $data['category_id'],
                'sku' => $data['sku'],
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'price' => $data['price'],
                'stock_quantity' => $data['stock_quantity'],
                'is_active' => $data['is_active'] ? 1 : 0,
                'product_id' => $productId,
            ]);

            if ($newImageUrl === null) {
                $altStatement = $connection->prepare(
                    'UPDATE product_images SET alt_text = :alt_text WHERE product_id = :product_id'
                );
                $altStatement->execute([
                    'alt_text' => $data['name'],
                    'product_id' => $productId,
                ]);

                return [];
            }

            $imageQuery = $connection->prepare(
                'SELECT url FROM product_images WHERE product_id = :product_id'
            );
            $imageQuery->execute(['product_id' => $productId]);
            $oldUrls = array_map(
                static fn (array $row): string => (string) $row['url'],
                $imageQuery->fetchAll()
            );
            $deleteImages = $connection->prepare(
                'DELETE FROM product_images WHERE product_id = :product_id'
            );
            $deleteImages->execute(['product_id' => $productId]);
            $insertImage = $connection->prepare(
                'INSERT INTO product_images (product_id, url, alt_text, sort_order)
                 VALUES (:product_id, :url, :alt_text, 0)'
            );
            $insertImage->execute([
                'product_id' => $productId,
                'url' => $newImageUrl,
                'alt_text' => $data['name'],
            ]);

            return $oldUrls;
        });
    }

    /** @return list<string>|null */
    public function deleteIfUnordered(int $productId): ?array
    {
        return $this->database->transaction(static function (PDO $connection) use ($productId): ?array {
            $lock = $connection->prepare(
                'SELECT product_id FROM products WHERE product_id = :product_id FOR UPDATE'
            );
            $lock->execute(['product_id' => $productId]);

            if ($lock->fetchColumn() === false) {
                return null;
            }

            $orderCheck = $connection->prepare(
                'SELECT 1 FROM order_items WHERE product_id = :product_id LIMIT 1'
            );
            $orderCheck->execute(['product_id' => $productId]);

            if ($orderCheck->fetchColumn() !== false) {
                throw new ProductInUseException();
            }

            $imageQuery = $connection->prepare(
                'SELECT url FROM product_images WHERE product_id = :product_id'
            );
            $imageQuery->execute(['product_id' => $productId]);
            $imageUrls = array_map(
                static fn (array $row): string => (string) $row['url'],
                $imageQuery->fetchAll()
            );
            $delete = $connection->prepare(
                'DELETE FROM products WHERE product_id = :product_id'
            );
            $delete->execute(['product_id' => $productId]);

            return $imageUrls;
        });
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Product
    {
        return new Product(
            (int) $row['product_id'],
            $row['category_id'] === null ? null : (int) $row['category_id'],
            $row['category_name'] === null ? null : (string) $row['category_name'],
            (string) $row['sku'],
            (string) $row['name'],
            (string) $row['slug'],
            $row['description'] === null ? null : (string) $row['description'],
            (string) $row['price'],
            (int) $row['stock_quantity'],
            (bool) $row['is_active'],
            $row['product_image_id'] === null ? null : (int) $row['product_image_id'],
            $row['image_url'] === null ? null : (string) $row['image_url'],
            $row['image_alt'] === null ? null : (string) $row['image_alt'],
            (int) $row['order_count']
        );
    }
}
