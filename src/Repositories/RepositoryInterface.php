<?php

declare(strict_types=1);

namespace App\Repositories;

interface RepositoryInterface
{
    public function findById(int $id): ?object;
}
