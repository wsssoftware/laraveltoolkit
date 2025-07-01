<?php

namespace Laraveltoolkit\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Laraveltoolkit\Enum\Phone;

readonly class PhoneRule implements ValidationRule
{
    /**
     * @var \Illuminate\Support\Collection<string, Phone>|null
     */
    public Collection $types;

    protected function __construct(
        array $type = [Phone::GENERIC]
    ) {
        $this->types = collect($type)
            ->ensure(Phone::class)
            ->unique()
            ->reduce(function (Collection $types, Phone $type) {
                $types->put($type->value, $type);

                return $types->count() > 1 ? $types->except(Phone::GENERIC->value) : $types;
            }, collect());
    }

    public static function make(Phone ...$phones): self
    {
        return new self($phones);
    }

    public static function all(): self
    {
        return new self([Phone::GENERIC]);
    }

    public static function landline(): self
    {
        return new self([Phone::LANDLINE]);
    }

    public static function localFare(): self
    {
        return new self([Phone::LOCAL_FARE]);
    }

    public static function mobile(): self
    {
        return new self([Phone::MOBILE]);
    }

    public static function nonRegional(): self
    {
        return new self([Phone::NON_REGIONAL]);
    }

    public static function publicServices(): self
    {
        return new self([Phone::PUBLIC_SERVICES]);
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->types->count() === 1) {
            $type = $this->types->first();
            if (! $type->validate($value)) {
                $fail("laraveltoolkit::validation.phone.$type->value.invalid")->translate();
            }

            return;
        }
        $results = collect();
        foreach ($this->types as $type) {
            $results->put($type->value, $type->validate($value));
        }
        if ($results->filter(fn (bool $r) => $r === true)->count() === 0) {
            $labels = [];
            foreach ($this->types as $type) {
                $labels[] = __("laraveltoolkit::validation.phone.$type->value.label");
            }
            $fail('laraveltoolkit::validation.phone.multiple')
                ->translate([
                    'available' => Arr::join($labels, ',', ' '.__('laraveltoolkit::validation.phone.and').' '),
                ]);
        }
    }
}
