<?php

declare(strict_types=1);

namespace App\Models;

final readonly class User
{
    public function __construct(
        public int $id,
        public string $username,
        public string $email,
        private string $passwordHash,
        public ?string $phone,
        public string $role
    ) {
    }

    public function passwordMatches(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function passwordNeedsRehash(): bool
    {
        return password_needs_rehash($this->passwordHash, PASSWORD_DEFAULT);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
