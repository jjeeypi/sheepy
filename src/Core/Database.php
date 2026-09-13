<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final class Database
{
    private ?PDO $connection = null;

    /**
     * @param array{
     *     driver: string,
     *     host: string,
     *     port: int,
     *     database: string,
     *     username: string,
     *     password: string,
     *     charset: string,
     *     options?: array<int, mixed>
     * } $config
     */
    public function __construct(private readonly array $config)
    {
        $this->validateConfig();
    }

    public function connection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $this->config['driver'],
            $this->config['host'],
            $this->config['port'],
            $this->config['database'],
            $this->config['charset']
        );

        try {
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options'] ?? []
            );
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'The application could not connect to the database.',
                0,
                $exception
            );
        }

        return $this->connection;
    }

    /**
     * Execute related writes atomically. The transaction is rolled back if
     * the callback throws, which is useful for checkout and other CRUD flows.
     *
     * @template T
     * @param callable(PDO): T $callback
     * @return T
     */
    public function transaction(callable $callback): mixed
    {
        $connection = $this->connection();
        $connection->beginTransaction();

        try {
            $result = $callback($connection);
            $connection->commit();

            return $result;
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }

    public function disconnect(): void
    {
        $this->connection = null;
    }

    private function validateConfig(): void
    {
        if (($this->config['driver'] ?? null) !== 'mysql') {
            throw new InvalidArgumentException('Only the MySQL/MariaDB PDO driver is supported.');
        }

        foreach (['host', 'database', 'username', 'charset'] as $key) {
            if (!isset($this->config[$key]) || trim((string) $this->config[$key]) === '') {
                throw new InvalidArgumentException(sprintf('Database setting "%s" is required.', $key));
            }
        }

        if (str_contains((string) $this->config['host'], ';')) {
            throw new InvalidArgumentException('The database host is invalid.');
        }

        if (!preg_match('/^[A-Za-z0-9_$-]+$/', (string) $this->config['database'])) {
            throw new InvalidArgumentException('The database name is invalid.');
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', (string) $this->config['charset'])) {
            throw new InvalidArgumentException('The database character set is invalid.');
        }

        $port = $this->config['port'] ?? 0;

        if (!is_int($port) || $port < 1 || $port > 65535) {
            throw new InvalidArgumentException('The database port must be between 1 and 65535.');
        }
    }
}
