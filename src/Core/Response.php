<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use JsonException;

final class Response
{
    /** @var array<string, array{name: string, values: list<string>}> */
    private array $headers = [];

    /**
     * @param array<string, string|list<string>> $headers
     */
    public function __construct(
        private string $body = '',
        private int $status = 200,
        array $headers = []
    ) {
        $this->assertStatus($status);

        foreach ($headers as $name => $values) {
            $values = is_array($values) ? $values : [$values];

            foreach ($values as $index => $value) {
                if ($index === 0) {
                    $this->setHeader($name, $value);
                } else {
                    $this->addHeader($name, $value);
                }
            }
        }
    }

    /** @param array<string, string|list<string>> $headers */
    public static function html(string $html, int $status = 200, array $headers = []): self
    {
        return new self(
            $html,
            $status,
            ['Content-Type' => 'text/html; charset=utf-8', ...$headers]
        );
    }

    /** @param array<string, string|list<string>> $headers */
    public static function text(string $text, int $status = 200, array $headers = []): self
    {
        return new self(
            $text,
            $status,
            ['Content-Type' => 'text/plain; charset=utf-8', ...$headers]
        );
    }

    /**
     * @param array<string, string|list<string>> $headers
     * @throws JsonException
     */
    public static function json(mixed $data, int $status = 200, array $headers = []): self
    {
        $json = json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        return new self(
            $json,
            $status,
            ['Content-Type' => 'application/json; charset=utf-8', ...$headers]
        );
    }

    public static function noContent(int $status = 204): self
    {
        return new self('', $status);
    }

    public static function redirect(string $location, int $status = 302): self
    {
        if ($status < 300 || $status > 399) {
            throw new InvalidArgumentException('Redirect responses require a 3xx status code.');
        }

        if (str_contains($location, "\r") || str_contains($location, "\n")) {
            throw new InvalidArgumentException('Redirect locations cannot contain line breaks.');
        }

        return new self('', $status, ['Location' => $location]);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function body(): string
    {
        return $this->body;
    }

    /** @return array<string, list<string>> */
    public function headers(): array
    {
        $headers = [];

        foreach ($this->headers as $header) {
            $headers[$header['name']] = $header['values'];
        }

        return $headers;
    }

    public function header(string $name): ?string
    {
        $header = $this->headers[strtolower($name)] ?? null;

        return $header['values'][0] ?? null;
    }

    public function withStatus(int $status): self
    {
        $this->assertStatus($status);
        $clone = clone $this;
        $clone->status = $status;

        return $clone;
    }

    public function withBody(string $body): self
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->setHeader($name, $value);

        return $clone;
    }

    public function withAddedHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->addHeader($name, $value);

        return $clone;
    }

    public function send(bool $sendBody = true): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);

            foreach ($this->headers as $header) {
                foreach ($header['values'] as $index => $value) {
                    header(
                        sprintf('%s: %s', $header['name'], $value),
                        $index === 0
                    );
                }
            }
        }

        if ($sendBody && !in_array($this->status, [204, 304], true)) {
            echo $this->body;
        }
    }

    private function setHeader(string $name, string $value): void
    {
        $this->assertHeader($name, $value);
        $this->headers[strtolower($name)] = [
            'name' => $name,
            'values' => [$value],
        ];
    }

    private function addHeader(string $name, string $value): void
    {
        $this->assertHeader($name, $value);
        $key = strtolower($name);

        if (!isset($this->headers[$key])) {
            $this->headers[$key] = ['name' => $name, 'values' => []];
        }

        $this->headers[$key]['values'][] = $value;
    }

    private function assertStatus(int $status): void
    {
        if ($status < 100 || $status > 599) {
            throw new InvalidArgumentException('HTTP status codes must be between 100 and 599.');
        }
    }

    private function assertHeader(string $name, string $value): void
    {
        if ($name === '' || preg_match('/^[A-Za-z0-9!#$%&*+.^_`|~-]+$/', $name) !== 1) {
            throw new InvalidArgumentException('The response header name is invalid.');
        }

        if (str_contains($value, "\r") || str_contains($value, "\n")) {
            throw new InvalidArgumentException('Response header values cannot contain line breaks.');
        }
    }
}
