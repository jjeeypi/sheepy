<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Category;

final class CategoryRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?Category
    {
        $statement = $this->database->connection()->prepare(
            'SELECT category_id, parent_category_id, name, slug
             FROM categories
             WHERE category_id = :category_id
             LIMIT 1'
        );
        $statement->execute(['category_id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function findBySlug(string $slug): ?Category
    {
        $statement = $this->database->connection()->prepare(
            'SELECT category_id, parent_category_id, name, slug
             FROM categories
             WHERE slug = :slug
             LIMIT 1'
        );
        $statement->execute(['slug' => $slug]);
        $row = $statement->fetch();

        return is_array($row) ? $this->hydrate($row) : null;
    }

    /** @return list<Category> */
    public function departments(): array
    {
        $statement = $this->database->connection()->query(
            'SELECT category_id, parent_category_id, name, slug
             FROM categories
             WHERE parent_category_id IS NULL
             ORDER BY name ASC, category_id ASC'
        );

        return $this->hydrateMany($statement->fetchAll());
    }

    /** @return list<Category> */
    public function children(int $parentId): array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT category_id, parent_category_id, name, slug
             FROM categories
             WHERE parent_category_id = :parent_category_id
             ORDER BY name ASC, category_id ASC'
        );
        $statement->execute(['parent_category_id' => $parentId]);

        return $this->hydrateMany($statement->fetchAll());
    }

    /** @return list<int> */
    public function selfAndChildIds(int $categoryId): array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT category_id
             FROM categories
             WHERE category_id = :category_id OR parent_category_id = :parent_category_id
             ORDER BY category_id ASC'
        );
        $statement->execute([
            'category_id' => $categoryId,
            'parent_category_id' => $categoryId,
        ]);

        return array_map('intval', $statement->fetchAll(\PDO::FETCH_COLUMN));
    }

    /** @return list<Category> */
    public function breadcrumb(Category $category): array
    {
        if ($category->parentId === null) {
            return [$category];
        }

        $parent = $this->findById($category->parentId);

        return $parent instanceof Category ? [$parent, $category] : [$category];
    }

    /** @return list<array{department: Category, children: list<Category>}> */
    public function navigation(): array
    {
        $navigation = [];

        foreach ($this->departments() as $department) {
            $navigation[] = [
                'department' => $department,
                'children' => $this->children($department->id),
            ];
        }

        return $navigation;
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Category
    {
        return new Category(
            (int) $row['category_id'],
            $row['parent_category_id'] === null ? null : (int) $row['parent_category_id'],
            (string) $row['name'],
            (string) $row['slug']
        );
    }

    /** @param list<array<string, mixed>> $rows @return list<Category> */
    private function hydrateMany(array $rows): array
    {
        return array_map(fn (array $row): Category => $this->hydrate($row), $rows);
    }
}
