<?php

namespace Laraveltoolkit\ACL;

use Exception;

/**
 * @property bool $value
 */
class Rule
{
    private ?bool $value = null;

    private bool $dirty = false;

    public function __construct(
        public readonly string $key,
        public readonly string $name,
        public readonly string $description,
        public readonly ?int $denyStatus,
    ) {
        throw_if(
            in_array($this->key, ['items', 'column', 'name', 'description']),
            Exception::class,
            "$this->key is a reserved name and cannot used on key."
        );
    }

    public function __get(string $name): bool
    {
        if ($name === 'value') {
            return $this->value;

        }
        throw new Exception("Property $name does not exist.");
    }

    public function __set(string $name, $value): void
    {
        if ($name === 'value' && $this->value !== $value) {
            $this->value = $value;
            $this->dirty = true;
        }
    }

    public function setValue(bool $value): void
    {
        $this->value = $value;
    }

    public function isDirty(): bool
    {
        return $this->dirty;
    }
}
