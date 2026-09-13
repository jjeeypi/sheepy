<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use RuntimeException;

final class Container
{
    /** @var array<string, array{concrete: callable|string, shared: bool}> */
    private array $bindings = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<string, true> */
    private array $resolving = [];

    public function __construct()
    {
        $this->instances[self::class] = $this;
    }

    public function bind(string $id, callable|string|null $concrete = null, bool $shared = false): void
    {
        $this->assertIdentifier($id);

        $this->bindings[$id] = [
            'concrete' => $concrete ?? $id,
            'shared' => $shared,
        ];

        unset($this->instances[$id]);
    }

    public function singleton(string $id, callable|string|null $concrete = null): void
    {
        $this->bind($id, $concrete, true);
    }

    public function instance(string $id, mixed $instance): void
    {
        $this->assertIdentifier($id);
        $this->instances[$id] = $instance;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->instances)
            || array_key_exists($id, $this->bindings)
            || class_exists($id);
    }

    public function get(string $id): mixed
    {
        $this->assertIdentifier($id);

        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (isset($this->resolving[$id])) {
            throw new RuntimeException(sprintf('Circular dependency detected while resolving "%s".', $id));
        }

        $binding = $this->bindings[$id] ?? null;
        $concrete = $binding['concrete'] ?? $id;

        if ($binding === null && !class_exists($concrete)) {
            throw new RuntimeException(sprintf('No container binding exists for "%s".', $id));
        }

        $this->resolving[$id] = true;

        try {
            $resolved = is_string($concrete)
                ? $this->build($concrete)
                : $this->call($concrete);
        } finally {
            unset($this->resolving[$id]);
        }

        if (($binding['shared'] ?? false) === true) {
            $this->instances[$id] = $resolved;
        }

        return $resolved;
    }

    public function make(string $id): mixed
    {
        return $this->get($id);
    }

    /**
     * @param callable|array{0: object|class-string, 1: string}|class-string|string $callback
     * @param array<string, mixed> $parameters
     */
    public function call(callable|array|string $callback, array $parameters = []): mixed
    {
        [$callable, $reflection, $target] = $this->reflectCallable($callback);
        $arguments = $this->resolveParameters($reflection, $parameters);

        if ($reflection instanceof ReflectionMethod) {
            return $reflection->invokeArgs($target, $arguments);
        }

        return $reflection->invokeArgs($arguments);
    }

    private function build(string $class): object
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $exception) {
            throw new RuntimeException(sprintf('Class "%s" could not be reflected.', $class), 0, $exception);
        }

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException(sprintf('Class "%s" is not instantiable.', $class));
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        return $reflection->newInstanceArgs($this->resolveParameters($constructor));
    }

    /**
     * @param callable|array{0: object|class-string, 1: string}|class-string|string $callback
     * @return array{0: callable, 1: ReflectionFunctionAbstract, 2: object|null}
     */
    private function reflectCallable(callable|array|string $callback): array
    {
        if (is_string($callback) && str_contains($callback, '@')) {
            [$class, $method] = explode('@', $callback, 2);
            $callback = [$class, $method];
        }

        if (is_array($callback)) {
            if (count($callback) !== 2 || !is_string($callback[1])) {
                throw new InvalidArgumentException('Array callables must contain a target and method name.');
            }

            $target = is_string($callback[0]) ? $this->get($callback[0]) : $callback[0];
            $reflection = new ReflectionMethod($target, $callback[1]);

            if (!$reflection->isPublic()) {
                throw new RuntimeException('Container call targets must be public.');
            }

            return [[$target, $callback[1]], $reflection, $target];
        }

        if (is_string($callback) && class_exists($callback)) {
            $target = $this->get($callback);
            $reflection = new ReflectionMethod($target, '__invoke');

            return [[$target, '__invoke'], $reflection, $target];
        }

        if (is_object($callback) && !$callback instanceof Closure) {
            $reflection = new ReflectionMethod($callback, '__invoke');

            return [[$callback, '__invoke'], $reflection, $callback];
        }

        $reflection = new ReflectionFunction($callback);

        return [$callback, $reflection, null];
    }

    /**
     * @param array<string, mixed> $provided
     * @return list<mixed>
     */
    private function resolveParameters(
        ReflectionFunctionAbstract $function,
        array $provided = []
    ): array {
        $arguments = [];

        foreach ($function->getParameters() as $parameter) {
            if ($parameter->isVariadic()) {
                continue;
            }

            if (array_key_exists($parameter->getName(), $provided)) {
                $arguments[] = $this->coerceProvidedValue(
                    $parameter,
                    $provided[$parameter->getName()]
                );
                continue;
            }

            $dependency = $this->dependencyClass($parameter);

            if ($dependency !== null) {
                $arguments[] = $this->get($dependency);
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }

            if ($parameter->allowsNull()) {
                $arguments[] = null;
                continue;
            }

            throw new RuntimeException(sprintf(
                'Parameter "$%s" for %s cannot be resolved.',
                $parameter->getName(),
                $function->getName()
            ));
        }

        return $arguments;
    }

    private function dependencyClass(ReflectionParameter $parameter): ?string
    {
        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            return $type->getName();
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $unionType) {
                if (!$unionType->isBuiltin()) {
                    return $unionType->getName();
                }
            }
        }

        return null;
    }

    private function coerceProvidedValue(ReflectionParameter $parameter, mixed $value): mixed
    {
        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType || !$type->isBuiltin() || $value === null) {
            return $value;
        }

        return match ($type->getName()) {
            'string' => is_scalar($value) ? (string) $value : $value,
            'int' => is_int($value) || (is_string($value) && preg_match('/^-?\d+$/', $value) === 1)
                ? (int) $value
                : $value,
            'float' => is_numeric($value) ? (float) $value : $value,
            'bool' => is_bool($value) ? $value : filter_var($value, FILTER_VALIDATE_BOOL),
            default => $value,
        };
    }

    private function assertIdentifier(string $id): void
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException('Container identifiers cannot be empty.');
        }
    }
}
