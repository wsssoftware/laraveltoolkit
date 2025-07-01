<?php

namespace Laraveltoolkit\Macros;

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
    }

    public function toValueLabel(): void
    {
        Collection::macro(
            'toValueLabelFromArray',
            function (string $labelKey = 'label', string $valueKey = 'value'): Collection {
                return $this->map(fn(mixed $item, mixed $key) => [
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
                return $this->map(fn(object $item) => [
                    ...collect($keysToPreserve)
                        ->mapWithKeys(fn(string $key, mixed $index) => [
                            is_string($index) && !is_numeric($index) ? $index : $key => $item->{$key},
                        ])
                        ->toArray(),
                    $valueKey => $item->{$value},
                    $labelKey => $item->{$label},
                ]);
            }
        );
    }
}
