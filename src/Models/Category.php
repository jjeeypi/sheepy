<?php

declare(strict_types=1);

namespace App\Models;

final readonly class Category
{
    public function __construct(
        public int $id,
        public ?int $parentId,
        public string $name,
        public string $slug
    ) {
    }

    public function isDepartment(): bool
    {
        return $this->parentId === null;
    }
}
