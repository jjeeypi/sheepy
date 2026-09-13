<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use JsonSerializable;
use RuntimeException;

final class Router
{
    /**
     * @var list<array{
     *     methods: list<string>,
     *     path: string,
     *     pattern: string,
     *     parameters: list<string>,
     *     handler: mixed,
     *     middleware: list<mixed>
     * }>
     */
    private array $routes = [];

    /** @var list<mixed> */
    private array $middleware = [];

    /** @var list<mixed> */
    private array $groupMiddleware = [];

    private string $groupPrefix = '';

    public function __construct(private readonly Container $container)
    {
    }

    /** @param list<mixed> $middleware */
    public function add(
        string|array $methods,
        string $path,
        mixed $handler,
        array $middleware = []
    ): self {
        $methods = is_array($methods) ? $methods : [$methods];
        $methods = array_values(array_unique(array_map(
            static fn (string $method): string => strtoupper(trim($method)),
            $methods
        )));

        if ($methods === [] || array_diff($methods, [
            'GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS',
        ]) !== []) {
            throw new InvalidArgumentException('One or more HTTP methods are invalid.');
        }

        $this->assertHandler($handler);

        $routePath = $this->normalizePath($this->groupPrefix . '/' . ltrim($path, '/'));
        [$pattern, $parameters] = $this->compilePattern($routePath);

        $this->routes[] = [
            'methods' => $methods,
            'path' => $routePath,
            'pattern' => $pattern,
            'parameters' => $parameters,
            'handler' => $handler,
            'middleware' => [...$this->groupMiddleware, ...$middleware],
        ];

        return $this;
    }

    /** @param list<mixed> $middleware */
    public function get(string $path, mixed $handler, array $middleware = []): self
    {
        return $this->add('GET', $path, $handler, $middleware);
    }

    /** @param list<mixed> $middleware */
    public function post(string $path, mixed $handler, array $middleware = []): self
    {
        return $this->add('POST', $path, $handler, $middleware);
    }

    /** @param list<mixed> $middleware */
    public function put(string $path, mixed $handler, array $middleware = []): self
    {
        return $this->add('PUT', $path, $handler, $middleware);
    }

    /** @param list<mixed> $middleware */
    public function patch(string $path, mixed $handler, array $middleware = []): self
    {
        return $this->add('PATCH', $path, $handler, $middleware);
    }

    /** @param list<mixed> $middleware */
    public function delete(string $path, mixed $handler, array $middleware = []): self
    {
        return $this->add('DELETE', $path, $handler, $middleware);
    }

    /** @param list<mixed> $middleware */
    public function options(string $path, mixed $handler, array $middleware = []): self
    {
        return $this->add('OPTIONS', $path, $handler, $middleware);
    }

    /** @param list<mixed> $middleware */
    public function match(
        array $methods,
        string $path,
        mixed $handler,
        array $middleware = []
    ): self {
        return $this->add($methods, $path, $handler, $middleware);
    }

    public function use(mixed $middleware): self
    {
        $this->assertMiddleware($middleware);
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * @param callable(self): void $registrar
     * @param list<mixed> $middleware
     */
    public function group(string $prefix, callable $registrar, array $middleware = []): void
    {
        foreach ($middleware as $item) {
            $this->assertMiddleware($item);
        }

        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;
        $this->groupPrefix = $this->normalizePath($previousPrefix . '/' . trim($prefix, '/'));
        $this->groupMiddleware = [...$previousMiddleware, ...$middleware];

        try {
            $registrar($this);
        } finally {
            $this->groupPrefix = $previousPrefix;
            $this->groupMiddleware = $previousMiddleware;
        }
    }

    public function dispatch(Request $request): Response
    {
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $request->path(), $matches) !== 1) {
                continue;
            }

            $allowedMethods = [...$allowedMethods, ...$route['methods']];
            $methodMatches = in_array($request->method(), $route['methods'], true)
                || ($request->method() === 'HEAD' && in_array('GET', $route['methods'], true));

            if (!$methodMatches) {
                continue;
            }

            $routeParameters = [];

            foreach ($route['parameters'] as $parameter) {
                $routeParameters[$parameter] = rawurldecode((string) ($matches[$parameter] ?? ''));
            }

            $routedRequest = $request->withRouteParameters($routeParameters);
            $this->container->instance(Request::class, $routedRequest);

            return $this->runRoute($route, $routedRequest, $routeParameters);
        }

        $allowedMethods = array_values(array_unique($allowedMethods));

        if ($allowedMethods !== []) {
            if (in_array('GET', $allowedMethods, true) && !in_array('HEAD', $allowedMethods, true)) {
                $allowedMethods[] = 'HEAD';
            }

            if (!in_array('OPTIONS', $allowedMethods, true)) {
                $allowedMethods[] = 'OPTIONS';
            }

            sort($allowedMethods);
            $allow = implode(', ', $allowedMethods);

            if ($request->method() === 'OPTIONS') {
                return Response::noContent()->withHeader('Allow', $allow);
            }

            return Response::json(
                ['error' => 'Method not allowed.', 'allowed_methods' => $allowedMethods],
                405,
                ['Allow' => $allow]
            );
        }

        return Response::json(['error' => 'Page not found.'], 404);
    }

    /**
     * @param array{
     *     methods: list<string>,
     *     path: string,
     *     pattern: string,
     *     parameters: list<string>,
     *     handler: mixed,
     *     middleware: list<mixed>
     * } $route
     * @param array<string, string> $routeParameters
     */
    private function runRoute(array $route, Request $request, array $routeParameters): Response
    {
        $destination = function (Request $currentRequest) use ($route, $routeParameters): Response {
            $this->container->instance(Request::class, $currentRequest);
            $result = $this->container->call(
                $route['handler'],
                [...$routeParameters, 'request' => $currentRequest]
            );

            return $this->normalizeResponse($result);
        };

        $pipeline = array_reduce(
            array_reverse([...$this->middleware, ...$route['middleware']]),
            fn (callable $next, mixed $middleware): callable =>
                fn (Request $currentRequest): Response =>
                    $this->runMiddleware($middleware, $currentRequest, $next),
            $destination
        );

        return $pipeline($request);
    }

    private function runMiddleware(
        mixed $middleware,
        Request $request,
        callable $next
    ): Response {
        if (is_string($middleware) && class_exists($middleware)) {
            $middleware = $this->container->get($middleware);
        }

        if (is_object($middleware) && method_exists($middleware, 'handle')) {
            $result = $this->container->call(
                [$middleware, 'handle'],
                ['request' => $request, 'next' => $next]
            );
        } elseif (is_callable($middleware)) {
            $result = $this->container->call(
                $middleware,
                ['request' => $request, 'next' => $next]
            );
        } else {
            throw new RuntimeException('Middleware must be callable or expose a handle method.');
        }

        return $this->normalizeResponse($result);
    }

    private function normalizeResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if ($result === null) {
            return Response::noContent();
        }

        if (is_array($result) || $result instanceof JsonSerializable) {
            return Response::json($result);
        }

        if (is_string($result)) {
            return Response::html($result);
        }

        if (is_scalar($result)) {
            return Response::text((string) $result);
        }

        throw new RuntimeException('Route handlers must return a response-compatible value.');
    }

    /** @return array{0: string, 1: list<string>} */
    private function compilePattern(string $path): array
    {
        $parameters = [];
        $pattern = '';
        $offset = 0;
        preg_match_all(
            '/\{([A-Za-z_][A-Za-z0-9_]*)(?::([^{}]+))?\}/',
            $path,
            $matches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        );

        foreach ($matches as $match) {
            $placeholder = $match[0][0];
            $position = $match[0][1];
            $name = $match[1][0];
            $constraint = isset($match[2][0]) && $match[2][0] !== ''
                ? $match[2][0]
                : '[^/]+';

            if (in_array($name, $parameters, true)) {
                throw new InvalidArgumentException(sprintf('Route parameter "%s" is duplicated.', $name));
            }

            if (str_contains($constraint, '~') || @preg_match('~^(?:' . $constraint . ')$~u', '') === false) {
                throw new InvalidArgumentException(sprintf('Route constraint for "%s" is invalid.', $name));
            }

            $pattern .= preg_quote(substr($path, $offset, $position - $offset), '~');
            $pattern .= sprintf('(?P<%s>%s)', $name, $constraint);
            $parameters[] = $name;
            $offset = $position + strlen($placeholder);
        }

        $remaining = substr($path, $offset);

        if (str_contains($remaining, '{') || str_contains($remaining, '}')) {
            throw new InvalidArgumentException('The route contains an invalid parameter placeholder.');
        }

        $pattern .= preg_quote($remaining, '~');

        return ['~^' . $pattern . '$~uD', $parameters];
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function assertHandler(mixed $handler): void
    {
        $validControllerString = is_string($handler)
            && (class_exists($handler) || str_contains($handler, '@'));
        $validControllerArray = is_array($handler)
            && count($handler) === 2
            && (is_object($handler[0]) || is_string($handler[0]))
            && is_string($handler[1]);

        if (!is_callable($handler) && !$validControllerString && !$validControllerArray) {
            throw new InvalidArgumentException('The route handler is invalid.');
        }
    }

    private function assertMiddleware(mixed $middleware): void
    {
        if (
            !is_callable($middleware)
            && !is_string($middleware)
            && !(is_object($middleware) && method_exists($middleware, 'handle'))
        ) {
            throw new InvalidArgumentException('The middleware definition is invalid.');
        }
    }
}
