<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Exceptions\ValidationException;
use App\Models\User;
use App\Repositories\UserRepository;
use PDOException;

final class AuthService
{
    private const SESSION_USER_ID = 'auth_user_id';
    private const DUMMY_PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

    private ?User $resolvedUser = null;
    private bool $userResolved = false;

    public function __construct(
        private readonly UserRepository $users,
        private readonly Session $session
    ) {
    }

    /** @param array{username: string, email: string, phone: ?string, password: string} $data */
    public function register(array $data): User
    {
        $duplicates = $this->users->existingFields($data['username'], $data['email']);
        $this->throwForDuplicates($duplicates);

        try {
            $user = $this->users->createCustomer(
                $data['username'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['phone']
            );
        } catch (PDOException $exception) {
            if ((string) $exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->throwForDuplicates(
                $this->users->existingFields($data['username'], $data['email'])
            );
            throw $exception;
        }

        $this->authenticate($user);

        return $user;
    }

    public function attempt(string $identifier, string $password): ?User
    {
        $user = $this->users->findByIdentifier($identifier);

        if (!$user instanceof User) {
            password_verify($password, self::DUMMY_PASSWORD_HASH);

            return null;
        }

        if (!$user->passwordMatches($password)) {
            return null;
        }

        if ($user->passwordNeedsRehash()) {
            $this->users->updatePasswordHash(
                $user->id,
                password_hash($password, PASSWORD_DEFAULT)
            );
        }

        $this->authenticate($user);

        return $user;
    }

    public function currentUser(): ?User
    {
        if ($this->userResolved) {
            return $this->resolvedUser;
        }

        $this->userResolved = true;
        $userId = $this->session->get(self::SESSION_USER_ID);

        if (!is_int($userId) && !(is_string($userId) && ctype_digit($userId))) {
            return null;
        }

        $this->resolvedUser = $this->users->findById((int) $userId);

        if (!$this->resolvedUser instanceof User) {
            $this->session->forget(self::SESSION_USER_ID);
        }

        return $this->resolvedUser;
    }

    public function logout(): void
    {
        $this->resolvedUser = null;
        $this->userResolved = true;
        $this->session->invalidate();
    }

    private function authenticate(User $user): void
    {
        $this->session->regenerate();
        $this->session->put(self::SESSION_USER_ID, $user->id);
        $this->resolvedUser = $user;
        $this->userResolved = true;
    }

    /** @param array{username: bool, email: bool} $duplicates */
    private function throwForDuplicates(array $duplicates): void
    {
        $errors = [];

        if ($duplicates['username']) {
            $errors['username'] = 'That username is already in use.';
        }

        if ($duplicates['email']) {
            $errors['email'] = 'That email address is already registered.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }
}
