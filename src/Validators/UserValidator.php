<?php

declare(strict_types=1);

namespace App\Validators;

final class UserValidator
{
    /**
     * @param array<string, mixed> $input
     * @return array{
     *     data: array{username: string, email: string, phone: ?string, password: string},
     *     errors: array<string, string>
     * }
     */
    public function registration(array $input): array
    {
        $username = $this->scalar($input['username'] ?? null);
        $email = strtolower($this->scalar($input['email'] ?? null));
        $phoneValue = $this->scalar($input['phone'] ?? null);
        $phone = $phoneValue === '' ? null : $phoneValue;
        $password = $this->untrimmedScalar($input['password'] ?? null);
        $confirmation = $this->untrimmedScalar($input['password_confirmation'] ?? null);
        $errors = [];

        if ($username === '') {
            $errors['username'] = 'Username is required.';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'] = 'Username must contain 3 to 50 characters.';
        } elseif (preg_match('/^[A-Za-z0-9._-]+$/', $username) !== 1) {
            $errors['username'] = 'Use only letters, numbers, dots, underscores, and hyphens.';
        }

        if ($email === '') {
            $errors['email'] = 'Email address is required.';
        } elseif (strlen($email) > 191 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($phone !== null && (
            strlen($phone) > 30
            || preg_match('/^[0-9+(). -]{7,30}$/', $phone) !== 1
        )) {
            $errors['phone'] = 'Enter a valid phone number or leave it blank.';
        }

        if (strlen($password) < 8 || strlen($password) > 72) {
            $errors['password'] = 'Password must contain 8 to 72 characters.';
        } elseif (
            preg_match('/[a-z]/', $password) !== 1
            || preg_match('/[A-Z]/', $password) !== 1
            || preg_match('/[0-9]/', $password) !== 1
        ) {
            $errors['password'] = 'Password must include uppercase, lowercase, and number characters.';
        }

        if ($confirmation === '' || !hash_equals($password, $confirmation)) {
            $errors['password_confirmation'] = 'Password confirmation does not match.';
        }

        return [
            'data' => [
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
            ],
            'errors' => $errors,
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array{
     *     data: array{identifier: string, password: string},
     *     errors: array<string, string>
     * }
     */
    public function login(array $input): array
    {
        $identifier = $this->scalar($input['identifier'] ?? null);
        $password = $this->untrimmedScalar($input['password'] ?? null);
        $errors = [];

        if ($identifier === '' || strlen($identifier) > 191) {
            $errors['identifier'] = 'Enter your username or email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        return [
            'data' => ['identifier' => $identifier, 'password' => $password],
            'errors' => $errors,
        ];
    }

    private function scalar(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function untrimmedScalar(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
