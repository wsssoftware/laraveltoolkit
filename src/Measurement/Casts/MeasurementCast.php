<?php

namespace Laraveltoolkit\Measurement\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JsonException;
use Laraveltoolkit\Measurement\Models\Number;
use UnexpectedValueException;

/**
 * @implements CastsAttributes<Number|null, string|null>
 */
final readonly class MeasurementCast implements CastsAttributes
{
    /**
     * @param  class-string<Number>  $measurementClass
     */
    public function __construct(private string $measurementClass)
    {
        if (! is_subclass_of($this->measurementClass, Number::class)) {
            throw new InvalidArgumentException(
                sprintf('Class [%s] must extend [%s].', $this->measurementClass, Number::class),
            );
        }
    }

    /**
     * @param  class-string<Number>  $measurementClass
     */
    public static function of(string $measurementClass): string
    {
        return MeasurementCast::class.':'.$measurementClass;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Number
    {
        if ($value === null) {
            return null;
        }

        $measurementClass = $this->measurementClass;

        if ($value instanceof $measurementClass) {
            return $value;
        }

        $payload = $this->decode($value, $key);

        if (! isset($payload['reference_value'], $payload['unit'])
            || (! is_int($payload['reference_value']) && ! is_float($payload['reference_value']) && ! is_string($payload['reference_value']))
            || ! is_string($payload['unit'])) {
            throw new UnexpectedValueException(
                "Measurement attribute [{$key}] must contain a numeric [reference_value] and a string [unit].",
            );
        }

        if (is_string($payload['reference_value'])
            && preg_match('/^-?\d+(?:\.\d+)?$/', $payload['reference_value']) !== 1) {
            throw new UnexpectedValueException(
                "Measurement attribute [{$key}] must contain a valid decimal [reference_value].",
            );
        }

        return $measurementClass::fromReferenceValue(
            $payload['reference_value'],
            $measurementClass::resolveUnit($payload['unit']),
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        $measurementClass = $this->measurementClass;

        if (! $value instanceof $measurementClass) {
            throw new InvalidArgumentException(
                "Measurement attribute [{$key}] must be an instance of [{$measurementClass}].",
            );
        }

        return json_encode([
            'reference_value' => $value->referenceValueString(),
            'unit' => $value->unit()->value(),
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(mixed $value, string $key): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value)) {
            throw new UnexpectedValueException(
                "Measurement attribute [{$key}] must be a JSON string or an array.",
            );
        }

        try {
            $payload = json_decode($value, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException(
                "Measurement attribute [{$key}] contains invalid JSON.",
                previous: $exception,
            );
        }

        if (! is_array($payload)) {
            throw new UnexpectedValueException(
                "Measurement attribute [{$key}] must contain a JSON object.",
            );
        }

        return $payload;
    }
}
