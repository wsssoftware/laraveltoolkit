<?php

namespace Laraveltoolkit\Macros;

use Closure;
use Collator;
use Illuminate\Support\Collection;

class CollectionMacro
{
    public function __invoke(): void
    {
        $this->toValueLabel();
        $this->localeSort();
    }

    public function localeSort(): void
    {
        Collection::macro(
            'localeSortBy',
            function (array|string|callable $callback, bool $descending = false) {
                return $this->sortBy($callback, SORT_LOCALE_STRING, $descending);
            }
        );
        Collection::macro(
            'localeSortByDesc',
            function (array|string|callable $callback) {
                return $this->sortBy($callback, SORT_LOCALE_STRING, true);
            }
        );
        Collection::macro(
            'localeSort',
            function () {
                return $this->sort(SORT_LOCALE_STRING);
            }
        );
        Collection::macro(
            'localeSortDesc',
            function () {
                return $this->sortDesc(SORT_LOCALE_STRING);
            }
        );
        Collection::macro('collatorSort',
            function (?int $options = SORT_REGULAR, int $direction = SORT_ASC, ?string $locale = null) {
                $collator = new Collator($locale ?? config('app.locale'));

                $collator = match ($options) {
                    SORT_STRING,
                    SORT_LOCALE_STRING => tap($collator, fn (Collator $c) => $c->setStrength(Collator::TERTIARY)),
                    SORT_STRING | SORT_FLAG_CASE => tap($collator,
                        fn (Collator $c) => $c->setStrength(Collator::SECONDARY)),
                    SORT_NATURAL => tap($collator, function (Collator $c) {
                        $c->setAttribute(Collator::NUMERIC_COLLATION, Collator::ON);
                        $c->setStrength(Collator::TERTIARY);
                    }),
                    SORT_NATURAL | SORT_FLAG_CASE => tap($collator, function (Collator $c) {
                        $c->setAttribute(Collator::NUMERIC_COLLATION, Collator::ON);
                        $c->setStrength(Collator::SECONDARY);
                    }),
                    default => $collator
                };
                $m = match ($direction) {
                    SORT_ASC => 1,
                    SORT_DESC => -1,
                    default => null,
                };
                throw_if($m === null, 'Invalid direction for collatorSort method');

                $fn = match ($options) {
                    SORT_REGULAR => fn ($a, $b) => is_string($a) && is_string($b)
                        ? $m * $collator->compare($a, $b)
                        : $m * ($a <=> $b),
                    SORT_NUMERIC => fn ($a, $b) => $m * ((float) $a <=> (float) $b),
                    SORT_STRING,
                    SORT_STRING | SORT_FLAG_CASE,
                    SORT_NATURAL,
                    SORT_NATURAL | SORT_FLAG_CASE,
                    SORT_LOCALE_STRING => fn ($a, $b) => $m * $collator->compare((string) $a, (string) $b),
                    default => null
                };
                throw_if(! $fn instanceof Closure, 'Invalid options for collatorSort method');

                return $this->sort($fn);
            });
    }

    public function toValueLabel(): void
    {
        Collection::macro(
            'toValueLabelFromArray',
            function (string $labelKey = 'label', string $valueKey = 'value'): Collection {
                return $this->map(fn (mixed $item, mixed $key) => [
                    $valueKey => $key,
                    $labelKey => $item,
                ])->values();
            }
        );
        Collection::macro(
            'toValueLabelFromObject',
            function (
                string $label,
                string $value,
                string $labelKey = 'label',
                string $valueKey = 'value',
                array $keysToPreserve = [],
            ): Collection {
                return $this->map(fn (object $item) => [
                    ...collect($keysToPreserve)
                        ->mapWithKeys(fn (string $key, mixed $index) => [
                            is_string($index) && ! is_numeric($index) ? $index : $key => $item->{$key},
                        ])
                        ->toArray(),
                    $valueKey => $item->{$value},
                    $labelKey => $item->{$label},
                ]);
            }
        );
    }
}
