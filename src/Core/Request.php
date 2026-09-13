<?php

declare(strict_types=1);

namespace App\Core;

use JsonException;
use UnexpectedValueException;

final class Request
{
    /** @var array<string, string> */
    private array $headers;

    /** @var array<string, string> */
    private array $routeParameters = [];

    /** @var array<string, mixed> */
    private array $attributes = [];

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     * @param array<string, mixed> $files
     * @param array<string, string> $cookies
     * @param array<string, mixed> $server
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly string $originalMethod,
        private readonly string $requestPath,
        private readonly array $query = [],
        private readonly array $body = [],
        private readonly array $files = [],
        private readonly array $cookies = [],
        private readonly array $server = [],
        array $headers = [],
        private readonly string $rawBody = '',
        private readonly ?string $overriddenMethod = null,
        private readonly string $applicationBasePath = ''
    ) {
        $normalizedHeaders = [];

        foreach ($headers as $name => $value) {
            $normalizedHeaders[strtolower(trim($name))] = trim($value);
        }

        $this->headers = $normalizedHeaders;
    }

    public static function capture(?string $basePath = null): self
    {
        $server = $_SERVER;
        $headers = self::headersFromServer($server);
        $originalMethod = strtoupper((string) ($server['REQUEST_METHOD'] ?? 'GET'));
        $rawBody = file_get_contents('php://input');
        $rawBody = $rawBody === false ? '' : $rawBody;
        $body = $_POST;
        $contentType = strtolower($headers['content-type'] ?? '');

        if (str_contains($contentType, 'application/json') && trim($rawBody) !== '') {
            try {
                $decodedBody = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new UnexpectedValueException('The JSON request body is invalid.', 0, $exception);
            }

            if (!is_array($decodedBody)) {
                throw new UnexpectedValueException('The JSON request body must be an object or array.');
            }

            $body = $decodedBody;
        } elseif (
            $body === []
            && in_array($originalMethod, ['PUT', 'PATCH', 'DELETE'], true)
            && str_contains($contentType, 'application/x-www-form-urlencoded')
        ) {
            parse_str($rawBody, $parsedBody);
            $body = is_array($parsedBody) ? $parsedBody : [];
        }

        $overriddenMethod = null;

        if ($originalMethod === 'POST') {
            $requestedOverride = $headers['x-http-method-override'] ?? ($body['_method'] ?? null);

            if (is_string($requestedOverride)) {
                $requestedOverride = strtoupper(trim($requestedOverride));

                if (in_array($requestedOverride, ['PUT', 'PATCH', 'DELETE'], true)) {
                    $overriddenMethod = $requestedOverride;
                    unset($body['_method']);
                }
            }
        }

        $uri = (string) ($server['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH);
        $path = is_string($path) && $path !== '' ? $path : '/';
        $applicationBasePath = $basePath ?? self::detectBasePath($server);
        $path = self::stripBasePath($path, $applicationBasePath);

        return new self(
            $originalMethod,
            self::normalizePath($path),
            $_GET,
            $body,
            $_FILES,
            $_COOKIE,
            $server,
            $headers,
            $rawBody,
            $overriddenMethod,
            $applicationBasePath
        );
    }

    public function method(): string
    {
        return $this->overriddenMethod ?? strtoupper($this->originalMethod);
    }

    public function originalMethod(): string
    {
        return strtoupper($this->originalMethod);
    }

    public function isMethod(string ...$methods): bool
    {
        $currentMethod = $this->method();

        foreach ($methods as $method) {
            if ($currentMethod === strtoupper($method)) {
                return true;
            }
        }

        return false;
    }

    public function path(): string
    {
        return self::normalizePath($this->requestPath);
    }

    public function basePath(): string
    {
        $basePath = '/' . trim(str_replace('\\', '/', $this->applicationBasePath), '/');

        return $basePath === '/' ? '' : $basePath;
    }

    public function url(string $path = '/'): string
    {
        $path = self::normalizePath($path);

        return $this->basePath() . $path;
    }

    /** @return array<string, mixed>|mixed */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->query : self::dataGet($this->query, $key, $default);
    }

    /** @return array<string, mixed>|mixed */
    public function body(?string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->body : self::dataGet($this->body, $key, $default);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $input = array_replace_recursive($this->query, $this->body);

        return self::dataGet($input, $key, $default);
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return array_replace_recursive($this->query, $this->body);
    }

    /**
     * @param list<string> $keys
     * @return array<string, mixed>
     */
    public function only(array $keys): array
    {
        $selected = [];

        foreach ($keys as $key) {
            if ($this->has($key)) {
                $selected[$key] = $this->input($key);
            }
        }

        return $selected;
    }

    public function has(string $key): bool
    {
        $sentinel = new \stdClass();

        return $this->input($key, $sentinel) !== $sentinel;
    }

    public function string(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);

        return is_scalar($value) ? trim((string) $value) : $default;
    }

    public function integer(string $key, ?int $default = null): ?int
    {
        $value = $this->input($key);

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
            return (int) $value;
        }

        return $default;
    }

    public function boolean(string $key, bool $default = false): bool
    {
        $value = $this->input($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        return $this->headers;
    }

    public function cookie(string $name, ?string $default = null): ?string
    {
        return $this->cookies[$name] ?? $default;
    }

    public function file(string $name): mixed
    {
        return $this->files[$name] ?? null;
    }

    public function server(string $name, mixed $default = null): mixed
    {
        return $this->server[$name] ?? $default;
    }

    public function rawBody(): string
    {
        return $this->rawBody;
    }

    public function isJson(): bool
    {
        return str_contains(strtolower($this->header('Content-Type', '') ?? ''), 'application/json');
    }

    public function expectsJson(): bool
    {
        return $this->isJson()
            || str_contains(strtolower($this->header('Accept', '') ?? ''), 'application/json');
    }

    public function bearerToken(): ?string
    {
        $authorization = $this->header('Authorization');

        if ($authorization === null || preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]);
    }

    public function route(string $name, ?string $default = null): ?string
    {
        return $this->routeParameters[$name] ?? $default;
    }

    /** @return array<string, string> */
    public function routeParameters(): array
    {
        return $this->routeParameters;
    }

    /** @param array<string, string> $parameters */
    public function withRouteParameters(array $parameters): self
    {
        $clone = clone $this;
        $clone->routeParameters = $parameters;

        return $clone;
    }

    public function attribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute(string $name, mixed $value): self
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /** @param array<string, mixed> $server */
    private static function detectBasePath(array $server): string
    {
        $scriptName = str_replace('\\', '/', (string) ($server['SCRIPT_NAME'] ?? ''));
        $directory = str_replace('\\', '/', dirname($scriptName));

        return $directory === '/' || $directory === '.' ? '' : rtrim($directory, '/');
    }

    private static function stripBasePath(string $path, string $basePath): string
    {
        $basePath = '/' . trim(str_replace('\\', '/', $basePath), '/');

        if ($basePath === '/') {
            return $path;
        }

        if ($path === $basePath) {
            return '/';
        }

        if (str_starts_with($path, $basePath . '/')) {
            return substr($path, strlen($basePath));
        }

        return $path;
    }

    private static function normalizePath(string $path): string
    {
        $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
        $path = preg_replace('#/+#', '/', $path) ?? '/';

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    /**
     * @param array<string, mixed> $server
     * @return array<string, string>
     */
    private static function headersFromServer(array $server): array
    {
        $headers = [];

        foreach ($server as $name => $value) {
            if (!is_string($value)) {
                continue;
            }

            if (str_starts_with($name, 'HTTP_')) {
                $header = strtolower(str_replace('_', '-', substr($name, 5)));
                $headers[$header] = $value;
            }
        }

        foreach (['CONTENT_TYPE' => 'content-type', 'CONTENT_LENGTH' => 'content-length'] as $key => $header) {
            if (isset($server[$key]) && is_string($server[$key])) {
                $headers[$header] = $server[$key];
            }
        }

        return $headers;
    }

    /** @param array<string, mixed> $data */
    private static function dataGet(array $data, string $key, mixed $default): mixed
    {
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        $current = $data;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $default;
            }

            $current = $current[$segment];
        }

        return $current;
    }
}
