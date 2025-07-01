<?php

namespace Laraveltoolkit\Support;

use ArrayAccess;
use BackedEnum;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Fluent;
use ReflectionClass;
use ReflectionMethod;

abstract class ExtendedFluent extends Fluent
{
    private function getAttribute($offset): ?Attribute
    {
        $offset = str($offset)->camel()->toString();
        $reflection = new ReflectionClass($this);
        $method = $reflection->hasMethod($offset) ? $reflection->getMethod($offset) : null;
        if (
            $method instanceof ReflectionMethod &&
            $method->isProtected() &&
            $method->getReturnType()?->getName() === Attribute::class &&
            $method->getNumberOfParameters() === 0
        ) {
            return rescue(fn() => $method->invoke($this), null, false);
        }

        return null;
    }

    public function offsetSet($offset, $value): void
    {
        if ($attribute = $this->getAttribute($offset)) {
            $callable = $attribute->set;
            $value = !empty($callable) ? $callable($value) : $value;
        }
        parent::offsetSet($offset, $value);
    }

    public function value($key, $default = null): mixed
    {
        $value = parent::value($key, $default);
        if ($attribute = $this->getAttribute($key)) {
            $callable = $attribute->get;
            $value = !empty($callable) ? $callable($value) : $value;
        }

        return $value;
    }

    public function toStorageArray(): array
    {
        $values = [];
        foreach ($this->getAttributes() as $key => $value) {
            $values[$key] = $this->normalizeStorage($this->{$key});
        }

        return $values;
    }

    protected function normalizeStorage(mixed $value): mixed
    {
        return match (true) {
            $value instanceof ExtendedFluent => $value->toStorageArray(),
            $value instanceof ArrayAccess || is_array($value) => $this->normalizeArrayAccessStorage($value),
            $value instanceof BackedEnum => $value->value,
            default => $value
        };
    }

    protected function normalizeArrayAccessStorage(ArrayAccess|array $arrayAccess): array
    {
        $values = [];
        foreach ($arrayAccess as $key => $item) {
            $values[$key] = $this->normalizeStorage($item);
        }

        return $values;
    }

    public function toStorageJson($options = 0): string
    {
        return json_encode($this->toStorageArray(), $options);
    }

    public function toArray(): array
    {
        $values = [];
        foreach ($this->getAttributes() as $key => $value) {
            $values[$key] = $this->normalizeArray($this->{$key});
        }

        return $values;
    }

    protected function normalizeArray(mixed $value): mixed
    {
        return match (true) {
            $value instanceof Arrayable => $value->toArray(),
            $value instanceof ArrayAccess || is_array($value) => $this->normalizeArrayAccessArray($value),
            default => $value
        };
    }

    protected function normalizeArrayAccessArray(ArrayAccess|array $arrayAccess): array
    {
        $values = [];
        foreach ($arrayAccess as $key => $item) {
            $values[$key] = $this->normalizeArray($item);
        }

        return $values;
    }
}
