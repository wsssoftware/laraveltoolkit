<?php

namespace Laraveltoolkit\ACL;

use Exception;
use Illuminate\Support\Collection;

/**
 * @property Collection<string, Rule> $rules
 */
readonly class Policy
{
    public function __construct(
        public Collection $rules,
        public string $column,
        public string $name,
        public string $description,
    ) {
        //
    }

    public function __get(string $name): Rule
    {
        if ($this->rules->offsetExists($name)) {
            return $this->rules->offsetGet($name);
        }
        throw new Exception("Property {$name} does not exist.");
    }
}
