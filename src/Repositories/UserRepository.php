<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;
use PDO;

final class UserRepository implements RepositoryInterface
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findById(int $id): ?User
    {
        $statement = $this->database->connection()->prepare(
            'SELECT user_id, username, email, password_hash, phone, role
             FROM users
             WHERE user_id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $id]);

        return $this->hydrate($statement->fetch());
    }

    public function findByIdentifier(string $identifier): ?User
    {
        $statement = $this->database->connection()->prepare(
            'SELECT user_id, username, email, password_hash, phone, role
             FROM users
             WHERE email = :email OR username = :username
             LIMIT 1'
        );
        $statement->execute([
            'email' => $identifier,
            'username' => $identifier,
        ]);

        return $this->hydrate($statement->fetch());
    }

    /** @return array{username: bool, email: bool} */
    public function existingFields(string $username, string $email): array
    {
        $statement = $this->database->connection()->prepare(
            'SELECT username, email
             FROM users
             WHERE username = :username OR email = :email'
        );
        $statement->execute([
            'username' => $username,
            'email' => $email,
        ]);

        $existing = ['username' => false, 'email' => false];

        while (($row = $statement->fetch()) !== false) {
            $existing['username'] = $existing['username']
                || strcasecmp((string) $row['username'], $username) === 0;
            $existing['email'] = $existing['email']
                || strcasecmp((string) $row['email'], $email) === 0;
        }

        return $existing;
    }

    public function createCustomer(
        string $username,
        string $email,
        string $passwordHash,
        ?string $phone
    ): User {
        $statement = $this->database->connection()->prepare(
            "INSERT INTO users (username, email, password_hash, phone, role)
             VALUES (:username, :email, :password_hash, :phone, 'customer')"
        );
        $statement->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'phone' => $phone,
        ]);

        $user = $this->findById((int) $this->database->connection()->lastInsertId());

        if (!$user instanceof User) {
            throw new \RuntimeException('The new account could not be loaded.');
        }

        return $user;
    }

    public function updatePasswordHash(int $userId, string $passwordHash): void
    {
        $statement = $this->database->connection()->prepare(
            'UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id'
        );
        $statement->execute([
            'password_hash' => $passwordHash,
            'user_id' => $userId,
        ]);
    }

    private function hydrate(mixed $row): ?User
    {
        if (!is_array($row)) {
            return null;
        }

        return new User(
            (int) $row['user_id'],
            (string) $row['username'],
            (string) $row['email'],
            (string) $row['password_hash'],
            $row['phone'] === null ? null : (string) $row['phone'],
            (string) $row['role']
        );
    }
}
