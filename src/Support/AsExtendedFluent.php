<?php

namespace Laraveltoolkit\Support;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @property class-string<ExtendedFluent> $fqn
 */
class AsExtendedFluent implements Castable
{
    /**
     * Specify the Fluent for the cast.
     *
     * @param  class-string  $class
     */
    public static function of(string $class): string
    {
        return static::class.':'.$class;
    }

    public static function castUsing(array $arguments): CastsAttributes
    {

        return new class($arguments[0]) implements CastsAttributes {
            public function __construct(
                public readonly string $fqn
            ) {
                throw_if(!class_exists($this->fqn),
                    new \InvalidArgumentException("Class {$this->fqn} does not exist."));
                throw_if(
                    !is_subclass_of($this->fqn, ExtendedFluent::class),
                    new \InvalidArgumentException("Class {$this->fqn} does not extend ExtendedFluent.")
                );
            }

            public function get(Model $model, string $key, mixed $value, array $attributes)
            {
                if (json_validate($value)) {
                    return new $this->fqn(json_decode($value, true));
                }

                return $value;
            }

            public function set(Model $model, string $key, mixed $value, array $attributes)
            {
                if ($value instanceof ExtendedFluent) {
                    return $value->toStorageJson();
                }

                return $value;
            }
        };
    }
}
